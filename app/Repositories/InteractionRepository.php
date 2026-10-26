<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class InteractionRepository
{
    private const TABLES = ['caso' => 'cases', 'reporte' => 'reports'];

    /** @return array<int, array<string, mixed>> */
    public function comments(string $type, int $targetId, bool $includeHidden = false): array
    {
        $statusSql = $includeHidden ? '' : " and c.status = 'visible'";
        return Database::select(
            "select c.*, u.name as author_name, u.avatar_url as author_avatar, u.role as author_role,
                    u.status as author_status, i.acronym as institution_acronym
             from comments c
             join users u on u.id = c.user_id
             left join institutions i on i.id = u.institution_id
             where c.target_type = $1 and c.target_id = $2{$statusSql}
             order by c.created_at asc",
            [$type, $targetId]
        );
    }

    public function addComment(string $type, int $targetId, int $userId, string $body): int
    {
        $row = Database::insert(
            'insert into comments (target_type, target_id, user_id, body) values ($1, $2, $3, $4) returning id',
            [$type, $targetId, $userId, $body]
        );
        return (int) ($row['id'] ?? 0);
    }

    public function hideComment(int $id): void
    {
        Database::execute("update comments set status = 'oculto' where id = $1", [$id]);
    }

    public function showComment(int $id): void
    {
        Database::execute("update comments set status = 'visible' where id = $1", [$id]);
    }

    /** @return array<int, array<string, mixed>> */
    public function recentComments(int $limit = 12): array
    {
        return Database::select(
            "select c.*, u.name as author_name,
                    coalesce(ca.title, r.title) as target_title,
                    coalesce(ca.slug, r.slug) as target_slug
             from comments c
             join users u on u.id = c.user_id
             left join cases ca on c.target_type = 'caso' and ca.id = c.target_id
             left join reports r on c.target_type = 'reporte' and r.id = c.target_id
             where c.status = 'visible'
             order by c.created_at desc limit $1",
            [$limit]
        );
    }

    public function reactionsCount(string $type, int $targetId): int
    {
        return (int) Database::value(
            'select count(*) from reactions where target_type = $1 and target_id = $2',
            [$type, $targetId]
        );
    }

    public function hasReacted(string $type, int $targetId, int $userId): bool
    {
        return (bool) Database::value(
            'select exists(select 1 from reactions where target_type = $1 and target_id = $2 and user_id = $3)',
            [$type, $targetId, $userId]
        );
    }

    /** @return array{active: bool, count: int} */
    public function toggleReaction(string $type, int $targetId, int $userId): array
    {
        $exists = Database::value(
            'select id from reactions where target_type = $1 and target_id = $2 and user_id = $3',
            [$type, $targetId, $userId]
        );
        if ($exists !== null) {
            Database::execute(
                'delete from reactions where target_type = $1 and target_id = $2 and user_id = $3',
                [$type, $targetId, $userId]
            );
            $active = false;
        } else {
            Database::execute(
                'insert into reactions (target_type, target_id, user_id) values ($1, $2, $3) on conflict do nothing',
                [$type, $targetId, $userId]
            );
            $active = true;
        }
        return ['active' => $active, 'count' => $this->reactionsCount($type, $targetId)];
    }

    public function isBookmarked(string $type, int $targetId, int $userId): bool
    {
        return (bool) Database::value(
            'select exists(select 1 from bookmarks where target_type = $1 and target_id = $2 and user_id = $3)',
            [$type, $targetId, $userId]
        );
    }

    /** @return array{active: bool} */
    public function toggleBookmark(string $type, int $targetId, int $userId): array
    {
        $exists = $this->isBookmarked($type, $targetId, $userId);
        if ($exists) {
            Database::execute(
                'delete from bookmarks where target_type = $1 and target_id = $2 and user_id = $3',
                [$type, $targetId, $userId]
            );
        } else {
            Database::execute(
                'insert into bookmarks (target_type, target_id, user_id) values ($1, $2, $3) on conflict do nothing',
                [$type, $targetId, $userId]
            );
        }
        return ['active' => !$exists];
    }

    /** @return array<int, array<string, mixed>> */
    public function bookmarks(int $userId): array
    {
        return Database::select(
            "select b.target_type, b.target_id, b.created_at,
                    coalesce(ca.title, r.title) as title,
                    coalesce(ca.slug, r.slug) as slug,
                    coalesce(ca.summary, r.summary) as summary,
                    coalesce(cat.name, catr.name) as category_name,
                    coalesce(ca.species_text, r.species_text) as species_text
             from bookmarks b
             left join cases ca on b.target_type = 'caso' and ca.id = b.target_id
             left join reports r on b.target_type = 'reporte' and r.id = b.target_id
             left join categories cat on cat.id = ca.category_id
             left join categories catr on catr.id = r.category_id
             where b.user_id = $1
             order by b.created_at desc",
            [$userId]
        );
    }

    public function addReview(string $type, int $targetId, int $reviewerId, string $decision, string $notes): void
    {
        Database::execute(
            'insert into reviews (target_type, target_id, reviewer_id, decision, notes) values ($1, $2, $3, $4, $5)',
            [$type, $targetId, $reviewerId, $decision, $notes]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function reviews(string $type, int $targetId): array
    {
        return Database::select(
            "select r.*, u.name as reviewer_name from reviews r
             join users u on u.id = r.reviewer_id
             where r.target_type = $1 and r.target_id = $2
             order by r.created_at desc",
            [$type, $targetId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function commentsOnAuthorContent(int $authorId, int $limit = 8): array
    {
        return Database::select(
            "select c.*, u.name as author_name,
                    coalesce(ca.title, r.title) as target_title,
                    coalesce(ca.slug, r.slug) as target_slug
             from comments c
             join users u on u.id = c.user_id
             left join cases ca on c.target_type = 'caso' and ca.id = c.target_id and ca.author_id = $1
             left join reports r on c.target_type = 'reporte' and r.id = c.target_id and r.author_id = $1
             where c.status = 'visible' and coalesce(ca.author_id, r.author_id) = $1
             order by c.created_at desc limit $2",
            [$authorId, $limit]
        );
    }

    public function incrementViews(string $type, int $targetId): void
    {
        $table = self::TABLES[$type] ?? null;
        if ($table === null) {
            return;
        }
        Database::execute("update {$table} set views = views + 1 where id = $1", [$targetId]);
    }

    /** @return array<string, int> */
    public function stats(): array
    {
        $row = Database::first(
            "select
                (select count(*) from comments where status = 'visible') as comments,
                (select count(*) from reactions) as reactions,
                (select count(*) from bookmarks) as bookmarks,
                (select count(*) from reviews) as reviews"
        ) ?? [];
        return array_map(static fn ($value): int => (int) $value, $row);
    }
}
