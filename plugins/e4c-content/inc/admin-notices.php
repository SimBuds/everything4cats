<?php
/**
 * Dependency notices.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', 'e4c_content_acf_notice' );
/**
 * Warns when the custom fields plugin is missing. The post types still register
 * without it, so the content stays reachable and only the editing UI is gone.
 *
 * The guard tests for the ACF function rather than for a plugin slug, which is
 * what lets Secure Custom Fields satisfy it unchanged: it is a fork of ACF and
 * keeps the original function names. The function name below is left as
 * e4c_content_acf_notice for the same reason the guard is, since ACF's API is
 * still what this depends on whichever plugin provides it.
 */
function e4c_content_acf_notice(): void {
	if ( function_exists( 'acf_add_local_field_group' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	?>
	<div class="notice notice-warning">
		<p>
			<?php
			esc_html_e( 'Everything4Cats Content: Secure Custom Fields is not active. Reviews and roundups still work and stay published, but the verdict, pros, cons and specification fields have no editing UI until it is.', 'e4c-content' );
			?>
		</p>
	</div>
	<?php
}
