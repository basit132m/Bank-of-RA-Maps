<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Everything the site needs to read and write about maps.
 *
 * Filtering is built here rather than in the controller so the browse page,
 * tag pages and the admin list all share one definition of "a published map".
 */
final class Map
{
    public const PER_PAGE = 12;

    /** Terrain theaters available in RA2 / Yuri's Revenge. */
    public const THEATERS = [
        'temperate' => 'Temperate',
        'snow'      => 'Snow',
        'urban'     => 'Urban',
        'new-urban' => 'New Urban',
        'desert'    => 'Desert',
        'lunar'     => 'Lunar',
    ];

    public const GAMES = [
        'yr'   => "Yuri's Revenge",
        'ra2'  => 'Red Alert 2',
        'both' => 'RA2 and YR',
    ];

    public const ORE_DENSITIES = [
        'low'    => 'Low',
        'medium' => 'Medium',
        'high'   => 'High',
    ];

    public const SORTS = [
        'newest'   => 'Newest first',
        'oldest'   => 'Oldest first',
        'popular'  => 'Most downloaded',
        'title'    => 'Title A–Z',
    ];

    /**
     * Paginated list of published maps.
     *
     * @param  array<string, string|int|null>  $filters  q, players, theater, game, mode, tag, sort
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, pages: int, per_page: int}
     */
    public static function browse(array $filters = [], int $page = 1, int $perPage = self::PER_PAGE): array
    {
        [$where, $bindings] = self::buildFilters($filters);

        $total = (int) Database::value(
            'SELECT COUNT(*) FROM maps WHERE ' . $where,
            $bindings
        );

        $pages = max(1, (int) ceil($total / $perPage));
        $page  = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $items = Database::all(
            'SELECT * FROM maps WHERE ' . $where
            . ' ORDER BY ' . self::orderBy(isset($filters['sort']) ? (string) $filters['sort'] : 'newest')
            . ' LIMIT ' . $perPage . ' OFFSET ' . $offset,
            $bindings
        );

        return [
            'items'    => array_map([self::class, 'withCounts'], $items),
            'total'    => $total,
            'page'     => $page,
            'pages'    => $pages,
            'per_page' => $perPage,
        ];
    }

    /**
     * Build the WHERE clause shared by every public listing.
     *
     * @param  array<string, string|int|null>  $filters
     * @return array{0: string, 1: array<int, mixed>}
     */
    private static function buildFilters(array $filters): array
    {
        $where    = ["maps.status = 'published'"];
        $bindings = [];

        $search = isset($filters['q']) ? trim((string) $filters['q']) : '';
        if ($search !== '') {
            $where[] = '(maps.title LIKE ? OR maps.summary LIKE ?)';
            $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
            $bindings[] = $term;
            $bindings[] = $term;
        }

        $players = isset($filters['players']) ? (int) $filters['players'] : 0;
        if ($players > 0) {
            $where[] = 'maps.players = ?';
            $bindings[] = $players;
        }

        $theater = isset($filters['theater']) ? (string) $filters['theater'] : '';
        if ($theater !== '' && isset(self::THEATERS[$theater])) {
            $where[] = 'maps.theater = ?';
            $bindings[] = $theater;
        }

        $game = isset($filters['game']) ? (string) $filters['game'] : '';
        if ($game !== '' && isset(self::GAMES[$game])) {
            // A map marked "both" satisfies a filter for either single game.
            $where[] = '(maps.game = ? OR maps.game = ?)';
            $bindings[] = $game;
            $bindings[] = 'both';
        }

        $mode = isset($filters['mode']) ? (string) $filters['mode'] : '';
        if ($mode !== '') {
            $where[] = 'EXISTS (SELECT 1 FROM map_mode mm'
                . ' JOIN modes md ON md.id = mm.mode_id'
                . ' WHERE mm.map_id = maps.id AND md.slug = ?)';
            $bindings[] = $mode;
        }

        $tag = isset($filters['tag']) ? (string) $filters['tag'] : '';
        if ($tag !== '') {
            $where[] = 'EXISTS (SELECT 1 FROM map_tag mt'
                . ' JOIN tags t ON t.id = mt.tag_id'
                . ' WHERE mt.map_id = maps.id AND t.slug = ?)';
            $bindings[] = $tag;
        }

        return [implode(' AND ', $where), $bindings];
    }

    /** Whitelist of sort orders — never interpolate user input into ORDER BY. */
    private static function orderBy(string $sort): string
    {
        return match ($sort) {
            'oldest'  => 'maps.published_at ASC, maps.id ASC',
            'popular' => 'maps.download_count DESC, maps.published_at DESC',
            'title'   => 'maps.title ASC',
            default   => 'maps.published_at DESC, maps.id DESC',
        };
    }

    /** @return array<string, mixed>|null */
    public static function findBySlug(string $slug, bool $publishedOnly = true): ?array
    {
        $sql = 'SELECT * FROM maps WHERE slug = ?';
        if ($publishedOnly) {
            $sql .= " AND status = 'published'";
        }

        $map = Database::first($sql, [$slug]);

        return $map === null ? null : self::withCounts($map);
    }

    /** @return array<int, array<string, mixed>> */
    public static function latest(int $limit = 6): array
    {
        $limit = max(1, min($limit, 50));

        return array_map([self::class, 'withCounts'], Database::all(
            "SELECT * FROM maps WHERE status = 'published'"
            . ' ORDER BY published_at DESC, id DESC LIMIT ' . $limit
        ));
    }

    /** @return array<string, mixed>|null */
    public static function featured(): ?array
    {
        $map = Database::first(
            "SELECT * FROM maps WHERE status = 'published' AND featured = 1"
            . ' ORDER BY published_at DESC LIMIT 1'
        );

        return $map === null ? null : self::withCounts($map);
    }

    /**
     * Maps a visitor is likely to want next: same player count first, then
     * anything else recent.
     *
     * @param  array<string, mixed>  $map
     * @return array<int, array<string, mixed>>
     */
    public static function related(array $map, int $limit = 3): array
    {
        $limit = max(1, min($limit, 12));

        $rows = Database::all(
            "SELECT * FROM maps WHERE status = 'published' AND id <> ?"
            . ' ORDER BY (players = ?) DESC, published_at DESC LIMIT ' . $limit,
            [(int) $map['id'], (int) $map['players']]
        );

        return array_map([self::class, 'withCounts'], $rows);
    }

    /** @return array<int, array<string, mixed>> */
    public static function files(int $mapId): array
    {
        return Database::all(
            'SELECT * FROM map_files WHERE map_id = ? ORDER BY id ASC',
            [$mapId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function images(int $mapId): array
    {
        return Database::all(
            'SELECT * FROM map_images WHERE map_id = ? ORDER BY sort_order ASC, id ASC',
            [$mapId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function versions(int $mapId): array
    {
        // Newest release first. Ordered by date rather than insert order, since
        // history is often entered oldest-first.
        return Database::all(
            'SELECT * FROM map_versions WHERE map_id = ? ORDER BY released_at DESC, id DESC',
            [$mapId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function tags(int $mapId): array
    {
        return Database::all(
            'SELECT t.* FROM tags t JOIN map_tag mt ON mt.tag_id = t.id'
            . ' WHERE mt.map_id = ? ORDER BY t.name ASC',
            [$mapId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function modes(int $mapId): array
    {
        return Database::all(
            'SELECT md.* FROM modes md JOIN map_mode mm ON mm.mode_id = md.id'
            . ' WHERE mm.map_id = ? ORDER BY md.name ASC',
            [$mapId]
        );
    }

    /** Tags that have at least one published map, for the filter bar. @return array<int, array<string, mixed>> */
    public static function usedTags(): array
    {
        return Database::all(
            'SELECT t.*, COUNT(m.id) AS map_count FROM tags t'
            . ' JOIN map_tag mt ON mt.tag_id = t.id'
            . " JOIN maps m ON m.id = mt.map_id AND m.status = 'published'"
            . ' GROUP BY t.id, t.slug, t.name ORDER BY map_count DESC, t.name ASC'
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function usedModes(): array
    {
        return Database::all(
            'SELECT md.*, COUNT(m.id) AS map_count FROM modes md'
            . ' JOIN map_mode mm ON mm.mode_id = md.id'
            . " JOIN maps m ON m.id = mm.map_id AND m.status = 'published'"
            . ' GROUP BY md.id, md.slug, md.name ORDER BY md.name ASC'
        );
    }

    /** Player counts present in the catalogue, for the filter bar. @return array<int, int> */
    public static function usedPlayerCounts(): array
    {
        $rows = Database::all(
            "SELECT DISTINCT players FROM maps WHERE status = 'published' ORDER BY players ASC"
        );

        return array_map(static fn (array $row): int => (int) $row['players'], $rows);
    }

    public static function publishedCount(): int
    {
        return (int) Database::value("SELECT COUNT(*) FROM maps WHERE status = 'published'");
    }

    public static function totalDownloads(): int
    {
        return (int) Database::value("SELECT COALESCE(SUM(download_count), 0) FROM maps WHERE status = 'published'");
    }

    public static function incrementViews(int $mapId): void
    {
        Database::execute('UPDATE maps SET view_count = view_count + 1 WHERE id = ?', [$mapId]);
    }

    public static function incrementDownloads(int $mapId): void
    {
        Database::execute('UPDATE maps SET download_count = download_count + 1 WHERE id = ?', [$mapId]);
    }

    /** Normalise numeric columns, which SQLite and MySQL return as strings. @param array<string, mixed> $map @return array<string, mixed> */
    private static function withCounts(array $map): array
    {
        foreach (['id', 'players', 'size_x', 'size_y', 'tech_structures', 'oil_derricks',
                  'gem_count', 'download_count', 'view_count', 'featured', 'cncnet_ready'] as $column) {
            if (isset($map[$column])) {
                $map[$column] = (int) $map[$column];
            }
        }

        return $map;
    }

    /** Human label for a map's size, or null when unknown. @param array<string, mixed> $map */
    public static function sizeLabel(array $map): ?string
    {
        if (empty($map['size_x']) || empty($map['size_y'])) {
            return null;
        }

        return $map['size_x'] . ' x ' . $map['size_y'];
    }

    /** @param array<string, mixed> $map */
    public static function theaterLabel(array $map): string
    {
        $theater = (string) ($map['theater'] ?? '');

        return self::THEATERS[$theater] ?? ucfirst($theater);
    }

    /** @param array<string, mixed> $map */
    public static function gameLabel(array $map): string
    {
        $game = (string) ($map['game'] ?? '');

        return self::GAMES[$game] ?? strtoupper($game);
    }
}
