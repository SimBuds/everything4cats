<?php
/**
 * Plugin Name: Everything4Cats Content
 * Description: Registers the review and roundup post types and their ACF field groups. Lives outside the theme so switching a theme cannot 404 the reviews or empty the admin.
 * Version: 0.1.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: SimBuds
 * License: GPL-2.0-or-later
 * Text Domain: e4c-content
 *
 * Why this is a plugin and not theme code, in the same terms as e4c-compliance:
 * deactivating the compliance plugin costs a disclosure, but losing post type
 * registration costs the content. Every review 404s and disappears from the
 * admin the moment the theme changes. A theme swap must be able to change how
 * the site looks and nothing else.
 *
 * Fields are registered in PHP rather than stored in the database so they are
 * version-controlled and deploy with the code. ACF Pro is a deliberate paid
 * dependency: repeater and flexible-content fields are what pros and cons
 * lists and spec tables actually need.
 */

defined( 'ABSPATH' ) || exit;

define( 'E4C_CONTENT_VERSION', '0.1.0' );
define( 'E4C_CONTENT_PATH', plugin_dir_path( __FILE__ ) );

require_once E4C_CONTENT_PATH . 'inc/post-types.php';
require_once E4C_CONTENT_PATH . 'inc/taxonomies.php';
require_once E4C_CONTENT_PATH . 'inc/fields.php';
require_once E4C_CONTENT_PATH . 'inc/admin-notices.php';

register_activation_hook( __FILE__, 'e4c_content_activate' );
/**
 * Registers the post types once, then flushes, so the archive permalinks exist
 * on the first request after activation rather than after a manual resave.
 */
function e4c_content_activate(): void {
	e4c_content_register_post_types();
	e4c_content_register_taxonomies();
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
