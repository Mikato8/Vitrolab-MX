<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class CatalogRepository
{
    /** @var array<string, array<int, array<string, mixed>>> */
    private static array $cache = [];

    /** @return array<int, array<string, mixed>> */
    public function states(): array
    {
        return self::$cache['states'] ??= Database::select('select code, name, region from states order by name');
    }

    /** @return array<int, array<string, mixed>> */
    public function categories(): array
    {
        return self::$cache['categories'] ??= Database::select(
            "select c.*,
                    (select count(*) from cases x where x.category_id = c.id and x.status = 'publicado' and x.deleted_at is null) as cases_count,
                    (select count(*) from reports r where r.category_id = c.id and r.status = 'publicado' and r.deleted_at is null) as reports_count
             from categories c order by c.sort_order, c.name"
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function speciesList(): array
    {
        return self::$cache['species'] ??= Database::select(
            "select s.*,
                    (select count(*) from cases x where x.species_id = s.id and x.status = 'publicado' and x.deleted_at is null) as cases_count,
                    (select count(*) from reports r where r.species_id = s.id and r.status = 'publicado' and r.deleted_at is null) as reports_count
             from species s order by s.scientific_name"
        );
    }

    /** @return array<string, mixed>|null */
    public function species(int $id): ?array
    {
        return Database::first('select * from species where id = $1', [$id]);
    }

    /** @return array<string, mixed>|null */
    public function speciesBySlug(string $slug): ?array
    {
        return Database::first('select * from species where slug = $1', [$slug]);
    }

    /** @return array<int, array<string, mixed>> */
    public function speciesByGroup(): array
    {
        $grouped = [];
        foreach ($this->speciesList() as $row) {
            $grouped[(string) $row['group_name']][] = $row;
        }
        ksort($grouped);
        return $grouped;
    }

    /** @return array<int, array<string, mixed>> */
    public function institutions(): array
    {
        $rows = self::$cache['institutions'] ??= Database::select(
            "select i.*, s.name as state_name,
                    (select count(*) from users x where x.institution_id = i.id and x.status = 'verificado' and x.deleted_at is null) as members_count,
                    (select count(*) from cases c join users u on u.id = c.author_id where u.institution_id = i.id and c.status = 'publicado' and c.deleted_at is null) as cases_count,
                    (select count(*) from reports r join users u on u.id = r.author_id where u.institution_id = i.id and r.status = 'publicado' and r.deleted_at is null) as reports_count
             from institutions i
             left join states s on s.code = i.state_code
             order by i.name"
        );

        foreach ($rows as &$row) {
            $row['email_domains_list'] = pg_array($row['email_domains']);
        }
        unset($row);
        return $rows;
    }

    /** @return array<string, mixed>|null */
    public function institution(int $id): ?array
    {
        return Database::first(
            'select i.*, s.name as state_name from institutions i
             left join states s on s.code = i.state_code where i.id = $1',
            [$id]
        );
    }

    /** @return array<string, mixed>|null */
    public function institutionBySlug(string $slug): ?array
    {
        return Database::first(
            'select i.*, s.name as state_name from institutions i
             left join states s on s.code = i.state_code where i.slug = $1',
            [$slug]
        );
    }

    /**
     * Busca la institución por dominio de correo. Devuelve la primera
     * coincidencia; los dominios se guardan sin "@".
     *
     * @return array<string, mixed>|null
     */
    public function institutionByEmailDomain(string $domain): ?array
    {
        $domain = mb_strtolower(trim($domain));
        if ($domain === '') {
            return null;
        }
        return Database::first(
            "select i.*, s.name as state_name from institutions i
             left join states s on s.code = i.state_code
             where exists (select 1 from unnest(i.email_domains) d where lower(d) = $1)
             limit 1",
            [$domain]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function institutionsByState(): array
    {
        $grouped = [];
        foreach ($this->institutions() as $row) {
            $grouped[(string) ($row['state_name'] ?? 'Sin estado')][] = $row;
        }
        ksort($grouped);
        return $grouped;
    }

    /** @return array<string, int> */
    public function stats(): array
    {
        $row = Database::first(
            "select
                (select count(*) from cases where status = 'publicado' and deleted_at is null) as cases,
                (select count(*) from reports where status = 'publicado' and deleted_at is null) as reports,
                (select count(*) from species) as species,
                (select count(*) from institutions) as institutions,
                (select count(*) from users where status = 'verificado' and deleted_at is null) as members,
                (select count(distinct state_code) from (
                    select state_code from cases where status = 'publicado' and state_code is not null
                    union select state_code from reports where status = 'publicado' and state_code is not null
                ) t) as states,
                (select count(distinct category_id) from cases where status = 'publicado' and category_id is not null) as categories,
                (select coalesce(sum(views), 0) from cases where status = 'publicado') as views"
        ) ?? [];

        return array_map(static fn ($value): int => (int) $value, $row);
    }

    /** @return array<int, int> */
    public function years(): array
    {
        $rows = Database::select(
            "select distinct year from cases where status = 'publicado' and year between 2000 and 2100 order by year desc"
        );
        return array_map(static fn (array $row): int => (int) $row['year'], $rows);
    }

    /** @return array<int, array{name: string, total: int}> */
    public function popularStates(int $limit = 8): array
    {
        return Database::select(
            "select s.name, count(*)::int as total
             from (
                select state_code from cases where status = 'publicado' and state_code is not null
                union all select state_code from reports where status = 'publicado' and state_code is not null
             ) x join states s on s.code = x.state_code
             group by s.name order by total desc, s.name limit $1",
            [$limit]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function topInstitutions(int $limit = 6): array
    {
        return Database::select(
            "select i.name, i.acronym, i.slug, s.name as state_name, count(x.id)::int as total
             from users x
             join institutions i on i.id = x.institution_id
             left join states s on s.code = i.state_code
             where x.status = 'verificado' and x.deleted_at is null
             group by i.id, i.name, i.acronym, i.slug, s.name
             order by total desc, i.name limit $1",
            [$limit]
        );
    }

    public static function flush(): void
    {
        self::$cache = [];
    }
}
