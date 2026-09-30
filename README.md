# Bank of YR Maps

The website behind [bankofyrmaps.com](https://bankofyrmaps.com) — a catalogue of
original multiplayer maps for **Command & Conquer: Red Alert 2 — Yuri's
Revenge**, and a gathering point for the people still playing it.

Custom PHP 8, MySQL in production, no framework and no build step.

## Documentation

| Document | What's in it |
|---|---|
| [docs/PLAN.md](docs/PLAN.md) | What the site is, sitemap, community strategy, roadmap |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Directory layout, request lifecycle, schema, conventions |
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | Namecheap / cPanel deployment, step by step |

## Running it locally

Requires PHP 8.1+ with `pdo_sqlite`, `gd` and `mbstring`. No database server
needed — local development uses SQLite.

```bash
php database/migrate.php     # create the tables
php database/seed.php        # add sample maps to look at
php -S 127.0.0.1:8000 -t public_html server.php
```

Open <http://127.0.0.1:8000>.

`config/config.local.php` is used automatically whenever `config/config.php` is
absent, so there is nothing to configure to get started.

Reset the sample data at any time with `php database/seed.php --fresh`.

## Project status

Phase 1 (the public catalogue) is built. See the roadmap in
[docs/PLAN.md](docs/PLAN.md) for what comes next.

- [x] Routing, database layer, templates, migrations
- [x] Home, browse with filters, map detail, install guide, about, community
- [x] Counted download endpoint
- [ ] Admin back-office for publishing maps
- [ ] Comments
- [ ] Map submissions from other designers
