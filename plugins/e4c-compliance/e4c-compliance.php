<?php
/**
 * Plugin Name: Everything4Cats Compliance
 * Description: Affiliate disclosure, sponsored/nofollow tagging and Article schema. Deliberately a plugin, not theme code.
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Author: SimBuds
 * License: GPL-2.0-or-later
 *
 * WHY THIS IS A PLUGIN
 *
 * This started life inside a theme, in a file whose own comment said it was
 * "kept in its own file ... so a future theme change can carry it forward".
 * That change arrived on 2026-08-10 when the project pivoted to
 * Everything4Cats and a different base theme, and the prediction held: the
 * disclosure, the sponsored-link tagging and the Article schema are the parts
 * that must not die with a theme.
 *
 * They are legal and structural obligations, not presentation. Switch a theme
 * and the site should still tag paid links and still disclose. So they live
 * here, where deactivating them is a deliberate act rather than a side effect
 * of changing how the site looks.
 *
 * WHAT IT DOES
 *
 * 1. `rel="sponsored nofollow"` on links to monetised domains written into
 *    article prose — structural, so it does not depend on an author
 *    remembering.
 * 2. A per-article disclosure above the first paragraph. A site-wide footer bar
 *    is not a substitute: both the FTC and the Competition Bureau want a
 *    disclosure the reader meets *before* acting on a link, and a bar below the
 *    fold is after every link on the page.
 * 3. Article JSON-LD, carrying author and dates, with no `aggregateRating` —
 *    marking up an affiliate comparison as a rating is what Google's
 *    self-serving-review policy prohibits, and the penalty is a manual action.
 *
 * Both 1 and 2 key off ONE filterable list of monetised domains rather than off
 * "any outbound link". That distinction was learned the expensive way on the
 * previous project: the first version tagged editorial citations as paid
 * placements and printed a disclosure on articles that earned nothing. Both are
 * misstatements, and on a site whose credibility is the product they are the
 * expensive kind.
 *
 * The list is EMPTY by default, so nothing is tagged and no disclosure renders
 * until a programme is actually joined:
 *
 *     add_filter( 'e4c_compliance_affiliate_domains', function ( $d ) {
 *         $d[] = 'chewy.com';
 *         $d[] = 'amazon.com';
 *         return $d;
 *     } );
 *
 * @package Everything4Cats
 */

defined( 'ABSPATH' ) || exit;



/**
 * Domains whose links are monetised.
 *
 * Empty by default and filterable, so the list is configuration rather than a
 * code edit. Add hosts through the `e4c_compliance_affiliate_domains` filter as
 * programmes are joined.
 *
 * This list is what separates a paid link from a citation, and both the `rel`
 * filter and the disclosure key off it. Before it existed, every outbound link
 * was treated as an affiliate link, which marked editorial citations as paid
 * placements and printed an affiliate disclosure on articles that earned
 * nothing. Both are misstatements, and on a site whose credibility is the
 * product they are the expensive kind.
 *
 * @return string[] Lowercased bare domains, no scheme and no leading dot.
 */
function e4c_compliance_affiliate_domains() {
	$domains = (array) apply_filters( 'e4c_compliance_affiliate_domains', array() );

	return array_filter( array_map( 'strtolower', $domains ) );
}

/**
 * Whether a host belongs to a monetised domain.
 *
 * Matches subdomains, so `amazon.com` on the list covers `www.amazon.com` and
 * `smile.amazon.com`. The boundary check on the dot is what stops `amazon.com`
 * from also matching a lookalike such as `notamazon.com`.
 *
 * @param string $host Host from a link's href.
 * @return bool
 */
function e4c_compliance_is_affiliate_host( $host ) {
	$host = strtolower( (string) $host );

	if ( '' === $host ) {
		return false;
	}

	foreach ( e4c_compliance_affiliate_domains() as $domain ) {
		if ( $host === $domain || str_ends_with( $host, '.' . $domain ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Force rel="sponsored nofollow" on affiliate links in post content.
 *
 * PLAN.md requires this be structural rather than a rule authors remember. A
 * documented convention that depends on tagging every affiliate link by hand
 * will eventually be missed, so it is applied at render time instead.
 *
 * Only links to a domain on the affiliate list are touched. Internal links,
 * anchors, and outbound editorial citations are left exactly as written: a
 * citation is a link this site vouches for, which is the opposite of what
 * `sponsored nofollow` tells a search engine.
 *
 * Existing rel values are preserved and merged rather than overwritten, so a
 * hand-set rel is never silently discarded.
 *
 * @param string $content Post content.
 * @return string
 */
function e4c_compliance_sponsor_outbound_links( $content ) {
	if ( empty( $content ) || false === strpos( $content, '<a ' ) ) {
		return $content;
	}

	// Nothing to do until at least one programme is configured.
	if ( ! e4c_compliance_affiliate_domains() ) {
		return $content;
	}

	// Tags are stripped from an excerpt anyway, so rewriting rel attributes
	// there is wasted work on every card in every listing.
	if ( doing_filter( 'get_the_excerpt' ) ) {
		return $content;
	}

	return preg_replace_callback(
		'/<a\s([^>]+)>/i',
		static function ( $matches ) {
			$attrs = $matches[1];

			if ( ! preg_match( '/href=(["\'])(.*?)\1/i', $attrs, $href ) ) {
				return $matches[0];
			}

			$host = wp_parse_url( $href[2], PHP_URL_HOST );

			if ( ! e4c_compliance_is_affiliate_host( $host ) ) {
				return $matches[0];
			}

			$rel = array( 'sponsored', 'nofollow' );

			if ( preg_match( '/rel=(["\'])(.*?)\1/i', $attrs, $existing ) ) {
				$rel   = array_unique( array_merge( preg_split( '/\s+/', trim( $existing[2] ) ), $rel ) );
				$attrs = str_replace( $existing[0], 'rel="' . esc_attr( implode( ' ', array_filter( $rel ) ) ) . '"', $attrs );
			} else {
				$attrs .= ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"';
			}

			return '<a ' . $attrs . '>';
		},
		$content
	);
}
add_filter( 'the_content', 'e4c_compliance_sponsor_outbound_links', 20 );

/**
 * Prepend an affiliate disclosure to any article that carries an outbound link.
 *
 * PLAN.md requires visible FTC and Competition Bureau disclosures. Both regimes
 * ask for the same thing in substance: a disclosure a reader actually encounters
 * before acting on the link, not one filed at the foot of the page or on a
 * separate policy page.
 *
 * Structural for the same reason the rel filter is. A disclosure that depends on
 * an author remembering to paste it is a disclosure that will eventually be
 * missing from the one post that most needed it, and "we have a documented
 * convention" is not a defence anyone has ever won with.
 *
 * Runs at 21, after the rel filter at 20, so it inspects the same content the
 * reader gets.
 *
 * @param string $content Post content.
 * @return string
 */
function e4c_compliance_affiliate_disclosure( $content ) {
	/*
	 * Guarded hard. `the_content` runs far more often than a page render: it
	 * fires inside wp_trim_excerpt(), so without this the disclosure would be
	 * baked into every card excerpt on the blog home, and inside feeds and REST
	 * responses too. The three conditions together mean this only ever applies to
	 * the article body of the page actually being read.
	 */
	if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	/*
	 * Not while an excerpt is being built. wp_trim_excerpt() runs the_content
	 * through this filter and then strips the tags, so without this the notice
	 * is flattened into plain text and glued to the front of the excerpt.
	 *
	 * The three conditions above were written for the previous theme, where no
	 * excerpt was ever generated on a singular page. The current theme (theme/,
	 * "Everything 4 Cats - Theme") generates one
	 * for the article dek, so this passed every guard and printed the disclosure
	 * directly under the headline, running into the first sentence with no space
	 * between them. Observed 2026-08-01 by screenshot.
	 */
	if ( doing_filter( 'get_the_excerpt' ) ) {
		return $content;
	}

	/*
	 * Keyed off the affiliate domain list, not off "any outbound link". An
	 * article that cites a news site earns nothing from it, and a disclosure
	 * claiming otherwise is inaccurate in the direction that costs trust: it
	 * trains readers to skip the notice on the articles where it is true.
	 */
	/*
	 * The theme also carries a manual "this post has affiliate links" checkbox
	 * (_e4c_post_affiliate). Honouring it here rather than rendering a second
	 * notice in single.php: an author who ticks the box should get the same
	 * disclosure, in the same place, not a second differently-worded one under
	 * the first. It also covers the case the domain list cannot see — a post
	 * monetised by a discount code rather than by a link.
	 */
	if ( '1' === get_post_meta( get_the_ID(), '_e4c_post_affiliate', true ) ) {
		return e4c_compliance_disclosure_markup() . $content;
	}

	$found = false;

	if ( preg_match_all( '/<a\s[^>]*href=(["\'])(.*?)\1/i', $content, $matches ) ) {
		foreach ( $matches[2] as $href ) {
			if ( e4c_compliance_is_affiliate_host( wp_parse_url( $href, PHP_URL_HOST ) ) ) {
				$found = true;
				break;
			}
		}
	}

	if ( ! $found ) {
		return $content;
	}

	return e4c_compliance_disclosure_markup() . $content;
}
add_filter( 'the_content', 'e4c_compliance_affiliate_disclosure', 21 );


/**
 * Emit Article structured data on single posts and pages.
 *
 * A theme's single-post template typically shows a publication line and, when a
 * post has been edited after publishing, a reviewed date. Those are readable by
 * a person and invisible to a search engine, which is the gap this closes.
 * Product reviews age, and a review whose freshness a search engine cannot read
 * is judged as though it were never updated.
 *
 * Deliberately conservative about what it claims:
 *
 * - `Article`, not `NewsArticle` or `Review`. The stricter types carry
 *   expectations this site does not meet, and a type that overstates what a page
 *   is invites a manual action rather than a rich result.
 * - `dateModified` is emitted only when the post really was modified after
 *   publication, matching the visible line exactly. A `dateModified` equal to
 *   `datePublished` on every post is noise that claims freshness nobody earned.
 * - No `aggregateRating`, no `Review`, no `priceRange`. This site will carry
 *   affiliate comparisons, and the temptation to mark them up as ratings is
 *   exactly what Google's structured-data policy on self-serving reviews
 *   prohibits.
 *
 * @return void
 */
function e4c_compliance_structured_data() {
	/*
	 * Reviews and roundups are on this list, and their absence was a real bug
	 * rather than a judgement call.
	 *
	 * This read is_singular( array( 'post', 'page' ) ) until 2026-08-17, which
	 * was correct when it was written and stopped being correct the moment
	 * plugins/e4c-content registered the review and roundup post types. The
	 * effect was that every page carrying the site's actual commercial content
	 * shipped with no structured data at all, while the Privacy Policy had it.
	 *
	 * It stayed invisible because it fails silently in both directions. Phase 16
	 * turned Rank Math's Schema module OFF specifically so it would not emit a
	 * second Article block alongside this one, so once this one stopped covering
	 * reviews there was nothing left to notice the gap. And the check that would
	 * have caught it, "exactly one Article block per page", was deferred out of
	 * Phase 16 because nothing was published yet to run it against. Found the
	 * first time it ran with content on the page.
	 *
	 * Filterable rather than hardcoded, matching how the affiliate domain list
	 * works, so a future post type is one filter rather than an edit here.
	 */
	$e4c_types = (array) apply_filters(
		'e4c_compliance_schema_post_types',
		array( 'post', 'page', 'review', 'roundup' )
	);

	if ( ! is_singular( $e4c_types ) ) {
		return;
	}

	$post = get_queried_object();

	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$data = array(
		'@context'         => 'https://schema.org',
		'@type'            => 'Article',
		'mainEntityOfPage' => array(
			'@type' => 'WebPage',
			'@id'   => get_permalink( $post ),
		),

		/*
		 * Google truncates headlines past 110 characters, and an over-long one is
		 * a documented reason for a page to be dropped from rich results.
		 * Truncating here rather than trusting every future title to be short.
		 */
		'headline'         => wp_html_excerpt( get_the_title( $post ), 110, '' ),
		'datePublished'    => get_the_date( DATE_W3C, $post ),
		'author'           => array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', $post->post_author ),
		),
		'publisher'        => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
		),
	);

	// Same condition as the visible "Reviewed" line in single.php. If the two
	// ever disagree, the markup is claiming something the page does not say.
	if ( get_the_modified_date( 'Ymd', $post ) > get_the_date( 'Ymd', $post ) ) {
		$data['dateModified'] = get_the_modified_date( DATE_W3C, $post );
	}

	$description = wp_strip_all_tags( get_the_excerpt( $post ) );

	if ( $description ) {
		$data['description'] = $description;
	}

	if ( has_post_thumbnail( $post ) ) {
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'e4c-hero' );

		if ( $image ) {
			$data['image'] = $image[0];
		}
	}

	/*
	 * No JSON_UNESCAPED_SLASHES on purpose. Leaving slashes escaped as \/ is what
	 * makes a literal </script> inside any string value harmless, so the block
	 * cannot be closed early by a post title or an excerpt.
	 */
	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $data )
	);
}
add_action( 'wp_head', 'e4c_compliance_structured_data' );

/**
 * The disclosure markup itself, in one place.
 *
 * Separated so the automatic path (a link to a monetised domain) and the manual
 * path (the author's checkbox) cannot drift into two different wordings.
 *
 * @return string
 */
function e4c_compliance_disclosure_markup() {
	return sprintf(
		'<aside class="art-disclose"><p><strong>%1$s</strong> %2$s</p></aside>',
		esc_html__( 'Disclosure:', 'e4c' ),
		esc_html__( 'Some links in this article are affiliate links. If you buy through one, this site may earn a commission at no extra cost to you. Commissions never determine which products are covered or what is said about them.', 'e4c' )
	);
}

/**
 * Styling for the disclosure.
 *
 * Enqueued only on singular views, which is the only place the disclosure can
 * render, so no other page pays for it. A theme that styles `.art-disclose`
 * itself overrides this by loading later.
 */
function e4c_compliance_styles() {
	if ( ! is_singular() ) {
		return;
	}

	$rel  = 'disclosure.css';
	$path = plugin_dir_path( __FILE__ ) . $rel;

	wp_enqueue_style(
		'e4c-compliance',
		plugin_dir_url( __FILE__ ) . $rel,
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : '1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'e4c_compliance_styles' );


/*
 * ---------------------------------------------------------------------------
 * THE EMPTY-LIST TRAP
 * ---------------------------------------------------------------------------
 *
 * Everything above keys off e4c_compliance_affiliate_domains(), which is empty
 * until a filter is registered. Empty is the correct state today: no programme
 * has been joined, so nothing is monetised and tagging or disclosing anything
 * would be a misstatement.
 *
 * The trap is that the list stays empty by doing nothing, and joining a
 * programme is a separate act in a separate place. On the day an affiliate
 * link is first pasted into an article, this plugin's two structural
 * guarantees quietly do not apply to it: no rel="sponsored nofollow", and no
 * disclosure unless the author also ticked _e4c_post_affiliate by hand. That
 * hand-tick is exactly the "documented convention an author remembers" that
 * the file header rejects as a defence.
 *
 * Nothing here can know which domains are monetised, and guessing is the
 * original sin this design exists to avoid. What it can do is refuse to let
 * the question go unasked: report the outbound hosts that published content
 * actually links to, minus the ones already on the list, and let a human say
 * which are commercial. An editorial citation showing up here is not a fault,
 * it is the check working and being answered "no".
 */

/**
 * Every outbound link host in published content, with a post count.
 *
 * Internal links are excluded by comparing against the home host, so a site
 * that links to itself does not register. The affiliate list is deliberately
 * NOT applied here: this is the raw scan, and it is what gets cached.
 *
 * Cached because it parses post bodies and Site Health runs it on a page load.
 * The cache is dropped whenever a post is saved, so the answer cannot lag the
 * content that changed it.
 *
 * @return array<string,int> Host => number of published posts linking to it.
 */
function e4c_compliance_outbound_hosts() {
	$cached = get_transient( 'e4c_compliance_outbound_hosts' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;

	// LIMIT is a guard against this becoming slow on a site far larger than
	// this one is planned to be, not a correctness boundary. If it is ever hit,
	// the check has stopped being complete and that is worth knowing.
	$contents = $wpdb->get_col(
		"SELECT post_content FROM {$wpdb->posts}
		 WHERE post_status = 'publish'
		   AND post_type NOT IN ('attachment', 'revision', 'nav_menu_item')
		 LIMIT 500"
	);

	$home  = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	$hosts = array();

	foreach ( (array) $contents as $content ) {
		if ( false === strpos( (string) $content, 'href' ) ) {
			continue;
		}

		if ( ! preg_match_all( '/<a\s[^>]*href=(["\'])(.*?)\1/i', $content, $matches ) ) {
			continue;
		}

		// Per post, not per link: three links to one shop is one post to check,
		// and counting links would make a comparison table look like a crisis.
		$seen = array();

		foreach ( $matches[2] as $href ) {
			$host = strtolower( (string) wp_parse_url( $href, PHP_URL_HOST ) );

			if ( '' === $host || isset( $seen[ $host ] ) ) {
				continue;
			}

			// Internal, including subdomains of the site's own host.
			if ( $host === $home || str_ends_with( $host, '.' . $home ) ) {
				continue;
			}

			/*
			 * `www.` is dropped so the report names the value that belongs on
			 * the list rather than the value that happened to be in the href.
			 * e4c_compliance_is_affiliate_host() matches subdomains, so
			 * `chewy.com` covers `www.chewy.com` while `www.chewy.com` does
			 * NOT cover a bare or differently-prefixed link. Reporting the
			 * href host verbatim invites copying the narrower one, which fails
			 * silently and in the direction that omits a disclosure.
			 *
			 * Only `www.` is stripped. Any other subdomain stays visible,
			 * because `shop.example.com` being monetised does not imply the
			 * parent is, and that is the author's call rather than this
			 * function's.
			 */
			if ( str_starts_with( $host, 'www.' ) ) {
				$host = substr( $host, 4 );
			}

			if ( '' === $host || isset( $seen[ $host ] ) ) {
				continue;
			}

			$seen[ $host ]  = true;
			$hosts[ $host ] = isset( $hosts[ $host ] ) ? $hosts[ $host ] + 1 : 1;
		}
	}

	arsort( $hosts );

	set_transient( 'e4c_compliance_outbound_hosts', $hosts, HOUR_IN_SECONDS );

	return $hosts;
}

/**
 * Outbound hosts that are not covered by the affiliate list.
 *
 * The list is subtracted here rather than inside the cached scan above, and
 * that split is the whole point. The scan is a function of the content and is
 * invalidated by saving a post. The affiliate list is a function of code, and
 * registering the filter fires no save hook at all. Caching the subtraction
 * would mean adding a domain and still being told for the next hour that it is
 * undeclared, which teaches the reader to disbelieve the check.
 *
 * @return array<string,int> Host => number of published posts linking to it.
 */
function e4c_compliance_unlisted_outbound_hosts() {
	$unlisted = array();

	foreach ( e4c_compliance_outbound_hosts() as $host => $count ) {
		if ( ! e4c_compliance_is_affiliate_host( $host ) ) {
			$unlisted[ $host ] = $count;
		}
	}

	return $unlisted;
}

/**
 * Drop the scan cache when content changes.
 */
function e4c_compliance_flush_outbound_cache() {
	delete_transient( 'e4c_compliance_outbound_hosts' );
}
add_action( 'save_post', 'e4c_compliance_flush_outbound_cache' );
add_action( 'deleted_post', 'e4c_compliance_flush_outbound_cache' );

/**
 * Site Health test: are any linked outbound hosts monetised but undeclared?
 *
 * Reported at Tools > Site Health rather than as an admin notice. A notice on
 * every screen for a condition that is usually correct is a notice that gets
 * dismissed and then ignored on the day it matters.
 *
 * The three outcomes are deliberately not "pass, warn, fail":
 *
 *   - Nothing links out at all: green. There is nothing to declare.
 *   - The list is populated and covers everything linked: green.
 *   - Something links out that is not on the list: orange, with the hosts
 *     named. Orange rather than red because an editorial citation is the
 *     expected answer and is not a defect.
 *
 * @return array Site Health result.
 */
function e4c_compliance_site_health_affiliate() {
	$listed   = e4c_compliance_affiliate_domains();
	$unlisted = e4c_compliance_unlisted_outbound_hosts();

	$result = array(
		'label'       => __( 'Affiliate links are tagged and disclosed', 'e4c' ),
		'status'      => 'good',
		'badge'       => array(
			'label' => __( 'Compliance', 'e4c' ),
			'color' => 'blue',
		),
		'description' => '',
		'actions'     => '',
		'test'        => 'e4c_compliance_affiliate',
	);

	if ( ! $unlisted ) {
		if ( $listed ) {
			$result['description'] = '<p>' . sprintf(
				/* translators: %d: number of declared affiliate domains. */
				esc_html__( 'Every outbound link in published content points at one of the %d declared affiliate domains, so each is tagged sponsored nofollow and its article carries a disclosure.', 'e4c' ),
				count( $listed )
			) . '</p>';
		} else {
			$result['description'] = '<p>' . esc_html__( 'No affiliate programme is declared and no published post links to an outside site, so there is nothing to tag or disclose. This is the expected state before the first programme is joined.', 'e4c' ) . '</p>';
		}

		return $result;
	}

	$result['status'] = 'recommended';
	$result['label']  = __( 'Outbound links point at hosts that are not declared affiliate domains', 'e4c' );

	$items = '';
	foreach ( $unlisted as $host => $count ) {
		$items .= sprintf(
			'<li><code>%1$s</code> %2$s</li>',
			esc_html( $host ),
			esc_html( sprintf(
				/* translators: %d: number of posts linking to this host. */
				_n( 'in %d published post', 'in %d published posts', $count, 'e4c' ),
				$count
			) )
		);
	}

	$result['description'] =
		'<p>' . esc_html__( 'These hosts are linked from published content and are not on the affiliate domain list. Links to them are left exactly as written: no sponsored nofollow, and no disclosure on the article.', 'e4c' ) . '</p>'
		. '<ul>' . $items . '</ul>'
		. '<p>' . esc_html__( 'That is correct for an editorial citation and wrong for a monetised link. Add any host you earn from to the list, in the theme functions file or a site plugin:', 'e4c' ) . '</p>'
		. '<pre>add_filter( \'e4c_compliance_affiliate_domains\', function ( $d ) {'
		. "\n\t" . '$d[] = \'' . esc_html( (string) array_key_first( $unlisted ) ) . '\';'
		. "\n\t" . 'return $d;'
		. "\n" . '} );</pre>'
		. '<p>' . esc_html__( 'Subdomains are covered automatically, so the bare domain is the right entry.', 'e4c' ) . '</p>';

	$result['actions'] = '<p>' . esc_html__( 'A link monetised by a discount code rather than by the URL will never appear here. Tick "This post has affiliate links" on the post itself for that case.', 'e4c' ) . '</p>';

	return $result;
}

/**
 * Register the test.
 */
function e4c_compliance_register_site_health( $tests ) {
	$tests['direct']['e4c_compliance_affiliate'] = array(
		'label' => __( 'Affiliate disclosure coverage', 'e4c' ),
		'test'  => 'e4c_compliance_site_health_affiliate',
	);

	return $tests;
}
add_filter( 'site_status_tests', 'e4c_compliance_register_site_health' );
