<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Str;

final class CaseRepository
{
    public const NUMERIC_FIELDS = [
        'contamination_pct', 'survival_pct', 'multiplication_rate',
        'rooting_pct', 'acclimatization_pct',
    ];

    /**
     * @param array<string, mixed> $filters
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function search(array $filters, int $page = 1, int $perPage = 9, bool $admin = false): array
    {
        $where = [];
        $params = [];

        if ($admin) {
            $where[] = 'c.deleted_at is null';
            $status = (string) ($filters['status'] ?? '');
            if (in_array($status, ['borrador', 'en_revision', 'cambios_solicitados', 'publicado', 'rechazado', 'archivado'], true)) {
                $params[] = $status;
                $where[] = 'c.status = $' . count($params);
            }
        } else {
            $where[] = "c.status = 'publicado' and c.deleted_at is null";
        }

        $q = trim((string) ($filters['q'] ?? ''));
        $rankSql = '0::real';
        if ($q !== '') {
            $params[] = $q;
            $fts = '$' . count($params);
            $params[] = '%' . $q . '%';
            $like = '$' . count($params);
            $where[] = "(c.search_vector @@ websearch_to_tsquery('spanish', {$fts}) or c.title ilike {$like} or c.summary ilike {$like} or c.species_text ilike {$like})";
            $params[] = $q;
            $rankSql = "ts_rank_cd(c.search_vector, websearch_to_tsquery('spanish', $" . count($params) . '))';
        }

        $category = (string) ($filters['category'] ?? '');
        if ($category !== '') {
            $params[] = $category;
            $where[] = 'cat.code = $' . count($params);
        }

        $species = (string) ($filters['species'] ?? '');
        if ($species !== '') {
            $params[] = $species;
            $where[] = 'sp.slug = $' . count($params);
        }

        $state = (string) ($filters['state'] ?? '');
        if ($state !== '') {
            $params[] = $state;
            $where[] = 'c.state_code = $' . count($params);
        }

        $year = (int) ($filters['year'] ?? 0);
        if ($year > 2000 && $year < 2100) {
            $params[] = $year;
            $where[] = 'c.year = $' . count($params);
        }

        $authorId = (int) ($filters['author_id'] ?? 0);
        if ($authorId > 0) {
            $params[] = $authorId;
            $where[] = 'c.author_id = $' . count($params);
        }

        $institutionId = (int) ($filters['institution_id'] ?? 0);
        if ($institutionId > 0) {
            $params[] = $institutionId;
            $where[] = 'u.institution_id = $' . count($params);
        }

        if (!empty($filters['featured'])) {
            $where[] = 'c.featured = true';
        }

        $sort = (string) ($filters['sort'] ?? 'recientes');
        $orderSql = match ($sort) {
            'relevancia' => $q !== '' ? $rankSql . ' desc, c.published_at desc nulls last' : 'c.published_at desc nulls last',
            'vistos' => 'c.views desc, c.published_at desc nulls last',
            'antiguos' => 'c.published_at asc nulls last',
            'guardados' => "(select count(*) from bookmarks b where b.target_type = 'caso' and b.target_id = c.id) desc",
            default => 'c.published_at desc nulls last, c.created_at desc',
        };

        $offset = max(0, ($page - 1) * $perPage);
        $params[] = $perPage;
        $limit = '$' . count($params);
        $params[] = $offset;
        $offsetParam = '$' . count($params);

        $items = Database::select(
            "select c.id, c.slug, c.folio, c.title, c.summary, c.year, c.species_text,
                    c.contamination_pct, c.survival_pct, c.multiplication_rate, c.rooting_pct,
                    c.acclimatization_pct, c.duration_weeks,
                    c.views, c.published_at, c.created_at, c.featured, c.is_demo, c.status,
                    c.objective, c.results,
                    cat.name as category_name, cat.code as category_code, cat.emoji as category_emoji,
                    sp.scientific_name, sp.common_name, sp.slug as species_slug, sp.family,
                    st.name as state_name, u.name as author_name, u.avatar_url as author_avatar,
                    u.role as author_role, i.acronym as institution_acronym, i.name as institution_name,
                    (select ci.path from case_images ci where ci.case_id = c.id order by ci.sort_order, ci.id limit 1) as cover_image,
                    (select count(*) from comments cc where cc.target_type = 'caso' and cc.target_id = c.id and cc.status = 'visible') as comments_count,
                    (select count(*) from reactions cr where cr.target_type = 'caso' and cr.target_id = c.id) as reactions_count,
                    count(*) over() as total_count,
                    {$rankSql} as rank
             from cases c
             left join categories cat on cat.id = c.category_id
             left join species sp on sp.id = c.species_id
             left join states st on st.code = c.state_code
             join users u on u.id = c.author_id
             left join institutions i on i.id = u.institution_id
             where " . implode(' and ', $where) . "
             order by {$orderSql}
             limit {$limit} offset {$offsetParam}",
            $params
        );

        $total = $items === [] ? 0 : (int) $items[0]['total_count'];
        foreach ($items as &$item) {
            $item = $this->hydrateList($item);
        }
        unset($item);

        return ['items' => $items, 'total' => $total];
    }

    /** @return array<string, mixed>|null */
    public function findPublishedBySlug(string $slug): ?array
    {
        $row = Database::first($this->detailSql() . " where c.slug = $1 and c.status = 'publicado' and c.deleted_at is null", [$slug]);
        return $row !== null ? $this->hydrate($row) : null;
    }

    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        $row = Database::first($this->detailSql() . ' where c.slug = $1 and c.deleted_at is null', [$slug]);
        return $row !== null ? $this->hydrate($row) : null;
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $row = Database::first($this->detailSql() . ' where c.id = $1 and c.deleted_at is null', [$id]);
        return $row !== null ? $this->hydrate($row) : null;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $slug = $this->uniqueSlug((string) $data['title']);
        $folio = 'VIT-CAS-' . date('Y') . '-' . str_pad((string) Database::value("select nextval('case_folio_seq')"), 4, '0', STR_PAD_LEFT);
        $row = Database::insert(
            'insert into cases (slug, folio, author_id, title, summary, category_id, species_id, species_text,
                                state_code, municipality, year, objective, context, explant, disinfection, medium, pgr,
                                conditions, protocol, results, challenges, lessons, contamination_pct, survival_pct,
                                multiplication_rate, rooting_pct, acclimatization_pct, duration_weeks, references_text,
                                license, status, featured, is_demo, published_at)
             values ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,
                     $25,$26,$27,$28,$29,$30,$31,$32,$33,$34)
             returning id',
            [
                $slug,
                $folio,
                (int) $data['author_id'],
                $data['title'],
                $data['summary'] ?? '',
                $data['category_id'] ?: null,
                $data['species_id'] ?: null,
                $data['species_text'] ?? '',
                $data['state_code'] ?: null,
                $data['municipality'] ?? '',
                (int) ($data['year'] ?? date('Y')),
                $data['objective'] ?? '',
                $data['context'] ?? '',
                $data['explant'] ?? '',
                $data['disinfection'] ?? '',
                $data['medium'] ?? '',
                $data['pgr'] ?? '',
                $data['conditions'] ?? '',
                $data['protocol'] ?? '',
                $data['results'] ?? '',
                $data['challenges'] ?? '',
                $data['lessons'] ?? '',
                $this->nullableNumber($data['contamination_pct'] ?? null),
                $this->nullableNumber($data['survival_pct'] ?? null),
                $this->nullableNumber($data['multiplication_rate'] ?? null),
                $this->nullableNumber($data['rooting_pct'] ?? null),
                $this->nullableNumber($data['acclimatization_pct'] ?? null),
                $data['duration_weeks'] !== null && $data['duration_weeks'] !== '' ? (int) $data['duration_weeks'] : null,
                $data['references_text'] ?? '',
                $data['license'] ?? 'CC BY-NC-SA 4.0',
                $data['status'] ?? 'borrador',
                !empty($data['featured']),
                !empty($data['is_demo']),
                ($data['status'] ?? '') === 'publicado' ? date('c') : null,
            ]
        );
        return (int) ($row['id'] ?? 0);
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): void
    {
        Database::execute(
            'update cases set title = $1, summary = $2, category_id = $3, species_id = $4, species_text = $5,
                    state_code = $6, municipality = $7, year = $8, objective = $9, context = $10, explant = $11,
                    disinfection = $12, medium = $13, pgr = $14, conditions = $15, protocol = $16, results = $17,
                    challenges = $18, lessons = $19, contamination_pct = $20, survival_pct = $21,
                    multiplication_rate = $22, rooting_pct = $23, acclimatization_pct = $24, duration_weeks = $25,
                    references_text = $26, license = $27
             where id = $28',
            [
                $data['title'],
                $data['summary'] ?? '',
                $data['category_id'] ?: null,
                $data['species_id'] ?: null,
                $data['species_text'] ?? '',
                $data['state_code'] ?: null,
                $data['municipality'] ?? '',
                (int) ($data['year'] ?? date('Y')),
                $data['objective'] ?? '',
                $data['context'] ?? '',
                $data['explant'] ?? '',
                $data['disinfection'] ?? '',
                $data['medium'] ?? '',
                $data['pgr'] ?? '',
                $data['conditions'] ?? '',
                $data['protocol'] ?? '',
                $data['results'] ?? '',
                $data['challenges'] ?? '',
                $data['lessons'] ?? '',
                $this->nullableNumber($data['contamination_pct'] ?? null),
                $this->nullableNumber($data['survival_pct'] ?? null),
                $this->nullableNumber($data['multiplication_rate'] ?? null),
                $this->nullableNumber($data['rooting_pct'] ?? null),
                $this->nullableNumber($data['acclimatization_pct'] ?? null),
                $data['duration_weeks'] !== null && $data['duration_weeks'] !== '' ? (int) $data['duration_weeks'] : null,
                $data['references_text'] ?? '',
                $data['license'] ?? 'CC BY-NC-SA 4.0',
                $id,
            ]
        );
    }

    public function updateStatus(int $id, string $status, bool $featured = false): void
    {
        Database::execute(
            'update cases set status = $1, featured = $2,
                    published_at = case when $1 = \'publicado\' and published_at is null then now()
                                        when $1 <> \'publicado\' then null else published_at end
             where id = $3',
            [$status, $featured, $id]
        );
    }

    public function feature(int $id, bool $featured): void
    {
        Database::execute('update cases set featured = $1 where id = $2', [$featured, $id]);
    }

    public function addImage(int $caseId, array $image, string $caption = '', int $sort = 0): void
    {
        Database::execute(
            'insert into case_images (case_id, path, caption, alt_text, width, height, sort_order)
             values ($1, $2, $3, $4, $5, $6, $7)',
            [$caseId, $image['path'], $caption, $caption, $image['width'], $image['height'], $sort]
        );
    }

    public function deleteImage(int $imageId, int $caseId): ?string
    {
        $path = Database::value('select path from case_images where id = $1 and case_id = $2', [$imageId, $caseId]);
        Database::execute('delete from case_images where id = $1 and case_id = $2', [$imageId, $caseId]);
        return is_string($path) ? $path : null;
    }

    /** @return array<int, array<string, mixed>> */
    public function images(int $caseId): array
    {
        return Database::select('select * from case_images where case_id = $1 order by sort_order, id', [$caseId]);
    }

    /** @return array<int, array<string, mixed>> */
    public function similar(array $case, int $limit = 3): array
    {
        return Database::select(
            "select c.id, c.slug, c.title, c.summary, c.species_text, c.published_at,
                    cat.name as category_name, cat.emoji as category_emoji,
                    sp.scientific_name, st.name as state_name, u.name as author_name,
                    (select ci.path from case_images ci where ci.case_id = c.id order by ci.sort_order, ci.id limit 1) as cover_image
             from cases c
             left join categories cat on cat.id = c.category_id
             left join species sp on sp.id = c.species_id
             left join states st on st.code = c.state_code
             join users u on u.id = c.author_id
             where c.status = 'publicado' and c.deleted_at is null and c.id <> $1
               and (c.category_id = $2 or c.species_id = $3)
             order by (c.species_id = $3) desc, c.published_at desc
             limit $4",
            [(int) $case['id'], (int) $case['category_id'], (int) ($case['species_id'] ?? 0), $limit]
        );
    }

    /** @return array<string, int> */
    public function stats(): array
    {
        $row = Database::first(
            "select count(*) filter (where status = 'publicado') as published,
                    count(*) filter (where status = 'en_revision') as review,
                    count(*) filter (where status = 'borrador') as drafts,
                    coalesce(sum(views) filter (where status = 'publicado'), 0) as views
             from cases where deleted_at is null"
        ) ?? [];
        return array_map(static fn ($value): int => (int) $value, $row);
    }

    /** @return array<int, array<string, mixed>> */
    public function mine(int $authorId): array
    {
        return Database::select(
            "select c.*, cat.name as category_name, cat.emoji as category_emoji, st.name as state_name,
                    sp.scientific_name,
                    (select count(*) from comments cc where cc.target_type = 'caso' and cc.target_id = c.id and cc.status = 'visible') as comments_count,
                    (select count(*) from reports r where r.case_id = c.id and r.status = 'publicado') as reports_count
             from cases c
             left join categories cat on cat.id = c.category_id
             left join species sp on sp.id = c.species_id
             left join states st on st.code = c.state_code
             where c.author_id = $1 and c.deleted_at is null
             order by c.updated_at desc",
            [$authorId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function featured(int $limit = 3): array
    {
        $result = $this->search(['featured' => true, 'sort' => 'recientes'], 1, $limit);
        return $result['items'];
    }

    /** @return array<int, array<string, mixed>> */
    public function latest(int $limit = 6): array
    {
        $result = $this->search(['sort' => 'recientes'], 1, $limit);
        return $result['items'];
    }

    public function incrementViews(int $id): void
    {
        Database::execute('update cases set views = views + 1 where id = $1', [$id]);
    }

    public function uniqueSlug(string $title): string
    {
        $base = Str::slug($title, 70);
        $slug = $base;
        $i = 2;
        while ((bool) Database::value('select exists(select 1 from cases where slug = $1)', [$slug])) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    private function detailSql(): string
    {
        return "select c.*, cat.name as category_name, cat.code as category_code, cat.emoji as category_emoji,
                       cat.description as category_description,
                       sp.scientific_name, sp.common_name, sp.slug as species_slug, sp.family, sp.group_name,
                       st.name as state_name,
                       u.name as author_name, u.email as author_email, u.bio as author_bio,
                       u.avatar_url as author_avatar_url, u.role as author_role, u.status as author_status,
                       u.career as author_career, u.semester as author_semester, u.orcid as author_orcid,
                       i.name as institution_name, i.acronym as institution_acronym, i.slug as institution_slug,
                       coalesce((select json_agg(json_build_object(
                           'id', ci.id, 'path', ci.path, 'caption', ci.caption, 'alt_text', ci.alt_text,
                           'width', ci.width, 'height', ci.height
                       ) order by ci.sort_order, ci.id) from case_images ci where ci.case_id = c.id), '[]'::json) as images_json,
                       (select count(*) from comments cc where cc.target_type = 'caso' and cc.target_id = c.id and cc.status = 'visible') as comments_count,
                       (select count(*) from reactions cr where cr.target_type = 'caso' and cr.target_id = c.id) as reactions_count,
                       (select count(*) from bookmarks b where b.target_type = 'caso' and b.target_id = c.id) as bookmarks_count,
                       (select count(*) from reports r where r.case_id = c.id and r.status = 'publicado' and r.deleted_at is null) as reports_count
                from cases c
                left join categories cat on cat.id = c.category_id
                left join species sp on sp.id = c.species_id
                left join states st on st.code = c.state_code
                join users u on u.id = c.author_id
                left join institutions i on i.id = u.institution_id";
    }

    /** @param array<string, mixed> $row
     *  @return array<string, mixed>
     */
    private function hydrate(array $row): array
    {
        $row['id'] = (int) $row['id'];
        $row['author_id'] = (int) $row['author_id'];
        $row['views'] = (int) $row['views'];
        $row['comments_count'] = (int) $row['comments_count'];
        $row['reactions_count'] = (int) $row['reactions_count'];
        $row['bookmarks_count'] = (int) ($row['bookmarks_count'] ?? 0);
        $row['reports_count'] = (int) ($row['reports_count'] ?? 0);
        $row['featured'] = $row['featured'] === true || $row['featured'] === 't';
        $row['is_demo'] = $row['is_demo'] === true || $row['is_demo'] === 't';
        $row['images'] = json_decode((string) ($row['images_json'] ?? '[]'), true) ?: [];
        unset($row['images_json']);
        return $row;
    }

    /** @param array<string, mixed> $item
     *  @return array<string, mixed>
     */
    private function hydrateList(array $item): array
    {
        $item['id'] = (int) $item['id'];
        $item['views'] = (int) $item['views'];
        $item['comments_count'] = (int) $item['comments_count'];
        $item['reactions_count'] = (int) $item['reactions_count'];
        $item['featured'] = $item['featured'] === true || $item['featured'] === 't';
        $item['is_demo'] = $item['is_demo'] === true || $item['is_demo'] === 't';
        $item['duration_weeks'] = $item['duration_weeks'] !== null ? (int) $item['duration_weeks'] : null;
        unset($item['total_count'], $item['rank']);
        return $item;
    }

    private function nullableNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        return is_numeric($value) ? (float) $value : null;
    }
}
