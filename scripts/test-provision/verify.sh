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

echo "== Themes (expected from scripts/themes.txt) =="
# Driven from the same file and the same grammar as the provisioner, for the
# reason the plugin loop above is: a check that hardcodes the theme names would
# not catch the two disagreeing. Added 2026-08-14, when the live host had
# twentytwentythree and twentytwentyfour deleted by hand and nothing in this
# harness would have noticed that a rebuild restored them.
while read -r line <&3; do
	line="${line%%#*}"; line="$(echo "$line" | xargs || true)"
	[ -n "$line" ] || continue
	slug="${line%%:*}"
	got="$(W theme get "$slug" --field=status)"
	if [ "$line" = "${slug}:delete" ]; then
		ck "$slug" "MISSING" "${got:-MISSING}"
	else
		ck "$slug present" "1" "$([ -n "$got" ] && echo 1 || echo 0)"
	fi
done 3< /repo/scripts/themes.txt

echo "== Filesystem =="
ck "docroot owner"         "www-data"     "$(stat -c %U /var/www/everything4cats)"
if [ -n "${THEME_DIR:-}" ]; then
	ck "theme is a symlink"     "1" "$([ -L "/var/www/everything4cats/wp-content/themes/$THEME_DIR" ] && echo 1 || echo 0)"
	ck "theme symlink resolves" "1" "$([ -f "/var/www/everything4cats/wp-content/themes/$THEME_DIR/style.css" ] && echo 1 || echo 0)"
fi
# Every plugin directory in the repository must be symlinked and active. Driven
# from the directory listing rather than a hardcoded name, because the
# provisioner deploying only e4c-compliance while plugins/e4c-content sat
# undeployed is exactly the defect this replaced, and a check naming one plugin
# would not have caught it either.
for plugin_path in /repo/plugins/*/; do
	plugin_slug="$(basename "$plugin_path")"
	ck "$plugin_slug is a symlink" "1" "$([ -L "/var/www/everything4cats/wp-content/plugins/$plugin_slug" ] && echo 1 || echo 0)"
	ck "$plugin_slug active"       "active" "$(W plugin get "$plugin_slug" --field=status)"
done
# The review post type comes from e4c-content. If that plugin is not deployed
# this is empty, which is the failure that reads as a broken theme rather than
# as a missing plugin.
ck "review post type registered"  "1" "$(W post-type get review --field=name >/dev/null && echo 1 || echo 0)"
ck "roundup post type registered" "1" "$(W post-type get roundup --field=name >/dev/null && echo 1 || echo 0)"
# The field layer, added 2026-08-13 when secure-custom-fields replaced the
# hand-installed ACF Pro. Until then no version of this harness could see the
# fields at all: the container had no ACF, so every render it proved went
# through the raw post-meta fallback in the theme's e4c_field() and the
# get_field() path shipped untested.
#
# Three checks rather than one because they fail for different reasons. The API
# can be present while the repeater field type is not, which is exactly the
# difference between ACF free and ACF Pro and the single thing that made this
# swap worth verifying. And both can be present while e4c-content's groups
# never register, which is what a bad hook or a fatal in fields.php looks like.
ck "custom fields API present" "1" \
	"$(W eval 'echo function_exists("acf_add_local_field_group") ? 1 : 0;')"
ck "repeater field type available" "1" \
	"$(W eval 'echo function_exists("acf_get_field_type") && acf_get_field_type("repeater") ? 1 : 0;')"
ck "e4c field groups registered" "2" \
	"$(W eval 'echo function_exists("acf_get_local_field_groups") ? count( preg_grep( "/^group_e4c_/", wp_list_pluck( acf_get_local_field_groups(), "key" ) ) ) : 0;')"
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
# search.php renders with no matches, which is the path that exercises its empty
# state and its facet loop against zero counts. A search template that only
# works when something matches is half a template.
ck "GET /?s=litter (search)"             "200" "$(H '/?s=litter')"
# A PHP notice still returns 200, so status alone proves nothing about whether
# the new templates are clean. These fetch the body and count error strings.
E() { curl -s -H 'Host: e4c.test' "http://127.0.0.1$1" | grep -ciE 'fatal error|parse error|warning:|notice:|deprecated:' || true; }
ck "search body has no PHP errors"       "0" "$(E '/?s=litter')"
ck "404 body has no PHP errors"          "0" "$(E /definitely-not-here/)"
ck "home body has no PHP errors"         "0" "$(E /)"
# xmlrpc.php must be refused, and POST is the check that matters. Measured here
# before the block existed: GET returned 405, which reads like a refusal and is
# not one, while POST returned 200 and answered system.listMethods advertising
# system.multicall. A GET-only check would have passed against a live endpoint.
XP() { curl -s -o /dev/null -w '%{http_code}' -X POST -H 'Host: e4c.test' \
	-d '<methodCall><methodName>system.listMethods</methodName></methodCall>' \
	http://127.0.0.1/xmlrpc.php; }
# The fonts theme.json declares must actually be served. Until 2026-08-12 all
# three were absent, so WordPress generated @font-face rules pointing at 404s
# and both families fell back silently. A missing font never errors, it just
# renders in something else, which is why this is asserted rather than eyeballed.
if [ -n "${THEME_DIR:-}" ]; then
	for font in caprasimo-400 figtree-variable figtree-variable-italic; do
		ck "font $font.woff2 served" "200" "$(H "/wp-content/themes/$THEME_DIR/assets/fonts/$font.woff2")"
	done
fi
ck "GET /xmlrpc.php is denied"  "403" "$(H /xmlrpc.php)"
ck "POST /xmlrpc.php is denied" "403" "$(XP)"
# Asserted on the body as well as the status, because a 403 from the wrong
# layer, or a future config that returns an error page listing methods, would
# still pass a status-only check. This is the amplifier, by name.
ck "system.multicall not advertised" "0" "$(curl -s -X POST -H 'Host: e4c.test' \
	-d '<methodCall><methodName>system.listMethods</methodName></methodCall>' \
	http://127.0.0.1/xmlrpc.php | grep -c 'system.multicall' || true)"
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
