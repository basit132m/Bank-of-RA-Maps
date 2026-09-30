# Bank of YR Maps — Astra child theme

Custom site header replacing Astra's. Self-contained: no page builder, no extra
plugins, no external requests.

## Installing

These files go into your existing Astra child theme folder on the server —
usually `wp-content/themes/astra-child/`.

```
wp-content/themes/astra-child/
├── functions.php              ← REPLACES the generated one (see note below)
├── style.css                  ← REPLACES the generated one
├── inc/site-header.php
└── assets/
    ├── css/header.css
    ├── js/header.js
    ├── fonts/chakra-petch-{500,600,700}.woff2
    └── img/logo.webp
```

> **If you already added your own code** to the generated `functions.php` or
> `style.css`, merge rather than overwrite — paste your existing code at the
> bottom of the new files. The generated Astra child `functions.php` normally
> contains only a parent-stylesheet enqueue, which this version already does.

Upload via cPanel File Manager or FTP. No build step, nothing to compile.

## After uploading

1. **Appearance → Menus** — create a menu and assign it to the **Primary**
   location. Until you do, the header shows a placeholder menu
   (Maps / Guides / Community / About) so it never looks broken.

2. **Appearance → Customize → Site Identity** — upload the logo if you want to
   manage it from WordPress. If you skip this, the bundled
   `assets/img/logo.webp` is used automatically.

3. **Add your Discord invite.** In `functions.php`, find `byrm_header_settings()`
   and set:

   ```php
   $config['discord_url'] = 'https://discord.gg/your-invite';
   ```

   While it is empty the Discord link stays hidden, rather than showing a dead link.

## How Astra's header is replaced

`byrm_replace_astra_header()` unhooks Astra's `astra_header_markup` and hooks
ours in its place, so our header renders inside Astra's own wrappers.

If a future Astra release moves that callback, `remove_action()` returns false
and the code **automatically falls back** to rendering at `wp_body_open` and
hiding Astra's header with CSS. You will never get two headers stacked on top
of each other.

## Customising

Everything configurable is in `byrm_header_config()` in `inc/site-header.php`,
overridable via the `byrm_header_config` filter — status line, Discord URL,
call-to-action label and target, install-guide link.

Colours are CSS custom properties at the top of `assets/css/header.css`,
sampled from the logo artwork:

| Token | Value | Used for |
|---|---|---|
| `--byrm-red` | `#e01f1f` | Primary accent, CTA |
| `--byrm-red-bright` | `#ff3a2a` | Hover states, beacon |
| `--byrm-ember` | `#ff8c1a` | Gradient highlight in the lit edge |
| `--byrm-chrome` | `#c3ced8` | Menu text |
| `--byrm-steel-800` | `#0d1117` | Header background |

## Notes

- **The font is self-hosted** (Chakra Petch, ~30 KB for three weights). No
  request ever reaches Google, which keeps it fast and avoids the EU privacy
  problem with hotlinked Google Fonts.
- **The header works with JavaScript disabled.** JS only adds the condensed
  scroll state, the mobile panel and the search drawer.
- **Keyboard and screen reader support**: skip link, visible focus rings,
  `aria-expanded` on both toggles, Escape closes the menu and the search
  drawer and returns focus to the button that opened it.
- **Reduced motion** is respected — all animation is disabled for visitors who
  ask for that in their OS settings.

## Previewing changes without WordPress

`tools/header-preview/` in the repository root renders this header with stubbed
WordPress functions, so the design can be checked in a browser directly:

```bash
php -S 127.0.0.1:8001 -t .
# then open http://127.0.0.1:8001/tools/header-preview/
```

It is a development tool only and is never deployed.
