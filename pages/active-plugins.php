<?php
/*
// Based on: http://wordpress.stackexchange.com/a/54782/57684
// Updated to fix $wpdb->prepare warning and display plugin name.
*/

function surbma_wp_control_active_plugins() {
?>
<div class="wp-control uk-grid uk-margin-top">
	<div class="wrap uk-width-9-10">
		<h1 class="dashicons-before dashicons-admin-plugins"><?php _e( 'WP Control | Active Plugins', 'surbma-wp-control' ); ?></h1>

		<?php
			global $wpdb;
			$blogs = $wpdb->get_results("
				SELECT blog_id
				FROM {$wpdb->blogs}
				WHERE site_id = '{$wpdb->siteid}'
				AND spam = '0'
				AND deleted = '0'
				AND archived = '0'
			");
			echo '<table cellpadding="10" cellspacing="0" border="0" style="background: #fff;width: 100%;border: 1px solid #ccc;border-bottom: 0;margin: 0 0 20px;">';
			echo '<thead>';
			echo '<tr style="background: #333;color: #fff;">';
			echo '<th style="width: 60%;text-align: left;border-bottom: 1px solid #ccc;border-right: 1px solid #ccc;">' . __( 'Site name', 'surbma-wp-control' ) . '</th>';
			echo '<th style="width: 40%;text-align: left;border-bottom: 1px solid #ccc;">' . __( 'Active Plugins', 'surbma-wp-control' ) . '</th>';
			echo '</tr></thead>';
			echo '<tbody>';
			foreach ( $blogs as $blog ) {
				echo '<tr>';
				$the_plugs = get_blog_option( $blog->blog_id, 'active_plugins' );
				printf( '<td style="border-bottom: 1px solid #ccc;border-right: 1px solid #ccc;vertical-align: top;"><strong><a href="%splugins.php" title="Go to the Dashboard for %s" target="_blank">%s</a></strong></td>', get_admin_url( $blog->blog_id ), get_blog_option( $blog->blog_id, 'blogname' ), get_blog_option( $blog->blog_id, 'blogname' ) );
				echo '<td style="border-bottom: 1px solid #ccc;vertical-align: top;">';
				if( $the_plugs ) {
					echo '<ul style="margin: 0;">';
					foreach( $the_plugs as $key => $value ) {
						$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $value );
						echo '<li>' . $plugin_data['Name'] . ' | ' . $plugin_data['Version'] . ' | <a href="' . $plugin_data['PluginURI'] . '" target="_blank">' . __( 'Visit plugin site' ) . '</a></li>';
					}
					echo '</ul>';
				} else {
					echo __( 'No active plugins on this site.', 'surbma-wp-control' );
				}
				echo '</td>';
				echo '</tr>';
			}
			echo '</tbody>';
			echo '</table>';
		?>

		<div class="section-block uk-panel uk-panel-box uk-panel-box-secondary uk-panel-header">
			<h3 class="uk-panel-title"><?php _e( 'Network Activated Plugins', 'surbma-wp-control' ); ?></h3>
			<?php
				$the_plugs = get_site_option( 'active_sitewide_plugins' );
				if( $the_plugs ) {
					echo '<ul>';
					ksort( $the_plugs );
					foreach( $the_plugs as $key => $value ) {
						$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $key );
						echo '<li>' . $plugin_data['Name'] . ' | ' . $plugin_data['Version'] . ' | <a href="' . $plugin_data['PluginURI'] . '" target="_blank">' . __( 'Visit plugin site' ) . '</a></li>';
					}
					echo '</ul>';
				} else {
					_e( 'No network actived plugins.', 'surbma-wp-control' );
				}
			?>
		</div>

	</div>
</div>
<?php
}
