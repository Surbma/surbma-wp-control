<?php

// Enable shortcodes in Text Widgets
if ( !has_filter( 'widget_text', 'do_shortcode' ) )
	add_filter( 'widget_text', 'do_shortcode' );

// Remove version number from source code
function surbma_wp_control_remove_version() {
	return '';
}
add_filter( 'the_generator', 'surbma_wp_control_remove_version' );

// Custom footer creds text
function surbma_wp_control_footer_creds() {
	$options = get_option( 'pwp_control_option' );
	$bloglink = '<a href="' . get_bloginfo( 'url' ) . '" title="' . get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' ) . '">' . get_bloginfo( 'name' ) . '</a>';
	if ( $options['backlink'] != '1' && defined( 'SURBMA_WP_CONTROL_FOOTER_CREDS' ) ) {
		$creds = $bloglink . SURBMA_WP_CONTROL_FOOTER_CREDS;
	} else {
		$creds = $bloglink;
	}
	return $creds;
}

// Add extra "Lost your password?" link
function surbma_wp_control_add_lost_your_password_link() {
	echo '<a class="swc-lyp-button button button-large" href="' . esc_url( wp_lostpassword_url() ) . '">' . __( 'Lost your password?' ) . '</a>';
}
add_action( 'login_form','surbma_wp_control_add_lost_your_password_link', 999 );

// Custom login style
function surbma_wp_control_custom_login_style() {
	echo '<style>';
	echo 'body.login #login {width: 90%;max-width: 450px;}';
	echo '.wp-core-ui .button.swc-lyp-button {float: left;}';
	echo 'body.login.wp-core-ui .button {width: 49%;text-align: center;}';
	echo 'body.login form .forgetmenot, body.login #nav {display: none;}';
	echo 'body.login #backtoblog {text-align: center;}';
	echo '@media screen and (max-width: 479px) {body.login.wp-core-ui .button {width: 100%;margin-bottom: 16px;}}';
	if ( defined( 'SURBMA_WP_CONTROL_LOGIN_STYLE' ) )
		echo SURBMA_WP_CONTROL_LOGIN_STYLE;
	echo '</style>';
}
add_action( 'login_enqueue_scripts', 'surbma_wp_control_custom_login_style' );

// Custom login text
function surbma_wp_control_add_login_text() {
	echo '<div id="login-info">' . SURBMA_WP_CONTROL_LOGIN_TEXT . '</div>';
}
if ( defined( 'SURBMA_WP_CONTROL_LOGIN_TEXT' ) )
	add_action( 'login_footer','surbma_wp_control_add_login_text' );

// Add link to read more text
function surbma_wp_control_read_more_link( $excerpt ) {
	return ' <a href="' . get_permalink() . '">' . $excerpt . '</a>';
}
add_filter( 'excerpt_more', 'surbma_wp_control_read_more_link' );

// Add language code in body class if WPML plugin is activated
function surbma_wp_control_add_wpml_lang_body_class( $classes ) {
	if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		$classes[] = ICL_LANGUAGE_CODE;
	}
	return $classes;
}
add_filter( 'body_class', 'surbma_wp_control_add_wpml_lang_body_class' );

// Redirect all attachment pages
function surbma_wp_control_attachment_redirect() {
	global $post;
	if ( is_attachment() ) {
		if ( ( is_object( $post ) && isset( $post->post_parent ) ) && ( is_numeric( $post->post_parent ) && $post->post_parent != 0 ) ) {
			wp_safe_redirect( get_permalink( $post->post_parent ), 301 );
			exit;
		} else {
			wp_safe_redirect( home_url(), 301 );
			exit;
		}
	}
	return false;
}
add_action( 'template_redirect', 'surbma_wp_control_attachment_redirect', 1 );

// Disable comments on attachement pages
function surbma_wp_control_filter_media_comment_status( $open, $post_id ) {
	$post = get_post( $post_id );
	if( $post->post_type == 'attachment' ) {
		return false;
	}
	return $open;
}
// add_filter( 'comments_open', 'surbma_wp_control_filter_media_comment_status', 10, 2 );

/*
// Fixes "Lost Password?" URLs on login page
// Fixes other password reset related urls
// Fixes URLs in email that goes out
// Fixes title in password reset email
// https://gist.github.com/eteubert/293e07a49f56f300ddbb
*/
add_filter( 'lostpassword_url', function( $lostpassword_url, $redirect ) {
	$args = array( 'action' => 'lostpassword' );
	if ( !empty( $redirect ) )
		$args['redirect_to'] = $redirect;
	return add_query_arg( $args, site_url( 'wp-login.php' ) );
}, 10, 2);

add_filter( 'network_site_url', function($url, $path, $scheme) {
	if ( stripos( $url, 'action=lostpassword' ) !== false )
		return site_url( 'wp-login.php?action=lostpassword', $scheme );
	if ( stripos( $url, 'action=resetpass' ) !== false )
		return site_url( 'wp-login.php?action=resetpass', $scheme );
	return $url;
}, 10, 3 );

add_filter( 'retrieve_password_message', function( $message, $key ) {
	return str_replace( get_site_url(1), get_site_url(), $message );
}, 10, 2);

add_filter( 'retrieve_password_title', function( $title ) {
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	$title = sprintf( __('[%s] Password Reset'), $blogname );
	return $title;
});
