# Everything4Cats — plan

## The idea

A WordPress affiliate site about cats: **blog content and product reviews**.
Revenue comes from affiliate commissions on products that get reviewed.

The audience is people who live with cats, and the job the site does is
organising their daily needs: toys, litter, food, furniture, and the rest of
the things a cat requires on a repeating schedule.

### Why this niche

Three things have to be true at once for an affiliate review site to work, and
cat products are one of the few categories where all three hold.

- **Reviews are defensible.** The products can genuinely be owned and used, so
  a review can say something a spec sheet cannot. That is the whole basis of
  the site's credibility.
- **The affiliate programmes are real and open.** Chewy, Amazon Associates and
  pet insurance all run programmes a new site can actually join.
- **Product data is a photograph and a price**, not a licensed feed. Nothing on
  this site depends on data that has to be bought or maintained by hand.

Daily-needs categories are also repeat purchases, which is the difference
between a review that earns once and a review that keeps earning.

### The one constraint on content

**Pet health content edges toward YMYL.** Anything touching illness,
medication or diet needs either real sourcing or an explicit "ask your vet"
posture. Litter boxes, toys, furniture and food *storage* do not.

### What good looks like

Ten published reviews and four newsletter sends, before any further
engineering. Infrastructure is not the bottleneck on finding out whether
anyone wants the thing, and building more of it does not change that.

---

## Current state, 2026-08-10

The engineering is written and the hosting decision is made. Nothing is live.

### What exists

| | |
|---|---|
| `docker/`, `compose.yaml` | Ubuntu 24.04 container mirroring a real server: Apache, php-fpm, MySQL, WP-CLI, sshd. A **test harness**, not the deployment target. Also holds the two Apache configs the real host uses, the vhost and `e4c-xmlrpc.conf`. |
| `scripts/provision.sh` | Bare Ubuntu to a working site, idempotent, safe to re-run. |
| `scripts/test-provision/` | Builds a throwaway host, provisions it **twice**, then runs 42 checks. Costs nothing and burns no hosting credits. |
| `scripts/inventory.sh` | Read-only audit of a server, run once before anything is installed to establish the starting point. Writes nothing. |
| `scripts/plugins.txt` | The plugin baseline, read by `provision.sh` so the two cannot drift. |
| `plugins/e4c-compliance/` | Affiliate disclosure, `rel="sponsored nofollow"`, Article schema. **A plugin, not theme code.** |

### Decided

- **The domain is `everything4cats.ca`**, already registered.
- **Hosting is AWS Lightsail**, on an account carrying $100 of credits, using
  the **OS-only Ubuntu 24.04 blueprint**. Not the Lightsail WordPress
  blueprint, which ships the Bitnami stack at `/opt/bitnami`, a layout
  `provision.sh` does not target and which would discard the server build in
  this repository.
- **The plan is the $12 tier**: 2 GB memory, 2 vCPU, 60 GB SSD, 3 TB transfer.
  That is roughly 8.3 months of runway on $100, and the price is flat, so the
  date the credits end is known rather than calculated.
- **2 GB is the floor, not a preference.** MySQL, PHP workers and image
  processing sharing less is exactly where a WordPress host runs out of memory,
  and the process the kernel kills is usually MySQL, which presents as data
  corruption rather than as memory pressure.
- **EC2 was priced against this on 2026-08-10 and rejected.** Recorded so it is
  not rediscovered. A `t3.small` carries the same 2 GB at $0.0208 per hour,
  which is $15.18 a month, and on EC2 the disk, the public IPv4 address and
  the transfer are all billed separately: about $2.40 for a 30 GB gp3 volume
  and about $3.65 for an Elastic IP, reaching roughly $21 a month for half the
  disk. That is about 4.7 months of runway against 8.3. The decisive line is
  transfer: this plan includes 3 TB, while EC2 includes 100 GB a month and then
  charges about $0.09 per GB. On an image-heavy review site that is the one
  cost that scales with success rather than with time.
- **DNS stays at the registrar, Namecheap, on their BasicDNS nameservers.** An
  A record on the apex pointing at the Lightsail static IP, and a CNAME on
  `www` pointing at the apex. Delegating to Route 53 adds a billed hosted zone
  to buy nothing this site needs yet. Certbot follows the CNAME, so a single
  certificate covers both names.
- **A static IP is attached.** Without one the address is released when the
  instance stops and starts, which moves it under DNS and breaks TLS renewal
  later rather than now. On Lightsail it is included in the plan price while
  attached.
- **The Lightsail firewall is the outer layer and it always applies.** A port
  open in ufw but closed in the console is closed, and neither side explains
  why. This is the most common way a correctly provisioned host appears broken.

### What does not exist yet

- **The base theme.** Casey is supplying it. `provision.sh` therefore leaves
  WordPress on its bundled default unless `THEME_DIR` is set, rather than
  failing or activating something that is not there.
- Automatic snapshots. The instance exists with none configured, so nothing is
  backed up. Addressed with the other backup work once the site is live.
- Any content.
- A newsletter provider.
- TLS, mail, consent and analytics, all of which follow the server.

---

## Decisions

These are settled. They are not up for rediscovery without a reason.

**Disclosure is a legal requirement under two regimes.** The FTC's endorsement
guides apply because the audience is substantially American, and Canada's
Competition Bureau applies because the site operates from Canada on a `.ca`
domain. Both ask for the same thing in substance: a disclosure the reader
actually encounters before acting on the link, not one filed at the foot of the
page or on a separate policy page. This is the requirement
`plugins/e4c-compliance/` exists to meet.

**Compliance is a plugin, not theme code.** The disclosure, the sponsored-link
tagging and the Article schema are legal and structural obligations. Changing
how the site looks must not change whether it discloses. A theme change is a
normal event and must not be able to switch off a legal obligation as a side
effect.

**Disclosure and link tagging are structural, not conventions.** Both are
applied at render time rather than left to an author remembering to paste them.
A documented convention will eventually be missed, and the post it is missed on
is the one that most needed it.

**Affiliate tagging and disclosure key off one list of monetised domains**,
empty by default, set through the `e4c_compliance_affiliate_domains` filter,
not off "any outbound link". Keying off every outbound link tags editorial
citations as paid placements and prints a disclosure on articles that earn
nothing. Both are misstatements, and on a review site credibility is the
product.

**No `aggregateRating` schema on affiliate comparisons.** Marking one up as a
rating is what Google's self-serving-review policy prohibits, and the penalty
is a manual action rather than a lost rich result. Star ratings need a real,
defensible methodology before they need markup.

**The local database is disposable.** Only the repository transfers. Plugin
configuration and real content happen once, on the server. Local is a
development environment, not a staging copy.

**`provision.sh` is proven in a container before it touches the paid host.**
The harness runs it twice in a row, because every deploy after the first is a
re-run, and non-idempotency shows up as a duplicate database or a prompt
hanging forever in a non-interactive session. The container cannot prove the
systemd-dependent steps (swap, ufw, fail2ban, certbot), which report `skipped`
there rather than falsely passing. Those are proven on the real host only.

**Secrets never enter the repository.** No password, key, token or login in
code, commits, logs or documentation, local-only ones included. `provision.sh`
prompts with a silent read so credentials stay out of shell history and the
process list. AWS account identifiers, instance IDs and IP addresses stay out
too.

**The AWS account and the server are Casey's alone.** No agent runs a provider
CLI or opens a shell on the instance, read-only included. Server work is
written out as labelled commands and run by hand.

---

## Order of work

**Now:**

1. ~~**Prove `provision.sh` in the container.**~~ Done 2026-08-10. Two passes,
   exit 0 both times, 34 checks passed.
2. ~~**Create the Lightsail instance** and attach a static IP.~~ Done
   2026-08-11. Baseline captured and recorded in the README build log.
3. **Point DNS** at that address, ahead of installing WordPress. Installing
   against a bare IP and moving to the domain later forces a database-wide URL
   migration, and propagation takes time regardless.
4. **Provision the server** with `SITE_DOMAIN=everything4cats.ca`.
5. **Issue TLS** with certbot, once DNS resolves.

**Then:**

6. **Integrate the base theme** once it lands, by setting `THEME_DIR` and
   re-running `provision.sh`.
7. **Write ten reviews and four newsletter sends.** Nothing below this line
   matters if this produces nobody.
8. **Choose a newsletter provider.** beehiiv is free to 2,500 subscribers, Kit
   to 1,000, both verified against the vendors' own pricing pages. Mailchimp is
   out: its terms prohibit affiliate marketing as a business model, which is
   this site's model.
9. **Join an affiliate programme** and add its domains to the filter. Until
   then nothing is tagged and no disclosure renders, which is correct.
10. **Flip `blog_public` to 1**, last, and only once the fixtures are gone.
    That switch also gates the sitemaps, which is verified behaviour rather
    than an assumption.

---

## Known risks

**Pet health is YMYL-adjacent.** Reviews of objects are safe. Advice about
illness, diet or medication is not, and needs sourcing or an explicit deferral
to a vet.

**Amazon Associates is easy to join and easy to lose.** Its operating agreement
is strict about disclosure wording, about stating prices that go stale, and
about where links may appear. The compliance plugin covers disclosure and
`rel`. It does not cover quoting prices, which is a content discipline.

**Review sites live or die on trust.** The cheapest way to lose it is a review
of something never actually used. The second cheapest is a comparison table
nobody maintains.

**The credits run out.** $100 is a runway, not a budget. The monthly burn gets
read from the AWS console and recorded when the instance is created, so the
date it ends is known rather than discovered.
