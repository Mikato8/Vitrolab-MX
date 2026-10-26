<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Str;

final class ReportRepository
{
    /**
     * @param array<string, mixed> $filters
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function search(array $filters, int $page = 1, int $perPage = 9, bool $admin = false): array
    {
        $where = [];
        $params = [];

        if ($admin) {
            $where[] = 'r.deleted_at is null';
            $status = (string) ($filters['status'] ?? '');
            if (in_array($status, ['borrador', 'en_revision', 'cambios_solicitados', 'publicado', 'rechazado', 'archivado'], true)) {
                $params[] = $status;
                $where[] = 'r.status = $' . count($params);
            }
        } else {
            $where[] = "r.status = 'publicado' and r.deleted_at is null";
        }

        $q = trim((string) ($filters['q'] ?? ''));
        $rankSql = '0::real';
        if ($q !== '') {
            $params[] = $q;
            $fts = '$' . count($params);
            $params[] = '%' . $q . '%';
            $like = '$' . count($params);
            $where[] = "(r.search_vector @@ websearch_to_tsquery('spanish', {$fts}) or r.title ilike {$like} or r.summary ilike {$like} or r.species_text ilike {$like})";
            $params[] = $q;
            $rankSql = "ts_rank_cd(r.search_vector, websearch_to_tsquery('spanish', $" . count($params) . '))';
        }

        $type = (string) ($filters['type'] ?? '');
        if (in_array($type, ['ensayo', 'protocolo', 'diagnostico', 'seguimiento', 'validacion', 'datos'], true)) {
            $params[] = $type;
            $where[] = 'r.report_type = $' . count($params);
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
            $where[] = 'r.state_code = $' . count($params);
        }

        $year = (int) ($filters['year'] ?? 0);
        if ($year > 2000 && $year < 2100) {
            $params[] = $year;
            $where[] = 'extract(year from coalesce(r.period_end, r.published_at::date)) = $' . count($params);
        }

        $authorId = (int) ($filters['author_id'] ?? 0);
        if ($authorId > 0) {
            $params[] = $authorId;
            $where[] = 'r.author_id = $' . count($params);
        }

        $institutionId = (int) ($filters['institution_id'] ?? 0);
        if ($institutionId > 0) {
            $params[] = $institutionId;
            $where[] = 'u.institution_id = $' . count($params);
        }

        $caseId = (int) ($filters['case_id'] ?? 0);
        if ($caseId > 0) {
            $params[] = $caseId;
            $where[] = 'r.case_id = $' . count($params);
        }

        if (!empty($filters['featured'])) {
            $where[] = 'r.featured = true';
        }

        $sort = (string) ($filters['sort'] ?? 'recientes');
        $orderSql = match ($sort) {
            'relevancia' => $q !== '' ? $rankSql . ' desc, r.published_at desc nulls last' : 'r.published_at desc nulls last',
            'vistos' => 'r.views desc, r.published_at desc nulls last',
            'antiguos' => 'r.published_at asc nulls last',
            default => 'r.published_at desc nulls last, r.created_at desc',
        };

        $offset = max(0, ($page - 1) * $perPage);
        $params[] = $perPage;
        $limit = '$' . count($params);
        $params[] = $offset;
        $offsetParam = '$' . count($params);

        $items = Database::select(
            "select r.id, r.slug, r.folio, r.title, r.summary, r.report_type, r.period_start, r.period_end,
                    r.replicates, r.views, r.published_at, r.created_at, r.featured, r.is_demo, r.status,
                    r.objective, r.results, r.species_text, r.case_id,
                    cat.name as category_name, cat.code as category_code, cat.emoji as category_emoji,
                    sp.scientific_name, sp.common_name, sp.slug as species_slug,
                    st.name as state_name, u.name as author_name, u.avatar_url as author_avatar,
                    u.role as author_role, i.acronym as institution_acronym, i.name as institution_name,
                    c.title as linked_case_title, c.slug as linked_case_slug,
                    (select count(*) from report_files rf where rf.report_id = r.id) as files_count,
                    (select count(*) from report_treatments rt where rt.report_id = r.id) as treatments_count,
                    (select count(*) from comments cc where cc.target_type = 'reporte' and cc.target_id = r.id and cc.status = 'visible') as comments_count,
                    count(*) over() as total_count,
                    {$rankSql} as rank
             from reports r
             left join categories cat on cat.id = r.category_id
             left join species sp on sp.id = r.species_id
             left join states st on st.code = r.state_code
             join users u on u.id = r.author_id
             left join institutions i on i.id = u.institution_id
             left join cases c on c.id = r.case_id
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
        $row = Database::first($this->detailSql() . " where r.slug = $1 and r.status = 'publicado' and r.deleted_at is null", [$slug]);
        return $row !== null ? $this->hydrate($row) : null;
    }

    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug): ?array
    {
        $row = Database::first($this->detailSql() . ' where r.slug = $1 and r.deleted_at is null', [$slug]);
        return $row !== null ? $this->hydrate($row) : null;
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $row = Database::first($this->detailSql() . ' where r.id = $1 and r.deleted_at is null', [$id]);
        return $row !== null ? $this->hydrate($row) : null;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $slug = $this->uniqueSlug((string) $data['title']);
        $folio = 'VIT-REP-' . date('Y') . '-' . str_pad((string) Database::value("select nextval('report_folio_seq')"), 4, '0', STR_PAD_LEFT);
        $row = Database::insert(
            'insert into reports (slug, folio, author_id, case_id, title, summary, report_type, category_id,
                                  species_id, species_text, state_code, period_start, period_end, objective, hypothesis,
                                  design, treatments_text, replicates, variables, results, analysis, conclusions,
                                  recommendations, limitations, references_text, license, status, featured, is_demo,
                                  published_at)
             values ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,
                     $25,$26,$27,$28,$29,$30)
             returning id',
            [
                $slug,
                $folio,
                (int) $data['author_id'],
                $data['case_id'] ?: null,
                $data['title'],
                $data['summary'] ?? '',
                $data['report_type'] ?? 'ensayo',
                $data['category_id'] ?: null,
                $data['species_id'] ?: null,
                $data['species_text'] ?? '',
                $data['state_code'] ?: null,
                $data['period_start'] ?: null,
                $data['period_end'] ?: null,
                $data['objective'] ?? '',
                $data['hypothesis'] ?? '',
                $data['design'] ?? '',
                $data['treatments_text'] ?? '',
                $data['replicates'] !== null && $data['replicates'] !== '' ? (int) $data['replicates'] : null,
                $data['variables'] ?? '',
                $data['results'] ?? '',
                $data['analysis'] ?? '',
                $data['conclusions'] ?? '',
                $data['recommendations'] ?? '',
                $data['limitations'] ?? '',
                $data['references_text'] ?? '',
                $data['license'] ?? 'CC BY-NC-SA 4.0',
                $data['status'] ?? 'borrador',
                !empty($data['featured']),
                !empty($data['is_demo']),
                ($data['status'] ?? '') === 'publicado' ? date('c') : null,
            ]
        );
        $id = (int) ($row['id'] ?? 0);
        $this->saveTreatments($id, $data['treatments'] ?? []);
        return $id;
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): void
    {
        Database::execute(
            'update reports set case_id = $1, title = $2, summary = $3, report_type = $4, category_id = $5,
                    species_id = $6, species_text = $7, state_code = $8, period_start = $9, period_end = $10,
                    objective = $11, hypothesis = $12, design = $13, treatments_text = $14, replicates = $15,
                    variables = $16, results = $17, analysis = $18, conclusions = $19, recommendations = $20,
                    limitations = $21, references_text = $22, license = $23
             where id = $24',
            [
                $data['case_id'] ?: null,
                $data['title'],
                $data['summary'] ?? '',
                $data['report_type'] ?? 'ensayo',
                $data['category_id'] ?: null,
                $data['species_id'] ?: null,
                $data['species_text'] ?? '',
                $data['state_code'] ?: null,
                $data['period_start'] ?: null,
                $data['period_end'] ?: null,
                $data['objective'] ?? '',
                $data['hypothesis'] ?? '',
                $data['design'] ?? '',
                $data['treatments_text'] ?? '',
                $data['replicates'] !== null && $data['replicates'] !== '' ? (int) $data['replicates'] : null,
                $data['variables'] ?? '',
                $data['results'] ?? '',
                $data['analysis'] ?? '',
                $data['conclusions'] ?? '',
                $data['recommendations'] ?? '',
                $data['limitations'] ?? '',
                $data['references_text'] ?? '',
                $data['license'] ?? 'CC BY-NC-SA 4.0',
                $id,
            ]
        );
        $this->saveTreatments($id, $data['treatments'] ?? []);
    }

    public function updateStatus(int $id, string $status, bool $featured = false): void
    {
        Database::execute(
            'update reports set status = $1, featured = $2,
                    published_at = case when $1 = \'publicado\' and published_at is null then now()
                                        when $1 <> \'publicado\' then null else published_at end
             where id = $3',
            [$status, $featured, $id]
        );
    }

    public function feature(int $id, bool $featured): void
    {
        Database::execute('update reports set featured = $1 where id = $2', [$featured, $id]);
    }

    /** @param array<int, array<string, mixed>> $treatments */
    public function saveTreatments(int $reportId, array $treatments): void
    {
        Database::execute('delete from report_treatments where report_id = $1', [$reportId]);
        $sort = 0;
        foreach ($treatments as $treatment) {
            $label = trim((string) ($treatment['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            Database::execute(
                'insert into report_treatments (report_id, label, n, response, unit, notes, sort_order)
                 values ($1, $2, $3, $4, $5, $6, $7)',
                [
                    $reportId,
                    mb_substr($label, 0, 160),
                    ($treatment['n'] ?? '') !== '' ? (int) $treatment['n'] : null,
                    ($treatment['response'] ?? '') !== '' ? (float) str_replace(',', '.', (string) $treatment['response']) : null,
                    mb_substr(trim((string) ($treatment['unit'] ?? '')), 0, 40),
                    mb_substr(trim((string) ($treatment['notes'] ?? '')), 0, 240),
                    $sort++,
                ]
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function treatments(int $reportId): array
    {
        return Database::select('select * from report_treatments where report_id = $1 order by sort_order, id', [$reportId]);
    }

    public function addFile(int $reportId, array $file): void
    {
        Database::execute(
            'insert into report_files (report_id, path, original_name, mime, size_bytes, kind)
             values ($1, $2, $3, $4, $5, $6)',
            [$reportId, $file['path'], $file['original_name'], $file['mime'], $file['size'], $file['kind'] ?? 'anexo']
        );
    }

    public function deleteFile(int $fileId, int $reportId): ?string
    {
        $path = Database::value('select path from report_files where id = $1 and report_id = $2', [$fileId, $reportId]);
        Database::execute('delete from report_files where id = $1 and report_id = $2', [$fileId, $reportId]);
        return is_string($path) ? $path : null;
    }

    /** @return array<int, array<string, mixed>> */
    public function files(int $reportId): array
    {
        return Database::select('select * from report_files where report_id = $1 order by id', [$reportId]);
    }

    /** @return array<int, array<string, mixed>> */
    public function linkedReports(int $caseId): array
    {
        return Database::select(
            "select r.id, r.slug, r.title, r.report_type, r.published_at, r.summary, u.name as author_name
             from reports r join users u on u.id = r.author_id
             where r.case_id = $1 and r.status = 'publicado' and r.deleted_at is null
             order by r.published_at desc",
            [$caseId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function mine(int $authorId): array
    {
        return Database::select(
            "select r.*, cat.name as category_name, cat.emoji as category_emoji, st.name as state_name,
                    sp.scientific_name,
                    (select count(*) from report_files rf where rf.report_id = r.id) as files_count,
                    (select count(*) from comments cc where cc.target_type = 'reporte' and cc.target_id = r.id and cc.status = 'visible') as comments_count
             from reports r
             left join categories cat on cat.id = r.category_id
             left join species sp on sp.id = r.species_id
             left join states st on st.code = r.state_code
             where r.author_id = $1 and r.deleted_at is null
             order by r.updated_at desc",
            [$authorId]
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
             from reports where deleted_at is null"
        ) ?? [];
        return array_map(static fn ($value): int => (int) $value, $row);
    }

    /** @return array<int, array<string, mixed>> */
    public function featured(int $limit = 3): array
    {
        return $this->search(['featured' => true], 1, $limit)['items'];
    }

    /** @return array<int, array<string, mixed>> */
    public function latest(int $limit = 6): array
    {
        return $this->search([], 1, $limit)['items'];
    }

    /** @return array<int, array<string, mixed>> */
    public function forCaseForm(int $authorId, int $limit = 100): array
    {
        return Database::select(
            "select id, title, slug, status from cases
             where (author_id = $1 or status = 'publicado') and deleted_at is null
             order by (author_id = $1) desc, published_at desc nulls last limit $2",
            [$authorId, $limit]
        );
    }

    public function incrementViews(int $id): void
    {
        Database::execute('update reports set views = views + 1 where id = $1', [$id]);
    }

    public function uniqueSlug(string $title): string
    {
        $base = Str::slug($title, 70);
        $slug = $base;
        $i = 2;
        while ((bool) Database::value('select exists(select 1 from reports where slug = $1)', [$slug])) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    private function detailSql(): string
    {
        return "select r.*, cat.name as category_name, cat.code as category_code, cat.emoji as category_emoji,
                       sp.scientific_name, sp.common_name, sp.slug as species_slug, sp.family,
                       st.name as state_name,
                       u.name as author_name, u.email as author_email, u.bio as author_bio,
                       u.avatar_url as author_avatar_url, u.role as author_role, u.status as author_status,
                       u.career as author_career, u.semester as author_semester, u.orcid as author_orcid,
                       i.name as institution_name, i.acronym as institution_acronym, i.slug as institution_slug,
                       c.title as linked_case_title, c.slug as linked_case_slug, c.species_text as linked_case_species,
                       (select count(*) from comments cc where cc.target_type = 'reporte' and cc.target_id = r.id and cc.status = 'visible') as comments_count,
                       (select count(*) from reactions cr where cr.target_type = 'reporte' and cr.target_id = r.id) as reactions_count,
                       (select count(*) from bookmarks b where b.target_type = 'reporte' and b.target_id = r.id) as bookmarks_count,
                       (select count(*) from report_files rf where rf.report_id = r.id) as files_count,
                       (select count(*) from report_treatments rt where rt.report_id = r.id) as treatments_count
                from reports r
                left join categories cat on cat.id = r.category_id
                left join species sp on sp.id = r.species_id
                left join states st on st.code = r.state_code
                join users u on u.id = r.author_id
                left join institutions i on i.id = u.institution_id
                left join cases c on c.id = r.case_id";
    }

    /** @param array<string, mixed> $row
     *  @return array<string, mixed>
     */
    private function hydrate(array $row): array
    {
        $row['id'] = (int) $row['id'];
        $row['author_id'] = (int) $row['author_id'];
        $row['case_id'] = $row['case_id'] !== null ? (int) $row['case_id'] : null;
        $row['views'] = (int) $row['views'];
        $row['comments_count'] = (int) $row['comments_count'];
        $row['reactions_count'] = (int) $row['reactions_count'];
        $row['bookmarks_count'] = (int) ($row['bookmarks_count'] ?? 0);
        $row['files_count'] = (int) ($row['files_count'] ?? 0);
        $row['treatments_count'] = (int) ($row['treatments_count'] ?? 0);
        $row['replicates'] = $row['replicates'] !== null ? (int) $row['replicates'] : null;
        $row['featured'] = $row['featured'] === true || $row['featured'] === 't';
        $row['is_demo'] = $row['is_demo'] === true || $row['is_demo'] === 't';
        return $row;
    }

    /** @param array<string, mixed> $item
     *  @return array<string, mixed>
     */
    private function hydrateList(array $item): array
    {
        $item['id'] = (int) $item['id'];
        $item['case_id'] = $item['case_id'] !== null ? (int) $item['case_id'] : null;
        $item['views'] = (int) $item['views'];
        $item['comments_count'] = (int) $item['comments_count'];
        $item['files_count'] = (int) $item['files_count'];
        $item['treatments_count'] = (int) $item['treatments_count'];
        $item['replicates'] = $item['replicates'] !== null ? (int) $item['replicates'] : null;
        $item['featured'] = $item['featured'] === true || $item['featured'] === 't';
        $item['is_demo'] = $item['is_demo'] === true || $item['is_demo'] === 't';
        unset($item['total_count'], $item['rank']);
        return $item;
    }
}
