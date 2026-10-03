#!/bin/bash
#
# One-time WordPress wiring, run on the server over SSH.
#
#   ssh -p 21098 USER@SERVER 'bash -s' < tools/deploy/wp-setup.sh
#
# It creates the pages the theme's templates expect and puts them in the
# Primary menu. Everything it does is checked first, so running it twice is
# harmless — it reports "already there" and moves on. It never deletes a page,
# a menu or a menu item.
#
# WP-CLI is not preinstalled on Namecheap shared hosting, so the first run
# downloads wp-cli.phar into ~/bin and uses it from there.

set -euo pipefail

WP_PATH="${WP_PATH:-$HOME/public_html}"
BIN="$HOME/bin"
WP="$BIN/wp-cli.phar"

say() { printf '  %s\n' "$*"; }

# ---------------------------------------------------------------- wp-cli ----

mkdir -p "$BIN"

if [ ! -f "$WP" ]; then
	echo "Fetching WP-CLI..."
	curl -fsSL -o "$WP" https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
	chmod +x "$WP"
fi

wp() { php "$WP" --path="$WP_PATH" --skip-plugins=akismet "$@"; }

if ! wp core is-installed 2>/dev/null; then
	echo "No WordPress found at $WP_PATH."
	echo "Set WP_PATH to the folder holding wp-config.php and run this again."
	exit 1
fi

echo
echo "WordPress found at $WP_PATH"
echo

# ----------------------------------------------------------------- pages ----

# slug|Title — add a line here and the next run creates it.
PAGES="
about|About us
community|Community
guides|Guides
map-editor|Map editor
"

declare -A PAGE_ID

echo "Pages"

while IFS='|' read -r slug title; do
	[ -z "$slug" ] && continue

	id="$(wp post list --post_type=page --name="$slug" --field=ID --posts_per_page=1 2>/dev/null || true)"
	id="$(printf '%s' "$id" | tr -d '[:space:]')"

	if [ -n "$id" ]; then
		say "already there: /$slug/  (ID $id)"
	else
		id="$(wp post create \
			--post_type=page \
			--post_title="$title" \
			--post_name="$slug" \
			--post_status=publish \
			--porcelain)"
		say "created: /$slug/  (ID $id)"
	fi

	PAGE_ID["$slug"]="$id"
done <<< "$PAGES"

# ------------------------------------------------------------------ menu ----

echo
echo "Primary menu"

MENU="$(wp menu location list --format=csv --fields=location,menu_slug 2>/dev/null \
	| awk -F, '$1=="primary"{print $2}' | head -1)"

if [ -z "$MENU" ]; then
	# Nothing assigned to the Primary location yet, so make one and assign it.
	if ! wp menu list --fields=slug --format=csv 2>/dev/null | grep -qx 'main-menu'; then
		wp menu create "Main menu" >/dev/null
	fi
	MENU=main-menu
	wp menu location assign "$MENU" primary >/dev/null
	say "created a menu and assigned it to the Primary location"
else
	say "using the menu already assigned: $MENU"
fi

# What is in the menu now, so nothing gets added twice.
EXISTING="$(wp menu item list "$MENU" --fields=object_id --format=csv 2>/dev/null | tail -n +2 || true)"

add_page() {
	local slug="$1" id="${PAGE_ID[$1]:-}"

	[ -z "$id" ] && return 0

	if printf '%s\n' "$EXISTING" | grep -qx "$id"; then
		say "already in the menu: $slug"
		return 0
	fi

	wp menu item add-post "$MENU" "$id" >/dev/null
	say "added to the menu: $slug"
}

# The maps archive is a post type archive, not a page, so it is added by name.
if ! wp menu item list "$MENU" --fields=title --format=csv 2>/dev/null | grep -qi '^"\?Maps"\?$'; then
	wp menu item add-post-type "$MENU" map --title="Maps" >/dev/null 2>&1 \
		&& say "added to the menu: maps archive" \
		|| say "skipped the maps archive — add it by hand in Appearance, Menus"
else
	say "already in the menu: maps archive"
fi

add_page guides
add_page map-editor
add_page community
add_page about

# --------------------------------------------------------------- tidy up ----

echo
wp rewrite flush --hard >/dev/null 2>&1 && echo "Permalinks flushed." || true
wp cache flush >/dev/null 2>&1 || true

echo
echo "Done. Check https://www.bankofyrmaps.com/about/"
