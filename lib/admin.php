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

// Show site ID on network admin all sites page.
add_filter( 'wpmu_blogs_columns', function( $columns ) {
	$columns['surbma_posts'] = 'Posts';
	$columns['surbma_pages'] = 'Pages';
	$columns['surbma_comments'] = 'Comments';
	if ( !defined( 'WPE_APIKEY' ) )
		$columns['surbma_site_id'] = 'ID';
	$columns['surbma_ssl'] = 'SSL';
	$columns['surbma_public'] = 'INDEX';
	return $columns;
} );

function surbma_wp_control_site_id_columns( $column, $blog_id ) {
	if ( $column == 'surbma_posts' ) {
		switch_to_blog( $blog_id );
		$post_count = wp_count_posts();
		echo 'Published: ' . $post_count->publish . '<br>';
		echo 'Future: ' . $post_count->future . '<br>';
		echo 'Draft: ' . $post_count->draft . '<br>';
		echo 'Pending: ' . $post_count->pending . '<br>';
		echo 'Private: ' . $post_count->private . '<br>';
		echo 'Trash: ' . $post_count->trash;
		restore_current_blog();
	}
	if ( $column == 'surbma_pages' ) {
		switch_to_blog( $blog_id );
		$page_count = wp_count_posts( 'page' );
		echo 'Published: ' . $page_count->publish . '<br>';
		echo 'Future: ' . $page_count->future . '<br>';
		echo 'Draft: ' . $page_count->draft . '<br>';
		echo 'Pending: ' . $page_count->pending . '<br>';
		echo 'Private: ' . $page_count->private . '<br>';
		echo 'Trash: ' . $page_count->trash;
		restore_current_blog();
	}
	if ( $column == 'surbma_comments' ) {
		switch_to_blog( $blog_id );
		$comments = wp_count_comments();
		echo 'Pending: ' . $comments->moderated . '<br>';
		echo 'Approved: ' . $comments->approved . '<br>';
		echo 'Spam: ' . $comments->spam . '<br>';
		echo 'Trash: ' . $comments->trash . '<br>';
		echo '------------<br>';
		echo '<strong>Total: ' . $comments->total_comments . '</strong>';
		restore_current_blog();
	}
	if ( !defined( 'WPE_APIKEY' ) && $column == 'surbma_site_id' ) {
		echo $blog_id;
	}
	if ( $column == 'surbma_ssl' ) {
		$ssl = strpos( get_blog_option( $blog_id, 'siteurl' ), 'https' ) === 0 ? '✔' : 'NO SSL';
		echo $ssl;
	}
	if ( $column == 'surbma_public' ) {
		$public = get_blog_option( $blog_id, 'blog_public' ) == 1 ? '✔' : 'NOINDEX';
		echo $public;
	}
}
add_action( 'manage_sites_custom_column', 'surbma_wp_control_site_id_columns', 10, 2 );
add_action( 'manage_blogs_custom_column', 'surbma_wp_control_site_id_columns', 10, 2 );

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

// Some css fixes for the admin
add_action( 'admin_head', function() {
	echo '<style>#wpmu-install-dashboard, .notice.litespeed-banner-promo-full {display: none !important;}</style>';
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

// Remove the version number from the right bottom of admin footer
add_action( 'admin_init', function() {
	remove_filter( 'update_footer', 'core_update_footer' );
} );

// Remove the WP logo from the Admin bar
add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'wp-logo' );
}, 999 );

// Fix for Soliloquy menu capability in a Multisite Network
add_filter( 'soliloquy_menu_cap', function( $cap ) {
	if ( class_exists( 'Soliloquy' ) ) {
		return 'unfiltered_html';
	}
} );

// Remove some unwanted Widgets
add_action( 'widgets_init', function() {
	unregister_widget( 'WP_Widget_Pages' );
	unregister_widget( 'WP_Widget_Calendar' );
	unregister_widget( 'WP_Widget_Links' );
	unregister_widget( 'WP_Widget_Meta' );
	wp_unregister_sidebar_widget( 'wpe_widget_powered_by' );
} );

// Remove some unwanted Dashboard Widgets
add_action( 'wp_dashboard_setup', function() {
	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_plugins', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'core' );
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
	remove_meta_box( 'wpe_dify_news_feed', 'dashboard', 'core' );
} );

// Disable Welcome Screen
remove_action( 'welcome_panel', 'wp_welcome_panel' );

// Remove WordPress core update notifications from the admin
add_action( 'admin_head', function() {
	remove_action( 'admin_notices', 'update_nag', 3 );
	remove_action( 'network_admin_notices', 'update_nag', 3 );
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
