<?php
/**
 * Dependency notices.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', 'e4c_content_acf_notice' );
/**
 * Warns when ACF Pro is missing. The post types still register without it, so
 * the content stays reachable; only the editing UI for the fields is gone.
 */
function e4c_content_acf_notice(): void {
	if ( function_exists( 'acf_add_local_field_group' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	?>
	<div class="notice notice-warning">
		<p>
			<?php
			esc_html_e( 'Everything4Cats Content: ACF Pro is not active. Reviews and roundups still work and stay published, but the verdict, pros, cons and specification fields have no editing UI until it is.', 'e4c-content' );
			?>
		</p>
	</div>
	<?php
}
