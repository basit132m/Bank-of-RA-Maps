<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Application configuration, loaded once from a plain PHP array file and read
 * with dot notation: Config::get('db.driver').
 */
final class Config
{
    /** @var array<string, mixed> */
    private static array $values = [];

    private static bool $loaded = false;

    public static function load(string $file): void
    {
        if (! is_file($file)) {
            throw new RuntimeException(
                'Configuration file not found: ' . $file . '. '
                . 'Copy config/config.example.php to config/config.php and fill it in.'
            );
        }

        $values = require $file;

        if (! is_array($values)) {
            throw new RuntimeException('Configuration file must return an array: ' . $file);
        }

        self::$values = $values;
        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (! self::$loaded) {
            throw new RuntimeException('Config::load() must be called before Config::get().');
        }

        $value = self::$values;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public static function isDebug(): bool
    {
        return (bool) self::get('app.debug', false);
    }

    public static function isLocal(): bool
    {
        return self::get('app.env', 'production') === 'local';
    }
}
