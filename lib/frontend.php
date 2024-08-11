<?php

// Enable shortcodes in Text Widgets
if ( !has_filter( 'widget_text', 'do_shortcode' ) )
	add_filter( 'widget_text', 'do_shortcode' );

// Custom footer creds text
function surbma_wp_control_footer_creds() {
	$options = get_option( 'pwp_control_option' );
	$backlinkValue = isset( $options['backlink'] ) ? $options['backlink'] : 0;
	$bloglink = '<a href="' . get_bloginfo( 'url' ) . '" title="' . get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' ) . '">' . get_bloginfo( 'name' ) . '</a>';
	if ( $backlinkValue != '1' && defined( 'SURBMA_WP_CONTROL_FOOTER_CREDS' ) ) {
		$creds = $bloglink . SURBMA_WP_CONTROL_FOOTER_CREDS;
	} else {
		$creds = $bloglink;
	}
	return $creds;
}

// Disable comments on attachement pages
function surbma_wp_control_filter_media_comment_status( $open, $post_id ) {
	$post = get_post( $post_id );
	if( $post->post_type == 'attachment' ) {
		return false;
	}
	return $open;
}
// add_filter( 'comments_open', 'surbma_wp_control_filter_media_comment_status', 10, 2 );
