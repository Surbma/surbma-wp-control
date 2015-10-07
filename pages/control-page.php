<?php

// Register setting
function surbma_wp_control_options_init() {
	register_setting( 'pwp_control_options', 'pwp_control_option', 'surbma_wp_control_options_validate' );
}
add_action( 'admin_init', 'surbma_wp_control_options_init' );

// Admin page
function surbma_wp_control_page() {
?>
	<div class="wrap wp-control">
		<h1 class="dashicons-before dashicons-visibility"><?php _e( 'Superadmin Settings', 'surbma-wp-control' ); ?></h1>

		<?php if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true ) { ?>
			<div class="updated notice is-dismissible"><p><strong><?php _e( 'Settings saved.' ); ?></strong></p></div>
		<?php } ?>

		<div class="section-block">
			<form method="post" action="options.php">
				<?php settings_fields( 'pwp_control_options' ); ?>
				<?php $options = get_option( 'pwp_control_option' ); ?>

				<h2><?php _e( 'Display Options', 'surbma-wp-control' ); ?></h2>
				<table class="form-table">
					<tr valign="top"><th scope="row"><?php _e( 'Display backlink?', 'surbma-wp-control' ); ?></th>
						<td>
							<input id="pwp_control_option[backlink]" name="pwp_control_option[backlink]" type="checkbox" value="1" <?php checked( '1', $options['backlink'] ); ?> />
							<label class="description" for="pwp_control_option[backlink]"><?php _e( 'Remove backlink from footer creds text', 'surbma-wp-control' ); ?></label><br />
						</td>
					</tr>
				</table>
				<p><input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ); ?>" /></p>
			</form>
		</div>
	    <div class="section-block">
			<h2><?php _e( 'Blog Roles', 'surbma-wp-control' ); ?></h2>
			<?php $wp_roles = new WP_Roles(); ?>
			<?php $names = $wp_roles->get_names(); ?>
			<pre><?php print_r( $names ); ?></pre>
		</div>
		<?php if ( is_multisite() ) { ?>
		<div class="section-block">
			<h2><?php _e( 'Blog Details', 'surbma-wp-control' ); ?></h2>
			<?php global $blog_id; ?>
			<?php $blog_details = get_blog_details( $blog_id ); ?>
			<pre><?php print_r( $blog_details ); ?></pre>
		</div>
		<?php } ?>
	</div>
<?php
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function surbma_wp_control_options_validate( $input ) {
	// Our checkbox value is either 0 or 1
	if ( ! isset( $input['backlink'] ) )
		$input['backlink'] = null;
	$input['backlink'] = ( $input['backlink'] == 1 ? 1 : 0 );
	return $input;
}
