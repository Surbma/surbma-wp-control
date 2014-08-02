<?php
/* Admin options menu */
include_once(PWP_CONTROL_PLUGIN_DIR . '/pages/control-page.php');

function pwp_control_add_menu() {
	add_menu_page( 'Prémium WordPress Control', 'PWP Control', 'update_core', 'pwp-control', 'pwp_control_page', PWP_CONTROL_PLUGIN_URL . '/images/star16.png' );
}
add_action( 'admin_menu', 'pwp_control_add_menu', 999 );

/* Custom style for admin */
function pwp_control_admin_styles() {
?><style type="text/css">
	.pwp .clearline{border-top:1px solid #ccc;clear:both;margin:10px 0;}
	.pwp .section-block{background:#fdfdfd;padding:20px;border:1px solid #ccc;border-radius: 3px;}
	.pwp .section-block h3{margin:0 0 20px;}
	.pwp .icon{float:left;margin:7px 7px 0 0;}
</style>
<?php
}
add_action( 'admin_head', 'pwp_control_admin_styles' );

/* Custom text in admin footer */
function pwp_control_custom_admin_footer() {
	$blogname = get_option( 'blogname' );
	echo '<a href="' . get_site_option( 'siteurl' ) . '" target="_blank">' . get_site_option( 'site_name', $blogname ) . '</a>';
}
add_filter( 'admin_footer_text', 'pwp_control_custom_admin_footer' );

function pwp_control_soliloquy_change_cap( $cap ) {
	if ( class_exists( 'Soliloquy' ) ) {
		return 'install_plugins';
	}
}
add_filter( 'soliloquy_menu_cap', 'pwp_control_soliloquy_change_cap' );

function pwp_control_remove_wpmudev_notice() {
	if ( function_exists( 'wdp_un_check' ) ) {
		remove_action( 'admin_notices', 'wdp_un_check', 5 );
		remove_action( 'network_admin_notices', 'wdp_un_check', 5 );
	}
}
add_action ( 'admin_init', 'pwp_control_remove_wpmudev_notice' );

// if ( !isset( $_GET['page'] ) OR @$_GET['paged'] != 'wpengine-common' )
// 	add_action('admin_init', function() { wp_dequeue_style('wpe-common'); });

