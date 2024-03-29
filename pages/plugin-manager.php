<?php
/*
// Based on: http://wordpress.stackexchange.com/a/54782/57684
// Updated to fix $wpdb->prepare warning and display plugin name.
*/

function surbma_wp_control_plugin_manager() {
?>
<div class="wp-control uk-grid uk-margin-top">
	<div class="wrap uk-width-9-10">
		<h1 class="dashicons-before dashicons-admin-plugins"><?php _e( 'WP Control | Active Plugins', 'surbma-wp-control' ); ?></h1>

		<?php
			if ( !wp_is_large_network() ) {
				$sites = get_sites( ['number'  => 10000] );
				echo '<table cellpadding="10" cellspacing="0" border="0" style="background: #fff;width: 100%;border: 1px solid #ccc;border-bottom: 0;margin: 0 0 20px;">';
				echo '<thead>';
				echo '<tr style="background: #333;color: #fff;">';
				echo '<th style="width: 60%;text-align: left;border-bottom: 1px solid #ccc;border-right: 1px solid #ccc;">' . __( 'Site', 'surbma-wp-control' ) . '</th>';
				echo '<th style="width: 40%;text-align: left;border-bottom: 1px solid #ccc;">' . __( 'Active Plugins', 'surbma-wp-control' ) . '</th>';
				echo '</tr></thead>';
				echo '<tbody>';
				foreach ( $sites as $site ) {
					echo '<tr>';
					$deleted = get_blog_status( $site->blog_id, 'deleted' ) == 1 ? 'background: #ff8573;' : '';
					echo '<td style="' . $deleted . 'border-bottom: 1px solid #ccc;border-right: 1px solid #ccc;vertical-align: top;">';
					printf( '<strong>%s</strong> | <a href="%splugins.php" target="_blank">%s</a> | <a href="%s" target="_blank">%s</a>', get_blog_option( $site->blog_id, 'blogname' ), get_admin_url( $site->blog_id ), __( 'Dashboard' ), get_home_url( $site->blog_id ), __( 'Visit' ) );
					echo '</td>';
					echo '<td style="' . $deleted . 'border-bottom: 1px solid #ccc;vertical-align: top;">';

					$active_plugins = get_blog_option( $site->blog_id, 'active_plugins' );					
					// Sort the array alphabetically by the values
					asort( $active_plugins );
					if ( $active_plugins ) {
						echo '<ul style="margin: 0;">';
						foreach( $active_plugins as $key => $value ) {
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
			} else {
				echo '<p>' . __( 'Sorry, your Multisite install is too large, this plugin is not optimized for such a large network.', 'surbma-wp-control' ) . '</p>';
			}
		?>

		<div class="section-block uk-panel uk-panel-box uk-panel-box-secondary uk-panel-header">
			<h3 class="uk-panel-title"><?php _e( 'Network Activated Plugins', 'surbma-wp-control' ); ?></h3>
			<p><?php _e( 'These Plugins are network activated, so they are used on all subsites of this Multisite network.', 'surbma-wp-control' ); ?></p>
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

		<div class="section-block uk-panel uk-panel-box uk-panel-box-secondary uk-panel-header">
			<h3 class="uk-panel-title"><?php _e( 'Not Activated Plugins', 'surbma-wp-control' ); ?></h3>
			<p><?php _e( 'These Plugins are not used on any subsite of this Multisite network.', 'surbma-wp-control' ); ?></p>
			<?php
				if ( !wp_is_large_network() ) {
					$all_plugins = get_plugins();
					$sites = get_sites( ['number'  => 10000] );
					foreach( $sites as $site ) {
						switch_to_blog( $site->blog_id );
						foreach ( $all_plugins as $key => $data ) {
							if ( is_plugin_active( $key ) ) {
								unset( $all_plugins[$key] );
							}
						}
						restore_current_blog();
					}
					echo '<ul>';
					foreach ( $all_plugins as $key => $data ) {
						echo '<li>' . $data['Name'] . ' | ' . $data['Version'] . ' | <a href="' . $data['PluginURI'] . '" target="_blank">' . __( 'Visit plugin site' ) . '</a></li>';
					}
					echo '</ul>';
				} else {
					echo '<p>' . __( 'Sorry, your Multisite install is too large, this plugin is not optimized for such a large network.', 'surbma-wp-control' ) . '</p>';
				}
			?>
		</div>

	</div>
</div>
<?php
}
