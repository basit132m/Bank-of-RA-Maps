<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Database;
use App\Core\NotFoundException;
use App\Models\Map;

/**
 * Serves map files through PHP so downloads can be counted, and so the stored
 * filename never has to be guessable from the page.
 */
final class DownloadController extends Controller
{
    /** How long before the same visitor counts as a new download of one map. */
    private const DEDUPE_WINDOW = '-24 hours';

    /** Download a map's primary file. */
    public function map(string $slug): never
    {
        $map = Map::findBySlug($slug);

        if ($map === null) {
            throw new NotFoundException('No published map with slug ' . $slug);
        }

        $files = Map::files((int) $map['id']);

        if ($files === []) {
            throw new NotFoundException('Map ' . $slug . ' has no downloadable file');
        }

        $this->send($map, $files[0]);
    }

    /** Download one specific file belonging to a map. */
    public function file(string $slug, string $fileId): never
    {
        $map = Map::findBySlug($slug);

        if ($map === null) {
            throw new NotFoundException('No published map with slug ' . $slug);
        }

        $file = Database::first(
            'SELECT * FROM map_files WHERE id = ? AND map_id = ?',
            [(int) $fileId, (int) $map['id']]
        );

        if ($file === null) {
            throw new NotFoundException('No file ' . $fileId . ' on map ' . $slug);
        }

        $this->send($map, $file);
    }

    /**
     * @param  array<string, mixed>  $map
     * @param  array<string, mixed>  $file
     */
    private function send(array $map, array $file): never
    {
        $directory = (string) Config::get('uploads.map_dir', '');
        $stored    = basename((string) $file['stored_name']);
        $path      = $directory . '/' . $stored;

        if (! is_file($path) || ! is_readable($path)) {
            throw new NotFoundException('Missing file on disk: ' . $stored);
        }

        $this->record((int) $map['id'], (int) $file['id']);

        // Offer the original, human-readable name rather than the stored one.
        $downloadName = basename((string) $file['original_name']);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . str_replace('"', '', $downloadName) . '"');
        header('Content-Length: ' . (string) filesize($path));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('X-Content-Type-Options: nosniff');

        readfile($path);
        exit;
    }

    /** Count the download, ignoring repeats from the same visitor within the dedupe window. */
    private function record(int $mapId, int $fileId): void
    {
        $ipHash = $this->request->ipHash();

        $recent = Database::value(
            'SELECT id FROM download_events WHERE map_id = ? AND ip_hash = ? AND created_at > ? LIMIT 1',
            [$mapId, $ipHash, date('Y-m-d H:i:s', strtotime(self::DEDUPE_WINDOW))]
        );

        Database::execute(
            'INSERT INTO download_events (map_id, file_id, ip_hash, created_at) VALUES (?, ?, ?, ?)',
            [$mapId, $fileId, $ipHash, Database::now()]
        );

        if ($recent === null) {
            Map::incrementDownloads($mapId);
        }
    }
}
