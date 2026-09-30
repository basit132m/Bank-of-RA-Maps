<?php

/**
 * Copy this file to config/config.php and fill it in.
 * config/config.php is git-ignored — real credentials never enter the repo.
 */

return [
    'app' => [
        'name'     => 'Bank of YR Maps',
        'tagline'  => 'Original maps for Red Alert 2: Yuri\'s Revenge',
        'url'      => 'https://bankofyrmaps.com',
        'env'      => 'production',   // production | local
        'debug'    => false,          // never true in production
        'timezone' => 'UTC',
    ],

    'db' => [
        // 'mysql' on Namecheap, 'sqlite' for local development
        'driver'   => 'mysql',
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'cpaneluser_bankofyrmaps',
        'username' => 'cpaneluser_byrm',
        'password' => '',
        'charset'  => 'utf8mb4',
        // Used only when driver is 'sqlite'
        'path'     => __DIR__ . '/../database/development.sqlite',
    ],

    'security' => [
        // Random 32+ character string. Used to salt hashed visitor IPs.
        // Generate with: php -r "echo bin2hex(random_bytes(32));"
        'app_key' => 'change-me-to-a-long-random-string',
    ],

    'uploads' => [
        'map_dir'            => __DIR__ . '/../storage/maps',
        'preview_dir'        => __DIR__ . '/../public_html/uploads/previews',
        'max_map_bytes'      => 10 * 1024 * 1024,   // 10 MB
        'max_image_bytes'    => 4 * 1024 * 1024,    // 4 MB
        'allowed_map_ext'    => ['map', 'mpr', 'yrm', 'zip'],
        'allowed_image_ext'  => ['png', 'jpg', 'jpeg', 'webp'],
    ],

    'community' => [
        'discord_invite' => '',       // https://discord.gg/xxxxxxx
        'contact_email'  => '',
    ],
];
