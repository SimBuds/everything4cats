<?php
/**
 * Everything4Cats theme bootstrap. Wiring only: presentation helpers live in inc/.
 *
 * Deliberately absent, and not to be added here:
 * - Article or Product JSON-LD. plugins/e4c-compliance already emits Article
 *   schema on every singular view, and two Article nodes on one page is an
 *   error rather than a tie-break.
 * - Post type and field registration. Those live in plugins/e4c-content, so a
 *   theme switch cannot 404 the review archive or empty the admin.
 * - Affiliate disclosure and rel="sponsored" tagging. Compliance is a legal
 *   obligation and must not be switchable by changing how the site looks.
 */

defined( 'ABSPATH' ) || exit;

define( 'E4C_THEME_VERSION', '0.1.0' );

require_once get_theme_file_path( 'inc/setup.php' );
require_once get_theme_file_path( 'inc/assets.php' );
require_once get_theme_file_path( 'inc/patterns.php' );
require_once get_theme_file_path( 'inc/template-helpers.php' );
require_once get_theme_file_path( 'inc/admin-image-check.php' );
