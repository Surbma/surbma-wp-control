<?php

defined( 'ABSPATH' ) || die;

/**
 * Register WP Control menus in site admin (each site on multisite; all installs on single site).
 */
function surbma_wp_control_register_site_admin_menu() {
	if ( is_network_admin() ) {
		return;
	}

	add_menu_page(
		__( 'WP Control', 'surbma-wp-control' ),
		'WP Control',
		'manage_options',
		'surbma-wp-control',
		'surbma_wp_control_render_dashboard',
		'dashicons-code-standards',
		81
	);

	add_submenu_page(
		'surbma-wp-control',
		__( 'Dashboard', 'surbma-wp-control' ),
		__( 'Dashboard', 'surbma-wp-control' ),
		'manage_options',
		'surbma-wp-control',
		'surbma_wp_control_render_dashboard'
	);

	add_submenu_page(
		'surbma-wp-control',
		__( 'Images & thumbnails', 'surbma-wp-control' ),
		__( 'Images & thumbnails', 'surbma-wp-control' ),
		'manage_options',
		'surbma-wp-control-images',
		'surbma_wp_control_render_images_thumbnails'
	);

	add_submenu_page(
		'surbma-wp-control',
		__( 'External link checker', 'surbma-wp-control' ),
		__( 'External link checker', 'surbma-wp-control' ),
		'manage_options',
		'surbma-wp-control-external-links',
		'surbma_wp_control_render_external_link_checker'
	);
}
add_action( 'admin_menu', 'surbma_wp_control_register_site_admin_menu', 999 );
