<?php

defined( 'ABSPATH' ) || die;

/**
 * Option key storing the per-site list of disabled image size slugs.
 */
const SURBMA_WP_CONTROL_DISABLED_IMAGE_SIZES_OPTION = 'surbma_wp_control_disabled_image_sizes';

/**
 * admin_post action slug for saving the Images & thumbnails toggles.
 */
const SURBMA_WP_CONTROL_SAVE_IMAGE_SIZES_ACTION = 'surbma_wp_control_save_image_sizes';

/**
 * Disabled image size slugs for the current site.
 *
 * @return array<int, string>
 */
function surbma_wp_control_get_disabled_image_sizes() {
	$disabled = get_option( SURBMA_WP_CONTROL_DISABLED_IMAGE_SIZES_OPTION, array() );

	if ( ! is_array( $disabled ) ) {
		return array();
	}

	return array_values( array_filter( array_map( 'strval', $disabled ) ) );
}

/**
 * Strip disabled sizes from get_intermediate_image_sizes() output.
 *
 * @param array<int, string> $sizes Registered size slugs.
 * @return array<int, string>
 */
function surbma_wp_control_filter_intermediate_image_sizes( $sizes ) {
	if ( ! is_array( $sizes ) ) {
		return $sizes;
	}

	$disabled = surbma_wp_control_get_disabled_image_sizes();

	if ( empty( $disabled ) ) {
		return $sizes;
	}

	return array_values( array_diff( $sizes, $disabled ) );
}
add_filter( 'intermediate_image_sizes', 'surbma_wp_control_filter_intermediate_image_sizes', 999 );

/**
 * Strip disabled sizes from the resolved size definitions used during upload.
 *
 * @param array<string, array{width: int, height: int, crop: bool}> $sizes Sizes keyed by slug.
 * @return array<string, array{width: int, height: int, crop: bool}>
 */
function surbma_wp_control_filter_intermediate_image_sizes_advanced( $sizes ) {
	if ( ! is_array( $sizes ) ) {
		return $sizes;
	}

	$disabled = surbma_wp_control_get_disabled_image_sizes();

	if ( empty( $disabled ) ) {
		return $sizes;
	}

	foreach ( $disabled as $slug ) {
		unset( $sizes[ $slug ] );
	}

	return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'surbma_wp_control_filter_intermediate_image_sizes_advanced', 999 );

/**
 * Handle the Images & thumbnails save form submission.
 */
function surbma_wp_control_handle_save_image_sizes() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to change image size settings.', 'surbma-wp-control' ), '', array( 'response' => 403 ) );
	}

	check_admin_referer( SURBMA_WP_CONTROL_SAVE_IMAGE_SIZES_ACTION );

	$submitted = isset( $_POST['surbma_wp_control_submitted_sizes'] ) && is_array( $_POST['surbma_wp_control_submitted_sizes'] )
		? array_map( 'sanitize_key', wp_unslash( $_POST['surbma_wp_control_submitted_sizes'] ) )
		: array();

	$enabled = isset( $_POST['surbma_wp_control_enabled_sizes'] ) && is_array( $_POST['surbma_wp_control_enabled_sizes'] )
		? array_map( 'sanitize_key', wp_unslash( $_POST['surbma_wp_control_enabled_sizes'] ) )
		: array();

	$submitted = array_values( array_unique( array_filter( $submitted ) ) );
	$enabled   = array_values( array_unique( array_filter( $enabled ) ) );
	$disabled  = array_values( array_diff( $submitted, $enabled ) );

	update_option( SURBMA_WP_CONTROL_DISABLED_IMAGE_SIZES_OPTION, $disabled, false );

	// Invalidate cached registry and usage scan so the UI reflects the new state.
	delete_transient( surbma_wp_control_get_image_sizes_registry_transient_key( get_current_blog_id() ) );
	delete_transient( surbma_wp_control_get_media_cleaner_transient_key() );

	$redirect = add_query_arg(
		array( 'settings-updated' => '1' ),
		surbma_wp_control_get_images_thumbnails_page_url()
	);

	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_' . SURBMA_WP_CONTROL_SAVE_IMAGE_SIZES_ACTION, 'surbma_wp_control_handle_save_image_sizes' );
