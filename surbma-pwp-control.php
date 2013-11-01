<?php

/*
Plugin Name: Surbma - Premium WordPress Control
Plugin URI: http://premiumwp.hu/
GitHub Plugin URI: Surbma/surbma-pwp-control
Description: Global control plugin for Premium WordPress sites
Version: 3.0.0
Author: Surbma
Author URI: http://surbma.hu/
License: GPL2
*/

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
add_filter( 'wp_mail_from',	'pwp_control_wp_mail_from' );
add_filter( 'wp_mail_from_name', 'pwp_control_wp_mail_from' );
