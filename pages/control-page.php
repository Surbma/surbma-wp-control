<?php

// Register setting
function surbma_wp_control_options_init() {
	register_setting( 'pwp_control_options', 'pwp_control_option', 'surbma_wp_control_options_validate' );
}
add_action( 'admin_init', 'surbma_wp_control_options_init' );

// Admin page
function surbma_wp_control_page() {
	if ( ! isset( $_REQUEST['settings-updated'] ) )
		$_REQUEST['settings-updated'] = false;

	?>
	<div class="wrap wp-control">
		<img class="icon" alt="icon" src="<?php echo SURBMA_WP_CONTROL_PLUGIN_URL . '/images/star32.png'; ?>" />
		<h2><?php _e( 'Superadmin Settings', 'surbma-wp-control' ); ?></h2>

		<?php if ( false !== $_REQUEST['settings-updated'] ) : ?>
		<div class="updated fade is-dismissible"><p><strong><?php _e( 'Settings saved successfully.', 'surbma-wp-control' ); ?></strong></p></div>
		<?php endif; ?>

		<div class="clearline"></div>
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
		<div class="clearline"></div>
	    <div class="section-block">
	      <h2><?php _e( 'Blog Roles', 'surbma-wp-control' ); ?></h2>
	      <?php
	        $wp_roles = new WP_Roles();
	        $names = $wp_roles->get_names();
	        print_r( $names );
	      ?>
	    </div>
		<div class="clearline"></div>
		<?php if ( is_multisite() ) { ?>
		<div class="section-block">
			<h2><?php _e( 'Blog Details', 'surbma-wp-control' ); ?></h2>
			<?php
				global $blog_id;
				$blog_details = get_blog_details( $blog_id );
				print_r( $blog_details );
			?>
		</div>
		<div class="clearline"></div>
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
