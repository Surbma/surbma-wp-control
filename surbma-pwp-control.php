<?php

/*
Plugin Name: Surbma - Premium WordPress Control
Plugin URI: http://premiumwp.hu/
Description: Global control plugin for Premium WordPress sites
Version: 3.7.0
Author: Surbma
Author URI: http://surbma.hu/
License: GPL2
*/

// Prevent direct access to the plugin
if ( !defined( 'ABSPATH' ) ) {
	die( 'Sorry, you are not allowed to access this page directly.' );
}

define( 'PWP_CONTROL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PWP_CONTROL_PLUGIN_URL', plugins_url( '', __FILE__ ) );

// Include files
if ( is_admin() ) {
	include_once( PWP_CONTROL_PLUGIN_DIR . '/lib/admin.php' );
}

if ( !is_admin() ) {
	include_once( PWP_CONTROL_PLUGIN_DIR . '/lib/frontend.php' );
	if ( wp_basename( get_bloginfo( 'template_directory' ) ) == 'genesis' )
		include_once( PWP_CONTROL_PLUGIN_DIR . '/lib/frontend-genesis.php' );
}

// Load custom functions if file exists
$uploads = wp_upload_dir();
if ( file_exists( $uploads['basedir'] . '/pwp-control/custom-functions.php' ) )
	include_once( $uploads['basedir'] . '/pwp-control/custom-functions.php' );

// Change the default wordpress@siteurl email address to the admin's email address
// Change the default WordPress email to the site's title
function pwp_control_wp_mail_from( $input ) {
	// Not the default address, probably a comment notification
	if ( 0 !== stripos( $input, 'wordpress' ) )
		return $input;

	return get_option( 'wp_mail_from' === current_filter() ? 'admin_email' : 'blogname' );
}
add_filter( 'wp_mail_from', 'pwp_control_wp_mail_from' );
add_filter( 'wp_mail_from_name', 'pwp_control_wp_mail_from' );

function pwp_control_add_google_analytics() {
?>
	ga('create', '<?php echo PWP_CONTROL_GOOGLE_ANALYTICS; ?>', 'auto', {'name': 'pwp'}, {'allowLinker': true});
	ga('pwp.send', 'pageview');
<?php
}
function pwp_control_do_google_analytics() {
	$options = get_option( 'pwp_google_analytics_fields' );
	if ( function_exists( 'pwp_google_analytics_display' ) && $options['universalid'] != '' ) {
		add_action( 'pwp_universal_analytics_objects', 'pwp_control_add_google_analytics', 999 );
	} else {
?>
<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', '<?php echo PWP_CONTROL_GOOGLE_ANALYTICS; ?>', 'auto', {'name': 'pwp'}, {'allowLinker': true});
	ga('pwp.send', 'pageview');
</script>
<?php }
}
if ( defined( 'PWP_CONTROL_GOOGLE_ANALYTICS' ) ) {
	add_action( 'wp_head', 'pwp_control_do_google_analytics', 999 );
	add_action( 'admin_head', 'pwp_control_do_google_analytics', 999 );
	add_action( 'login_head', 'pwp_control_do_google_analytics', 999 );
}

