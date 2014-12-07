<?php
/* Admin options menu */
include_once( SURBMA_PWP_CONTROL_PLUGIN_DIR . '/pages/control-page.php' );

function surbma_pwp_control_add_menu() {
	add_menu_page( 'Prémium WordPress Control', 'PWP Control', 'update_core', 'surbma-pwp-control', 'surbma_pwp_control_page', SURBMA_PWP_CONTROL_PLUGIN_URL . '/images/star16.png' );
}
add_action( 'admin_menu', 'surbma_pwp_control_add_menu', 999 );

/* Custom style for admin */
function surbma_pwp_control_admin_styles() {
?><style type="text/css">
	.pwp .clearline{border-top:1px solid #ccc;clear:both;margin:10px 0;}
	.pwp .section-block{background:#fdfdfd;padding:20px;border:1px solid #ccc;border-radius: 3px;}
	.pwp .section-block h3{margin:0 0 20px;}
	.pwp .icon{float:left;margin:7px 7px 0 0;}
</style>
<?php
}
add_action( 'admin_head', 'surbma_pwp_control_admin_styles' );

/* Custom text in admin footer */
function surbma_pwp_control_custom_admin_footer() {
	$blogname = get_option( 'blogname' );
	echo '<a href="' . get_site_option( 'siteurl' ) . '" target="_blank">' . get_site_option( 'site_name', $blogname ) . '</a>';
}
add_filter( 'admin_footer_text', 'surbma_pwp_control_custom_admin_footer' );

function surbma_pwp_control_soliloquy_change_cap( $cap ) {
	if ( class_exists( 'Soliloquy' ) ) {
		return 'install_plugins';
	}
}
add_filter( 'soliloquy_menu_cap', 'surbma_pwp_control_soliloquy_change_cap' );

function surbma_pwp_control_remove_wpmudev_notice() {
	if ( class_exists( 'WPMUDEV_Dashboard_Notice3' ) ) {
		global $WPMUDEV_Dashboard_Notice3;
		remove_action( 'admin_print_styles', array( $WPMUDEV_Dashboard_Notice3, 'notice_styles' ) );
		remove_action( 'all_admin_notices', array( $WPMUDEV_Dashboard_Notice3, 'install_notice' ), 5 );
		remove_action( 'all_admin_notices', array( $WPMUDEV_Dashboard_Notice3, 'activate_notice' ), 5 );
	}
}
add_action ( 'admin_init', 'surbma_pwp_control_remove_wpmudev_notice' );

function surbma_pwp_control_remove_widgets() {
	unregister_widget( 'WP_Widget_Pages' );
	unregister_widget( 'WP_Widget_Calendar' );
	unregister_widget( 'WP_Widget_Links' );
	unregister_widget( 'WP_Widget_Meta' );
	wp_unregister_sidebar_widget( 'wpe_widget_powered_by' );
}
add_action( 'widgets_init', 'surbma_pwp_control_remove_widgets' );

function surbma_pwp_control_remove_dashboard_widgets() {
    remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'core' );
    remove_meta_box( 'dashboard_plugins', 'dashboard', 'core' );
    remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'core' );
    remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
    remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
}
add_action( 'wp_dashboard_setup', 'surbma_pwp_control_remove_dashboard_widgets' );

remove_action( 'welcome_panel', 'wp_welcome_panel' );

function surbma_pwp_control_disable_jetpack_modules ( $modules ) {
	$pwp_control_jp_mods_to_disable = array(
		'after-the-deadline',
		'comments',
		'contact-form',
		'gravatar-hovercards',
		'infinite-scroll',
		'json-api',
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

	return $modules;
}

function surbma_pwp_control_set_jetpack_modules() {
	if ( class_exists( 'Jetpack' ) ) {
		add_filter( 'jetpack_get_default_modules', '__return_empty_array' );
		add_filter( 'jetpack_get_available_modules', 'surbma_pwp_control_disable_jetpack_modules' );
	}
}
add_action( 'init', 'surbma_pwp_control_set_jetpack_modules', 11 );

