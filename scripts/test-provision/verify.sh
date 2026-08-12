#!/usr/bin/env bash
# Read-only checks against whatever provision.sh actually produced.
# Prints PASS/FAIL per check and exits non-zero if any failed.

FAILS=0

# Each Docker RUN is a fresh container, so nothing provision.sh started is still
# running here. Bring the services up before checking anything that needs them.
# A real host has systemd and would already have them running.
FPM_CONF="$(basename "$(ls /etc/apache2/conf-available/php*-fpm.conf | head -n1)" .conf)"

# Docker freezes /var/run into the image layer, so the pid files written when
# provision.sh started these services are still here, pointing at pids that
# belong to unrelated processes in this new container. The init scripts read
# them, conclude the service is already up, and exit 0 without starting
# anything. A real host has /run on tmpfs and boots with it empty, so this is
# purely an artifact of testing in a container. Cleared, not worked around.
rm -f /var/run/apache2/apache2.pid /run/php/*.pid /var/run/mysqld/*.pid

service mysql start        >/dev/null 2>&1 || true
[ -d /run/mysqld ] && chmod 0755 /run/mysqld
service "$FPM_CONF" start  >/dev/null 2>&1 || true
service apache2 start      >/dev/null 2>&1 || true
sleep 4

ck() {
	local label="$1" expected="$2" actual="$3"
	if [ "$expected" = "$actual" ]; then
		printf '  PASS  %-42s %s\n' "$label" "$actual"
	else
		printf '  FAIL  %-42s got=%s want=%s\n' "$label" "$actual" "$expected"
		FAILS=$((FAILS+1))
	fi
}

W() { sudo -u www-data -- wp --path=/var/www/everything4cats "$@" 2>/dev/null; }

echo "== WordPress =="
ck "core installed"        "1"            "$(W core is-installed && echo 1 || echo 0)"
# Theme checks are conditional: the base theme is supplied separately and is
# not in the repository yet, so provision.sh deliberately leaves WordPress on
# its bundled default. Asserting a theme that does not exist would make the
# harness fail for the one reason that is currently correct.
if [ -n "${THEME_DIR:-}" ]; then
	ck "active theme"      "$THEME_DIR" "$(W theme list --status=active --field=name)"
else
	printf '  INFO  %-42s %s\n' "active theme (THEME_DIR unset)" "$(W theme list --status=active --field=name)"
fi
ck "permalink structure"   "/%postname%/" "$(W option get permalink_structure)"
ck "WP_HOME"               "https://e4c.test" "$(W eval 'echo WP_HOME;')"
ck "DISALLOW_FILE_EDIT"    "1"            "$(W eval 'echo DISALLOW_FILE_EDIT ? 1 : 0;')"
# A fresh install must not be indexable. WordPress ships blog_public=1, and a
# brand new site carrying only Hello World and Sample Page is exactly what
# should not be crawled. Flipping it to 1 is a launch step.
ck "blog_public (noindex)" "0"            "$(W option get blog_public)"
# The byline must not be the login. e4c-compliance publishes display_name into
# the Article JSON-LD, and user_nicename becomes the author archive URL, so
# either one matching ADMIN_USER leaks the login in machine-readable form.
# Compared against the login rather than against a literal, so this check fails
# on the thing that actually matters instead of on a renamed fixture.
ck "display_name is not the login" "1" "$([ "$(W user get casey --field=display_name)" != "casey" ] && echo 1 || echo 0)"
ck "user_nicename is not the login" "1" "$([ "$(W user get casey --field=user_nicename)" != "casey" ] && echo 1 || echo 0)"

echo "== Plugins (expected from scripts/plugins.txt) =="
while read -r line <&3; do
	line="${line%%#*}"; line="$(echo "$line" | xargs || true)"
	[ -n "$line" ] || continue
	slug="${line%%:*}"
	want_status="active"
	[ "$line" = "${slug}:inactive" ] && want_status="inactive"
	# A ":delete" entry asserts absence. `wp plugin get` prints nothing for a
	# plugin that is not there, so the empty capture collapses to MISSING and the
	# check compares MISSING against MISSING. Driving this from the same file and
	# the same loop as the install cases is what stops the harness and the
	# provisioner from disagreeing about what plugins.txt means.
	[ "$line" = "${slug}:delete" ] && want_status="MISSING"
	got="$(W plugin get "$slug" --field=status)"
	ck "$slug" "$want_status" "${got:-MISSING}"
done 3< /repo/scripts/plugins.txt

echo "== Filesystem =="
ck "docroot owner"         "www-data"     "$(stat -c %U /var/www/everything4cats)"
if [ -n "${THEME_DIR:-}" ]; then
	ck "theme is a symlink"     "1" "$([ -L "/var/www/everything4cats/wp-content/themes/$THEME_DIR" ] && echo 1 || echo 0)"
	ck "theme symlink resolves" "1" "$([ -f "/var/www/everything4cats/wp-content/themes/$THEME_DIR/style.css" ] && echo 1 || echo 0)"
fi
# The compliance plugin is symlinked from the repository the same way a theme
# is, and unlike the theme it exists today. This is the check that proves the
# repository-linked-extension path works at all.
ck "compliance plugin is a symlink" "1" "$([ -L /var/www/everything4cats/wp-content/plugins/e4c-compliance ] && echo 1 || echo 0)"
ck "compliance plugin active"       "active" "$(W plugin get e4c-compliance --field=status)"
ck ".htaccess has rules"   "1"            "$(grep -qc 'RewriteEngine On' /var/www/everything4cats/.htaccess 2>/dev/null && echo 1 || echo 0)"
ck "wp-config.php present" "1"            "$([ -f /var/www/everything4cats/wp-config.php ] && echo 1 || echo 0)"
ck "wp-config.php owner"   "www-data"     "$(stat -c %U /var/www/everything4cats/wp-config.php)"
printf '  INFO  %-42s %s\n' "wp-config.php mode" "$(stat -c %a /var/www/everything4cats/wp-config.php)"

echo "== Apache =="
ck "configtest"            "Syntax OK"    "$(apache2ctl configtest 2>&1 | tail -n1)"
ck "everything4cats enabled"     "1"            "$([ -L /etc/apache2/sites-enabled/everything4cats.conf ] && echo 1 || echo 0)"
ck "000-default disabled"  "0"            "$([ -e /etc/apache2/sites-enabled/000-default.conf ] && echo 1 || echo 0)"
ck "ServerName substituted" "1"           "$(grep -qc 'ServerName e4c.test' /etc/apache2/sites-available/everything4cats.conf && echo 1 || echo 0)"
ck "ServerAlias substituted" "1"          "$(grep -qc 'ServerAlias www.e4c.test' /etc/apache2/sites-available/everything4cats.conf && echo 1 || echo 0)"
for m in proxy_fcgi setenvif rewrite headers; do
	ck "mod_$m enabled" "1" "$(apache2ctl -M 2>/dev/null | grep -qc "${m}_module" && echo 1 || echo 0)"
done

echo "== HTTP =="
H() { curl -s -o /dev/null -w '%{http_code}' -H 'Host: e4c.test' "http://127.0.0.1$1"; }
# A 200 here proves the whole chain: Apache routed to PHP-FPM, PHP ran, and
# WordPress bootstrapped against the database.
ck "GET /"                 "200"          "$(H /)"
# These two are the pretty-permalink test. Without a working .htaccess rewrite
# they 404 while the home page keeps returning 200, which is exactly the failure
# mode the explicit .htaccess write in provision.sh exists to prevent.
ck "GET /hello-world/ (post permalink)"  "200" "$(H /hello-world/)"
ck "GET /sample-page/ (page permalink)"  "200" "$(H /sample-page/)"
ck "GET /definitely-not-here/ is a 404"  "404" "$(H /definitely-not-here/)"
# Served through the symlink, so this fails if the link dangles. Conditional for
# the same reason as the other theme checks: there is no theme yet.
if [ -n "${THEME_DIR:-}" ]; then
	assets="$(curl -s -H 'Host: e4c.test' http://127.0.0.1/ \
		| grep -oc "wp-content/themes/$THEME_DIR/" || true)"
	ck "theme assets served via symlink" "1" "$([ "${assets:-0}" -gt 0 ] && echo 1 || echo 0)"
fi

echo
if [ "$FAILS" -eq 0 ]; then
	echo "ALL CHECKS PASSED"
else
	echo "$FAILS CHECK(S) FAILED"
fi
exit "$FAILS"
