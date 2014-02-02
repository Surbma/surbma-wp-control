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
	.pwp .section-block{background:#fafafa;padding:20px;border:1px solid #ccc;border-radius: 3px;}
	.pwp .section-block h3{margin:0 0 20px;font-weight:normal;font-size:23px;font-family:"HelveticaNeue-Light","Helvetica Neue Light","Helvetica Neue",sans-serif;}
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

/* Tracking across multiple domains */
function pwp_control_global_google_analytics_admin() {
?>
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(
  	['pwp._setAccount', '<?php echo PWP_CONTROL_GOOGLE_ANALYTICS ?>'],
  	['pwp._setDomainName', 'none'],
  	['pwp._setAllowLinker', true],
  	['pwp._trackPageview']
  );

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
<?php
}
/*
if ( defined( 'PWP_CONTROL_GOOGLE_ANALYTICS' ) ) {
	add_action( 'admin_head', 'pwp_control_global_google_analytics_admin', 99 );
}
*/

function pwp_control_soliloquy_change_cap( $cap ) {
	return 'install_plugins';
}
if ( function_exists( 'soliloquy_slider' ) )
	add_filter( 'tgmsp_settings_cap', 'pwp_control_soliloquy_change_cap' );

function pwp_control_remove_wpmudev_notice() {
	if ( function_exists( 'wdp_un_check' ) ) {
		remove_action( 'admin_notices', 'wdp_un_check', 5 );
		remove_action( 'network_admin_notices', 'wdp_un_check', 5 );
	}
}
add_action ( 'admin_init', 'pwp_control_remove_wpmudev_notice' );

// if ( !isset( $_GET['page'] ) OR @$_GET['paged'] != 'wpengine-common' )
// 	add_action('admin_init', function() { wp_dequeue_style('wpe-common'); });

