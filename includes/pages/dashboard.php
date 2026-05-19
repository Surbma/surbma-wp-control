<?php

defined( 'ABSPATH' ) || die;

/**
 * Render the WP Control dashboard page.
 */
function surbma_wp_control_render_dashboard() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'WP Control', 'surbma-wp-control' ); ?></h1>

		<div class="card">
			<h2 class="title"><?php esc_html_e( 'Dashboard', 'surbma-wp-control' ); ?></h2>
			<p><?php esc_html_e( 'Central data view coming soon.', 'surbma-wp-control' ); ?></p>
		</div>
	</div>
	<?php
}
