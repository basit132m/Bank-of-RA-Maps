<?php

/**
 * Local development configuration, used by `php -S` and the CLI scripts.
 * Safe to commit: it contains no secrets and uses a local SQLite database.
 */

return [
    'app' => [
        'name'     => 'Bank of YR Maps',
        'tagline'  => 'Original maps for Red Alert 2: Yuri\'s Revenge',
        'url'      => 'http://localhost:8000',
        'env'      => 'local',
        'debug'    => true,
        'timezone' => 'UTC',
    ],

    'db' => [
        'driver' => 'sqlite',
        'path'   => __DIR__ . '/../database/development.sqlite',
    ],

    'security' => [
        'app_key' => 'local-development-key-not-a-secret',
    ],

    'uploads' => [
        'map_dir'           => __DIR__ . '/../storage/maps',
        'preview_dir'       => __DIR__ . '/../public_html/uploads/previews',
        'max_map_bytes'     => 10 * 1024 * 1024,
        'max_image_bytes'   => 4 * 1024 * 1024,
        'allowed_map_ext'   => ['map', 'mpr', 'yrm', 'zip'],
        'allowed_image_ext' => ['png', 'jpg', 'jpeg', 'webp'],
    ],

    'community' => [
        'discord_invite' => '',
        'contact_email'  => '',
    ],
];
