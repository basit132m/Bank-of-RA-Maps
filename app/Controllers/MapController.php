<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\NotFoundException;
use App\Models\Map;

final class MapController extends Controller
{
    /** Browse and filter the catalogue. */
    public function index(): string
    {
        $filters = [
            'q'       => $this->request->query('q', ''),
            'players' => $this->request->queryInt('players'),
            'theater' => $this->request->query('theater', ''),
            'game'    => $this->request->query('game', ''),
            'mode'    => $this->request->query('mode', ''),
            'tag'     => $this->request->query('tag', ''),
            'sort'    => $this->request->query('sort', 'newest'),
        ];

        $results = Map::browse($filters, max(1, $this->request->queryInt('page', 1)));

        return $this->view('maps/index', [
            'title'        => 'Browse maps',
            'description'  => 'Every map in the Bank of YR Maps catalogue. Filter by player count, '
                . 'terrain theater, game mode and more.',
            'heading'      => 'Browse maps',
            'filters'      => $filters,
            'results'      => $results,
            'playerCounts' => Map::usedPlayerCounts(),
            'modes'        => Map::usedModes(),
            'tags'         => Map::usedTags(),
        ]);
    }

    /** Maps carrying one tag. */
    public function tag(string $tag): string
    {
        $filters = [
            'tag'  => $tag,
            'sort' => $this->request->query('sort', 'newest'),
        ];

        $results = Map::browse($filters, max(1, $this->request->queryInt('page', 1)));

        if ($results['total'] === 0) {
            throw new NotFoundException('No maps tagged ' . $tag);
        }

        $label = ucwords(str_replace('-', ' ', $tag));

        return $this->view('maps/index', [
            'title'        => $label . ' maps',
            'description'  => 'Maps tagged ' . $label . ' in the Bank of YR Maps catalogue.',
            'heading'      => $label . ' maps',
            'filters'      => $filters,
            'results'      => $results,
            'playerCounts' => Map::usedPlayerCounts(),
            'modes'        => Map::usedModes(),
            'tags'         => Map::usedTags(),
        ]);
    }

    /** A single map. */
    public function show(string $slug): string
    {
        $map = Map::findBySlug($slug);

        if ($map === null) {
            throw new NotFoundException('No published map with slug ' . $slug);
        }

        $mapId = (int) $map['id'];
        Map::incrementViews($mapId);

        return $this->view('maps/show', [
            'title'       => $map['title'],
            'description' => $map['summary'] !== null && $map['summary'] !== ''
                ? (string) $map['summary']
                : $map['players'] . '-player ' . Map::theaterLabel($map) . " map for Yuri's Revenge.",
            'ogImage'     => preview_url($map['preview_image'] ?? null),
            'map'         => $map,
            'files'       => Map::files($mapId),
            'images'      => Map::images($mapId),
            'versions'    => Map::versions($mapId),
            'tags'        => Map::tags($mapId),
            'modes'       => Map::modes($mapId),
            'related'     => Map::related($map),
        ]);
    }
}
