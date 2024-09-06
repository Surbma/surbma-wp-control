<?php

/*
Plugin Name: Surbma | WP Control
Plugin URI: https://surbma.com/wordpress-plugins/
Description: Very useful fixes and add-ons for WordPress Multisite installations.
Network: True

Version: 21.0

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
add_action( 'plugins_loaded', function() {
	load_plugin_textdomain( 'surbma-wp-control', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
} );

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
