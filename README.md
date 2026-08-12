# Everything4Cats

A WordPress affiliate site about cats: blog content and product reviews, at
`everything4cats.ca`.

`PLAN.md` is the plan and the record of decisions. This file is how to run it.

---

## Layout

```
compose.yaml            local test harness: one container, Ubuntu 24.04
docker/                 the image, entrypoint, Apache vhost, xmlrpc deny config
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

### ACF Pro is a manual step

`plugins/e4c-content` registers its field groups through Advanced Custom Fields
Pro, which is commercial and not on wordpress.org, so `scripts/plugins.txt`
cannot install it and the provisioner cannot either. Install and licence it by
hand in wp-admin.

Nothing breaks without it, which is the reason this is easy to miss. The post
types still register, reviews stay published, `e4c-content` prints an admin
notice, and the theme's `e4c_field()` falls back to raw post meta of the same
name. What is missing is the editing UI: verdict, pros, cons and the spec table
have no fields in the editor until ACF Pro is active. That fallback is proven
rather than assumed, because the container has no ACF and renders a review with
every field populated.

### Design source lives outside this repository

The design canvas, the photography and the logo files are not kept here. They
were removed on 2026-08-12 once the theme had been extracted from them, and the
originals live outside the repository.

`figma/` is the exception and stays, because it is a handoff format rather than
a working file: the W3C token export, the two Tokens Studio theme files, and the
frame bundle for html.to.design.

Nothing design-related belongs inside `theme/`. The theme is symlinked into the
webroot, so anything placed there becomes publicly fetchable.

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

EC2 was briefly chosen and then reversed the same day, after the two were priced
against each other. The numbers are in `PLAN.md` under *Decided* so the
comparison is not run a third time.

#### Step 1 — Lightsail instance created and baselined ✅

**Goal:** a bare Ubuntu 24.04 host in Canada (Central), reachable over SSH, with
a stable address and the web ports open, and its starting state recorded.

**Why it matters:** every later change is read against this baseline. Without a
record of what the image shipped, a problem three steps from now cannot be told
apart from something that was always there. The static IP matters for a reason
that is invisible today: the default address is released on stop and start, and
that failure surfaces weeks later as a dead site and a TLS renewal that cannot
validate.

**Commands:**

```bash
# ON HOST
ssh-keygen -t ed25519 -C "everything4cats" -f ~/.ssh/everything4cats
```

```bash
# ON HOST
scp -i ~/.ssh/everything4cats scripts/inventory.sh ubuntu@<static-ip>:/tmp/inventory.sh \
  && ssh -i ~/.ssh/everything4cats ubuntu@<static-ip> 'sudo bash /tmp/inventory.sh'
```

The key pair was generated locally and only the public half was uploaded, so the
private key has never left the workstation. Console work (instance creation,
static IP, firewall) is browser-only and was done by hand.

**Verify:**

- Ubuntu 24.04.4 LTS, x86-64, 2 vCPU.
- `1.9Gi` memory and a `58G` root filesystem, which together pin the instance to
  the 2 GB plan. The next tier down would have reported roughly 1 GB and 40 G,
  so the plan is confirmed from the shell rather than from the console.
- `Hardware Model: t3.small`, confirming Lightsail runs on EC2 underneath. This
  is the same hardware class that costs more on raw EC2 once the volume and the
  public address are billed separately.
- apache2, nginx, php, php-fpm, mysql, mariadb, wp, docker and certbot all
  absent. Nothing listening on 80 or 443. The host is genuinely clean, so the
  provisioner's do-not-clobber guard will pass without warnings.
- `passwordauthentication no` and pubkey-only SSH as delivered.
- `Swap: 0B`, ufw `inactive`, fail2ban `not installed`. These are the three
  steps the container could only skip, and they will actually execute here.
- Static IP attached, ports 80 and 443 open for IPv4 and IPv6, region Canada
  (Central), all confirmed in the console.

**Q&A:**

- *Lightsail or EC2?* Settled on Lightsail, after a reversal. At 2 GB the
  Lightsail plan is $12 flat against roughly $21 on EC2 once the volume and the
  public IPv4 address are added, and it includes 3 TB of transfer where EC2
  includes 100 GB. Transfer was the deciding line, because it is the only cost
  here that scales with success rather than with time.
- *Own SSH key or a downloaded one?* Own key, uploaded. The private half never
  leaves the workstation, and there is no `.pem` to lose. This also matches the
  existing per-project key convention on the workstation.
- *Automatic snapshots?* Not enabled. Deliberate for now, since there is no data
  to lose. It must be turned on before any real content exists, and it is
  tracked as its own phase rather than left as a note here.
- *Monthly cost recorded?* No. The plan price is $12 and is treated as the
  working figure for the runway. Confirm against the first real bill.

#### Step 2 — DNS pointed at the instance ✅

**Goal:** `everything4cats.ca` and `www` both resolve to the instance, before
WordPress is installed rather than after.

**Why it matters:** provisioning against a bare IP and moving to the domain
later means a database-wide URL migration, because WordPress stores absolute
URLs. Pointing DNS first makes that migration unnecessary. Propagation also
takes time that is free to spend now and expensive to spend later, and certbot
cannot issue a certificate until both names already resolve.

**Commands:** records created by hand at Namecheap, on their BasicDNS
nameservers. An `A` on the apex to the static IP, and a `CNAME` on `www` to the
apex. Verified with a DNS-over-HTTPS query against two public resolvers, which
bypasses the local cache and shows what Let's Encrypt will see:

```bash
# ON HOST
curl -sS -H 'accept: application/dns-json' \
  "https://cloudflare-dns.com/dns-query?name=everything4cats.ca&type=A"
curl -sS "https://dns.google/resolve?name=www.everything4cats.ca&type=A"
```

**Verify:** four checks, two names against two independent resolvers, all
`Status: 0`. The apex returns a single A record with `TTL 300`. `www` returns a
type-5 CNAME to `everything4cats.ca.` followed by the same A record, which is
the shape certbot needs to validate both names from one request. Google's
response named the authoritative server as `dns2.registrar-servers.com`,
confirming the zone is served by Namecheap BasicDNS rather than by a leftover
custom or web-hosting nameserver set. No parking record and no second A record
appeared in any answer.

**Q&A:**

- *Two A records, or A plus CNAME?* A plus CNAME, which is what was built. A
  single certificate still covers both names because certbot follows the CNAME.
  The apex cannot itself be a CNAME, so this is the conventional shape.
- *Why not `dig`?* It is not installed on the workstation, and neither is `nc`.
  Two checks were handed over that failed on a missing tool and misreported the
  cause as a DNS failure. The DNS-over-HTTPS check replaced them because it
  needs only `curl` and `python3`, and it distinguishes a failed request from a
  real negative answer.
- *`resolvectl` returned an error, is that a problem?* No. `systemd-resolved`
  is not running on the workstation, which is a local tooling gap and says
  nothing about the zone. The external resolvers are the authority here.

**Deferred:** TLS. Certbot runs after the site is serving, not before.

#### Step 3 — WordPress provisioned on the server ✅

**Goal:** run `provision.sh` on the Lightsail instance and get WordPress
serving `everything4cats.ca`.

**Why it matters:** this is the first time the provisioner has run anywhere
other than a container, and the container could never execute three of its
steps. Everything about the stack that had only ever been asserted was either
confirmed here or was wrong here.

**Commands:**

```bash
# ON SERVER
sudo install -d -o ubuntu -g ubuntu -m 755 /srv/everything4cats
git clone https://github.com/SimBuds/everything4cats.git /srv/everything4cats
cd /srv/everything4cats

sudo SITE_DOMAIN=everything4cats.ca SITE_TITLE='Everything4Cats' \
     ADMIN_USER=casey ADMIN_EMAIL=<admin-email> \
     bash scripts/provision.sh
```

The checkout lives in `/srv`, not in `/home/ubuntu`. Ubuntu creates the home
directory mode `0750`, and `www-data` has to traverse the checkout to follow
the theme symlink. That would not have failed here, because `THEME_DIR` is
unset and no symlink is created for the theme. It would have failed when the
theme landed, which is the expensive place to find it.

The clone is anonymous over HTTPS from a public repository, so no deploy key,
token, or credential of any kind exists on the server.

**Verify:** the three steps that only ever reported `skipped` in the container
all executed. `swapon --show` reports a 2 GB `/swapfile` with
`vm.swappiness = 10`. `ufw` is active and, critically, allows `22/tcp` as well
as `80,443/tcp` on both IPv4 and IPv6. `fail2ban` is active with the `sshd`
jail loaded. `apache2`, `mysql`, `php8.3-fpm` and `fail2ban` all report
`active`.

`wp option get` returns `https://everything4cats.ca` for both `home` and
`siteurl`, and `/%postname%/` for the permalink structure. `wp plugin list`
matches `scripts/plugins.txt` exactly, with `wp-super-cache` and
`google-site-kit` inactive as that file specifies. The compliance plugin is a
symlink into `/srv/everything4cats/plugins/e4c-compliance` and is **active**,
which is what proves the `/srv` placement works. Apache returned `200` locally
with a `Link` header pointing at the site's REST route.

**Q&A:**

- *`wp rewrite flush` printed a warning about `.htaccess`. Is that a failure?*
  No, and it is anticipated. The script checks for `RewriteEngine On`
  afterwards and writes the rewrite block itself when WP-CLI declines to. The
  file was verified to contain the full `BEGIN WordPress` block, owned by
  `www-data`. Without it every URL except the home page would 404, and it would
  look like a TLS problem rather than a rewrite problem.
- *Does `WP_HOME=https` with no certificate cause a redirect loop?* No. It
  changes the URLs WordPress generates, not the ones Apache accepts. The site
  answered `200` over plain HTTP with https asset URLs in the body, which is
  mixed content until certbot runs, not a loop.
- *Was anything wrong?* One thing. `blog_public` was `1`, the WordPress install
  default, leaving a fresh domain indexable while carrying only the sample post
  and the default theme. Set to `0` immediately. It is flipped back to `1` at
  launch, which is the last item in the plan's order of work and also what
  gates the sitemaps.

**Deferred:** TLS, and the remaining items the provisioner prints on exit:
mail relay, backups, page cache, and narrowing SSH.

#### Step 4 — TLS issued, site live ✅

**Goal:** serve `everything4cats.ca` and `www` over HTTPS, with renewal proven
rather than assumed.

**Why it matters:** the provisioner pins `WP_HOME` and `WP_SITEURL` to `https`
before a certificate exists, which is deliberate. It means no database-wide URL
migration is ever needed, at the cost of a short window where the site emits
https URLs it cannot yet serve. This step closes that window. A certificate
also expires in ninety days, so an unproven renewal is a scheduled outage.

**Commands:**

```bash
# ON SERVER
sudo certbot --apache -d everything4cats.ca -d www.everything4cats.ca
sudo certbot renew --dry-run
```

**Verify:** certificate issued for both names and deployed to a generated
`everything4cats-le-ssl.conf`, expiring 2026-11-09. `certbot renew --dry-run`
reported all simulated renewals succeeded, which is the part that matters,
because the renewal timer was already installed with the package and only the
dry run proves it can actually complete.

Checked from a workstation rather than from the server:

```bash
# ON HOST
curl -sS -D- https://everything4cats.ca -o /dev/null
curl -sS -D- http://everything4cats.ca -o /dev/null
curl -sS -o /dev/null -w '%{http_code}\n' https://everything4cats.ca/hello-world/
```

The apex returns `200`. `http://` returns `301` to `https://` from Apache. The
`www` name returns `301` to the apex from WordPress, carrying
`X-Redirect-By: WordPress`, which is the canonical redirect rather than a TLS
failure: the handshake to `www` completed before the redirect was issued, so
the certificate genuinely covers both names. `curl` validates the chain by
default, so a silent `200` is itself the certificate check, and
`ssl_verify_result` was confirmed as `0` from a second machine.

`hello-world/` returns `200` and a nonexistent path returns `404`.

**Q&A:**

- *Two different 301s, is one of them wrong?* No. They come from different
  layers and both are correct. Apache issues the http-to-https redirect that
  certbot configured, identifiable by its `iso-8859-1` error-page content type.
  WordPress issues the www-to-apex canonical redirect, identifiable by
  `X-Redirect-By`.
- *Was the `.htaccess` warning from Step 3 ever a real problem?* No, and this
  is where that is finally settled. `hello-world/` returning `200` while a
  missing path returns `404` can only happen if the rewrite rules are in force.
  The provisioner's fallback had written them correctly.
- *A CAA record was added, does it break renewal?* No. A CAA record pinning
  `0 issue "letsencrypt.org"` was added at Namecheap after issuance, so that no
  other certificate authority can issue for this domain. Public resolvers still
  reported NODATA afterwards, which proved nothing either way: the zone's SOA
  minimum is 3601 seconds, so an earlier query had cached the negative answer
  for an hour. `certbot renew --dry-run` was used instead and succeeded. That is
  definitive where a resolver query is not, because Let's Encrypt reads CAA from
  the authoritative nameservers with no cache, and the staging environment it
  dry-runs against enforces CAA exactly as production does. Re-run the dry run
  after any future change to the CAA record, since a malformed one fails
  silently and only at renewal.

**Deferred:** the four items the provisioner still prints on exit, namely
narrowing SSH, the mail relay, backups and snapshots, and the page cache. Plus
the theme, the content, and flipping `blog_public` back to `1` at launch.

#### Step 5 — SSH verified key-only ✅

**Goal:** close item 5 of the provisioner's exit list by proving SSH cannot be
brute-forced. No change was made, because the Ubuntu cloud image already
shipped the required state.

**Why it matters:** item 5 offers two routes, restricting the source address in
the Lightsail firewall, or key-only authentication plus fail2ban. The source
restriction was planned first and then rejected, because this account connects
through a VPN whose exit address rotates, so a single-address rule would cause
routine self-lockout.

That is not a weakening. Source restriction is defence in depth and was only
ever buying quieter logs and one less reachable service. The boundary is
key-only authentication: with `PasswordAuthentication no` in force, an attacker
who reaches port 22 has nothing to guess without the private key. `fail2ban` was
already verified running in Step 3 and covers the log-noise half.

Two details make this check trustworthy rather than decorative:

- **`sshd -T`, not `/etc/ssh/sshd_config`.** Ubuntu cloud images drop overrides
  into `/etc/ssh/sshd_config.d/*.conf`, so the main file can state one thing
  while the running daemon does another. `sshd -T` resolves the `Include`
  directives and prints what is actually in force.
- **`KbdInteractiveAuthentication` matters as much as `PasswordAuthentication`.**
  With it enabled alongside `UsePAM yes`, PAM can still complete a
  password-equivalent exchange even though password authentication reads as
  disabled. A host can look locked down on the first directive and not be.

**Commands:**

```bash
# ON SERVER
sudo sshd -T | grep -iE '^(port|permitrootlogin|passwordauthentication|pubkeyauthentication|permitemptypasswords|kbdinteractiveauthentication|usepam) '
```

**Verify:** all seven directives returned the required values.

```
port 22
usepam yes
permitrootlogin without-password
pubkeyauthentication yes
passwordauthentication no
kbdinteractiveauthentication no
permitemptypasswords no
```

`without-password` is the legacy spelling of `prohibit-password` and has
identical behaviour, so root can only ever authenticate by key. Port 22 stays
open at the Lightsail firewall deliberately, which the provisioner's own item 5
sanctions as the alternative to source restriction.

**Q&A:**

- *Why not restrict the source address anyway, as a second layer?* Because the
  exit address rotates behind a VPN, so the rule would need editing on most
  reconnects and would be reverted in frustration rather than maintained. If the
  VPN ever offers a dedicated or static exit address, the restriction becomes
  practical and goes on top of key-only auth rather than instead of it.
- *Is leaving port 22 open to the internet a real exposure?* Not a meaningful
  one here. Password authentication is off, so the only credential that works is
  a private key held on one machine. What remains is log noise from automated
  scanners, which is what fail2ban is for. Millions of servers run exactly this
  way.
- *Should `PermitRootLogin` be tightened from `without-password` to `no`?*
  Marginal. Root login already requires a key, and Ubuntu cloud images install a
  forced-command entry in root's `authorized_keys` that refuses the session and
  prints a message directing the user to the `ubuntu` account. Tightening it is
  a one-line change with close to zero practical gain, so it was left alone
  rather than changed for the sake of a visible edit.

#### Step 6 — xmlrpc.php refused ✅

**Goal:** make `xmlrpc.php` return 403 on every vhost, closing the
`system.multicall` amplification route.

**Why it matters:** measured on this stack before the change, `GET
/xmlrpc.php` returned `405` and `POST /xmlrpc.php` returned `200`, answering
`system.listMethods` with `system.multicall` and `pingback.ping` advertised.
The `405` is the trap: it reads like a refusal while the endpoint is fully live,
so a check that only issued a GET would have reported this closed.

`system.multicall` carries many method calls inside a single HTTP request, so
hundreds of password attempts arrive as one request. That is precisely what a
login throttler counting requests cannot see, and it is why
`limit-login-attempts-reloaded` and fail2ban are the wrong layer for this.

Two design decisions, both recorded because both are easy to get wrong:

- **Server scope, not the vhost.** `provision.sh` manages only
  `sites-available/everything4cats.conf`, the port 80 vhost. Certbot generated
  `everything4cats-le-ssl.conf` for 443 and the repo does not manage it. This
  site serves HTTPS and redirects all HTTP to it, so a `<Files>` block in the
  managed vhost would have guarded the one path nobody uses while
  `https://everything4cats.ca/xmlrpc.php` stayed open. A fragment in
  `conf-available/` applies to every vhost, including the one certbot writes,
  and survives certbot regenerating it.
- **Apache, not a WordPress filter.** `add_filter( 'xmlrpc_enabled',
  '__return_false' )` still boots PHP and WordPress for every attempt, so the
  amplification still costs the server, and that filter governs only the
  authenticated methods. Apache refuses before PHP starts, and no plugin
  setting can switch it back on.

**Commands:**

```bash
# ON SERVER
git -C /srv/everything4cats pull
sudo install -m 0644 /srv/everything4cats/docker/e4c-xmlrpc.conf \
  /etc/apache2/conf-available/e4c-xmlrpc.conf
sudo a2enconf e4c-xmlrpc
sudo apache2ctl configtest
sudo systemctl reload apache2
```

**Verify:** checked over HTTPS from a workstation, which is the path that was
actually exposed.

```bash
# ON HOST
curl -s -o /dev/null -w 'GET  xmlrpc  %{http_code}\n' https://everything4cats.ca/xmlrpc.php
curl -s -o /dev/null -w 'POST xmlrpc  %{http_code}\n' -X POST \
  -d '<methodCall><methodName>system.listMethods</methodName></methodCall>' \
  https://everything4cats.ca/xmlrpc.php
curl -s -o /dev/null -w 'GET  /       %{http_code}\n' https://everything4cats.ca/
```

```
GET  xmlrpc  403
POST xmlrpc  403
GET  /       200
```

The home page check is not padding. `<Files "xmlrpc.php">` at server scope
applies to every vhost, so a still-working home page is what rules out the block
denying more than intended.

Proven in the container first, where the harness now runs 42 checks including a
POST assertion and a check that `system.multicall` is absent from the response
body. All three were shown to fail against the pre-change image before the fix
existed.

**Q&A:**

- *If `GET` already returned `405`, was it ever really open?* Yes, completely.
  `xmlrpc.php` rejects GET by design and answers POST, which is the only method
  it uses. The `405` was WordPress saying "wrong verb", not Apache saying "no".
  This is the reason the harness check issues a POST and reads the body rather
  than trusting a status code.
- *Does anything break?* Nothing in use here. Jetpack is not installed, the
  WordPress mobile app uses the REST API, and no plugin in `scripts/plugins.txt`
  needs xmlrpc. Pingbacks and trackbacks stop working, which is deliberate: they
  are a spam vector this site has no use for.
- *Why `403` rather than `404`?* A 403 is honest about what happened. Hiding the
  file behind a 404 adds nothing, because the path is in every WordPress
  install and scanners do not consult a directory listing before trying it.

#### Step 7 — theme and content plugin deployed ✅

**Goal:** put `theme/` (Everything 4 Cats - Theme) and `plugins/e4c-content` on
the live host, so the site serves the real design instead of a bundled default.

**Why it matters:** this is the first deploy that carries a theme, and it moved
three things at once: the theme symlink, a second repository plugin, and the
self-hosted fonts. Each fails differently and only one of them is visible on the
home page, which is why the verification below checks four surfaces rather than
looking at the site.

**Commands:**

```bash
# ON SERVER
cd /srv/everything4cats
sudo SITE_DOMAIN=everything4cats.ca ADMIN_USER=casey ADMIN_EMAIL=<admin-email> \
     THEME_DIR=theme bash scripts/provision.sh
```

**Verify:** checked over HTTPS from a workstation.

```
home    200
reviews 200
search  200
font    200
404     404
```

`reviews 200` is the load-bearing one. That URL exists only if `e4c-content`
registered the `review` post type, so it is the check that proves the second
repository plugin actually deployed. `font 200` proves the theme symlink serves
assets, not just templates. `404` proves the new `404.php` is reached rather
than a server default.

**Q&A:**

- *The first run died with a PHP TypeError inside WP-CLI. What was it?* The
  working directory. The script was run from `~`, and Ubuntu creates
  `/home/ubuntu` as `0750`, so `www-data` cannot traverse it. Most `wp` commands
  survive that because they address files absolutely, but `wp rewrite structure`
  spawns a subprocess through `proc_open`, and `posix_spawn` fails with `EACCES`
  when the child cannot resolve its own working directory. WP-CLI then called
  `proc_close()` on the `false` it got back, which is the TypeError. The message
  names WP-CLI and says nothing about directory permissions. `provision.sh` now
  does `cd /` before any work, so the operator's shell location cannot matter.
  Reproduced deliberately afterwards: the same command produced four error lines
  from `/home/ubuntu` and zero from `/`.
- *Why did the container harness never catch that?* Docker's default working
  directory is `/`, which every user can traverse, so the harness always ran
  from a safe location. The bug could only appear on a real host. This is a gap
  in where the harness runs rather than in what it checks, and `cd /` closes it
  by removing the dependency instead of adding a check.
- *Nothing was printed for `e4c-content` during the run. Did it deploy?* It did.
  A successful activation prints to stdout, which the script suppresses, while
  "already active" prints to stderr, which shows. So silence was ambiguous and
  had to be verified rather than read, which is what `reviews 200` did.
- *Why does the closing list still say "not yet TLS" and tell me to point DNS?*
  The script prints its full exit list on every run and has no memory of which
  steps are done. Items 1 to 4 were completed in Steps 1, 2 and 4. Cosmetic.
