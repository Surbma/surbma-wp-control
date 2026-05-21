<?php

/*
Plugin Name: Surbma | WP Control
Plugin URI: https://surbma.com/wordpress-plugins/
Description: Very useful fixes and add-ons for WordPress Multisite installations.
Network: True

Version: 26.0

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
add_action( 'init', function() {
	load_plugin_textdomain( 'surbma-wp-control', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
} );

// Fix Image Rotation — always active, no options needed
include_once SURBMA_WP_CONTROL_PLUGIN_DIR . 'includes/fix-image-rotation.php';

// Admin
if ( is_admin() ) {
	require_once SURBMA_WP_CONTROL_PLUGIN_DIR . 'includes/all-admin.php';
}
