<?php

// Register setting
function surbma_wp_control_options_init() {
	register_setting( 'pwp_control_options', 'pwp_control_option', 'surbma_wp_control_options_validate' );
}
add_action( 'admin_init', 'surbma_wp_control_options_init' );

// Admin page
function surbma_wp_control_page() {
?>
<div class="wp-control uk-grid uk-margin-top">
	<div class="wrap uk-width-9-10">
		<h1 class="dashicons-before dashicons-visibility"><?php _e( 'Superadmin Settings', 'surbma-wp-control' ); ?></h1>

		<?php if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true ) { ?>
			<div class="updated notice is-dismissible"><p><strong><?php _e( 'Settings saved.' ); ?></strong></p></div>
		<?php } ?>

		<?php if ( is_multisite() ) { ?>
		<div class="section-block uk-panel uk-panel-box uk-panel-box-secondary uk-panel-header">
			<h3 class="uk-panel-title"><?php _e( 'Blog Details', 'surbma-wp-control' ); ?></h3>
			<?php global $blog_id; ?>
			<?php $blog_details = get_blog_details( $blog_id ); ?>
			<pre><?php print_r( $blog_details ); ?></pre>
		</div>
		<?php } ?>
	    <div class="section-block uk-panel uk-panel-box uk-panel-box-secondary uk-panel-header">
			<h3 class="uk-panel-title"><?php _e( 'Blog Roles', 'surbma-wp-control' ); ?></h3>
			<?php $wp_roles = new WP_Roles(); ?>
			<?php $names = $wp_roles->get_names(); ?>
			<pre><?php print_r( $names ); ?></pre>
		</div>
	    <div class="section-block uk-panel uk-panel-box uk-panel-box-secondary uk-panel-header">
			<h3 class="uk-panel-title"><?php _e( 'Admin Menu', 'surbma-wp-control' ); ?></h3>
    		<?php global $menu; ?>
    		<?php echo '<pre>', print_r( $menu, 1 ), '</pre>'; ?>
		</div>
		<div class="section-block uk-panel uk-panel-box uk-panel-box-secondary uk-panel-header">
			<form class="uk-form" method="post" action="options.php">
				<?php settings_fields( 'pwp_control_options' ); ?>
				<?php $options = get_option( 'pwp_control_option' ); ?>

				<h3 class="uk-panel-title"><?php _e( 'Display Options', 'surbma-wp-control' ); ?></h3>
				<table class="form-table">
					<tr valign="top"><th scope="row"><?php _e( 'Display backlink?', 'surbma-wp-control' ); ?></th>
						<td>
							<?php $backlinkValue = isset( $options['backlink'] ) ? $options['backlink'] : 0; ?>
							<input id="pwp_control_option[backlink]" name="pwp_control_option[backlink]" type="checkbox" value="1" <?php checked( '1', $backlinkValue ); ?> />
							<label class="description" for="pwp_control_option[backlink]"><?php _e( 'Remove backlink from footer creds text', 'surbma-wp-control' ); ?></label><br />
						</td>
					</tr>
				</table>
				<p><input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ); ?>" /></p>
			</form>
		</div>
	</div>
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
