<?php

/*
Plugin Name: Surbma - Premium WordPress Control
Plugin URI: http://premiumwp.hu/
Description: Global control plugin for Premium WordPress sites
Version: 3.2.2
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
add_filter( 'wp_mail_from', 'pwp_control_wp_mail_from' );
add_filter( 'wp_mail_from_name', 'pwp_control_wp_mail_from' );

function pwp_control_add_google_analytics() {
?>
	_gaq.push(['pwp._setAccount', '<?php echo PWP_CONTROL_GOOGLE_ANALYTICS; ?>']);
	_gaq.push(['pwp._setDomainName', 'none']);
	_gaq.push(['pwp._setAllowLinker', true]);
	_gaq.push(['pwp._trackPageview']);
<?php
}
function pwp_control_do_google_analytics() {
	$options = get_option( 'pwp_google_analytics_fields' );
	if ( function_exists( 'pwp_google_analytics_display' ) && $options['trackingid'] != '' ) {
		add_action( 'pwp_google_analytics_after_trackpageview', 'pwp_control_add_google_analytics', 999 );
	} else {
?>
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['pwp._setAccount', '<?php echo PWP_CONTROL_GOOGLE_ANALYTICS; ?>']);
	_gaq.push(['pwp._setDomainName', 'none']);
	_gaq.push(['pwp._setAllowLinker', true]);
	_gaq.push(['pwp._trackPageview']);

	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>
<?php }
}
if ( defined( 'PWP_CONTROL_GOOGLE_ANALYTICS' ) ) {
	add_action( 'wp_head', 'pwp_control_do_google_analytics', 999 );
	add_action( 'admin_head', 'pwp_control_do_google_analytics', 999 );
}

