<?php

if ( ! has_filter( 'widget_text', 'do_shortcode' ) )
	add_filter( 'widget_text', 'do_shortcode' );

function pwp_control_enqueue_custom_style() {
	$uploads = wp_upload_dir();
	if ( file_exists( $uploads['basedir'] . '/pwp-control/custom-style.css' ) )
		wp_enqueue_style( 'pwp-control', $uploads['baseurl'] . '/pwp-control/custom-style.css', false );
}
add_action( 'wp_enqueue_scripts', 'pwp_control_enqueue_custom_style', 9999 );

function pwp_control_remove_script_version( $src ){
    $parts = explode( '?ver', $src );
    return $parts[0];
}
add_filter( 'script_loader_src', 'pwp_control_remove_script_version', 15, 1 );
add_filter( 'style_loader_src', 'pwp_control_remove_script_version', 15, 1 );

function pwp_control_remove_version() {
	return '';
}
add_filter( 'the_generator', 'pwp_control_remove_version' );

function pwp_control_footer_creds() {
	$options = get_option( 'pwp_control_option' );
	$bloglink = '<a href="' . get_bloginfo( 'url' ) . '" title="' . get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' ) . '">' . get_bloginfo( 'name' ) . '</a>';
	if ( $options['backlink'] != '1' && defined( 'PWP_CONTROL_FOOTER_CREDS' ) ) {
		$creds = $bloglink . PWP_CONTROL_FOOTER_CREDS;
	} else {
		$creds = $bloglink;
	}
	return $creds;
}

function pwp_control_custom_login_style() {
	echo PWP_CONTROL_LOGIN_STYLE;
}
if ( defined( 'PWP_CONTROL_LOGIN_STYLE' ) )
	add_action( 'login_enqueue_scripts','pwp_control_custom_login_style' );

function pwp_control_add_login_text() {
	echo '<div id="login-info">' . PWP_CONTROL_LOGIN_TEXT . '</div>';
}
if ( defined( 'PWP_CONTROL_LOGIN_TEXT' ) )
	add_action( 'login_footer','pwp_control_add_login_text' );

function pwp_control_add_google_analytics() {
?>
	_gaq.push(['pwp._setAccount', '<?php echo PWP_CONTROL_GOOGLE_ANALYTICS; ?>']);
	_gaq.push(['pwp._setDomainName', 'none']);
	_gaq.push(['pwp._setAllowLinker', true]);
	_gaq.push(['pwp._trackPageview']);
<?php
}
function pwp_control_do_google_analytics() {
	if ( !is_user_logged_in() ) {
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
}
if ( defined( 'PWP_CONTROL_GOOGLE_ANALYTICS' ) )
	add_action( 'wp_head', 'pwp_control_do_google_analytics', 999 );

