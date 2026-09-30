<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Thin PDO wrapper. Supports MySQL (production) and SQLite (local development)
 * from the same query code. Every query is prepared — no string interpolation
 * of user input anywhere in this application.
 */
final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $driver = (string) Config::get('db.driver', 'mysql');

        try {
            self::$connection = match ($driver) {
                'mysql'  => self::connectMysql(),
                'sqlite' => self::connectSqlite(),
                default  => throw new RuntimeException('Unsupported database driver: ' . $driver),
            };
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }

        return self::$connection;
    }

    private static function connectMysql(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            (string) Config::get('db.host', 'localhost'),
            (int) Config::get('db.port', 3306),
            (string) Config::get('db.database', ''),
            (string) Config::get('db.charset', 'utf8mb4')
        );

        return new PDO(
            $dsn,
            (string) Config::get('db.username', ''),
            (string) Config::get('db.password', ''),
            self::options()
        );
    }

    private static function connectSqlite(): PDO
    {
        $path = (string) Config::get('db.path', '');

        if ($path === '') {
            throw new RuntimeException('db.path must be set when using the sqlite driver.');
        }

        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0o775, true) && ! is_dir($directory)) {
            throw new RuntimeException('Cannot create database directory: ' . $directory);
        }

        $pdo = new PDO('sqlite:' . $path, null, null, self::options());
        $pdo->exec('PRAGMA foreign_keys = ON');

        return $pdo;
    }

    /** @return array<int, mixed> */
    private static function options(): array
    {
        return [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    }

    /** @param array<string|int, mixed> $bindings */
    public static function run(string $sql, array $bindings = []): PDOStatement
    {
        $statement = self::connection()->prepare($sql);
        $statement->execute($bindings);

        return $statement;
    }

    /**
     * @param  array<string|int, mixed>  $bindings
     * @return array<int, array<string, mixed>>
     */
    public static function all(string $sql, array $bindings = []): array
    {
        return self::run($sql, $bindings)->fetchAll();
    }

    /**
     * @param  array<string|int, mixed>  $bindings
     * @return array<string, mixed>|null
     */
    public static function first(string $sql, array $bindings = []): ?array
    {
        $row = self::run($sql, $bindings)->fetch();

        return $row === false ? null : $row;
    }

    /** @param array<string|int, mixed> $bindings */
    public static function value(string $sql, array $bindings = []): mixed
    {
        $value = self::run($sql, $bindings)->fetchColumn();

        return $value === false ? null : $value;
    }

    /** @param array<string|int, mixed> $bindings */
    public static function execute(string $sql, array $bindings = []): int
    {
        return self::run($sql, $bindings)->rowCount();
    }

    public static function lastInsertId(): int
    {
        return (int) self::connection()->lastInsertId();
    }

    /** Current timestamp in the format the schema stores. */
    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
