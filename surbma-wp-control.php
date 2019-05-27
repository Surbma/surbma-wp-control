<?php

/*
Plugin Name: Surbma | WP Control
Plugin URI: https://surbma.com/wordpress-plugins/
Description: Very useful fixes and add-ons for WordPress Multisite installations.
Network: True

Version: 11.0

Author: Surbma
Author URI: https://surbma.com/

License: GPLv2

Text Domain: surbma-wp-control
Domain Path: /languages/
*/

// Prevent direct access to the plugin
if ( !defined( 'ABSPATH' ) ) exit( 'Good try! :)' );

// Define some constants
define( 'SURBMA_WP_CONTROL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SURBMA_WP_CONTROL_PLUGIN_URL', plugins_url( '', __FILE__ ) );

// Localization
function surbma_wp_control_init() {
	load_plugin_textdomain( 'surbma-wp-control', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'surbma_wp_control_init' );

// Include files
if ( is_admin() ) {
	include_once( SURBMA_WP_CONTROL_PLUGIN_DIR . '/lib/admin.php' );
}

if ( !is_admin() ) {
	include_once( SURBMA_WP_CONTROL_PLUGIN_DIR . '/lib/frontend.php' );
	if ( wp_basename( get_bloginfo( 'template_directory' ) ) == 'genesis' )
		include_once( SURBMA_WP_CONTROL_PLUGIN_DIR . '/lib/frontend-genesis.php' );
}

// Load custom functions if file exists
$blog_id = get_current_blog_id();
$custom_functions_file = ABSPATH . 'wp-content/pwp-control/' . $blog_id . '/custom-functions.php';
if ( file_exists( $custom_functions_file ) )
	include_once( $custom_functions_file );

// Change the default wordpress@siteurl email address to the admin's email address
// Change the default WordPress email to the site's title
function surbma_wp_control_wp_mail_from( $input ) {
	// Not the default address, probably a comment notification
	if ( 0 !== stripos( $input, 'wordpress' ) )
		return $input;

	return get_option( 'wp_mail_from' === current_filter() ? 'admin_email' : 'blogname' );
}
add_filter( 'wp_mail_from', 'surbma_wp_control_wp_mail_from' );
add_filter( 'wp_mail_from_name', 'surbma_wp_control_wp_mail_from' );

function surbma_wp_control_clean_file_names ( $filename ) {
	$tmp = explode( '.', $filename );
	$reset = reset( $tmp );
	$end = end( $tmp );
	$ext = $reset == $end ? '' : '.' . $end;
	$file = $ext == '' ? $filename : substr( $filename, 0, -( strlen( $ext ) ) );
	$file = str_replace( ' ', '-', $file );
	$file = str_replace( '_', '-', $file );
	$file = preg_replace( '/-+/', '-', $file );
	$file = preg_replace( '/[^A-Za-z0-9\-]/', '', $file );
	$file = strtolower( $file );
	return $file . $ext;
}
add_filter( 'sanitize_file_name', 'surbma_wp_control_clean_file_names', 10 );

// Add global Google Analytics tracking
function surbma_wp_control_add_google_analytics() {
?>
	gtag('config', '<?php echo SURBMA_WP_CONTROL_GOOGLE_ANALYTICS; ?>', { 'anonymize_ip': true });
<?php
}
function surbma_wp_control_do_google_analytics() {
	// Check if Surbma - Premium WP plugin is activated and Google Analytics tracking is enabled
	$options = get_option( 'surbma_premium_wp_google_analytics_fields' );
	if ( function_exists( 'surbma_premium_wp_google_analytics_display' ) && isset( $options['universalid'] ) && $options['universalid'] != '' ) {
		add_action( 'surbma_premium_wp_gtag_settings', 'surbma_wp_control_add_google_analytics', 999 );
	} else {
?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo SURBMA_WP_CONTROL_GOOGLE_ANALYTICS; ?>"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', '<?php echo SURBMA_WP_CONTROL_GOOGLE_ANALYTICS; ?>', { 'anonymize_ip': true });
</script>
<?php }
}
if ( defined( 'SURBMA_WP_CONTROL_GOOGLE_ANALYTICS' ) ) {
	add_action( 'wp_head', 'surbma_wp_control_do_google_analytics', 998 );
	add_action( 'admin_head', 'surbma_wp_control_do_google_analytics', 998 );
	add_action( 'login_head', 'surbma_wp_control_do_google_analytics', 998 );
}
