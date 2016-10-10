<?php

// Admin options menu
include_once( SURBMA_WP_CONTROL_PLUGIN_DIR . '/pages/control-page.php' );

function surbma_wp_control_add_menu() {
	if ( is_plugin_active( 'surbma-premium-wp/surbma-premium-wp.php' ) ) {
		add_submenu_page( 'surbma-premium-wp-menu', __( 'WP Control', 'surbma-wp-control' ), __( 'WP Control', 'surbma-wp-control' ), 'update_core', 'surbma-wp-control', 'surbma_wp_control_page' );
	}
	else {
		add_menu_page( __( 'WP Control', 'surbma-wp-control' ), 'WP Control', 'update_core', 'surbma-wp-control', 'surbma_wp_control_page', 'dashicons-visibility' );
	}
}
add_action( 'admin_menu', 'surbma_wp_control_add_menu', 999 );

// Custom styles and scripts for admin pages
function surbma_wp_control_admin_scripts( $hook ) {
    if ( $hook == 'toplevel_page_surbma-wp-control' ) {
    	wp_enqueue_style( 'surbma-wp-control', SURBMA_WP_CONTROL_PLUGIN_URL . '/css/admin.css' );
    }
}
add_action( 'admin_enqueue_scripts', 'surbma_wp_control_admin_scripts' );

// Custom text in admin footer
function surbma_wp_control_custom_admin_footer( $text ) {
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
add_filter( 'admin_footer_text', 'surbma_wp_control_custom_admin_footer' );

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

// Remove some unwanted Jetpack modules
function surbma_wp_control_disable_jetpack_modules ( $modules ) {
	$pwp_control_jp_mods_to_disable = array(
		'after-the-deadline',
		'comments',
		'contact-form',
		'gravatar-hovercards',
		'infinite-scroll',
		'latex',
		'likes',
		'markdown',
		'photon',
		'post-by-email',
		'shortlinks',
		'sso',
		'subscriptions',
		'vaultpress',
		'verification-tools',
		'videopress',
		'wpcc',
	);

	foreach ( $pwp_control_jp_mods_to_disable as $mod ) {
		if ( isset( $modules[$mod] ) ) {
			unset( $modules[$mod] );
		}
	}

	if ( !defined( 'SURBMA_WP_CONTROL_JETPACK_ENABLE_PROTECT' ) ) {
		unset( $modules['protect'] );
	}

	return $modules;
}

// No active modules upon Jetpack activation
function surbma_wp_control_set_jetpack_modules() {
	if ( class_exists( 'Jetpack' ) ) {
		add_filter( 'jetpack_get_default_modules', '__return_empty_array' );
		add_filter( 'jetpack_get_available_modules', 'surbma_wp_control_disable_jetpack_modules' );
	}
}
add_action( 'init', 'surbma_wp_control_set_jetpack_modules', 11 );

// Enable Gravity Forms visibility option for form fields
function surbma_wp_control_add_gf_visibility_setting() {
	if ( class_exists( 'GFForms' ) ) {
		add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );
	}
}
add_action( 'init', 'surbma_wp_control_add_gf_visibility_setting' );

// Remove the WooThemes Helper notice from the admin
function surbma_wp_control_remove_woothemes_helper_nag() {
	if ( class_exists( 'WooCommerce' ) ) {
		remove_action( 'admin_notices', 'woothemes_updater_notice' );
	}
}
add_action( 'init', 'surbma_wp_control_remove_woothemes_helper_nag' );
