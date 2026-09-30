# Bank of YR Maps — Project Plan

A community site publishing original multiplayer maps for **Command & Conquer:
Red Alert 2 — Yuri's Revenge**, built to grow into a hub for RA2/YR players.

- **Domain:** bankofyrmaps.com (Namecheap shared hosting, cPanel)
- **Stack:** custom PHP 8 + MySQL, no framework, full control over design and logic
- **Repo layout:** application code outside the web root, `public_html/` is the
  only web-reachable directory

---

## 1. Branding note

The domain is `bankofyrmaps.com`, so the site brand is **Bank of YR Maps**.
"YR" is what the community actually says, and it matches the URL people type.
If the "Bank of RA Maps" name matters long-term, register `bankoframaps.com`
separately and 301-redirect it here — but run one brand, matching the domain.

## 2. What the site is, in priority order

1. **A map catalog that is genuinely good to browse and download from.**
   This is the product. It brings search traffic and repeat visits.
2. **A place for RA2/YR players to gather.**
   This is the retention layer, and it only matters once (1) is solid.

An empty forum on day one signals a dead site. A well-organised catalogue of
real maps signals a living one. So: catalogue first, community second.

## 3. Audience

| Who | What they want |
|---|---|
| Casual YR players | New maps that are easy to find, install and play with friends |
| Competitive 1v1 / clan players | Balanced, tournament-suitable maps with honest notes on how they play |
| CnCNet online players | Maps that work online, and clear install paths per launcher |
| Other map designers | Credit, feedback, and eventually a way to publish their own work |

## 4. Sitemap

```
/                          Home — newest maps, featured map, what this site is
/maps                      Browse all — filter + search + pagination
/maps/<slug>               Single map page (the heart of the site)
/maps/tag/<tag>            e.g. /maps/tag/naval, /maps/tag/1v1
/collections/<slug>        Curated packs, e.g. "Best 1v1 Starter Pack"   [Phase 2]
/guides                    Guide index
/guides/install            How to install YR maps — major search traffic
/community                 Discord invite, rules, what happens where
/about                     Who you are, why the site exists
/download/<slug>/<file>    Counted download endpoint
/submit                    Map submissions from other designers            [Phase 3]
/admin/*                   Your publishing back-office (login required)
```

## 5. The map page

The single most important template. Every map page shows:

- Large **minimap render** plus 2–3 in-game screenshots
- **Download button** with file size and download count
- **Spec table** — players, map size, theater, game, modes, tech structures,
  oil derricks, ore/gem density, starting positions, CnCNet compatibility
- **Designer's notes** — your own words on how the map is meant to play.
  This is the part no competitor can copy. Never ship a map without it.
- **Version and changelog** — maps get revised; show the history
- **Install instructions** inline, not just linked
- **Comments** (Phase 2)
- **Related maps** by tag and player count

## 6. Community plan — phased deliberately

| Phase | What | Why |
|---|---|---|
| Launch | Discord server linked prominently | RA2's community already lives on Discord (CnCNet, Mental Omega, Project Phantom). Meet players where they are instead of asking them to register somewhere new. |
| Phase 2 | Per-map comments with moderation | Map-specific feedback, visible to search engines, no empty-room problem |
| Phase 3 | Accounts, designer profiles, map submissions | Turns visitors into contributors |
| Phase 4 | On-site forum, ratings, map of the month | Only once there is enough traffic that it will not look abandoned |

## 7. Technical decisions

- **Custom PHP**, no framework — full control, nothing to fight, trivial to
  deploy on shared hosting. Structured as a small MVC so it stays maintainable
  as features are added.
- **Application code lives outside `public_html/`** so source and config are
  never web-readable.
- **PDO everywhere**, prepared statements only. MySQL in production,
  SQLite for local development, one codebase.
- **Pretty URLs** via `.htaccess` front-controller rewriting.
- **Uploads** are validated by extension allowlist and size, stored with
  generated filenames, and served through a PHP endpoint that counts downloads.
  Upload directories have script execution disabled.
- **No build step.** Deployment is `git pull` on the host (cPanel Git Version
  Control) or an FTP upload. Nothing to compile.

See `docs/ARCHITECTURE.md` for the directory layout and database schema, and
`docs/DEPLOYMENT.md` for the Namecheap setup.

## 8. Roadmap

### Phase 1 — MVP (ship this)
- [x] Project scaffold, router, database layer, view layer
- [x] Database schema
- [ ] Home, browse, map detail, install guide, about, community pages
- [ ] Counted download endpoint
- [ ] Admin login + map create/edit/publish with file and image upload
- [ ] 5–10 of your own maps published
- [ ] Deployed to bankofyrmaps.com

### Phase 2 — Depth
- [ ] Comments with moderation
- [ ] Collections / map packs
- [ ] Search improvements, sorting, tag pages
- [ ] OpenGraph images so Discord and Reddit links look right
- [ ] sitemap.xml, structured data, analytics

### Phase 3 — Community
- [ ] User accounts, designer profiles
- [ ] Map submission flow with a moderation queue
- [ ] Mapping tutorials
- [ ] Newsletter or Discord webhook announcing new maps

### Phase 4 — Only if earned by traffic
- [ ] Forum
- [ ] Ratings and reviews
- [ ] Tournament / ladder integration

## 9. Getting found

- One indexable page per map, map name in the title and URL
- `/guides/install` targets "how to install ra2 maps" and similar queries
- Structured data on map pages
- `sitemap.xml` and OpenGraph tags
- Launch traffic comes from posting new maps to r/commandandconquer, CnCNet
  forums and Discord servers — not from Google. Google follows later.
