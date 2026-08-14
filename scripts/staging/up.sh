#!/usr/bin/env bash
#
# Bring up a browsable staging site from the image the provisioning harness
# already built.
#
#   # ON HOST
#   bash scripts/staging/up.sh
#   # then browse http://e4c.test/
#
# One-time setup, because it needs root and this script deliberately does not:
#
#   echo '127.0.0.1 e4c.test' | sudo tee -a /etc/hosts
#
# WHY THIS REUSES THE TEST IMAGE
#
# e4c-provision-test is a bare Ubuntu 24.04 host that provision.sh built, ran
# against a second time, and that verify.sh then checked. Reusing it means the
# site you click around in is byte-identical to the one the harness passed.
# A staging-specific Dockerfile would be a second description of the same host
# and would drift from the first one the week nobody looked.
#
# WHY THEME AND PLUGIN EDITS ARE LIVE
#
# provision.sh does not copy the theme into WordPress, it symlinks it:
#
#     ln -s "$REPO_DIR/$THEME_DIR" "$THEMES_DIR/$THEME_DIR"
#
# and does the same for every directory under plugins/. Inside the image those
# symlinks point at /repo/theme and /repo/plugins. Bind-mounting the working
# tree over those two paths therefore puts your editor on the other end of the
# symlink WordPress is already following. Save a file, refresh the browser, see
# the change. No rebuild, no copy step, and no change to provision.sh.
#
# Mounted read-only. WordPress has no reason to write to either, and read-only
# means a container cannot damage the working tree.
#
# EPHEMERAL BY DESIGN
#
# No volumes. The database lives in the image layer, so every run starts from
# the same pristine site and `down.sh` discards whatever happened. That is the
# right shape for testing a theme change, and the wrong shape for drafting
# content. Real content comes from scripts/staging/restore.sh instead, which is
# repeatable precisely because the starting point never varies.

set -euo pipefail

IMAGE="e4c-provision-test"
NAME="e4c-staging"
SITE_HOST="e4c.test"
PORT="${PORT:-80}"

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

die() { printf '\nERROR: %s\n' "$1" >&2; exit 1; }

command -v docker >/dev/null 2>&1 || die "docker is not installed or not on PATH."

if ! docker image inspect "$IMAGE" >/dev/null 2>&1; then
	die "Image $IMAGE does not exist. Build it first, which also verifies it:

    bash scripts/test-provision/run.sh"
fi

# Ephemeral means the previous container is garbage, not state worth keeping.
# `|| true` here covers exactly one case, the container not existing, which is
# the normal first run. It cannot mask a failed removal, because the docker run
# below would then fail on the name conflict rather than silently continuing.
docker rm -f "$NAME" >/dev/null 2>&1 || true

echo "==> Starting $NAME from $IMAGE"

# The image has no init system, so the three services provision.sh installed are
# started by hand. php*-fpm is globbed rather than named: provision.sh discovers
# the PHP version at build time from /etc/apache2/conf-available/php*-fpm.conf,
# so hardcoding 8.3 here would break on the next Ubuntu bump.
docker run -d --name "$NAME" \
	-p "${PORT}:80" \
	-v "$REPO_DIR/theme:/repo/theme:ro" \
	-v "$REPO_DIR/plugins:/repo/plugins:ro" \
	"$IMAGE" \
	bash -c '
		# Deliberately no set -e. A service that fails to start must not kill
		# the container: the logs and the on-disk state are the only evidence
		# of why, and exiting deletes both. The first run of this script on
		# 2026-08-14 died exactly that way, and the error message claimed the
		# container had been left running when it had already gone.
		# Clear stale runtime state left in the image by the build.
		#
		# docker build commits each RUN layer by killing the container, so
		# mysqld never shuts down cleanly and its socket lock files and pid
		# file are baked into the image still holding PIDs from the build.
		# On a fresh start mysqld reads them and refuses:
		#
		#   [ERROR] [MY-010259] Another process with pid 189 is using unix
		#   socket file
		#   [ERROR] [MY-010268] Unable to setup unix socket lock file
		#   [ERROR] [MY-010119] Aborting
		#
		# Only .pid and .sock.lock are removed. Those are runtime artefacts by
		# definition and are recreated on every start. The databases in
		# /var/lib/mysql are not touched, which matters because they are the
		# provisioned WordPress install this whole image exists to serve.
		rm -f /var/run/mysqld/*.sock.lock /var/run/mysqld/*.pid /var/lib/mysql/*.pid
		service mysql start || echo "E4C: mysql failed to start"
		for f in /etc/init.d/php*-fpm; do
			if [ -x "$f" ]; then "$f" start || echo "E4C: $f failed to start"; fi
		done
		apache2ctl -D FOREGROUND || echo "E4C: apache exited"
		echo "E4C: holding the container open for inspection"
		sleep infinity
	' >/dev/null

# Poll rather than sleep. MySQL start time varies and a fixed sleep is either
# slower than it needs to be or occasionally too short.
echo -n "==> Waiting for the site to answer"
code=""
for _ in $(seq 1 60); do
	code="$(curl -s -o /dev/null -w '%{http_code}' \
		-H "Host: $SITE_HOST" "http://127.0.0.1:${PORT}/" 2>/dev/null || true)"
	case "$code" in
		200|301|302) break ;;
	esac
	echo -n "."
	sleep 1
done
echo

case "$code" in
	200|301|302) ;;
	*)
		echo "--- container log ---" >&2
		docker logs --tail 40 "$NAME" >&2 || true
		# The container is genuinely still up now, so the logs that actually
		# say why are reachable. "service mysql start ... fail" is the init
		# script giving up on its ping loop and says nothing about the cause;
		# mysqld's own error log is where the reason is.
		echo "--- mysqld error log ---" >&2
		docker exec "$NAME" bash -c '
			for f in /var/log/mysql/error.log /var/log/mysqld.log /var/log/syslog; do
				if [ -s "$f" ]; then echo "== $f"; tail -30 "$f"; fi
			done
			echo "== /var/run/mysqld"; ls -la /var/run/mysqld 2>&1 | head
			echo "== /var/lib/mysql owner"; stat -c "%U:%G %n" /var/lib/mysql 2>&1
		' >&2 || true
		die "Site did not answer (last HTTP code: ${code:-none}).
Container is still running. Poke at it with:
    docker exec -it $NAME bash"
		;;
esac

BASE="http://${SITE_HOST}"
[ "$PORT" = "80" ] || BASE="${BASE}:${PORT}"

# Repin WP_HOME and WP_SITEURL to what staging actually serves.
#
# provision.sh sets both as constants in wp-config.php, pinned to https, which
# is correct for the real host where TLS terminates in front of Apache. Staging
# serves plain http on the published port and has no certificate. Left at
# https, the front page still answers, which is why the harness never caught
# this: verify.sh curls paths directly and never follows a generated link. In a
# browser every internal link points at https://e4c.test and nothing listens on
# 443, so the site looks alive and is unnavigable.
#
# Constants, not options. A constant in wp-config.php overrides the stored
# option, so `wp option update siteurl` here would have had no effect whatever,
# which is what the first version of this block did.
echo "==> Pinning WP_HOME and WP_SITEURL to $BASE"
for c in WP_HOME WP_SITEURL; do
	docker exec "$NAME" sudo -u www-data -- \
		wp --path=/var/www/everything4cats config set "$c" "$BASE" --type=constant >/dev/null
done

URL="${BASE}/"

cat <<EOF

==> Staging is up.

    $URL

    theme/   and plugins/   are mounted live. Edit, save, refresh.
    Content: bash scripts/staging/restore.sh <backup-prefix>
    Shell:   docker exec -it $NAME bash
    Stop:    bash scripts/staging/down.sh

    If the browser cannot resolve $SITE_HOST, add it once:
        echo '127.0.0.1 $SITE_HOST' | sudo tee -a /etc/hosts
EOF
