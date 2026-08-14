# IMPLEMENT.md

## Current state
- Active phase: none. Phase 14 complete in the repo, awaiting Casey's commit and
  the live deploy.
- Last completed phase: Phase 14. The site is live at https://everything4cats.ca
- Phase status roll-call: **1 to 14 complete**, including 8 and 8b. Phase 12 is
  complete technically and still sandboxed pending AWS production access.
  Phases 15 to 18 are planned. Phase 18 was opened by a bug found while
  verifying Phase 14 and is not a launch blocker.
- What is left is no longer infrastructure. It is ACF Pro, content, the two
  plugin wizards, and the launch flip, in that order.
- The Current-state block above was stale until 2026-08-13, still naming Phase 7
  as last complete and Phase 11 as next while the roll-call beside it said 1 to
  13. Corrected rather than explained away, and worth noting because a stale
  header is the field most likely to be trusted without checking.
- Phases 9 to 14 were added 2026-08-11. They are items 5 to 8 of the
  provisioner's exit list plus the pre-launch work this session surfaced.
  Renumbered the same day when Phase 10 was inserted at Casey's request, so
  former 10 to 13 became 11 to 14.
- Transport decided and done: public GitHub repo at SimBuds/everything4cats,
  cloned anonymously to `/srv/everything4cats` on the host. No credential on
  the server.
- Open item carried forward: `blog_public` must stay `0` until launch, then
  flip to `1` as the last step. It was `1` on install and was corrected.
- `provision.sh` is proven. Phase 6's precondition is met.

## Inherited decisions

All made 2026-08-10, this session.

- **The project pivoted to a live build.** Docker is no longer the deployment
  target. Hosting is AWS, on a new account carrying $100 of credits.
- **Compute is AWS Lightsail, $12 plan** (2 GB memory, 2 vCPU, 60 GB SSD, 3 TB
  transfer), OS-only Ubuntu 24.04 blueprint. Reaffirmed 2026-08-10 after EC2
  was chosen, priced against real console figures, and reversed. The numbers
  are recorded in PLAN.md under *Decided* so the comparison is not redone.
- **Superseded, kept for the trail:** compute was briefly EC2 `t3.small` with a
  30 GB gp3 volume on 2026-08-10. PLAN.md was the only file that drifted before
  the reversal, and it has been restored. Everything else stayed Lightsail
  throughout.
- **Original Lightsail rationale.** Chosen over EC2
  for predictable burn against the credits (static IP and data transfer are
  included rather than separately billed) and over the Lightsail WordPress
  blueprint because that ships the Bitnami stack at `/opt/bitnami`, which
  `provision.sh` does not target and which would discard the proven server build
  in this repo.
- **The domain is `everything4cats.ca`, already purchased**, at Namecheap,
  GoDaddy or Cloudflare. Which one is not yet confirmed and only Phase 5 depends
  on it.
- **The Docker harness stays, as a test harness only.** Never the deployment
  target. Its job is to prove `provision.sh` before the script touches a paid
  host. The docs get corrected to describe it that way.
- **DNS moves ahead of the WordPress install**, deviating from the stated
  servers-WordPress-theme-domain order. Provisioning against a bare IP and
  moving to the domain later forces a database-wide URL migration, while
  provisioning directly with `SITE_DOMAIN=everything4cats.ca` avoids one
  entirely. Propagation is dead time that the Phase 3 rehearsal fills. Casey may
  override this and take the original order.
- **AWS is the production host, so tier 0 binds in full.** The agent does not
  SSH to the instance, does not open a browser console session on it, and does
  not run `aws` or `lightsail` CLI commands, mutating or read-only. Phases 4
  onward are execution-assist: the agent writes labelled commands, Casey runs
  them, Casey shares the output.
- **`reference/` was deliberately left out** of this project. The three doc
  entries claiming it exists are the error, not the missing directory.
- **`git init` has not been run.** No phase below depends on it, but the Stage 5
  `git diff --stat` audit is unavailable until it exists, so each phase reports
  its changed-file list by hand and names the substitution. Superseded during
  Phase 3: the repository now exists and the audit is available.

Added 2026-08-11.

- **`QUESTIONS.md` exists and is gitignored.** Casey's call: it is personal
  development and learning material, not public-facing documentation. It has no
  Git history and no copy on the server. The rule governing it is in `AGENTS.md`
  under *The question log*.
- **Search Console is verified by a DNS TXT record at Namecheap**, on a domain
  property rather than a URL-prefix property. Verified 2026-08-11. The record
  stays permanently, because Google rechecks it and silently un-verifies the
  property if it disappears. Chosen over the HTML-file and meta-tag methods
  because both can be destroyed by the imminent theme swap or by a reprovision.
- **Google Site Kit stays inactive.** Rank Math owns sitemaps, DNS owns
  verification, and Complianz must own the Analytics tag so it can be gated on
  consent. Site Kit would duplicate all three and add an OAuth surface.
- **Akismet and Hello Dolly were deleted.** They shipped with WordPress and are
  not in `plugins.txt`.
- **Plugin auto-updates stay off until backups exist and a restore is proven.**
  An unattended update that breaks an unbacked site is unrecoverable. Once
  Phase 12 lands, they go on, because plugin vulnerabilities are the actual
  compromise route for a WordPress host. **Resolved 2026-08-13:** Phase 13
  proved a restore, so auto-updates are now enabled for all plugins and all
  themes. The gate was real and it was cleared in the right order.
- **Undecided, and blocking Phase 11:** which mail relay FluentSMTP uses. Brevo
  was recommended for having no approval gate, over Amazon SES (cheapest and
  already in the AWS account, but starts sandboxed) and Postmark (best
  deliverability and logs, paid from day one).

## Phases

Phases 1 to 3 are the agent's own work and run straight through without an
approval gate between them, per *Build it, do not narrate it*. Phases 4 onward
are execution-assist and each stays open across turns.

### Phase 1: The four tracked docs describe AWS Lightsail and everything4cats.ca.
- Status: complete
- Files to touch: `PLAN.md`, `README.md`, `AGENTS.md`
- Functions to add or change: none
- Reuse audit: searched `grep -rn "reference/" PLAN.md README.md` (3 hits:
  PLAN.md:61, README.md:18, README.md:143), `grep -n "Command labels" AGENTS.md`
  (one declared label set, AGENTS.md:1206-1240, currently reduced to a
  no-provider state and carrying an explicit instruction to add a provider block
  back when one is chosen), and `grep -rn "ON DROPLET\|ON SERVER\|IN CONTAINER"`
  for the labels actually in use. There is one label set and one place it is
  declared, so it is extended rather than duplicated. README.md's Conventions
  table mirrors it and must move in the same change or the two drift.
- Simplest approach considered: edit the existing sections in place, adding one
  named AWS block to the AGENTS.md label set as that file's own instruction
  directs. Adopted. No new doc, no new section structure.
- Scenarios (written from the requirement, before any code):
  - PLAN.md states the hosting decision and the domain instead of "hosting
    undecided" and "no domain".
  - PLAN.md's order of work reflects that items 5 and 6 are now partly done.
  - The `reference/` entries are gone from both PLAN.md and README.md, including
    the build-log pointer at README.md:143.
  - `scripts/inventory.sh` appears in both inventories, where it currently
    appears in neither.
  - AGENTS.md declares a label for the Lightsail console, and README.md's
    Conventions table carries the same set.
  - README.md describes Docker as a test harness rather than as the way to run
    the site.
  - No path named in either inventory is absent from disk.
- Verification (three bullets or fewer):
  - Extract every path named in the PLAN.md table and the README.md Layout block
    and `test -e` each, printing PASS or FAIL per path and exiting non-zero on
    any FAIL.
  - Reverse check: every file on disk outside the ignore rules is named in an
    inventory.
  - Diff the AGENTS.md label set against the README.md Conventions table and
    show they agree.
- Deferred out of this phase: nothing.

### Phase 2: `provision.sh` and `inventory.sh` target Lightsail and carry no dangling references.
- Status: complete
- Files to touch: `scripts/provision.sh`, `scripts/inventory.sh`,
  `docker/Dockerfile`
- Functions to add or change: none. The closing "Remaining" heredoc in
  `provision.sh` (lines 470-526), three comments at `provision.sh:22`,
  `provision.sh:423` and `provision.sh:507`, one at `Dockerfile:168`, and the
  header block plus usage label of `inventory.sh`. No control flow changes.
- Reuse audit: searched `grep -rn "PLAN.md\|AGENTS.md" scripts/ docker/ plugins/`
  for every code-to-doc pointer, then checked each target with
  `grep -n -i "architecture\|swap\|step 5\|port 22\|divergence" PLAN.md AGENTS.md`
  — zero hits in PLAN.md, no port-22 entry in AGENTS.md, so four pointers dangle.
  Searched `grep -n "cat <<" scripts/provision.sh`: one human-facing block, no
  second copy to drift. Searched `grep -rn "certbot\|FluentSMTP\|UpdraftPlus" .`
  for duplicate instruction text: none. The script's own header already declares
  the provider-specific parts confined to that one block, so the existing
  structure is reused rather than reorganised.
- Simplest approach considered: rewrite the one "Remaining" block for Lightsail
  and inline the four dangling facts into the comments that were fetching them.
  Adopted over re-adding the deleted PLAN.md sections, because two of them
  describe a host that was never bought.
- Scenarios:
  - The `Remaining` block reads as one provider's instructions, not a
    droplet/EC2 branch per item.
  - The reasoning behind each of the eight items survives, since that reasoning
    is what makes them non-obvious. Provider knowledge is preserved as "what
    differs by provider", not deleted.
  - Elastic IP and Reserved IP language becomes Lightsail static IP.
  - Each of the four dangling pointers either resolves against the current docs
    or states its fact inline.
  - `inventory.sh` names no provider that is not AWS, uses a declared label, and
    still performs zero writes.
  - Both shell files parse and the image still builds.
- Verification (three bullets or fewer):
  - `bash -n scripts/provision.sh && bash -n scripts/inventory.sh`, then
    `docker compose build web`.
  - `grep -n -i "droplet\|digitalocean\|EC2\|Elastic IP\|portfolio\|Step 9"`
    across `scripts/` and `docker/` returns only deliberate
    what-differs-by-provider prose, shown and read line by line.
  - Render the `Remaining` heredoc with the variables set and read the actual
    printed text, confirming every expansion resolved and no literal `$`
    survived.
- Deferred out of this phase: running the script, which is Phase 3.

### Phase 3: `provision.sh` is proven twice in the container before it touches the paid host.
- Status: complete
- Files to touch: none expected. Any fix the run forces is a change to
  `scripts/provision.sh` and is reported as an overage against this list.
- Functions to add or change: none planned.
- Reuse audit: searched for an existing runner rather than writing one:
  `scripts/test-provision/run.sh` builds a bare `ubuntu:24.04`, runs
  `provision.sh` twice, then `verify.sh` runs 34 checks. It is the project's
  declared gate and is used as-is. No new harness.
- Simplest approach considered: run the existing harness unmodified and report
  what it prints. Adopted.
- Scenarios:
  - First run succeeds from bare Ubuntu 24.04.
  - Second run succeeds and is idempotent, creating no duplicate database and
    hanging on no prompt.
  - All 34 checks pass, or each failure is named with its output.
  - The renames from the The-abyss port (paths, database name, database user,
    site slug) are exercised rather than assumed, since the script has not run
    once since that port.
  - The systemd-dependent steps report `skipped` rather than falsely passing.
- Verification (three bullets or fewer):
  - `bash scripts/test-provision/run.sh`, observed output pasted in full,
    including the skip lines. Takes several minutes and downloads for real.
  - Confirm the run exercised both passes and that pass two printed idempotency
    skips rather than repeating work.
  - State explicitly which steps the container could not prove (swap, ufw,
    fail2ban, certbot) so the Phase 7 coverage gap is on the record.
- Deferred out of this phase: everything systemd-dependent, which only the real
  host can prove.

### Phase 4 (execution-assist): The Lightsail instance exists and answers on its static IP.
- Status: complete, 2026-08-11. Corrected 2026-08-11: this section said `planned`
  while the roll-call said complete. The instance exists, the static IP is
  attached, and the README build log carries the evidence.
- Confirmed specification: $12 plan, 2 GB memory, 2 vCPU, 60 GB SSD, 3 TB
  transfer, OS-only Ubuntu 24.04 blueprint, static IP attached. The $5 and $7
  plans are below the 2 GB floor and the console reported them unavailable for
  the selected blueprint anyway.
- Environment: `# IN AWS CONSOLE`, then `# ON SERVER`. Casey runs everything.
- Files to touch: none in the repo. The deliverable is a labelled instruction
  set plus Casey's pasted output.
- Reuse audit: `scripts/inventory.sh` already exists as the read-only
  starting-point audit and is used here rather than a fresh list of commands to
  paste, for the reason its own header gives: a script can be audited once and
  cannot drift between the version reviewed and the version run.
- Simplest approach considered: OS-only Ubuntu 24.04 blueprint, smallest plan
  carrying 2 GB of RAM, static IP attached, firewall opened for 80 and 443.
  Adopted. 1 GB is a false economy, and the repo already encodes why: the swap
  comment in `provision.sh` records that MySQL, PHP workers and image processing
  sharing too little RAM is exactly where a WordPress host OOMs, and the OOM
  killer usually takes MySQL, which looks like data corruption.
- Scenarios:
  - Instance is Ubuntu 24.04, OS-only, not the WordPress blueprint.
  - A static IP is created and attached, so the address survives a stop/start.
  - Ports 80 and 443 are open at the Lightsail firewall, and 22 is reachable.
  - `inventory.sh` runs clean and its output establishes the starting state.
  - Credit burn per month is read from the console and recorded, not estimated.
- Verification (three bullets or fewer):
  - Casey pastes `hostnamectl`, `free -h`, `lsblk` and `lsb_release -a` from the
    instance, confirming Ubuntu 24.04 and the expected RAM.
  - Casey pastes the full `scripts/inventory.sh` output.
  - The static IP is confirmed attached in the console before Phase 5 begins.
- Deferred out of this phase: any hardening, which `provision.sh` performs in
  Phase 6.

### Phase 5 (execution-assist): everything4cats.ca resolves to the static IP.
- Status: complete, 2026-08-11. Four checks, two names against two independent
  public resolvers, all Status 0 and all returning the static IP. The apex is
  an A record at TTL 300, `www` is a CNAME to the apex resolving to the same
  address. Namecheap BasicDNS confirmed authoritative via the resolver comment
  naming `dns2.registrar-servers.com`. No parking or duplicate records.
- Environment: `# IN REGISTRAR DNS`, Namecheap. Casey runs everything.
- Registrar confirmed 2026-08-11: Namecheap. Records already created by Casey,
  an A on the apex to the static IP and a CNAME on `www` to the apex. What
  remains is verification from outside, not creation.
- Files to touch: none.
- Reuse audit: no DNS tooling exists in this repo and none is added. A record
  set is created once, by hand, in the registrar's panel. Building a tool for a
  one-time action fails the simplicity gate outright.
- Simplest approach considered: two A records, apex and `www`, pointed at the
  static IP, DNS left at the registrar rather than delegated to Route 53.
  Adopted. Route 53 delegation adds a billed hosted zone and a nameserver change
  to buy nothing this site needs yet.
- Scenarios:
  - Apex `everything4cats.ca` resolves to the static IP.
  - `www.everything4cats.ca` resolves to the same address.
  - Both are confirmed resolving before Phase 7 runs certbot, or certbot burns a
    Let's Encrypt rate limit against a name that does not yet point anywhere.
  - Any pre-existing parking or forwarding record from the registrar is removed
    rather than left to conflict.
- Verification (three bullets or fewer):
  - `dig +short everything4cats.ca` and `dig +short www.everything4cats.ca` both
    return the static IP, run from Casey's own machine.
  - Confirm against a resolver outside the registrar as well, since a registrar
    panel showing a record saved is not the same as the internet seeing it.
- Deferred out of this phase: TLS, which needs the site serving first.

### Phase 6 (execution-assist): WordPress serves everything4cats.ca over HTTP.
- Status: complete, 2026-08-11. Provisioner ran clean on the real host. Swap,
  ufw and fail2ban, the three steps the container could only skip, all executed
  and were verified. Apache returned 200. Plugin states match plugins.txt. The
  compliance plugin symlink resolves from `/srv` and is active. One defect
  found and corrected: `blog_public` was 1 on install.
- Environment: `# ON SERVER`, then `# WP-CLI`. Casey runs everything.
- Blocked on: Phase 3 passing. The script does not touch the paid host until the
  container has run it twice cleanly.
- Files to touch: none in the repo.
- Reuse audit: `provision.sh` is the whole of this phase. Searched
  `grep -n '^log ' scripts/provision.sh` to confirm it already covers packages,
  Apache, php-fpm, WP-CLI, database, WordPress core, theme, plugins, swap,
  firewall and fail2ban. Nothing here is written twice.
- Simplest approach considered: get the repo onto the server, then run
  `provision.sh` once with `SITE_DOMAIN=everything4cats.ca` and
  `SITE_SCHEME=https` pinned from the start, so no URL migration is ever needed.
  Adopted.
- Scenarios:
  - The repository reaches the server. How is an open question, because
    `git init` has not run and there is no remote yet.
  - The script prompts for the database password with a silent read, so no
    credential enters the shell history, the process list or the repo.
  - It completes, and the site answers on the domain over HTTP.
  - `THEME_DIR` is unset, so it skips loudly and leaves the bundled default
    theme active rather than failing.
  - Re-running it changes nothing, since a re-run is guaranteed later.
  - The systemd-dependent steps that skipped in the container actually run here.
- Verification (three bullets or fewer):
  - `curl -sS -D- http://everything4cats.ca -o /dev/null` shows a 200 with
    unfiltered headers, and the body is fetched and read rather than assumed.
  - `wp --path=/var/www/everything4cats option get home` and `... siteurl`
    return the domain, not an IP.
  - `systemctl status apache2 mysql` and the swap and ufw state are read on the
    real host, confirming the steps the container could only skip.
- Deferred out of this phase: TLS, theme, content.

### Phase 7 (execution-assist): The site serves HTTPS and the hardening is verified.
- Status: complete, 2026-08-11. Certificate covers apex and www, expires
  2026-11-09, renewal dry run succeeded. Apache redirects http to https,
  WordPress canonicalises www to the apex. Permalinks verified live. Chain
  validated from a second machine. ufw, fail2ban and swap were verified in
  Phase 6 on the real host.
- Not done in this phase, moved to follow-up phases: narrowing SSH from
  "anywhere" to a restricted source, which is the one hardening item the
  provisioner lists that remains open.
- Environment: `# ON SERVER`. Casey runs everything.
- Blocked on: Phases 5 and 6.
- Files to touch: none in the repo.
- Reuse audit: the eight numbered items in `provision.sh`'s rewritten
  `Remaining` block are the source for this phase. The instructions are read out
  of the script rather than retyped, so the two cannot disagree.
- Simplest approach considered: `certbot --apache` for both names, then confirm
  renewal is armed. Adopted.
- Scenarios:
  - A certificate is issued covering apex and `www`.
  - HTTP redirects to HTTPS and the redirect does not loop.
  - Renewal is scheduled and its dry run succeeds, because a certificate nobody
    has tested renewing expires in ninety days without warning.
  - ufw and fail2ban are confirmed active on the real host.
  - Port 22 is narrowed rather than closed outright.
- Verification (three bullets or fewer):
  - `curl -sS -D- https://everything4cats.ca -o /dev/null` returns 200 with a
    valid chain, headers read unfiltered.
  - `certbot renew --dry-run` succeeds, output pasted.
  - `ufw status verbose`, `fail2ban-client status` and `swapon --show` read on
    the host.
- Deferred out of this phase: mail, backups and caching, which are items 6 to 8
  of the `Remaining` block and become their own phases once the site is live.

### Phase 8: The base theme is integrated.
- Status: complete, 2026-08-12. Deployed to the live host and verified over
  HTTPS: home 200, `/reviews/` 200, search 200, font 200, unknown path 404.
  Recorded as README build log Step 7. All four blockers cleared, all nine
  screens built in Phase 8b.
- Status history: in progress, 2026-08-12. The theme landed, so this is unblocked.
  Cleanup done and audited. **Not closeable yet**: four blockers below.
- Cleanup done 2026-08-12, at Casey's request:
  - `design/` created and split into `tool/` (generated design-tool artefacts,
    including an 783 KB state file and three `.dc.html` canvases) and `source/`
    (the cat photography, logo, favicons, and `tokens.json`). None of it sits in
    the theme, because `provision.sh` symlinks the theme into the webroot and
    anything placed there becomes publicly fetchable.
  - `e4c-theme/` renamed to `theme/`, Casey's choice. `theme` is therefore also
    the slug WordPress records, so the live site needs `THEME_DIR=theme`.
  - Display name set to "Everything 4 Cats - Theme". Author was already SimBuds.
  - Trailing references updated in `theme/README.md` and in the
    `e4c-compliance` docblock that named the old directory.
- Audit results, 2026-08-12, each run rather than reasoned:
  - **21 PHP files parse clean**, with a control proving the linter fails on
    broken input. The first attempt at this check was a false report: `php` is
    not installed on the workstation, so it printed 21 syntax errors that were
    all the checker failing. Re-run inside the container.
  - **`theme.json` is valid JSON, version 3.**
  - **Token resolution is clean.** Every `--wp--preset--*` reference in
    `style.css` resolves to a preset that exists: 15/16 colours, 2/2 font
    families, 13/14 font sizes, 5/6 spacing steps.
  - **Field contract is intact.** All eight fields the theme reads are
    registered by `e4c-content`. `e4c_picks` is registered and unread, which is
    correct rather than dead: it backs the roundup single, which is not built.
- **All four blockers cleared 2026-08-12.** Harness now runs 51 checks, up from
  42, and passes. Evidence in the phase report below.
- **Blockers as originally found, in the order they would bite:**
  1. **`provision.sh` deploys only `e4c-compliance`.** The symlink at line 453
     is hardcoded to that one directory, so `plugins/e4c-content/` never reaches
     the server. Without it the `review` and `roundup` post types do not exist,
     `/reviews/` 404s, and every review template is unreachable. This is the one
     that makes the theme look broken rather than unstyled.
  2. **The three declared fonts do not exist.** `theme.json` names
     `assets/fonts/caprasimo-400.woff2`, `figtree-variable.woff2` and
     `figtree-variable-italic.woff2`. The repo contains no woff2 at all, so
     WordPress generates `@font-face` rules pointing at 404s and both families
     fall back silently to Georgia and system-ui.
  3. **ACF Pro is not in the baseline and cannot be**, since it is commercial
     and not on wordpress.org. `e4c-content` degrades correctly, warning in the
     admin while keeping posts published and falling back to raw post meta, so
     this is a documentation gap rather than a crash.
  4. **The theme has never been rendered.** No template has been run against
     WordPress, on the server or in the container. Everything above is static
     analysis.
- Deferred, and correctly so: seven of the nine screens are unbuilt and
  `index.php` catches them, which the theme README documents as walking-skeleton
  order. Nine inline `style=` attributes across four templates duplicate what
  the component layer should own, worth a cleanup phase but not a blocker.
- Files to touch: unknown until the theme arrives. Planned properly then, not
  guessed at now.
- Reuse audit: `provision.sh` already deploys a theme by symlink from the
  checkout when `THEME_DIR` is set, and refuses loudly when the named directory
  is absent. That mechanism is used rather than a copy step.
- Simplest approach considered: set `THEME_DIR` and re-run `provision.sh`, which
  is a single documented command and is already idempotent. Adopted unless the
  theme turns out to need a build step, which is a plan change and goes through
  the decision gates.
- Scenarios: to be written from the theme when it exists. Writing them now would
  be guessing at a requirement rather than reading one.
- Verification: to be defined with the scenarios.
- Deferred out of this phase: content, newsletter provider, affiliate programme
  and the `blog_public` flip, all of which PLAN.md already orders.

### Phase 9 (execution-assist): SSH on the instance is provably key-only.
- Status: complete, 2026-08-11. Verification only, no change made. All seven
  directives already correct in the Ubuntu cloud image. Recorded as README build
  log Step 5.
- Status history: planned. **Re-planned 2026-08-11, before execution.** The original goal
  was restricting the Lightsail SSH rule to Casey's own address. Casey uses a
  VPN, so the apparent source address changes on every reconnect and a
  single-address rule would lock him out routinely.
- Why the replacement is not a weaker goal: source-address restriction is
  defence in depth, never the boundary. The boundary is key-only
  authentication, because an attacker who reaches port 22 with
  `PasswordAuthentication no` in force has nothing to guess. The restriction was
  buying quieter logs and one less reachable service, and against a rotating
  address it buys that at the price of routine self-lockout. `fail2ban` is
  already running and covers the log-noise half.
- Open alternative, Casey's call: if the VPN offers a dedicated or static exit
  address, the original source restriction becomes practical again and can be
  layered on top of key-only auth rather than instead of it.
- Environment: `# ON SERVER` to read and, if needed, change sshd's effective
  config. Casey runs everything.
- Files to touch: none in the repo. The deliverable is a labelled instruction
  set plus Casey's pasted output, and a README build-log entry at the end.
- Reuse audit: searched `grep -n "22\|ssh\|fail2ban" scripts/provision.sh`. Item
  5 of the script's own `Remaining` block is the source for this phase and is
  read out of the script rather than retyped. `ufw` already allows 22 at the OS
  layer and `fail2ban` is already running, both verified on the host in Phase 6.
  Neither is changed here. No new tooling.
- Simplest approach considered: read `sshd -T` and change nothing, on the
  possibility that the Ubuntu cloud image already ships the desired state.
  Adopted as the opening step. Ubuntu cloud images generally disable password
  authentication in `sshd_config.d/60-cloudimg-settings.conf`, so this phase may
  turn out to be a verification with no change at all. That is a legitimate
  outcome and is reported as one, rather than making a change to have something
  to show.
- Known risk: none material. Nothing is closed or restricted, so no lockout path
  is opened. The only change this phase can make is to sshd's own config, and it
  is guarded by `sshd -t` before reload and by keeping an existing session open
  across it.
- Note on `provision.sh` item 5: the script already offers "key-only plus
  fail2ban" as the alternative to source restriction, so this phase takes a path
  the script sanctions rather than diverging from it. Its adjacent claim that
  the console's browser SSH is a rescue path remains inherited and unverified,
  but nothing here depends on it, since no route is being closed. Left alone
  rather than tested, and recorded so a later phase that does close something
  knows to test it first.
- Scenarios (written from the requirement, before any change):
  - The effective config is read with `sshd -T`, not from `sshd_config` alone.
    Ubuntu cloud images drop overrides into `sshd_config.d/*.conf`, so the main
    file can say one thing while the running daemon does another.
  - `passwordauthentication` is `no`.
  - `kbdinteractiveauthentication` is `no`. This is the one that gets missed:
    with it enabled and `usepam yes`, PAM can still complete a
    password-equivalent exchange even though `passwordauthentication` is off.
  - `pubkeyauthentication` is `yes`, or key auth stops working.
  - `permitrootlogin` is `no` or `prohibit-password`.
  - `permitemptypasswords` is `no`.
  - If any of these is wrong, the fix lands in a new file under
    `sshd_config.d/`, never by editing a cloud-image file that a later update
    replaces.
  - An existing session stays open across any `sshd` reload, so a bad config
    cannot lock the door with the key inside.
- Verification (three bullets or fewer):
  - `sudo sshd -T` output for those six directives, pasted, before and after any
    change.
  - `sudo sshd -t` returns clean before any reload, since a syntax error found
    after the reload is found too late.
  - A fresh SSH connection from a second terminal succeeds while the original
    session is still open, proving key auth survived the change.
- Deferred out of this phase: blocking `xmlrpc.php`, which is Phase 10.

### Phase 10: The provisioner reproduces the corrections made by hand after it last ran.
- Status: complete, 2026-08-11. Harness passed, 39 checks, up from 34. All four
  new checks were shown to fail first, and the idempotency guard was proven
  directly rather than inferred.
- Status history: planned 2026-08-11, at Casey's request. Inserted ahead of the
  previously numbered Phase 10, which becomes Phase 11. Everything below it
  shifted by one.
- Why now: this is the *route every change to its durable home* rule. Three
  corrections were made by hand on the live server and exist nowhere in the
  repo, so the next rebuild silently reintroduces all three.
- Environment: agent's own work. `provision.sh` is repo code and the harness is
  local Docker, both of which the pace change of 2026-08-01 puts in scope. The
  paid host is not touched.
- Files to touch: `scripts/provision.sh`, `scripts/plugins.txt`,
  `scripts/test-provision/verify.sh`.
- Functions to add or change: no new functions. The `core install` guard block
  gains two post-install calls, the plugin loop gains a third branch, and the
  closing `Remaining` heredoc gains and revises items.
- Reuse audit: searched `grep -n "blog_public\|core install\|plugin delete\|
  display_name" scripts/provision.sh`, which returned no hits for any of the
  three corrections, confirming none is already handled. Searched
  `grep -n "robots\|sitemap\|blog_public\|plugin list" scripts/test-provision/
  verify.sh`, which returned nothing, so the harness has no existing check to
  extend and new ones are added rather than duplicated. The `skipped` helper and
  the `slug:inactive` parser already exist and are reused rather than replaced.
- Simplest approach considered: hardcode the two bundled plugin slugs in
  `provision.sh`. Rejected. `plugins.txt` declares itself the single source of
  truth for plugin state, and bundled plugins are plugin state, so a `:delete`
  suffix extends the existing one-line-per-slug grammar by one branch rather
  than creating a second place where plugin policy lives.
- The trap this phase must not fall into: setting `blog_public` unconditionally
  would de-index the live site on the next re-run, and Phase 8 re-runs
  `provision.sh` to install the theme. It goes inside the `core install` guard so
  it applies to a fresh install only.
- Scenarios (written from the requirement, before any code):
  - A fresh install ends with `blog_public` at `0`.
  - A re-run against an already-installed site leaves `blog_public` untouched,
    including when it has been set to `1`. This is the scenario that would cost
    the most and is the reason for the guard.
  - `akismet` and `hello` are absent after provisioning.
  - Deleting them twice is a no-op rather than an error, since every deploy
    after the first is a re-run.
  - `ADMIN_DISPLAY_NAME` when set produces a `display_name` and `user_nicename`
    that differ from the login.
  - `ADMIN_DISPLAY_NAME` when unset skips loudly and names the exposure, in the
    same idiom `THEME_DIR` already uses, rather than failing or guessing a
    byline.
- Verification (three bullets or fewer):
  - `bash scripts/test-provision/run.sh` passes, which runs `provision.sh` twice
    and is the project's declared gate. Observed output reported, not predicted.
  - New `verify.sh` checks assert `blog_public` is `0` and that both bundled
    plugins are gone, each shown to fail first by running them against the
    pre-change image.
  - The idempotency scenario is proven directly: set `blog_public` to `1` in the
    container, re-run `provision.sh`, and confirm it is still `1`.
- Deferred out of this phase: Rank Math's schema module, which is wizard state
  in the database rather than something the provisioner sets, and stays
  documented in `plugins.txt` as intent.

### Phase 11: xmlrpc.php returns 403 on every vhost.
- Status: complete, 2026-08-11. Harness passed at 42 checks, up from 39, all
  three new ones shown to fail first. Applied to the live host and verified over
  HTTPS: `GET` 403, `POST` 403, home page 200. Recorded as README build log
  Step 6.
- Status history: planned in full 2026-08-11. Renumbered from Phase 10 the same day.
- Goal: close the `system.multicall` amplification route, which lets an attacker
  carry many password attempts in one HTTP request and is the pattern the login
  throttler and fail2ban are least able to see, because it is one request.
- Measured before planning, not assumed. Against the current image: `GET
  /xmlrpc.php` returns `405`, and `POST /xmlrpc.php` returns `200` and answers
  `system.listMethods` with `system.multicall`, `pingback.ping` and the rest
  advertised. The GET status is therefore useless as a check, since 405 already
  looks like a refusal while the endpoint is fully live.
- Environment: agent's own work in the repo and the local container. A separate
  `# ON SERVER` handoff applies it to the live host, because the repo change
  alone does not reach a running instance.
- Files to touch: `docker/e4c-xmlrpc.conf` (new), `scripts/provision.sh`,
  `docker/Dockerfile`, `scripts/test-provision/verify.sh`.
- Functions to add or change: none. Two copy-and-enable lines in the
  provisioner beside the existing `a2enconf`, one `COPY` plus `a2enconf` in the
  Dockerfile, and two HTTP checks.
- Reuse audit: searched `grep -n "everything4cats.conf|a2ensite|a2enconf|Files|
  xmlrpc" scripts/provision.sh docker/Dockerfile`, which found the vhost copy at
  `provision.sh:182` and the `a2enconf` for php-fpm at `provision.sh:172`, and no
  existing xmlrpc handling anywhere. Searched `grep -n "== HTTP|H()"
  scripts/test-provision/verify.sh`, which found the `H()` helper already used by
  four checks, extended rather than duplicated. No plugin in `plugins.txt` needs
  xmlrpc, so nothing is being taken away from a working feature.
- **Why this is not a vhost change, which is the trap.** `provision.sh` writes
  only `sites-available/everything4cats.conf`, the port 80 vhost. Certbot
  generated `everything4cats-le-ssl.conf` for 443 and the repo does not manage
  it. The live site serves HTTPS and redirects all HTTP to it, so a `<Files>`
  block in the managed vhost would guard the one path nobody uses and leave
  `https://everything4cats.ca/xmlrpc.php` fully open. A fragment in
  `conf-available/`, enabled with `a2enconf`, applies at server scope to every
  vhost including certbot's, and survives certbot regenerating the SSL vhost.
- Simplest approach considered: `add_filter( 'xmlrpc_enabled', '__return_false' )`
  in the compliance plugin. Rejected on two counts. It still boots PHP and
  WordPress for every attempt, so the amplification still costs the server, and
  it leaves several methods reachable because that filter governs only
  authenticated ones. Apache refuses before PHP starts and cannot be re-enabled
  by a plugin setting.
- Scenarios (written from the requirement, before any code):
  - `POST /xmlrpc.php` returns 403. This is the scenario that matters, and the
    one a GET-only check would miss entirely.
  - `GET /xmlrpc.php` returns 403 rather than 405.
  - The rest of the site is unaffected, proven by the existing HTTP checks still
    passing rather than by inspection.
  - The fragment is enabled, so a config that exists but was never `a2enconf`ed
    fails the check rather than passing quietly.
  - Re-running the provisioner is a no-op, since `a2enconf` on an already
    enabled config is idempotent.
- Verification (three bullets or fewer):
  - `bash scripts/test-provision/run.sh` passes with the two new HTTP checks,
    each shown to fail first against the pre-change image, where the observed
    values are already recorded above as 405 and 200.
  - The `POST` check is the discriminating one and is asserted on the status
    code and on the absence of `system.multicall` from the body.
  - `apache2ctl configtest` clean inside the harness, which the existing Apache
    section already runs.
- Deferred out of this phase: applying it to the live host, which is a
  `# ON SERVER` handoff written at the end of this phase and run by Casey.

### Phase 8b: The seven remaining screens exist as templates.
- Status: complete in the repo, 2026-08-12. Harness passed at 56 checks, up from
  51. Every template rendered with real content, 200 and zero PHP errors on all
  six content-bearing screens. Not yet deployed to the live host.
- Status history: planned 2026-08-12, Casey chose build-then-deploy over deploy-first.
- Files to add: `theme/taxonomy-cat-category.php`, `theme/single-roundup.php`,
  `theme/single.php`, `theme/page-how-we-test.php`, `theme/search.php`,
  `theme/page-newsletter.php`, `theme/404.php`. Files to change:
  `theme/style.css` (component classes for the new parts),
  `theme/index.php` (its header claims these are unbuilt),
  `theme/README.md` (the "Still to build" list).
- Reuse audit: read every existing template before writing one.
  `template-parts/card-post.php` already renders a card for any of the three
  types and is reused by all four archive-shaped screens rather than copied.
  `e4c_field()`, `e4c_button()`, `e4c_hero_image()` and `e4c_method_statement()`
  exist in `inc/template-helpers.php` and are reused. `.e4c-section`,
  `.e4c-grid`, `.e4c-panel`, `.e4c-card`, `.e4c-tag`, `.e4c-list`, `.e4c-btn`,
  `.e4c-article`, `.e4c-cols` and `.e4c-shell` already exist in `style.css`. No
  new template part is created where `card-post.php` fits.
- Content model, read rather than assumed: `review` and `roundup` are the two
  CPTs, guides are core `post`, and `cat-category` is one hierarchical taxonomy
  registered across all three. That is why the category archive is
  `taxonomy-cat-category.php` and not three separate archives, and why the guide
  article is `single.php`.
- Simplest approach considered: let `index.php` keep catching all seven and only
  style it harder. Rejected on one concrete requirement: the roundup single has
  to render the `e4c_picks` repeater, whose subfields are `review`, `award` and
  `why`, and a generic archive template cannot render a field-driven ranking.
- **Inline styles are not propagated.** The existing templates carry nine
  `style=` attributes, already logged as a smell. New components get classes in
  `style.css` rather than growing that number.
- Scenarios (written from the requirement, before any code):
  - A `cat-category` term archive lists reviews, roundups and guides together.
  - A roundup renders its picks in order, each linking to the reviewed item,
    and renders nothing rather than an empty block when `e4c_picks` is unset.
  - A guide article renders body prose at the reading measure.
  - Search returns results and offers a post-type facet that preserves the query.
  - Search with no results, and 404, both offer a route onward rather than a
    dead end.
  - Every new template renders with ACF absent, since the container has none.
- Verification (three bullets or fewer):
  - `bash scripts/test-provision/run.sh` passes, with new HTTP checks covering
    the category archive, a roundup, a guide, search, and the 404.
  - A roundup with a populated `e4c_picks` repeater renders its picks, proven by
    creating one in the container and reading the output.
  - Every new template is fetched and grepped for `Fatal error`, `Warning:`,
    `Notice:` and `Deprecated:`, since a PHP notice still returns 200.
- Deferred: the roundup post-type archive at `/best/`, which `index.php`
  already handles correctly through `the_archive_title()`. Not in Casey's list
  of seven and not worth a template that would duplicate `archive-review.php`.

### Phase 12 (execution-assist): WordPress delivers mail through Amazon SES.
- Status: technically complete, 2026-08-12. A real WordPress password reset was
  delivered and authenticated end to end. **Still sandboxed**: AWS asked for
  more detail on the production access request, so delivery is limited to
  verified identities until that is granted.
- Evidence, from the received message's `Authentication-Results`:
  `dkim=pass header.d=everything4cats.ca`, `dkim=pass header.d=amazonses.com`,
  `spf=pass smtp.mailfrom=ca-central-1.amazonses.com`, `dmarc=pass action=none
  header.from=everything4cats.ca`, `compauth=pass reason=100`.
- **The DMARC prediction held.** Dropping the custom MAIL FROM meant SPF would
  authenticate `amazonses.com` and not align. DMARC passed anyway on DKIM
  alignment alone, which is what the decision was reasoned on. The measurable
  cost of losing the MX record was zero.
- Delivered to the Junk folder with that authentication result, which is
  new-domain reputation rather than a fault. Nothing to fix, only time and
  consistent sending.
- Status history: planned in full 2026-08-12. **Relay decided: Amazon SES**, chosen over
  Brevo on cost. At this site's volume, well under 50 messages a month, SES is
  roughly $0.10 per thousand and draws on the existing $100 of credits, so the
  running cost is pennies. The price paid instead is setup: SES starts
  sandboxed and production access is a request with a wait, where Brevo has no
  gate.
- Environment: `# IN AWS CONSOLE`, `# IN REGISTRAR DNS`, `# IN WP-ADMIN`. Casey
  runs all of it. No agent touches the AWS account, per tier 0.
- Files to touch: none in the repo during setup. `README.md` gains a build log
  entry at the end. **No credential of any kind enters the repository**: SMTP
  credentials and IAM keys live only in the SES console and in FluentSMTP's
  settings, which are database rows.
- Reuse audit: `fluent-smtp` is already installed and active from
  `scripts/plugins.txt` and has native Amazon SES support, so nothing new is
  installed.
- **The apex SPF record is not touched at all. Corrected 2026-08-12.** The
  earlier plan said the existing
  `v=spf1 include:spf.efwd.registrar-servers.com ~all` would gain
  `include:amazonses.com`. That was written before the custom MAIL FROM domain
  was configured, and the MAIL FROM decision makes it unnecessary: SPF
  authenticates the envelope sender, so receivers evaluate
  `mail.everything4cats.ca`, whose own TXT record SES supplies. The apex record
  is never consulted for SES mail, and DMARC still aligns because the default
  alignment mode is relaxed and `mail.` is a subdomain of the From domain.
- **That correction matters practically, not just theoretically.** Namecheap's
  panel shows the apex SPF record under Mail Settings, marked locked, because
  the Email Forwarding service manages it. It could not have been freely edited,
  and working around the lock would have meant either disabling forwarding or
  publishing a second SPF record, which is a permanent failure rather than a
  merge. The MAIL FROM subdomain sidesteps the whole problem, which is a second
  benefit of that decision beyond the DMARC alignment it was chosen for.
- Namecheap's Mail Settings dropdown stays on Email Forwarding. Changing it to
  Custom MX would break inbound mail for the domain silently.
- The second trap: while SES is sandboxed it will only deliver to verified
  addresses. A test that "works" in the sandbox proves nothing about delivery
  to a stranger, so the real proof waits for production access.
- Scenarios (written from the requirement, before any step):
  - The domain is verified in SES and all three Easy DKIM CNAMEs resolve.
  - The single SPF record contains both the registrar forwarder and
    `include:amazonses.com`.
  - Production access is granted, so delivery is not limited to verified
    addresses.
  - A real WordPress password reset arrives, rather than FluentSMTP's own test
    button reporting success.
  - The message passes SPF and DKIM at the receiving end, checked in the
    received headers rather than assumed from arrival.
- Verification (three bullets or fewer):
  - A password reset triggered from `wp-login.php` arrives at an address that
    was never verified in SES, which is only possible once out of the sandbox.
  - The received message's headers show `spf=pass` and `dkim=pass` for
    `everything4cats.ca`.
  - The SPF record resolves as exactly one TXT record containing both includes,
    checked from an external resolver rather than from the registrar panel.
- **Custom MAIL FROM: dropped, 2026-08-12. Final.** It was deferred, then
  included when the SES creation screen made it free to add, then abandoned when
  Namecheap turned out not to offer an `MX Record` type at all while Email
  Forwarding is enabled. SES verifies a MAIL FROM domain only when both its MX
  and TXT records exist, so without the MX the TXT is inert and both were
  skipped.
- The workaround was rejected rather than untried: switching Namecheap's Mail
  Settings to Custom MX disables the forwarding service itself, not merely its
  records, and the apex SPF record is locked by that same service. That trades
  working inbound mail for SPF alignment that is not needed.
- **What it costs: close to nothing.** DMARC passes when either SPF or DKIM
  aligns. Easy DKIM signs as `everything4cats.ca`, so DKIM aligns and DMARC
  passes on that alone. SPF authenticates `amazonses.com`, which does not align
  and does not need to. DKIM is the stronger signal regardless, since it
  survives forwarding where SPF does not.
- Four DNS records total, not six: three DKIM CNAMEs and one `_dmarc` TXT.
- **MX failure behaviour set to "use default MAIL FROM domain", not "reject
  message".** Reject turns a broken MX record into total silent mail loss,
  which is the exact failure this phase exists to end. Use-default degrades to
  `amazonses.com`, costing SPF alignment rather than every password reset.
  DMARC still passes on DKIM alone, since Easy DKIM signs as the domain.
- The MAIL FROM subdomain carries its own MX, separate from the apex, so
  Namecheap's existing email forwarding is unaffected.

### Phase 13 (execution-assist): The site is backed up and a restore is proven.
- Status: complete, 2026-08-13. UpdraftPlus to Google Drive, database daily
  retaining 14 and files weekly retaining 4, scoped to database and uploads
  only. **Restore proven**, not merely configured: the Drive copy was restored
  into the Docker container and served the site at 200 with zero PHP errors,
  29 tables and 5,101 options intact. Recorded as README build log Step 9.
- Two findings from actually doing the restore rather than reading about it:
  `unzip` is absent on a bare Ubuntu host, so the extraction step needs Python,
  which is always present. And `.gitignore` does not catch UpdraftPlus's
  `-db.gz` database export, which sat untracked and committable in the repo root
  while the uploads `.zip` beside it was correctly ignored. Local copies belong
  outside the repository.
- The first restore appeared to lose content and branding. It had not: the
  backup predated the logo and Site Icon being assigned, and the fixtures were
  already in the trash. A second backup taken afterwards contains all eight Site
  Icon crops. Faithfulness to the moment of capture is the correct behaviour and
  was misread as failure until the timestamps were compared.
- This satisfies the gate the auto-update decision was waiting on.
- Status history: planned 2026-08-12. **Tool decided: UpdraftPlus**, already installed.
  Remote destination still to confirm.
- **Scope narrowed deliberately, and this is the cost decision.** A default
  WordPress backup copies core, plugins, themes, uploads and the database. Here
  core comes from `wp core download`, the plugin baseline from `plugins.txt`,
  and the theme and both custom plugins from git. None of that is unique, so
  none of it is backed up. What is irreplaceable is the **database and
  `wp-content/uploads`**: the writing and the photography. That keeps the
  backup inside a free tier for a long time.
- **Lightsail automatic snapshots are deliberately not enabled.** They bill per
  GB-month and retain seven dailies, to duplicate a machine that `provision.sh`
  rebuilds from scratch. Skipping them costs recovery time rather than data:
  rebuild, re-issue the certificate, reinstall ACF Pro, restore from
  UpdraftPlus. One manual snapshot is taken once the host is fully configured,
  as a cheap fast-restore point. Revisit when the site earns anything.
  This revises the earlier "both layers, always" advice, which is the right
  general rule and is weaker here precisely because the infrastructure is code.
- Verification: a restore is performed, not just a backup taken. The check is
  that a restored database and uploads produce a working site, because a backup
  nobody has restored is not a backup.
- One-line goal: end the silent-failure state where FluentSMTP is active with no
  relay, so WordPress reports every message as sent while none leave the host.
- Carries a known dependency: the existing SPF record
  (`v=spf1 include:spf.efwd.registrar-servers.com ~all`) must gain the relay's
  `include:`, edited rather than duplicated, because two SPF records is a
  permanent failure rather than a merge.

### Phase 14: Secure Custom Fields replaces the ACF Pro dependency, so the field baseline installs itself.
- Status: **complete 2026-08-13.** Repo half done and verified in the container.
  The live host still needs the deploy, which is a `git pull` plus
  `wp plugin install secure-custom-fields --activate`, and is Casey's to run.
- One-line goal: end the state where writing a review means hand-editing post
  meta, and do it without taking on a commercial dependency.
- **Decision, 2026-08-13: Secure Custom Fields, not ACF Pro.** Casey asked for
  an option with no recurring cost. SCF is WordPress.org's October 2024 fork of
  ACF, free, and on the plugin directory.
- **Verified before planning, not assumed.** The published package was
  downloaded and inspected at version 6.9.5. Every field type
  `plugins/e4c-content/inc/fields.php` uses is present: `text`, `textarea`,
  `url`, `post_object`, and critically `repeater`, which was the whole reason
  the dependency was Pro rather than free. `flexible-content`, `clone`,
  `gallery` and the options-page assets ship too, so the fork carries the Pro
  feature set rather than only the free half. `acf_add_local_field_group`,
  `get_field` and `have_rows` all still resolve under their ACF names.
- **Why this is a drop-in and not a migration.** The templates never call
  `have_rows()` or `the_row()`. They call `e4c_field()` once and iterate the
  returned array, reading `$row['text']`, `$row['label']`, `$row['value']` and
  `$row['review']`, tolerating scalar rows and normalising a post object against
  a bare ID. The whole coupling to ACF is two function names, both of which the
  fork keeps. No PHP in the theme or in `fields.php` changes behaviour.
- **The real prize is not the money.** SCF is installable from wordpress.org,
  so it goes in `scripts/plugins.txt` and the "CANNOT be in this list, and still
  required" block disappears. That block existed only because ACF Pro is
  commercial. Removing it restores `plugins.txt` to a complete description of
  plugin state, deletes the manual step from disaster recovery, and makes the
  container harness exercise the real `get_field()` path instead of only the
  raw-meta fallback it has been proving against until now.
- Files to touch (five, at the budget limit, noted rather than split because
  they are one change): `scripts/plugins.txt`,
  `plugins/e4c-content/inc/admin-notices.php`,
  `plugins/e4c-content/inc/fields.php`, `scripts/test-provision/verify.sh`,
  `README.md`.
- Functions to add or change: `e4c_content_acf_notice()` only, for wording. Its
  `function_exists( 'acf_add_local_field_group' )` guard is already correct for
  SCF and does not change. No new function.
- Reuse audit: searched `acf_add_local_field_group`, `get_field`, `e4c_field`,
  `have_rows`. Both field groups already exist and register on
  `acf/include_fields`. Searched for an existing install mechanism before adding
  one: `scripts/plugins.txt` is already read by `provision.sh`, so SCF is a new
  line in an existing file rather than any new machinery.
- Simplest approach considered: install SCF by hand in wp-admin and change
  nothing in the repo. Rejected because it recreates the exact manual step
  `plugins.txt` exists to eliminate, and leaves the harness still blind to the
  ACF path.
- Scenarios: a bare host provisioned from scratch installs and activates SCF
  with no human step; both groups register on the correct post types and nowhere
  else; the admin notice stays silent while SCF is active and still fires with
  accurate wording if it is ever absent; a review saved through the editor is
  read by `get_field()` rather than by the fallback.
- Verification (three bullets):
  - `bash scripts/test-provision/run.sh` exits 0 with the new checks passing.
  - In the container, `acf_get_field_type( 'repeater' )` resolves and both field
    groups appear in `acf_get_local_field_groups()`.
  - On the live host after deploy, editing a review shows all eight fields, the
    ACF notice is gone, and `wp post meta list` on a saved review shows the
    underscore-paired keys the fallback path never writes.
- Deferred out of this phase: writing any content, which is Phase 15. No data
  migration is needed, because the only posts carrying these fields are
  fixtures that Phase 15 deletes.
- Consequence for disaster recovery, revising what Phase 13 recorded: a rebuild
  no longer needs a manual plugin reinstall before the UpdraftPlus restore is
  useful. That note under Phase 13 is now stale and this phase corrects it.

### Phase 15: The fixtures are gone and the site's own baseline content is published.
- Status: planned 2026-08-13. Blocked on Phase 14.
- One-line goal: the site says something true about cat products instead of
  shipping WordPress sample data.
- Not decomposed yet, deliberately. The shape depends on what Phase 14 makes
  editable and on how long a first review actually takes to write. Decompose at
  the start of the phase, not now.
- Known members: delete the remaining fixtures, set the site Tagline, which is
  still empty and which Rank Math will use, then the first reviews, at least one
  roundup pointing at them, and the How We Test page the theme already has a
  template for.
- Ordering note: the roundup cannot be written before the reviews it points at,
  because `e4c_picks` stores a post object rather than a copied title.

### Phase 16 (execution-assist): Consent, analytics and SEO are configured.
- Status: planned 2026-08-13. Blocked on Phase 15, because both wizards ask
  questions about content that does not exist yet.
- One-line goal: the three plugins that need a one-time interview get it, in the
  order that avoids a duplicate-schema error.
- Known members: the Complianz wizard, the Rank Math wizard **with its
  rich-snippet module off**, and GA4 connected behind Complianz consent rather
  than hardcoded into the theme.
- The schema constraint is not a preference. `plugins/e4c-compliance` already
  emits Article markup, and two Article blocks on one page is an error rather
  than a tie-break. Recorded in `scripts/plugins.txt` under the deliberate
  exclusions.

### Phase 17 (execution-assist): Pre-launch hygiene and the launch flip.
- Status: partly done. Renumbered from Phase 13 on 2026-08-11 and from Phase 14
  on 2026-08-13, when its content and configuration members were split out into
  Phases 14 to 16 because the phase failed the one-sentence test in an obvious
  way: it carried a security fix, two plugin wizards, an analytics integration
  and the launch itself under one heading.
- **Done 2026-08-12: the byline fix.** `display_name` and `user_nicename` were
  both `casey`, identical to the login, and `/wp-json/wp/v2/users` publishes
  both without authentication. Confirmed exposed on the live site while 48
  failed login attempts were recorded in 24 hours, so a bot could read a valid
  username and needed only the password. Now `SimBuds` and `simbuds`, verified
  two ways: the REST endpoint returns the new values, and `/?author=1` redirects
  to `/author/simbuds/` rather than `/author/casey/`.
- Why that closed it without blocking the endpoint: enumeration still works, but
  what it returns is a byline rather than a credential. Blocking
  `/wp-json/wp/v2/users` outright would break the block editor's author selector
  and buy nothing once the exposed value is not a login.
- Timing mattered: `user_nicename` is the author-archive URL, so changing it
  before anything is indexed costs nothing, and changing it afterwards moves a
  URL Google knows about.
- **Done 2026-08-13: auto-updates enabled** for all plugins and all themes, now
  that Phase 13 has proven a restore. Recorded here rather than in Phase 13
  because it is hygiene, not backup work, and because the decision it closes is
  logged in the decisions list above.
- Remaining members after the split: `blog_public` to `1`, submitting the
  sitemap, activating `wp-super-cache`, and taking one manual Lightsail snapshot
  as a fast-restore point now that the host is fully configured.
- Ordering constraint already known: `blog_public` flips last, and the sitemap
  is submitted the same day, because submitting it while the site is noindexed
  teaches Search Console the sitemap is broken.
- These four belong together in one phase because they are one event. The site
  is either launched or it is not, and half of this list applied is a site that
  is indexed with no cache or cached with no index.

### Phase 18: e4c_field() reconstructs repeater rows when the fields plugin is inactive.
- Status: planned 2026-08-13, **found by measurement during Phase 14** rather
  than by reading the code. Low priority and not a launch blocker. Can run
  before or after Phase 17.
- One-line goal: stop the degraded path from rendering a row count where the
  rows should be.
- **The bug, observed not theorised.** ACF and SCF store a repeater as a count
  in the parent key plus one row per index: `e4c_pros` is `2`, and the values
  live in `e4c_pros_0_text` and `e4c_pros_1_text`. The fallback in
  `theme/inc/template-helpers.php` calls `get_post_meta( 'e4c_pros' )`, gets the
  string `2`, and `single-review.php` renders one bullet reading **"2"** under
  "What works". Reproduced in the container by deactivating
  `secure-custom-fields` against a post whose fields were written through the
  plugin.
- Pre-existing, and not introduced by Phase 14. It has been latent since the
  fallback was written, and could not surface in any earlier container run
  because no container had ACF, so no container had data in ACF's shape.
- Narrow trigger, which is why it is low priority: it needs data written by the
  plugin **and** the plugin later inactive. Phase 14 made that less likely by
  putting the plugin in `plugins.txt`, so a provisioned host always activates
  it. A manual deactivation is now the only route in.
- Likely shape of the fix: in `e4c_field()`, when the raw value is numeric and
  `{key}_0_` meta exists, rebuild the array of rows from the indexed keys.
  Sub-field names are already known to the templates, so the reconstruction can
  be generic rather than per field.
- Deliberately not fixed inside Phase 14, per the no-piggybacking rule. It is a
  behaviour change to a shared helper with its own scenarios and deserves its
  own diff and its own test.

### Phase 19: README's opening sections describe the repo as it was before the theme landed.
- Status: planned 2026-08-13, noticed while editing `README.md` during Phase 14
  and deliberately not fixed there, per no-piggybacking.
- Two concrete staleness bugs, both above the fold and both in the first thing
  a reader sees. The `## Layout` block lists `compose.yaml`, `docker/`,
  `plugins/e4c-compliance/` and `scripts/`, and omits `theme/` and
  `plugins/e4c-content/`, which are now two of the most important directories.
  The line under it still reads "The base theme is not here yet. Casey is
  supplying it", which stopped being true when Phase 8 landed.
- Small, and worth its own diff precisely because it is small: a doc fix mixed
  into a behaviour change is the kind of thing that gets reverted together with
  it.

## Phase reports
<!-- pasted at Stage 5, newest first -->

### Phase 14, 2026-08-13

**Changed.** Five files, exactly as planned. `scripts/plugins.txt` gains
`secure-custom-fields` and loses the "CANNOT be in this list, and still
required" block. `plugins/e4c-content/inc/admin-notices.php` and
`plugins/e4c-content/inc/fields.php` change wording and docblocks only.
`scripts/test-provision/verify.sh` gains three checks. `README.md` gains build
log Step 11.

**Verified before planning, which changed the plan.** The published SCF package
was downloaded and inspected at 6.9.5 rather than trusted from memory. All five
field types `fields.php` uses are present, including `repeater`, and
`acf_add_local_field_group`, `get_field`, `have_rows`, `acf_get_field_type` and
`acf_get_local_field_groups` all resolve under their ACF names. That is what
turned this from a purchase into a one-line change to `plugins.txt`.

**Tested.** `bash scripts/test-provision/run.sh`, exit 0, `ALL CHECKS PASSED`,
59 checks against the previous 56. `secure-custom-fields active` proves
`provision.sh` installed it from `plugins.txt` with no human step.

**Rendered through the real path for the first time.** A review was created in
the container with `update_field()` and fetched: 36,461 bytes, **zero** PHP
errors, and all six values present including both repeater rows and the specs
table. `wp post meta list` shows the underscore-paired keys (`_e4c_pros` holding
`field_e4c_pros`) that the raw-meta fallback never writes, which is the proof
the ACF path ran. Every render proven before this phase went through the
fallback, because no container had ACF.

**Found while verifying, deferred to Phase 18.** The fallback misreads
ACF-written repeaters and renders the row count as a list item. Measured by
deactivating the plugin and re-fetching, not inferred. Pre-existing, narrow
trigger, and out of scope here.

**Deferred.** The live deploy, which is Casey's. The Phase 18 fallback fix.

### Phase 8b, 2026-08-12

**Changed.** Seven new templates: `taxonomy-cat-category.php`,
`single-roundup.php`, `single.php`, `page-how-we-test.php`,
`page-newsletter.php`, `search.php`, `404.php`. Three files edited:
`theme/style.css` (component classes for the new parts), `theme/index.php` (its
header described these as unbuilt), `theme/README.md` (the screen map).
`scripts/test-provision/verify.sh` gained five checks.

**Tested.** `bash scripts/test-provision/run.sh`, exit 0, `ALL CHECKS PASSED`,
56 checks against the previous 51. 28 PHP files parse clean, with a control
proving the linter rejects broken input.

**Rendered with real content, which status codes alone would not have shown.** A
category term, a review, a guide, a roundup with a populated `e4c_picks` row, and
the two pages were created in the container. All six content-bearing screens
returned 200 with zero occurrences of fatal, parse error, warning, notice or
deprecated in the body.

- The roundup rendered its award label, its `why` line, the linked review's
  title, the rank marker, and the buy URL **read from the linked review** rather
  than duplicated onto the pick.
- The category archive rendered all three content types on one page, which is
  the entire reason `cat-category` is registered across `review`, `roundup` and
  `post`.
- All of it ran through the raw post-meta fallback, because the container has no
  ACF. That is the harder path and the one least likely to be exercised by hand.

**Decisions made while building, each recorded because each could have gone the
other way.** One taxonomy template rather than three per-type archives. Guides
as core posts rather than a third CPT. Search facets as links carrying
`post_type` rather than JavaScript, so a filtered view has a shareable URL and
works before any script loads, with zero-count facets not rendered at all. No
`aggregateRating` on the picks, matching the existing constraint. The newsletter
template renders no form of its own, because CASL puts the burden of proving
consent on the sender and the provider's confirmed opt-in record is that proof.

**The inline-style smell was not propagated.** The nine existing `style=`
attributes are unchanged, but the new templates use classes added to
`style.css` in the pagination section rather than appended to the bottom.

**Deferred.** The roundup archive at `/best/`, which `index.php` handles through
`the_archive_title()`. The nine pre-existing inline styles. **Deployment to the
live host**, which is the whole of what remains in Phase 8.

### Phase 8 (repo half), 2026-08-12

**Changed.** `scripts/provision.sh`, `scripts/test-provision/Dockerfile`,
`scripts/test-provision/verify.sh`, `scripts/plugins.txt`, `README.md`,
`theme/README.md`, `theme/style.css`,
`plugins/e4c-compliance/e4c-compliance.php`, plus the `e4c-theme/` to `theme/`
rename, the `design/` split, and six new files under `theme/assets/fonts/`.

**Tested.** `bash scripts/test-provision/run.sh`, exit 0, `ALL CHECKS PASSED`,
51 checks against the previous 42.

**The four blockers, each cleared with evidence:**

1. **Plugin deploy generalised.** `provision.sh` hardcoded a symlink for
   `e4c-compliance`, so `plugins/e4c-content` would never have reached the
   server and the review post types would not have existed. It now loops every
   directory under `plugins/`. The harness check is driven from the same
   directory listing rather than from plugin names, because a check naming one
   plugin would have missed this defect too. `review post type registered` and
   `roundup post type registered` catch the symptom, which presents as a broken
   theme rather than as a missing plugin.
2. **Fonts fetched.** Three woff2 faces, roughly 20 KB each, `wOF2` magic
   verified, with both OFL licence files because OFL 1.1 requires them on
   redistribution. Three new HTTP checks assert each returns 200, since a
   missing font never errors and simply renders in a fallback family.
3. **ACF Pro documented** in `scripts/plugins.txt` and `README.md`. It is
   commercial and not on wordpress.org, so no slug can install it. Recorded in
   `plugins.txt` and not only the README precisely because nothing breaks
   without it.
4. **The theme now renders, and is proven to.** `THEME_DIR=theme` is set as
   `ENV` in the test Dockerfile rather than inline, because `verify.sh` runs in
   its own layer. Before this, no template had ever been executed by anything.

**The walking skeleton was proven end to end**, beyond what the harness asserts.
A review post was created in the container with all five scalar fields, and the
rendered page returned 200 at 34,542 bytes with zero fatals, warnings, notices
or deprecations, every field value present in the output, `/reviews/` returning
200, and no Apache error-log lines. That also proves `e4c_field()`'s fallback
contract: the container has no ACF, so every field rendered from raw post meta.

**Also removed a suppression.** The old plugin activation ended in
`>/dev/null 2>&1 || true`. Activating an already-active plugin warns and exits
0, so the only thing that could ever hide was a genuine fatal in a plugin.

**One earlier false report, corrected.** The first PHP lint printed 21 syntax
errors. `php` is not installed on the workstation, so all 21 were the checker
failing rather than the files. Re-run inside the container with a control
proving the linter fails on broken input.

**Deferred.** Seven of nine screens unbuilt, with `index.php` catching them, in
the walking-skeleton order the theme README documents. Nine inline `style=`
attributes across four templates duplicate what the component layer should own.
One unused colour preset (`second-deepest`) and one unused spacing step (`20`),
both still reachable from the editor palette, so neither is provably dead.
**The live host still has none of this**, which is the remaining half of this
phase.

### Phase 10, 2026-08-11

**Changed.** Five tracked files. `scripts/provision.sh` (+100), `README.md`
(+93, of which the Step 5 build-log entry belongs to Phase 9),
`scripts/test-provision/verify.sh` (+17), `scripts/plugins.txt` (+10),
`scripts/test-provision/drive.py` (+5). `.gitignore` also appears in the diff
and belongs to neither phase, carried over from the QUESTIONS.md work.

**Surface overage, named.** The plan listed three files. `drive.py` was the
fourth, because the harness had to pass `ADMIN_DISPLAY_NAME` for the new byline
path to be exercised at all. `README.md` was the fifth, required by definition
of done item 4: `ADMIN_DISPLAY_NAME` is a new environment variable and the
README documents the provisioning interface.

**Tested.** `bash scripts/test-provision/run.sh`, exit 0, `ALL CHECKS PASSED`,
39 checks against the previous 34. `[drive] answered 0 password prompt(s)` on
the second pass, which is the idempotency evidence the harness itself provides.

**Each new check was shown to fail first**, against the built image rather than
a second full build. Breaking exactly the four conditions produced exactly four
failures:

```
FAIL  blog_public (noindex)               got=1 want=0
FAIL  display_name is not the login       got=0 want=1
FAIL  user_nicename is not the login      got=0 want=1
FAIL  akismet                             got=inactive want=MISSING
PASS  hello                               MISSING
```

`hello` staying green is the control: the failures are targeted rather than a
blanket break.

**The guard was proven, not inferred.** The harness runs `provision.sh` twice,
but `blog_public` was already `0` on both passes, so it cannot distinguish a
guarded set from an unconditional one. A separate run set `blog_public` to `1`
to simulate a launched site, redeployed over it, and confirmed it was still `1`
afterwards. `akismet`, reinstalled by hand, was deleted again by the same run,
proving the `:delete` branch is idempotent and self-healing.

**One consequence found in the change itself and documented rather than left
buried.** `ADMIN_DISPLAY_NAME` sits inside the core-install guard, so setting it
and re-running does nothing to an existing install. That is the correct default,
because the alternative overwrites a byline changed in wp-admin on every
redeploy, but it is surprising. Recorded in the code comment and in README.md
with the direct `wp user update` command for a running site.

**Deferred.** Rank Math's schema module, which is wizard state in the database
rather than something the provisioner sets, and stays documented in
`plugins.txt` as intent. The live server still has `display_name` equal to the
login, since it was provisioned before this existed. This phase stops the next
rebuild reintroducing the defect. It does not fix the running site, which is
still the one-line `wp user update` in Phase 14.

### Phase 9, 2026-08-11

**Changed.** Two files, neither of them code. `README.md` gained build log
Step 5. `IMPLEMENT.md` was updated for state, the 2026-08-11 inherited
decisions, Phases 9 to 13, and this report. `AGENTS.md` gained the question-log
rule and then a same-day correction to it. `.gitignore` gained `QUESTIONS.md`.
No file under `scripts/`, `plugins/` or `docker/` was touched.

**Tested.** `sudo sshd -T` on the host, filtered to seven directives, output
pasted in full. All seven already correct: password authentication off,
keyboard-interactive off, public key on, empty passwords off, root key-only,
port 22.

**The phase made no change, and that is the result.** The Ubuntu cloud image
ships this state. The plan explicitly allowed for this outcome rather than
treating "no edit" as a failure to deliver, so no change was manufactured to
have something to show.

**Re-planned before execution, not after.** The original goal was restricting
the Lightsail SSH source address. Casey's VPN rotates the exit address, so the
rule would have caused routine self-lockout. The replacement goal is not weaker:
source restriction is defence in depth, and key-only authentication is the
actual boundary. Recorded in the phase entry and in the README Q&A.

**One rule corrected the day it was written.** The question-log rule allowed the
tracked `README.md` build log to cite `QUESTIONS.md` rather than repeat an
answer. That was written while `QUESTIONS.md` was tracked. Casey then made it
gitignored, which turns any such citation into a pointer that dangles on a fresh
clone. The rule now requires build-log `Q&A` blocks to stay self-contained.

**Deferred.** Blocking `xmlrpc.php`, which is Phase 10. Tightening
`PermitRootLogin` to `no`, judged marginal and recorded in the README Q&A with
the reason rather than left as an unexplained omission. Testing whether the
console's browser SSH survives a restrictive firewall rule, which nothing
currently depends on because no route was closed.

### Phase 3, 2026-08-10

**Changed.** One file, one line: `scripts/test-provision/run.sh` lost an
`--exclude=reference` for a directory that does not exist. Overage against a
phase that planned to touch nothing, named here rather than absorbed. It was a
stale reference the Phase 1-2 sweep missed, because that sweep's pattern
required a trailing slash and this occurrence had none.

**Tested.** `bash scripts/test-provision/run.sh`, exit 0, `ALL CHECKS PASSED`.
Thirty-one PASS lines and two INFO lines across WordPress, plugins, filesystem,
Apache and HTTP. The four HTTP checks fetched real URLs and included a 404 that
had to be a 404, so the check could fail on the right thing.

**Two passes confirmed, not assumed.** Pass one answered 2 password prompts and
exited 0. Pass two answered **0** and exited 0. That zero is the idempotency
evidence: the second run never reached the prompt because the database and user
already existed, corroborated by `database everything4cats already exists,
leaving it alone` and by every plugin reporting already-active.

**The coverage gap, on the record.** Three steps skipped in both passes and are
still unproven anywhere: `theme` (THEME_DIR unset, which is the correct
behaviour and was verified as a loud skip rather than a silent one), `swap`
(containers cannot swapon), and `firewall and fail2ban` (no init system). All
three run for the first time on the real host in Phase 6, and are verified
there, not before.

**Environment change found mid-phase.** A git repository now exists at
`0a08f2b`, so the `git diff --stat` audit is available from here on. The
substitution used in Phases 1-2 is retired.

**Flagged, not acted on.** `.gitignore` lines 34 and 35 exclude `AGENTS.md` and
`PLAN.md`. Only `README.md` of the tracked pillars survives a clone, so the
rule about landing durable findings in the tracked docs now points at
`README.md` alone. Nothing functional depends on either file at runtime.

**Deferred.** Nothing.

### Phases 1 and 2, 2026-08-10

**Changed.** Ten files. `PLAN.md` and `README.md` rewritten. `AGENTS.md`
project section updated in two places (the production-host rule now names
Lightsail, and the label set gained a named AWS block). `scripts/provision.sh`,
`scripts/inventory.sh`, `docker/Dockerfile`, `docker/entrypoint.sh`,
`compose.yaml`, `scripts/plugins.txt` and
`plugins/e4c-compliance/e4c-compliance.php` had stale references replaced.

**Surface overage, named.** The plan listed six files across the two phases.
Four more were touched: `docker/entrypoint.sh`, `compose.yaml`,
`scripts/plugins.txt` and `e4c-compliance.php`. Each carried a stale reference
that the sweep found and that the request explicitly covered. The overage is
real and is recorded here rather than absorbed silently.

**Two findings worth keeping.**

1. The PLAN.md rewrite initially dropped the FTC and Competition Bureau
   disclosure requirement, which `e4c-compliance.php` cites as its reason for
   existing. The cross-reference check caught it. It is restored, and it is now
   stated more fully than before, including why the Canadian regime applies.
   This is why the code-to-doc pointers get checked rather than counted.
2. `inventory.sh` carried a metadata-service HTTP lookup against a
   provider-specific endpoint path. Against a different provider it returns
   nothing and prints blank fields rather than erroring, which reads as an
   answer. It was removed rather than retargeted, and the two facts it produced
   come from the console section instead, where they cannot be silently empty.

**Deferred.** Nothing from these phases. Phase 3 is unchanged and unstarted.
