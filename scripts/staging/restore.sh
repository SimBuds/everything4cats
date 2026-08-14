#!/usr/bin/env bash
#
# Restore an UpdraftPlus backup into the running staging container.
#
#   # ON HOST
#   bash scripts/staging/up.sh
#   bash scripts/staging/restore.sh                 # auto-discovers one backup set
#   bash scripts/staging/restore.sh backup_2026-...-1512_Everything4Cats_7d5f4c50a6ac
#
# Backup sets live in backups/ off the repository root. Override with
# BACKUP_DIR= if they are somewhere else. The directory is not tracked and its
# contents never should be: .gitignore's *.gz, *.zip and backup_* patterns
# match at any depth, so files inside backups/ are already covered.
#
# DATABASE AND UPLOADS ONLY.
#
# UpdraftPlus also produces plugins, themes and others archives. Those are
# deliberately never restored. The staging container reaches the theme and the
# repo plugins through symlinks that provision.sh created into /repo, and
# up.sh bind-mounts the working tree onto the other end of them. Unpacking
# production's copies over the top would replace those symlinks with static
# files and destroy the live-edit behaviour that is the whole point. What is
# wanted from production is its content and its settings, not its code.
#
# THE BACKUP IS PRODUCTION DATA.
#
# The dump carries user accounts, e-mail addresses, password hashes and
# whatever the plugins keep in wp_options. It is fine on a local container and
# it must not leave one, so this script never prints row contents: the only
# database output it produces is the change report from wp search-replace,
# which is table, column and count. The backup files themselves are already
# covered by .gitignore (*.gz, *.zip, backup_*) and must stay that way.
#
# REPEATABLE BECAUSE STAGING IS EPHEMERAL.
#
# up.sh always starts from the same pristine image, so restoring is a fresh
# import every time rather than a merge into whatever the last session left
# behind. To reset, run down.sh, up.sh, then this again.

set -euo pipefail

NAME="e4c-staging"
SITE_HOST="e4c.test"
PORT="${PORT:-80}"
WP_DIR="/var/www/everything4cats"

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
BACKUP_DIR="${BACKUP_DIR:-$REPO_DIR/backups}"

die() { printf '\nERROR: %s\n' "$1" >&2; exit 1; }

WP()  { docker exec "$NAME" sudo -u www-data -- wp --path="$WP_DIR" "$@"; }
SH()  { docker exec "$NAME" bash -c "$1"; }

command -v docker >/dev/null 2>&1 || die "docker is not on PATH."

docker container inspect "$NAME" >/dev/null 2>&1 \
	|| die "$NAME is not running. Start it first:

    bash scripts/staging/up.sh"

# ---------------------------------------------------------------- locate set
PREFIX="${1:-}"

[ -d "$BACKUP_DIR" ] || die "No such directory: $BACKUP_DIR
Put the UpdraftPlus files there, or set BACKUP_DIR= to point somewhere else."

if [ -z "$PREFIX" ]; then
	# Auto-discovery only when the answer is unambiguous. Picking "the newest"
	# out of several would eventually restore something the user did not mean.
	mapfile -t found < <(find "$BACKUP_DIR" -maxdepth 1 -name '*-db.gz' -printf '%f\n' | sort)
	[ "${#found[@]}" -gt 0 ] || die "No *-db.gz found in $BACKUP_DIR."
	if [ "${#found[@]}" -gt 1 ]; then
		printf 'Several backup sets found in %s. Pass one explicitly:\n' "$BACKUP_DIR" >&2
		printf '    %s\n' "${found[@]%-db.gz}" >&2
		exit 1
	fi
	PREFIX="${found[0]%-db.gz}"
fi

# basename so that passing a full path, which is the natural thing to do after
# tab-completing one, resolves against BACKUP_DIR rather than being joined onto
# it twice.
PREFIX="$(basename "$PREFIX")"
DB_GZ="$BACKUP_DIR/${PREFIX}-db.gz"
UPLOADS_ZIP="$BACKUP_DIR/${PREFIX}-uploads.zip"

[ -f "$DB_GZ" ] || die "Not found: $DB_GZ"

echo "==> Restoring $PREFIX"
echo "    database: $(du -h "$DB_GZ" | cut -f1)"
[ -f "$UPLOADS_ZIP" ] \
	&& echo "    uploads:  $(du -h "$UPLOADS_ZIP" | cut -f1)" \
	|| echo "    uploads:  none alongside this dump, skipping"

# ------------------------------------------------------------------ database
echo "==> Importing database"
docker cp "$DB_GZ" "$NAME:/tmp/e4c-db.gz" >/dev/null
# 644 because wp runs as www-data and docker cp lands the file owned by root.
SH 'gunzip -f -c /tmp/e4c-db.gz > /tmp/e4c-db.sql && chmod 644 /tmp/e4c-db.sql'
WP db import /tmp/e4c-db.sql >/dev/null
SH 'rm -f /tmp/e4c-db.gz /tmp/e4c-db.sql'

# Read the URL out of the database, NOT with `wp option get siteurl`.
#
# provision.sh writes WP_HOME and WP_SITEURL into wp-config.php as constants,
# and a constant overrides the stored option. `wp option get siteurl` therefore
# returns e4c.test regardless of what was just imported. The first restore on
# 2026-08-14 did exactly that: it searched the database for the value it was
# about to write, replaced nothing, printed
#
#   ==> Rewriting URLs: e4c.test -> e4c.test
#   Success: Made 0 replacements.
#
# and reported success while every production URL stayed in the content.
#
# Querying the options table directly is also the only honest table-prefix
# check. The old version claimed to be one and could not have been: the
# constant satisfied it whether or not a single table imported.
PREFIX="$(WP db prefix 2>/dev/null | tr -d '\r\n' || true)"
[ -n "$PREFIX" ] || die "Could not read the table prefix from wp-config.php."

OLD_URL="$(WP db query \
	"SELECT option_value FROM ${PREFIX}options WHERE option_name = 'siteurl'" \
	--skip-column-names --silent 2>/dev/null | tr -d '\r' | head -n1 || true)"

if [ -z "$OLD_URL" ]; then
	echo "--- tables present after import ---" >&2
	WP db tables --all-tables 2>/dev/null | head -20 >&2 || true
	die "Imported, but ${PREFIX}options holds no siteurl row. Most likely the
dump uses a different table prefix than wp-config.php expects. Compare the
table names above with:
    docker exec $NAME grep table_prefix $WP_DIR/wp-config.php"
fi

# ------------------------------------------------------------------- rewrite
NEW_URL="http://${SITE_HOST}"
[ "$PORT" = "80" ] || NEW_URL="${NEW_URL}:${PORT}"

OLD_HOST="$(printf '%s' "$OLD_URL" | sed -E 's#^https?://##; s#/$##')"

echo "==> Rewriting URLs: ${OLD_HOST} -> ${SITE_HOST}"

# Both schemes, because content written over the life of the site can contain
# either. wp search-replace is used rather than sed precisely because it walks
# serialised values and re-lengths them; sed would corrupt every serialised
# option Complianz and Rank Math store.
#
# Only the scheme+host forms are replaced, never the bare domain. The bare form
# appears in e-mail addresses such as the contact address on How we test, and
# rewriting those would quietly corrupt real content.
for scheme in https http; do
	WP search-replace "${scheme}://${OLD_HOST}" "$NEW_URL" \
		--all-tables --precise --skip-columns=guid --report-changed-only || true
done

# --------------------------------------------------------------------- files
if [ -f "$UPLOADS_ZIP" ]; then
	echo "==> Restoring uploads"
	docker cp "$UPLOADS_ZIP" "$NAME:/tmp/e4c-uploads.zip" >/dev/null
	# python3 rather than unzip: python3 is in the base image by definition,
	# unzip is not in provision.sh's package list and adding a dependency to
	# the provisioner for a staging convenience would be the wrong trade.
	#
	# Extracted to a staging directory first because UpdraftPlus is not
	# consistent about whether the archive is rooted at uploads/ or at its
	# contents. The path is hardcoded and inside an ephemeral container, so the
	# rm -rf below cannot reach the working tree.
	SH '
		set -e
		rm -rf /tmp/e4c-up && mkdir -p /tmp/e4c-up
		python3 -m zipfile -e /tmp/e4c-uploads.zip /tmp/e4c-up
		SRC=/tmp/e4c-up
		# if/fi, not [ -d ] && ... : under set -e the false branch would abort.
		if [ -d /tmp/e4c-up/uploads ]; then SRC=/tmp/e4c-up/uploads; fi
		DEST=/var/www/everything4cats/wp-content/uploads
		rm -rf "$DEST" && mkdir -p "$DEST"
		cp -a "$SRC/." "$DEST/"
		chown -R www-data:www-data "$DEST"
		rm -rf /tmp/e4c-up /tmp/e4c-uploads.zip
	'
fi

# -------------------------------------------------------------------- finish
# Permalinks are stored as rewrite rules in the database, so the imported set is
# production's. Flushing regenerates them against this install.
WP rewrite flush >/dev/null
WP cache flush >/dev/null 2>&1 || true

echo
echo "==> Restored."
# The stored option and the wp-config constant are reported separately because
# they are different values by design and conflating them is what broke the
# first restore. The constant is what WordPress actually serves.
printf '    db siteurl:   %s\n' "$(WP db query \
	"SELECT option_value FROM ${PREFIX}options WHERE option_name = 'siteurl'" \
	--skip-column-names --silent 2>/dev/null | tr -d '\r' | head -n1 || echo '?')"
printf '    served as:    %s\n' "$(WP eval 'echo WP_HOME;' 2>/dev/null || echo '?')"
printf '    posts:        %s\n' "$(WP post list --post_type=any --post_status=publish --format=count 2>/dev/null || echo '?')"
printf '    theme:        %s\n' "$(WP theme list --status=active --field=name 2>/dev/null || echo '?')"
# Menus are the thing most likely to look "not set up" after a restore, because
# the assignment lives in theme_mods_<stylesheet> rather than with the menu.
printf '    menus:        %s\n' "$(WP menu list --format=count 2>/dev/null || echo '?')"
printf '    locations:    %s\n' "$(WP eval \
	'$l = get_nav_menu_locations(); echo $l ? implode( ", ", array_keys( array_filter( $l ) ) ) : "none assigned";' \
	2>/dev/null || echo '?')"
echo
echo "    Browse: ${NEW_URL}/"
echo "    Reset:  bash scripts/staging/down.sh && bash scripts/staging/up.sh"
