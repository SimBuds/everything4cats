# Everything 4 Cats - Theme

Directory `theme/`, which is also the slug WordPress records. The display name
in `style.css` is "Everything 4 Cats - Theme". Author: SimBuds.

Hybrid WordPress theme. Classic PHP templates render the field-driven pages
(reviews, roundups, home); `theme.json` carries the tokens so prose written in
Gutenberg looks like the published article instead of the editor's defaults.

Presentation only. Post types, fields and compliance live in plugins.

## Requires

- WordPress 6.5+, PHP 8.1+
- `e4c-content` (this repo, `plugins/e4c-content/`) — review and roundup post
  types, ACF field groups
- `e4c-compliance` (existing) — disclosure injection, outbound link tagging,
  Article schema
- ACF Pro — repeaters back the pros/cons lists and the spec table

## Install

1. Deploy `theme/`. `scripts/provision.sh` does this by symlink when run with
   `THEME_DIR=theme`, so a `git pull` on the server is the whole deploy.
2. Copy `plugins/e4c-content/` to `wp-content/plugins/e4c-content/` and activate
   it. Activation flushes rewrites, so `/reviews/` resolves on the first request.
3. Drop the three woff2 files into `assets/fonts/` (see below). **Not yet done:
   the directory exists and is empty**, so the font-face rules `theme.json`
   generates currently point at 404s and both families fall back.
4. Settings → Reading → assign a static front page to edit the hero and patch
   patterns. Without one, the theme renders the hero pattern directly.
5. Appearance → Menus → assign **Primary**, **Footer** and **Legal**.

## Fonts

`theme.json` declares two self-hosted families with `font-display: swap` and
expects:

    assets/fonts/caprasimo-400.woff2
    assets/fonts/figtree-variable.woff2
    assets/fonts/figtree-variable-italic.woff2

Self-hosted on purpose: `inc/assets.php` also strips the `fonts.gstatic.com`
preconnect core adds, so nothing opens a connection to a host the site never
contacts.

## What this theme deliberately does not do

Each of these is owned elsewhere, and doing it here would double it:

| Not here | Owner | Cost of duplicating |
| --- | --- | --- |
| Article JSON-LD | `e4c-compliance`, every singular view | Two Article nodes on one page is a validation error |
| Affiliate disclosure | `e4c-compliance`, injected as `.art-disclose` above the first paragraph when `_e4c_post_affiliate` is set | Two differently worded notices stack |
| `rel="sponsored nofollow"` on outbound buy links | `e4c-compliance` | Fights the plugin's own filter |
| Post type + field registration | `e4c-content` | A theme switch would 404 every review |

The theme does register `e4c-hero` (1600×900, hard crop) in `inc/setup.php`,
because `e4c-compliance` requests that size for the schema image. Unregistered,
WordPress falls back to the full-size upload and the structured data advertises
a camera-resolution file.

## Tokens

`theme.json` is the single source of truth. It generates `--wp--preset--*`
custom properties that both the editor and the front end read; `style.css`
aliases them to `--e4c-*` for legibility and never restates a literal value.
Changing a colour means editing `theme.json`, not the stylesheet.

Palette is **Pale sky & plum**: `#eef7fd` ground, `#3685ab` brand,
`#a0678c` second voice. `figma/tokens.json` is the design-side export of the
same set, in W3C design-token format for Tokens Studio.

## Structure

    theme/
      theme.json              tokens: colour, type, spacing, block styles
      style.css               header, sheet, component layer
      functions.php           wiring only
      inc/
        setup.php             supports, menus, image sizes (incl. e4c-hero)
        assets.php            stylesheet, editor styles, font hint cleanup
        patterns.php          pattern category
        template-helpers.php  e4c_field(), e4c_hero_image(), e4c_button()
      header.php footer.php index.php
      front-page.php          hero pattern + review and roundup feeds
      single-review.php       the field-driven review
      archive-review.php
      template-parts/
        card-post.php         one card, used by every grid
        related-reviews.php
      patterns/
        home-hero.php
        newsletter-patch.php

## Screens

Built in walking-skeleton order: sitewide parts, then one review end to end,
then home, so an integration mismatch surfaced on the first template rather than
the ninth. The remaining seven landed 2026-08-12.

| Screen | Template |
| --- | --- |
| Home | `front-page.php` |
| Review | `single-review.php` |
| Review archive | `archive-review.php` |
| Category | `taxonomy-cat-category.php` |
| Roundup | `single-roundup.php` |
| Guide article | `single.php` |
| How we test | `page-how-we-test.php` |
| Newsletter | `page-newsletter.php` |
| Search | `search.php` |
| Not found | `404.php` |
| Everything else | `index.php` |

`index.php` still catches the roundup archive at `/best/`, tag, author and date
archives, and the posts index. Each is a list of cards with a heading, so a
template per case would be five copies differing only in the title.

Guides are core posts, not a third custom type. They carry the same
`cat-category` taxonomy, and nothing about a guide needs a field prose cannot
express, so a CPT would have bought a menu item and cost a migration.

`page-how-we-test.php` and `page-newsletter.php` are named for their slugs
**and** carry `Template Name` headers. The filename means a page at
`/how-we-test/` or `/newsletter/` picks them up with no admin step, and the
header means a page at any other slug can still select them.

The newsletter template deliberately renders **no form of its own**. The list
lives with the provider, and their embed goes in the page body. CASL is opt-in
with the burden of proving consent on the sender, and the provider's confirmed
opt-in record is that proof. A form posting to this site would collect addresses
with no defensible consent trail.

## Field contract

`e4c_field()` reads through ACF when it is active and falls back to raw post
meta of the same name when it is not, so a template never renders empty just
because a plugin is off. Field names are therefore a contract between
`e4c-content` and this theme:

`e4c_dek`, `e4c_verdict`, `e4c_price`, `e4c_tested_for`, `e4c_buy_url`,
`e4c_pros[].text`, `e4c_cons[].text`, `e4c_specs[].label`, `e4c_specs[].value`,
`e4c_picks[].review|award|why`.
