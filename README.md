# Everything4Cats

A WordPress affiliate site about cats: blog content and product reviews, at
`everything4cats.ca`.

`PLAN.md` is the plan and the record of decisions. This file is how to run it.

---

## Layout

```
theme/                  the site theme, symlinked into the webroot on deploy
plugins/e4c-content/    post types, taxonomy, custom fields
plugins/e4c-compliance/ affiliate disclosure, sponsored rel, Article schema
scripts/provision.sh    bare Ubuntu -> working site, idempotent
scripts/inventory.sh    read-only audit of a server, writes nothing
scripts/plugins.txt     the plugin baseline, read by provision.sh
scripts/themes.txt      the theme baseline, read by provision.sh
scripts/test-provision/ proves provision.sh in a throwaway container
scripts/staging/        browsable local site: up, restore, down
backups/                UpdraftPlus sets for restore.sh. Untracked
docker/                 the image, entrypoint, Apache vhost, xmlrpc deny config
compose.yaml            local test harness: one container, Ubuntu 24.04
```

---

## Where this runs

**Production is AWS Lightsail**, an OS-only Ubuntu 24.04 instance. The server
and the AWS account are Casey's alone: every server instruction in these docs
is written out to be run by hand, and no agent opens a shell on the instance or
runs a provider CLI against the account.

**Docker is a test harness, not the deployment target.** Its job is to run
`provision.sh` twice against a throwaway Ubuntu 24.04 image so the script is
proven before it touches a paid host. It costs nothing and burns no credits.

---

## Running the test harness locally

```bash
# ON HOST
docker compose up -d
```

The container answers at <http://localhost:8080>. A shell:

```bash
# ON HOST
docker compose exec web bash
```

WP-CLI inside the container always needs an explicit path:

```bash
# IN CONTAINER
wp --allow-root --path=/var/www/everything4cats option get home
```

WordPress core is downloaded by the entrypoint on first run if the volume is
empty. `wp-config.php` is deliberately **not** generated, because it holds the
database password and that must not come from an image or a file in this
repository. Create it once, by hand or by running `provision.sh` inside the
container.

---

## Testing the provisioner

```bash
# ON HOST
bash scripts/test-provision/run.sh
```

Builds a bare `ubuntu:24.04`, runs `provision.sh` **twice**, then verifies.
Takes a few minutes and downloads WordPress and every plugin for real. A test
that skips the slow parts stops testing the parts that break.

Twice, not once, because once proves it works and twice proves it is
idempotent. Every deploy after the first is a re-run, and the failure mode of a
non-idempotent script is a second database or a prompt hanging forever with no
output.

**What the harness cannot prove:** anything that needs systemd. Swap, ufw,
fail2ban and certbot all report `skipped` in a container rather than falsely
passing. Those steps are proven on the real host only.

---

## Staging

A browsable copy of the site for trying theme and plugin changes before they
reach the live host.

```bash
# ON HOST, once
echo '127.0.0.1 e4c.test' | sudo tee -a /etc/hosts

# ON HOST
bash scripts/staging/up.sh          # http://e4c.test/
bash scripts/staging/restore.sh     # load an UpdraftPlus backup into it
bash scripts/staging/down.sh
```

Runs the image the harness above already built, so what you click around in is
byte-identical to what passed verification. There is no second Dockerfile to
drift.

**Theme and plugin edits are live.** `provision.sh` symlinks the theme and every
repo plugin out of the checkout rather than copying them, so `up.sh` bind-mounts
the working tree onto the far end of those symlinks. Save a file, refresh the
browser. Read-only, so the container cannot write back into the tree.

**It is ephemeral.** No volumes. The database lives in an image layer, so every
`up.sh` starts from the same pristine site and `down.sh` throws away whatever
happened. That is right for testing a change and wrong for drafting content:
real content comes from `restore.sh`, which is repeatable exactly because the
starting point never varies.

**`restore.sh` loads the database and uploads only**, never the plugins or
themes archives UpdraftPlus also writes. Those would land production's static
copies on top of the symlinks and destroy the live-edit behaviour. What is
wanted from production is its content and settings, not its code.

Backup sets live in `backups/` off the repository root, and `restore.sh` finds
the set on its own when there is exactly one. It refuses to guess when there are
several. `BACKUP_DIR=` overrides the location.

The dump is production data: real accounts, addresses and password hashes. It
belongs on a local container and nowhere else. `.gitignore` already covers
`*.gz`, `*.zip` and `backup_*`, and those patterns match at any depth, so
`backups/` needs no rule of its own. The script prints no row contents.

### The loop

`up.sh` once per session, `restore.sh` once if you want real content, then
**edit and refresh**. A save to `theme/` or `plugins/` is live on the next page
load and there is nothing to re-run.

Four inputs are baked into the image instead, so a running container cannot see
changes to them. After editing `plugins.txt`, `themes.txt`, `provision.sh` or
anything in `docker/`, rebuild with `scripts/test-provision/run.sh`, then
`down.sh` and `up.sh`.

`down.sh` destroys everything typed into wp-admin, because the database lives in
an image layer. Leave the container up between sessions and run it when a clean
slate is wanted. `restore.sh` replays production, not your edits, so test
content is re-entered rather than recovered.

### Before deploying

`theme/` and `plugins/` reach the server by symlink from the checkout, so a
`git pull` is the whole deploy and a syntax error is live immediately. There is
no PHP binary on the development machine, but the staging container has one and
the working tree is mounted at `/repo`:

```bash
# ON HOST
docker exec e4c-staging php -l /repo/theme/single-review.php
```

Lint every PHP file the diff touches. A parse error inside `plugins/e4c-content`
is not a broken template: that plugin loads on every request, so it takes out
`wp-admin` too and the recovery is over SSH.

**What staging cannot prove:** TLS issuance, DNS, SES delivery, or anything
Google must reach inward for, such as Search Console verification and sitemap
submission. Those are launch-day steps on the real host.

---

## Provisioning a server

```bash
# ON SERVER
sudo SITE_DOMAIN=everything4cats.ca SITE_TITLE='Everything4Cats' \
     ADMIN_USER=casey ADMIN_EMAIL=you@example.com \
     ADMIN_DISPLAY_NAME='SimBuds' \
     bash scripts/provision.sh
```

The script prompts for the database password with a silent read, so no
credential enters the shell history, the process list, or this repository.

`ADMIN_DISPLAY_NAME` is optional and sets the public byline, which WordPress
otherwise leaves equal to `ADMIN_USER`. That default matters more than it looks:
`plugins/e4c-compliance` publishes `display_name` into the Article JSON-LD on
every post, and `user_nicename` becomes the author archive URL, so an unset
byline publishes the login in machine-readable form on every page. Left unset,
the script says so and names the exposure rather than guessing a byline.

Two things this script does only on a **fresh install**, never on a re-run:
setting `blog_public` to `0`, and applying `ADMIN_DISPLAY_NAME`. Both are
guarded because the script is designed to be re-run, and an unconditional
version of either would silently undo a deliberate change on a live site. The
practical consequence is that setting `ADMIN_DISPLAY_NAME` and re-running does
nothing to an existing install. Change the byline on a running site directly:

```bash
# ON SERVER
sudo -u www-data wp --path=/var/www/everything4cats user update <login> \
  --display_name='SimBuds' --user_nicename='simbuds'
```

Before installing anything on a new server, establish the starting point:

```bash
# ON SERVER
sudo bash scripts/inventory.sh
```

Every command in that file is a read. It changes nothing.

---

## Adding the theme

The theme is `theme/`, display name **Everything 4 Cats - Theme**. Tell the
provisioner its directory name:

```bash
# ON SERVER
THEME_DIR=theme bash scripts/provision.sh
```

Without `THEME_DIR` the script says so and leaves WordPress on its bundled
default. It does not guess, and it does not activate a theme that is not there.

The theme is deployed by **symlink** from the checkout, so a pull is the whole
deploy and there is no copy step to forget. Every directory under `plugins/` is
deployed the same way and activated.

### The custom fields plugin is no longer a manual step

`plugins/e4c-content` registers its field groups through ACF's API, and
**Secure Custom Fields** provides it. SCF is WordPress.org's fork of ACF, free,
on the plugin directory, and therefore a normal line in `scripts/plugins.txt`.
The provisioner installs and activates it like everything else.

`fields.php` is written against ACF's API rather than anything fork-specific, so
it works unchanged against either.

Nothing breaks if the plugin is ever absent, which is the reason its absence is
easy to miss. The post types still register, reviews stay published,
`e4c-content` prints an admin notice, and `e4c_field()` falls back to raw post
meta of the same name. What is missing is the editing UI.

**One caveat on that fallback**, measured rather than assumed: it does not
reconstruct repeaters. ACF stores a repeater as a row count in the parent key
plus one entry per index, so a post whose fields were written by the plugin and
then read with the plugin inactive renders the count where the rows should be.
Tracked as a follow-up, and it needs the plugin to be deactivated by hand to
occur at all.

### Design source lives outside this repository

The design canvas, the photography and the logo files are not kept here. The
originals live outside the repository. The only design assets in the tree are
the two the theme actually serves, `theme/assets/everything4cats-logo.png` and
`everything4cats-favicon.png`.

Nothing design-related belongs inside `theme/` beyond those. The theme is
symlinked into the webroot, so anything placed there becomes publicly fetchable.

Colour and type tokens are not kept in a design file either. **`theme.json` is
the single source of truth**: the block editor reads it, `theme/style.css`
resolves every value through it, and a literal duplicated into the stylesheet
would drift from what Gutenberg renders.

---

## The compliance plugin

`plugins/e4c-compliance/` is a real plugin rather than theme code, because
disclosure is a legal obligation and changing how the site looks must not
change whether it discloses.

It does nothing until a programme is joined. Both the `rel="sponsored nofollow"`
tagging and the disclosure key off one list of monetised domains, empty by
default:

```php
add_filter( 'e4c_compliance_affiliate_domains', function ( $domains ) {
	$domains[] = 'chewy.com';
	$domains[] = 'amazon.com';
	return $domains;
} );
```

Keying off a domain list rather than off "any outbound link" is deliberate.
Tagging every outbound link marks editorial citations as paid placements and
prints an affiliate disclosure on articles that earn nothing. Both are
misstatements, and on a review site credibility is the product.

---

## Conventions

| Use | Value |
|---|---|
| Public name | `Everything4Cats` |
| Domain | `everything4cats.ca` |
| Machine slug, text domain | `e4c` |
| PHP function prefix | `e4c_` |
| PHP constant prefix | `E4C_` |
| WordPress path | `/var/www/everything4cats` |
| Local database / user | `everything4cats` |
| Container | `e4c-web` |

Command labels used throughout the docs:

- `# ON HOST` — your desktop terminal.
- `# IN CONTAINER` — a shell inside the local test container.
- `# ON SERVER` — an SSH session on the Lightsail instance.
- `# IN AWS CONSOLE` — the AWS or Lightsail web console.
- `# IN REGISTRAR DNS` — the domain registrar's DNS panel.
- `# IN WP-ADMIN` — the WordPress dashboard.
- `# IN MYSQL` — the `mysql>` prompt.
- `# WP-CLI` — the `wp` command running as the web user.

More than one environment exists. Every server, MySQL and WP-CLI instruction
names the environment it targets, and WP-CLI always carries an explicit
`--path`. The common way to damage a live WordPress site is to run a correct
command in the wrong environment.

---

## History and planning live elsewhere

This file is the developer reference: how to run things, and how the pieces fit
together. It deliberately carries no narrative.

- **`IMPLEMENT.md`** — the phase plan, the phase reports, and the build log:
  every step taken against the live host, in order, with the evidence.
- **`PLAN.md`** — the idea, the decisions behind it, and the order of work.
- **`QUESTIONS.md`** — background questions answered along the way. Untracked
  by design.

The build log moved out of this file on 2026-08-14. It was 1,069 of its 1,366
lines, so a reader looking for how to run the test harness was scrolling past
sixteen dated server-build entries to find it.
