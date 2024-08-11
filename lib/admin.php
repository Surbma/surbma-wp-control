<?php

// Admin options menu
include_once( SURBMA_WP_CONTROL_PLUGIN_DIR . '/pages/control-page.php' );
include_once( SURBMA_WP_CONTROL_PLUGIN_DIR . '/pages/plugin-manager.php' );
include_once( SURBMA_WP_CONTROL_PLUGIN_DIR . '/pages/theme-manager.php' );

// Add WP Control menu
add_action( 'admin_menu', function() {
	global $surbma_wp_control_page;
	if ( is_plugin_active( 'surbma-premium-wp/surbma-premium-wp.php' ) ) {
		$surbma_wp_control_page = add_submenu_page( 'surbma-premium-wp-menu', __( 'WP Control', 'surbma-wp-control' ), __( 'WP Control', 'surbma-wp-control' ), 'update_core', 'surbma-wp-control', 'surbma_wp_control_page' );
	}
	else {
		$surbma_wp_control_page = add_menu_page( __( 'WP Control', 'surbma-wp-control' ), 'WP Control', 'update_core', 'surbma-wp-control', 'surbma_wp_control_page', 'dashicons-visibility' );
	}
}, 999 );

// Add Network menu items
add_action( 'network_admin_menu', function() {
	global $surbma_wp_control_active_plugins_page;
	$surbma_wp_control_active_plugins_page = add_submenu_page( 'plugins.php', __( 'Plugin manager', 'surbma-wp-control' ), __( 'Plugin manager', 'surbma-wp-control' ), 'manage_network_plugins', 'surbma-wp-control-plugin-manager', 'surbma_wp_control_plugin_manager' );
	global $surbma_wp_control_active_themes_page;
	$surbma_wp_control_active_themes_page = add_submenu_page( 'themes.php', __( 'Theme manager', 'surbma-wp-control' ), __( 'Theme manager', 'surbma-wp-control' ), 'manage_network_themes', 'surbma-wp-control-theme-manager', 'surbma_wp_control_theme_manager' );
} );

// Custom styles and scripts for admin pages
add_action( 'admin_enqueue_scripts', function( $hook ) {
	global $surbma_wp_control_page;
	global $surbma_wp_control_active_plugins_page;
	global $surbma_wp_control_active_themes_page;
	if ( $hook == $surbma_wp_control_page || $hook == $surbma_wp_control_active_plugins_page || $hook == $surbma_wp_control_active_themes_page ) {
		wp_enqueue_style( 'surbma-wp-control', SURBMA_WP_CONTROL_PLUGIN_URL . '/css/admin.css' );
	}
} );

// https://github.com/audrasjb/disable-gutenberg-default-fullscreen-mode
add_action( 'enqueue_block_editor_assets', function() {
	$script = "jQuery( window ).load(function() { const isFullscreenMode = wp.data.select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' ); if ( isFullscreenMode ) { wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' ); } });";
	wp_add_inline_script( 'wp-blocks', $script );
} );

// Custom text in admin footer
add_filter( 'admin_footer_text', function( $text ) {
	$admin_footer = '';
	if ( defined( 'SURBMA_WP_CONTROL_ADMIN_FOOTER' ) )
		$admin_footer = SURBMA_WP_CONTROL_ADMIN_FOOTER;
	if ( $admin_footer == '' ) {
		return $text;
	}
	elseif ( $admin_footer === true ) {
		$blogname = get_option( 'blogname' );
		return '<a href="' . get_site_option( 'siteurl' ) . '" target="_blank">' . get_site_option( 'site_name', $blogname ) . '</a>';
	}
	else {
		return $admin_footer;
	}
}, 999 );

// Fix for Soliloquy menu capability in a Multisite Network
add_filter( 'soliloquy_menu_cap', function( $cap ) {
	if ( class_exists( 'Soliloquy' ) ) {
		return 'unfiltered_html';
	}
} );

// Enable Gravity Forms visibility option for form fields
add_action( 'init', function() {
	if ( class_exists( 'GFForms' ) ) {
		add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );
	}
} );

// Let's fix some things for WooCommerce
add_action( 'init', function() {
	if ( class_exists( 'WooCommerce' ) ) {
		remove_action( 'admin_notices', 'woothemes_updater_notice' );
		add_filter( 'woocommerce_helper_suppress_admin_notices', '__return_true' );
	}
} );
