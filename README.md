# Everything4Cats

A WordPress affiliate site about cats: blog content and product reviews, at
`everything4cats.ca`.

`PLAN.md` is the plan and the record of decisions. This file is how to run it.

---

## Layout

```
compose.yaml            local test harness: one container, Ubuntu 24.04
docker/                 the image, entrypoint, and Apache vhost
plugins/e4c-compliance/ affiliate disclosure, sponsored rel, Article schema
scripts/provision.sh    bare Ubuntu -> working site, idempotent
scripts/inventory.sh    read-only audit of a server, writes nothing
scripts/test-provision/ proves provision.sh in a throwaway container
scripts/plugins.txt     the plugin baseline, read by provision.sh
```

The base theme is not here yet. Casey is supplying it. See *Adding the theme*.

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

## Provisioning a server

```bash
# ON SERVER
sudo SITE_DOMAIN=everything4cats.ca SITE_TITLE='Everything4Cats' \
     ADMIN_USER=casey ADMIN_EMAIL=you@example.com \
     bash scripts/provision.sh
```

The script prompts for the database password with a silent read, so no
credential enters the shell history, the process list, or this repository.

Before installing anything on a new server, establish the starting point:

```bash
# ON SERVER
sudo bash scripts/inventory.sh
```

Every command in that file is a read. It changes nothing.

---

## Adding the theme

Put the theme directory in the repository root, then tell the provisioner its
name:

```bash
# IN CONTAINER
THEME_DIR=your-theme-dir bash /workspace/scripts/provision.sh
```

Without `THEME_DIR` the script says so and leaves WordPress on its bundled
default. It does not guess, and it does not activate a theme that is not there.

The theme is deployed by **symlink** from the checkout, so a pull is the whole
deploy and there is no copy step to forget.

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

## Build log

Kept in this file, newest last.

### 2026-08-10 — repository established

The container, `provision.sh` and its test harness, and the plugin baseline
were written and the compliance layer was built as a standalone plugin rather
than as theme code, so that a theme change cannot switch off a legal
obligation.

`provision.sh` does not assume a theme exists. It reads `THEME_DIR` and skips
loudly when unset, so the stack can be provisioned before the base theme lands.

### 2026-08-10 — hosting decided, docs retargeted

Hosting settled on AWS Lightsail, OS-only Ubuntu 24.04 blueprint, and
`everything4cats.ca` registered. Docker was reclassified from a deployment
avenue to a test harness, which is the role it actually serves: proving
`provision.sh` twice before the script touches a paid host.

`provision.sh` and `scripts/inventory.sh` were retargeted from an
undecided-provider state to Lightsail, and four cross-references from code into
`PLAN.md` and `AGENTS.md` that no longer resolved were replaced with the facts
they had been fetching, so they cannot dangle again.

Not yet verified on a real host. Nothing has been created on AWS.
