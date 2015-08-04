<?php

// Enable shortcodes in Text Widgets
if ( !has_filter( 'widget_text', 'do_shortcode' ) )
	add_filter( 'widget_text', 'do_shortcode' );

// Remove version parameters from static files for caching
function surbma_wp_control_remove_script_version( $src ) {
    $parts = explode( '?ver', $src );
    return $parts[0];
}
add_filter( 'script_loader_src', 'surbma_wp_control_remove_script_version', 15, 1 );
add_filter( 'style_loader_src', 'surbma_wp_control_remove_script_version', 15, 1 );

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

// Custom login style
function surbma_wp_control_custom_login_style() {
	echo SURBMA_WP_CONTROL_LOGIN_STYLE;
}
if ( defined( 'SURBMA_WP_CONTROL_LOGIN_STYLE' ) )
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

function surbma_wp_control_filter_media_comment_status( $open, $post_id ) {
	$post = get_post( $post_id );
	if( $post->post_type == 'attachment' ) {
		return false;
	}
	return $open;
}
add_filter( 'comments_open', 'surbma_wp_control_filter_media_comment_status', 10, 2 );

