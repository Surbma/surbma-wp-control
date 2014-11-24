<?php

/* Customizes breadcrumb */
function surbma_pwp_control_genesis_custom_breadcrumb( $args ) {
	$args['home'] = get_bloginfo( 'name' );
	$args['sep'] = ' &raquo; ';
	$args['labels']['prefix'] = '';
	return $args;
}
add_filter( 'genesis_breadcrumb_args', 'surbma_pwp_control_genesis_custom_breadcrumb' );

add_filter( 'genesis_footer_creds_text', 'surbma_pwp_control_footer_creds', 25 );

//* Remove the edit link
add_filter( 'genesis_edit_post_link' , '__return_false' );

