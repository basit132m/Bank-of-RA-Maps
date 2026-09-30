# Architecture

A small hand-rolled MVC. No framework, no Composer dependencies, no build step.
The whole thing is PHP files you can read top to bottom.

## Directory layout

```
bank-of-yr-maps/
├── app/                      Application code — NOT web accessible
│   ├── bootstrap.php         Autoloader, config loading, error setup
│   ├── routes.php            URL -> controller map
│   ├── Core/                 Framework pieces
│   │   ├── Config.php        Dot-notation config reader
│   │   ├── Csrf.php          Per-session CSRF tokens
│   │   ├── Database.php      PDO wrapper (MySQL + SQLite)
│   │   ├── NotFoundException.php
│   │   ├── Request.php       Read-only request accessor
│   │   ├── Response.php      Redirects and security headers
│   │   ├── Router.php        Pattern matching router
│   │   ├── View.php          Plain-PHP templates with layouts
│   │   └── helpers.php       e(), url(), asset(), format_bytes(), ...
│   ├── Controllers/
│   ├── Models/
│   └── Views/
│       ├── layouts/main.php  Site chrome, meta tags, header, footer
│       ├── partials/         Reusable fragments (map card, pagination)
│       ├── maps/             Browse and detail templates
│       ├── pages/            About, community
│       ├── guides/           Guide index, install guide
│       └── errors/           404, 419, 500
│
├── config/
│   ├── config.example.php    Template — copy to config.php on the server
│   ├── config.local.php      Development settings (SQLite, no secrets)
│   └── config.php            Real credentials — git-ignored
│
├── database/
│   ├── migrations/           NNN_name.mysql.sql / NNN_name.sqlite.sql
│   ├── migrate.php           Applies pending migrations
│   └── seed.php              Sample data for development
│
├── storage/                  NOT web accessible
│   ├── maps/                 Uploaded map files, served only via /download/*
│   ├── cache/
│   └── logs/
│
├── public_html/              The ONLY web-accessible directory
│   ├── index.php             Front controller
│   ├── .htaccess             Rewrites, security headers, caching
│   ├── robots.txt
│   ├── assets/               CSS, JS, images
│   └── uploads/previews/     Map preview images and screenshots
│
└── server.php                Router for PHP's built-in dev server
```

## Request lifecycle

1. Apache rewrites any non-file request to `public_html/index.php`.
2. `index.php` loads `app/bootstrap.php` — autoloader, config, error handling.
3. The session starts, security headers go out.
4. `app/routes.php` returns a configured `Router`.
5. `Router::dispatch()` matches the path, instantiates the controller and calls
   the action, which returns rendered HTML.
6. `NotFoundException` becomes a 404; anything else becomes a 500 and is logged.

## Conventions

- **Nothing user-supplied is ever concatenated into SQL.** Every query is
  prepared. Sort orders and column names come from whitelists (see
  `Map::orderBy()`), never from request input.
- **Every dynamic value in a view goes through `e()`.** Author-entered prose
  goes through `paragraphs()`, which escapes and then adds paragraph tags — the
  input is never treated as HTML.
- **Map files live outside the web root.** The only way to get one is through
  `/download/{slug}`, which counts the download. Preview images are ordinary
  static files under `public_html/uploads/previews`.
- **Filter logic lives in the model, not the controller**, so the browse page,
  tag pages and any future admin list share one definition of a published map.
- **Every POST form carries `Csrf::field()`** and every POST handler calls
  `Csrf::verify()` before touching data.

## Database schema

| Table | Purpose |
|---|---|
| `users` | Accounts. Role is `admin`, `mapper` or `member`. |
| `maps` | The catalogue. One row per map, with all its specs. |
| `map_files` | Downloadable files. A map may have more than one. |
| `map_images` | Minimap renders and screenshots. |
| `map_versions` | Changelog entries, newest first by `released_at`. |
| `tags` / `map_tag` | Free-form tags such as `1v1`, `naval`, `tournament`. |
| `modes` / `map_mode` | Game modes such as Standard, Naval, Megawealth. |
| `comments` | Per-map comments, moderated via `status`. (Phase 2) |
| `download_events` | One row per download, with a salted IP hash. Used to
  deduplicate the counter and to spot abuse. Never stores a raw IP address. |
| `migrations` | Which migration files have been applied. |

`maps.download_count` is a denormalised counter so listings never have to
aggregate `download_events`. It increments at most once per visitor per map per
24 hours.

## Adding a migration

Write both dialects, same number and name:

```
database/migrations/002_add_collections.mysql.sql
database/migrations/002_add_collections.sqlite.sql
```

Then `php database/migrate.php`. Applied migrations are recorded, so re-running
is safe. Never edit a migration that has already run on production — add a new
one.

## Why no framework

Shared hosting, one maintainer, and a site whose logic is genuinely simple.
A framework would add a dependency to keep patched, a build step to run before
deploying, and a lot of code to read past. This is roughly 1,500 lines total and
deploys with `git pull`.

If the site grows past what this comfortably handles — user accounts, a forum,
a submission queue — revisit that decision then, not now.
