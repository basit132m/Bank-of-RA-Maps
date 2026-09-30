<?php

/**
 * Applies pending SQL migrations for the configured database driver.
 *
 *   php database/migrate.php
 *
 * Migrations are files named database/migrations/NNN_name.<driver>.sql and are
 * applied in filename order. Applied migrations are recorded in the
 * `migrations` table, so re-running this is safe.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("This script must be run from the command line.\n");
}

use App\Core\Config;
use App\Core\Database;

require dirname(__DIR__) . '/app/bootstrap.php';

$driver = (string) Config::get('db.driver', 'mysql');

Database::execute(
    'CREATE TABLE IF NOT EXISTS migrations ('
    . ' migration VARCHAR(191) NOT NULL PRIMARY KEY,'
    . ' applied_at VARCHAR(25) NOT NULL)'
);

$applied = array_column(Database::all('SELECT migration FROM migrations'), 'migration');

$files = glob(__DIR__ . '/migrations/*.' . $driver . '.sql') ?: [];
sort($files);

if ($files === []) {
    exit("No migrations found for driver '{$driver}'.\n");
}

$ran = 0;

foreach ($files as $file) {
    $name = basename($file);

    if (in_array($name, $applied, true)) {
        continue;
    }

    echo "Applying {$name} ... ";

    foreach (splitStatements((string) file_get_contents($file)) as $statement) {
        Database::connection()->exec($statement);
    }

    Database::execute(
        'INSERT INTO migrations (migration, applied_at) VALUES (?, ?)',
        [$name, Database::now()]
    );

    echo "done\n";
    $ran++;
}

echo $ran === 0
    ? "Database already up to date.\n"
    : "Applied {$ran} migration(s).\n";

/**
 * Split a migration file into individual statements.
 *
 * @return array<int, string>
 */
function splitStatements(string $sql): array
{
    // Drop whole-line SQL comments so they do not confuse the split.
    $lines = array_filter(
        preg_split('/\R/', $sql) ?: [],
        static fn (string $line): bool => ! str_starts_with(ltrim($line), '--')
    );

    $statements = array_map('trim', explode(';', implode("\n", $lines)));

    return array_values(array_filter($statements, static fn (string $s): bool => $s !== ''));
}
