<?php

// Admin options menu
include_once( SURBMA_WP_CONTROL_PLUGIN_DIR . '/pages/control-page.php' );
include_once( SURBMA_WP_CONTROL_PLUGIN_DIR . '/pages/plugin-manager.php' );
include_once( SURBMA_WP_CONTROL_PLUGIN_DIR . '/pages/theme-manager.php' );

function surbma_wp_control_add_menu() {
	global $surbma_wp_control_page;
	if ( is_plugin_active( 'surbma-premium-wp/surbma-premium-wp.php' ) ) {
		$surbma_wp_control_page = add_submenu_page( 'surbma-premium-wp-menu', __( 'WP Control', 'surbma-wp-control' ), __( 'WP Control', 'surbma-wp-control' ), 'update_core', 'surbma-wp-control', 'surbma_wp_control_page' );
	}
	else {
		$surbma_wp_control_page = add_menu_page( __( 'WP Control', 'surbma-wp-control' ), 'WP Control', 'update_core', 'surbma-wp-control', 'surbma_wp_control_page', 'dashicons-visibility' );
	}
}
add_action( 'admin_menu', 'surbma_wp_control_add_menu', 999 );

function surbma_wp_control_add_network_menu() {
	global $surbma_wp_control_active_plugins_page;
	$surbma_wp_control_active_plugins_page = add_submenu_page( 'plugins.php', __( 'Plugin manager', 'surbma-wp-control' ), __( 'Plugin manager', 'surbma-wp-control' ), 'manage_network_plugins', 'surbma-wp-control-plugin-manager', 'surbma_wp_control_plugin_manager' );
	global $surbma_wp_control_active_themes_page;
	$surbma_wp_control_active_themes_page = add_submenu_page( 'themes.php', __( 'Theme manager', 'surbma-wp-control' ), __( 'Theme manager', 'surbma-wp-control' ), 'manage_network_themes', 'surbma-wp-control-theme-manager', 'surbma_wp_control_theme_manager' );
}
add_action( 'network_admin_menu', 'surbma_wp_control_add_network_menu' );

// Show site ID on network admin all sites page.
function surbma_wp_control_site_id( $columns ) {
	if ( !defined( 'WPE_APIKEY' ) )
		$columns['surbma_site_id'] = 'ID';
	$columns['surbma_ssl'] = 'SSL';
	return $columns;
}
add_filter( 'wpmu_blogs_columns', 'surbma_wp_control_site_id' );

function surbma_wp_control_site_id_columns( $column, $blog_id ) {
	if ( !defined( 'WPE_APIKEY' ) && $column == 'surbma_site_id' ) {
		echo $blog_id;
	}
	if ( $column == 'surbma_ssl' ) {
		$ssl = strpos( get_blog_option( $blog_id, 'siteurl' ), 'https' ) === 0 ? 'HTTPS' : '-';
		echo $ssl;
	}
}
add_action( 'manage_sites_custom_column', 'surbma_wp_control_site_id_columns', 10, 3 );
add_action( 'manage_blogs_custom_column', 'surbma_wp_control_site_id_columns', 10, 3 );

// Custom styles and scripts for admin pages
function surbma_wp_control_admin_scripts( $hook ) {
	global $surbma_wp_control_page;
	global $surbma_wp_control_active_plugins_page;
	global $surbma_wp_control_active_themes_page;
	if ( $hook == $surbma_wp_control_page || $hook == $surbma_wp_control_active_plugins_page || $hook == $surbma_wp_control_active_themes_page ) {
		wp_enqueue_style( 'surbma-wp-control', SURBMA_WP_CONTROL_PLUGIN_URL . '/css/admin.css' );
	}
}
add_action( 'admin_enqueue_scripts', 'surbma_wp_control_admin_scripts' );

// Custom text in admin footer
function surbma_wp_control_custom_admin_footer( $text ) {
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
}
add_filter( 'admin_footer_text', 'surbma_wp_control_custom_admin_footer', 999 );

// Remove the version number from the right bottom of admin footer
function surbma_wp_control_remove_footer_version() {
	remove_filter( 'update_footer', 'core_update_footer' );
}
add_action( 'admin_init', 'surbma_wp_control_remove_footer_version' );

// Remove the WP logo from the Admin bar
function surbma_wp_control_remove_wp_logo( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'wp-logo' );
}
add_action( 'admin_bar_menu', 'surbma_wp_control_remove_wp_logo', 999 );

// Fix for Soliloquy menu capability in a Multisite Network
function surbma_wp_control_soliloquy_change_cap( $cap ) {
	if ( class_exists( 'Soliloquy' ) ) {
		return 'install_plugins';
	}
}
add_filter( 'soliloquy_menu_cap', 'surbma_wp_control_soliloquy_change_cap' );

// Remove some unwanted Widgets
function surbma_wp_control_remove_widgets() {
	unregister_widget( 'WP_Widget_Pages' );
	unregister_widget( 'WP_Widget_Calendar' );
	unregister_widget( 'WP_Widget_Links' );
	unregister_widget( 'WP_Widget_Meta' );
	wp_unregister_sidebar_widget( 'wpe_widget_powered_by' );
}
add_action( 'widgets_init', 'surbma_wp_control_remove_widgets' );

// Remove some unwanted Dashboard Widgets
function surbma_wp_control_remove_dashboard_widgets() {
	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_plugins', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
	remove_meta_box( 'wpe_dify_news_feed', 'dashboard', 'core' );
}
add_action( 'wp_dashboard_setup', 'surbma_wp_control_remove_dashboard_widgets' );

// Disable Welcome Screen
remove_action( 'welcome_panel', 'wp_welcome_panel' );

// Remove WordPress core update notifications from the admin
function surbma_wp_control_hide_update_notice() {
	remove_action( 'admin_notices', 'update_nag', 3 );
	remove_action( 'network_admin_notices', 'update_nag', 3 );
}
add_action( 'admin_head', 'surbma_wp_control_hide_update_notice' );

// Enable Gravity Forms visibility option for form fields
function surbma_wp_control_add_gf_visibility_setting() {
	if ( class_exists( 'GFForms' ) ) {
		add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );
	}
}
add_action( 'init', 'surbma_wp_control_add_gf_visibility_setting' );

// Let's fix some things for WooCommerce
function surbma_wp_control_woocommerce_fixes() {
	if ( class_exists( 'WooCommerce' ) ) {
		remove_action( 'admin_notices', 'woothemes_updater_notice' );
		add_filter( 'woocommerce_helper_suppress_admin_notices', '__return_true' );
	}
}
add_action( 'init', 'surbma_wp_control_woocommerce_fixes' );
