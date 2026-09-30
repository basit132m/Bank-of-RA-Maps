<?php

/**
 * Seeds the database with sample maps so the site can be developed and
 * reviewed with realistic content.
 *
 *   php database/seed.php          add the sample maps (skips ones already there)
 *   php database/seed.php --fresh  wipe map data first, then seed
 *
 * Placeholder preview images and stub map files are generated on the fly, so
 * this only ever touches local development data — never run it in production
 * once real maps are published.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("This script must be run from the command line.\n");
}

use App\Core\Config;
use App\Core\Database;

require dirname(__DIR__) . '/app/bootstrap.php';

$fresh = in_array('--fresh', $argv, true);

if ($fresh) {
    foreach (['download_events', 'comments', 'map_tag', 'map_mode', 'map_versions',
              'map_images', 'map_files', 'maps', 'tags', 'modes'] as $table) {
        Database::execute('DELETE FROM ' . $table);
    }
    echo "Cleared existing map data.\n";
}

// ---------------------------------------------------------------- lookups ---

$modeNames = ['Standard', 'Naval', 'Co-op', 'Battle', 'Megawealth', 'Free-for-all'];
$modeIds   = [];

foreach ($modeNames as $name) {
    $modeIds[$name] = lookupId('modes', $name);
}

// ------------------------------------------------------------------- maps ---

$maps = [
    [
        'title'   => 'Dust Bowl Standoff',
        'players' => 2,
        'theater' => 'desert',
        'size'    => [90, 90],
        'summary' => 'A tight 1v1 desert map with one central chokepoint and no room to hide a tech-up.',
        'notes'   => "Built for players who want the fight to start early.\n\n"
            . "The two bases sit on opposite plateaus with a single wide ramp between them, so there is "
            . "exactly one ground approach and both players can see it. Expect contact by the four minute "
            . "mark. Air units are strong here because the ramp funnels everything; keep flak or patriots "
            . "near the ore field rather than on the base perimeter.\n\n"
            . "Position notes: the northern start has slightly better ore proximity, the southern start has "
            . "the shorter walk to the oil derricks. In testing that came out close to even, but if you are "
            . "playing a tournament set, swap sides between games.",
        'tags'    => ['1v1', 'chokepoint', 'tournament'],
        'modes'   => ['Standard'],
        'ore'     => 'medium',
        'tech'    => 2,
        'oil'     => 2,
        'gems'    => 1,
        'featured' => true,
        'versions' => [
            ['1.2', '-40 days', "Widened the central ramp by two cells — the chokepoint was so tight that defender's advantage decided the game."],
            ['1.1', '-70 days', 'Moved the southern ore field 4 cells further from the base to even out early economy.'],
            ['1.0', '-95 days', 'First release.'],
        ],
    ],
    [
        'title'   => 'Siberian Crossroads',
        'players' => 4,
        'theater' => 'snow',
        'size'    => [110, 110],
        'summary' => 'Four bases around a frozen interchange. Built for 2v2 with fast, messy mid-game fights.',
        'notes'   => "The centre of this map is a raised road junction that every route passes through, and "
            . "whoever holds it can shell three of the four bases from range.\n\n"
            . "That is deliberate. The junction is open ground with no cover, so holding it costs you units "
            . "constantly — it is a position you take when you are ready to push, not one you sit on from "
            . "minute five.\n\n"
            . "Teams are meant to be north/south. Diagonal pairings make the map lopsided because the "
            . "eastern ore is richer.",
        'tags'    => ['2v2', 'teamplay', 'open-field'],
        'modes'   => ['Standard', 'Battle'],
        'ore'     => 'high',
        'tech'    => 4,
        'oil'     => 4,
        'gems'    => 2,
        'versions' => [
            ['1.1', '-25 days', 'Added two tech bunkers at the junction so the centre is contestable without full army commitment.'],
            ['1.0', '-55 days', 'First release.'],
        ],
    ],
    [
        'title'   => 'Neon District',
        'players' => 4,
        'theater' => 'new-urban',
        'size'    => [100, 100],
        'summary' => 'Dense city blocks, garrisonable buildings everywhere, and almost no open ground.',
        'notes'   => "Infantry map, unapologetically. The streets are two to three cells wide and almost every "
            . "building can be garrisoned, so tank columns get taken apart by rocketeers and conscripts "
            . "firing from cover.\n\n"
            . "Bring engineers. There are six civilian tech structures scattered through the middle blocks "
            . "and holding four of them is usually the difference in a long game.\n\n"
            . "If you hate urban fighting you will hate this map, and that is fine — it is not trying to be "
            . "a neutral ladder map.",
        'tags'    => ['urban-combat', 'infantry', '2v2'],
        'modes'   => ['Standard', 'Battle'],
        'ore'     => 'low',
        'tech'    => 6,
        'oil'     => 3,
        'gems'    => 0,
        'versions' => [
            ['1.0', '-18 days', 'First release.'],
        ],
    ],
    [
        'title'   => 'Coral Gauntlet',
        'players' => 2,
        'theater' => 'temperate',
        'size'    => [120, 100],
        'summary' => 'A naval 1v1 where the land route is long enough that ignoring the water loses you the game.',
        'notes'   => "Two coastal bases separated by a wide channel, plus a land bridge that takes roughly "
            . "three times as long to traverse.\n\n"
            . "You can win this on land. You will find it much harder. The channel has two small islands "
            . "with ore on them, and contesting those with destroyers early tends to set up the whole game. "
            . "Sea scorpions are excellent here; dreadnoughts are less good than they look because the "
            . "islands block line of sight.\n\n"
            . "Naval players have been asking for more 1v1 water maps for years. This is one.",
        'tags'    => ['1v1', 'naval', 'tournament'],
        'modes'   => ['Standard', 'Naval'],
        'ore'     => 'medium',
        'tech'    => 2,
        'oil'     => 0,
        'gems'    => 3,
        'versions' => [
            ['1.1', '-12 days', 'Shortened the land bridge slightly after feedback that pure-naval was the only viable strategy.'],
            ['1.0', '-30 days', 'First release.'],
        ],
    ],
    [
        'title'   => 'Rust Valley',
        'players' => 6,
        'theater' => 'temperate',
        'size'    => [130, 120],
        'summary' => 'Six starts along a wide valley floor, with high ground on both ridges worth fighting for.',
        'notes'   => "A big map that plays surprisingly fast, because the valley floor gives everyone a "
            . "straight run at everyone else.\n\n"
            . "The two ridges overlook the whole valley and hold the best ore. Taking a ridge is the "
            . "standard opening; holding one against two players is the hard part.\n\n"
            . "Works as 3v3 or as a free-for-all. In FFA the two centre positions are noticeably harder "
            . "to defend, so hand those to the stronger players.",
        'tags'    => ['3v3', 'ffa', 'high-ore', 'teamplay'],
        'modes'   => ['Standard', 'Free-for-all', 'Megawealth'],
        'ore'     => 'high',
        'tech'    => 6,
        'oil'     => 6,
        'gems'    => 4,
        'versions' => [
            ['1.0', '-8 days', 'First release.'],
        ],
    ],
    [
        'title'   => 'Baku Oil Fields',
        'players' => 2,
        'theater' => 'desert',
        'size'    => [95, 95],
        'summary' => 'Eight oil derricks on open ground between two bases. Economy map with teeth.',
        'notes'   => "The ore on this map is deliberately thin. Almost all of your income comes from the "
            . "derricks in the middle, which means the map is a constant fight over static structures you "
            . "cannot retreat.\n\n"
            . "Engineers are the most valuable unit here by a distance. Keep two spare in the base at all "
            . "times, because derricks change hands repeatedly and the player who can retake one fastest "
            . "wins the economy.\n\n"
            . "Not recommended for beginners — the punishment for losing map control is immediate.",
        'tags'    => ['1v1', 'low-ore', 'economy'],
        'modes'   => ['Standard'],
        'ore'     => 'low',
        'tech'    => 2,
        'oil'     => 8,
        'gems'    => 0,
        'versions' => [
            ['1.0', '-4 days', 'First release.'],
        ],
    ],
    [
        'title'   => 'Lunar Assembly',
        'players' => 4,
        'theater' => 'lunar',
        'size'    => [100, 100],
        'summary' => 'Low-gravity novelty map on the moon theater. No cover, nowhere to run.',
        'notes'   => "Built mostly because the lunar theater almost never gets used, and it deserves better.\n\n"
            . "There is no terrain cover at all — the whole surface is flat crater floor with a few "
            . "impassable rock clusters. That makes long-range units unusually strong and makes stealth "
            . "genuinely useful for once.\n\n"
            . "Treat it as a fun map rather than a serious one. It is not balanced for competitive play and "
            . "it is not trying to be.",
        'tags'    => ['ffa', 'novelty', 'open-field'],
        'modes'   => ['Standard', 'Free-for-all'],
        'ore'     => 'medium',
        'tech'    => 0,
        'oil'     => 4,
        'gems'    => 2,
        'versions' => [
            ['1.0', '-2 days', 'First release.'],
        ],
    ],
];

$added = 0;

foreach ($maps as $index => $data) {
    $slug = slugify($data['title']);

    if (Database::value('SELECT id FROM maps WHERE slug = ?', [$slug]) !== null) {
        echo "Skipping {$data['title']} (already seeded).\n";
        continue;
    }

    $latestVersion = $data['versions'][0][0];
    $publishedAt   = date('Y-m-d H:i:s', (int) strtotime($data['versions'][0][1]));
    $preview       = $slug . '.png';

    Database::execute(
        'INSERT INTO maps (slug, title, version, game, players, size_x, size_y, theater, summary,'
        . ' designer_notes, tech_structures, oil_derricks, ore_density, gem_count, cncnet_ready,'
        . ' preview_image, author_name, status, featured, download_count, view_count,'
        . ' published_at, created_at, updated_at)'
        . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [
            $slug,
            $data['title'],
            $latestVersion,
            'yr',
            $data['players'],
            $data['size'][0],
            $data['size'][1],
            $data['theater'],
            $data['summary'],
            $data['notes'],
            $data['tech'],
            $data['oil'],
            $data['ore'],
            $data['gems'],
            1,
            $preview,
            'Basit',
            'published',
            ! empty($data['featured']) ? 1 : 0,
            random_int(120, 4800),
            random_int(400, 20000),
            $publishedAt,
            $publishedAt,
            $publishedAt,
        ]
    );

    $mapId = Database::lastInsertId();

    // Version history
    foreach ($data['versions'] as $version) {
        Database::execute(
            'INSERT INTO map_versions (map_id, version, released_at, notes) VALUES (?, ?, ?, ?)',
            [$mapId, $version[0], date('Y-m-d', (int) strtotime($version[1])), $version[2]]
        );
    }

    // Tags and modes
    foreach ($data['tags'] as $tagName) {
        Database::execute(
            'INSERT INTO map_tag (map_id, tag_id) VALUES (?, ?)',
            [$mapId, lookupId('tags', $tagName)]
        );
    }

    foreach ($data['modes'] as $modeName) {
        Database::execute(
            'INSERT INTO map_mode (map_id, mode_id) VALUES (?, ?)',
            [$mapId, $modeIds[$modeName]]
        );
    }

    // Stub downloadable file and generated preview image
    $fileName = writeStubMapFile($slug, $data['title'], $data['players'], $data['size']);
    Database::execute(
        'INSERT INTO map_files (map_id, stored_name, original_name, bytes, kind, created_at)'
        . ' VALUES (?, ?, ?, ?, ?, ?)',
        [
            $mapId,
            $fileName,
            $slug . '.yrm',
            (int) filesize((string) Config::get('uploads.map_dir') . '/' . $fileName),
            'map',
            $publishedAt,
        ]
    );

    writePreviewImage($slug, $data['players']);

    echo "Seeded {$data['title']}.\n";
    $added++;
}

echo "\nDone. Added {$added} map(s).\n";

// ------------------------------------------------------------- functions ----

/** Find or create a row in a slug/name lookup table and return its id. */
function lookupId(string $table, string $name): int
{
    $slug = slugify($name);
    $id   = Database::value('SELECT id FROM ' . $table . ' WHERE slug = ?', [$slug]);

    if ($id !== null) {
        return (int) $id;
    }

    Database::execute(
        'INSERT INTO ' . $table . ' (slug, name) VALUES (?, ?)',
        [$slug, $name]
    );

    return Database::lastInsertId();
}

/**
 * Write a placeholder map file so the download endpoint has something to serve.
 * Real maps are INI-format text files, so this is shaped like one.
 *
 * @param array{0: int, 1: int} $size
 */
function writeStubMapFile(string $slug, string $title, int $players, array $size): string
{
    $directory = (string) Config::get('uploads.map_dir');

    if (! is_dir($directory)) {
        mkdir($directory, 0o775, true);
    }

    $stored = $slug . '-' . bin2hex(random_bytes(4)) . '.yrm';

    $contents = "; Placeholder map file generated by database/seed.php\n"
        . "; This is development sample data, not a playable map.\n\n"
        . "[Basic]\n"
        . "Name={$title}\n"
        . "Player={$players}\n"
        . "GameMode=standard\n"
        . "MinPlayers=2\n"
        . "MaxPlayers={$players}\n\n"
        . "[Map]\n"
        . "Size=0,0,{$size[0]},{$size[1]}\n"
        . "LocalSize=2,4," . ($size[0] - 4) . ',' . ($size[1] - 8) . "\n";

    file_put_contents($directory . '/' . $stored, $contents);

    return $stored;
}

/** Generate a placeholder minimap so listings are not full of empty boxes. */
function writePreviewImage(string $slug, int $players): void
{
    $directory = (string) Config::get('uploads.preview_dir');

    if (! is_dir($directory)) {
        mkdir($directory, 0o775, true);
    }

    if (! function_exists('imagecreatetruecolor')) {
        return;
    }

    $size  = 480;
    $image = imagecreatetruecolor($size, $size);

    // Deterministic per map, so previews stay stable between runs.
    mt_srand(crc32($slug));

    $background = imagecolorallocate($image, 19, 26, 33);
    $gridColor  = imagecolorallocate($image, 40, 50, 61);
    $landColor  = imagecolorallocate($image, 44, 58, 48);
    $rockColor  = imagecolorallocate($image, 58, 72, 87);
    $oreColor   = imagecolorallocate($image, 242, 164, 19);
    $textColor  = imagecolorallocate($image, 142, 157, 173);

    imagefill($image, 0, 0, $background);

    // Terrain blobs
    for ($i = 0; $i < 22; $i++) {
        $x = mt_rand(20, $size - 20);
        $y = mt_rand(20, $size - 20);
        $w = mt_rand(50, 170);
        imagefilledellipse($image, $x, $y, $w, (int) ($w * 0.7), $landColor);
    }

    // Impassable rock clusters
    for ($i = 0; $i < 10; $i++) {
        $x = mt_rand(30, $size - 40);
        $y = mt_rand(30, $size - 40);
        imagefilledrectangle($image, $x, $y, $x + mt_rand(8, 26), $y + mt_rand(8, 22), $rockColor);
    }

    // Grid overlay
    for ($p = 0; $p < $size; $p += 32) {
        imageline($image, $p, 0, $p, $size, $gridColor);
        imageline($image, 0, $p, $size, $p, $gridColor);
    }

    // Player start positions evenly around a circle
    $radius = (int) ($size * 0.33);
    $centre = (int) ($size / 2);

    for ($i = 0; $i < $players; $i++) {
        $angle = (2 * M_PI * $i / $players) - (M_PI / 2);
        $x = (int) ($centre + $radius * cos($angle));
        $y = (int) ($centre + $radius * sin($angle));

        imagefilledellipse($image, $x, $y, 22, 22, $oreColor);
        imagefilledellipse($image, $x, $y, 12, 12, $background);
    }

    imagestring($image, 3, 12, $size - 22, 'PLACEHOLDER PREVIEW', $textColor);

    imagepng($image, $directory . '/' . $slug . '.png', 8);
    imagedestroy($image);
}
