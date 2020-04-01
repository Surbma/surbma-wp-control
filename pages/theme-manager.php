<?php
/*
// Based on: http://wordpress.stackexchange.com/a/54782/57684
// Updated to fix $wpdb->prepare warning and display plugin name.
*/

function surbma_wp_control_theme_manager() {
?>
<div class="wp-control uk-grid uk-margin-top">
	<div class="wrap uk-width-9-10">
		<h1 class="dashicons-before dashicons-admin-appearance"><?php _e( 'WP Control | Active Themes', 'surbma-wp-control' ); ?></h1>

		<?php
			if ( !wp_is_large_network() ) {
				$sites = get_sites( ['number'  => 10000] );
				echo '<table cellpadding="10" cellspacing="0" border="0" style="background: #fff;width: 100%;border: 1px solid #ccc;border-bottom: 0;margin: 0 0 20px;">';
				echo '<thead>';
				echo '<tr style="background: #333;color: #fff;">';
				echo '<th style="width: 60%;text-align: left;border-bottom: 1px solid #ccc;border-right: 1px solid #ccc;">' . __( 'Site', 'surbma-wp-control' ) . '</th>';
				echo '<th style="width: 20%;text-align: left;border-bottom: 1px solid #ccc;border-right: 1px solid #ccc;">' . __( 'Parent Theme', 'surbma-wp-control' ) . '</th>';
				echo '<th style="width: 20%;text-align: left;border-bottom: 1px solid #ccc;">' . __( 'Child Theme', 'surbma-wp-control' ) . '</th>';
				echo '</tr></thead>';
				echo '<tbody>';
				foreach ( $sites as $site ) {
					echo '<tr>';
					$the_template = get_blog_option( $site->blog_id, 'template' );
					$the_stylesheet = get_blog_option( $site->blog_id, 'stylesheet' );
					$deleted = get_blog_status( $site->blog_id, 'deleted' ) == 1 ? 'background: #ff8573;' : '';
					echo '<td style="' . $deleted . 'border-bottom: 1px solid #ccc;border-right: 1px solid #ccc;vertical-align: top;">';
					printf( '<strong>%s</strong> | <a href="%sthemes.php" target="_blank">%s</a> | <a href="%s" target="_blank">%s</a>', get_blog_option( $site->blog_id, 'blogname' ), get_admin_url( $site->blog_id ), __( 'Dashboard' ), get_home_url( $site->blog_id ), __( 'Visit' ) );
					echo '</td>';
					echo '<td style="' . $deleted . 'border-bottom: 1px solid #ccc;border-right: 1px solid #ccc;vertical-align: top;">';
					if( $the_template ) {
						echo $the_template;
					} else {
						echo __( 'No active theme on this site.', 'surbma-wp-control' );
					}
					echo '</td>';
					echo '<td style="' . $deleted . 'border-bottom: 1px solid #ccc;vertical-align: top;">';
					if( $the_stylesheet ) {
						echo $the_stylesheet;
					} else {
						echo __( 'No active theme on this site.', 'surbma-wp-control' );
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
			<h3 class="uk-panel-title"><?php _e( 'Network Activated Themes', 'surbma-wp-control' ); ?></h3>
			<?php
				$the_themes = get_site_option( 'allowedthemes' );
				if( $the_themes ) {
					echo '<p>' . __( 'These Themes are network activated, so they can be used on all subsites of this Multisite network:', 'surbma-wp-control' ) . '</p>';
					echo '<ul>';
					ksort( $the_themes );
					foreach( $the_themes as $key => $value ) {
						echo '<li>' . $key . '</li>';
					}
					echo '</ul>';
				} else {
					_e( 'No network actived themes.', 'surbma-wp-control' );
				}
			?>
		</div>

		<div class="section-block uk-panel uk-panel-box uk-panel-box-secondary uk-panel-header">
			<h3 class="uk-panel-title"><?php _e( 'Not Activated Themes', 'surbma-wp-control' ); ?></h3>
			<?php
				if ( !wp_is_large_network() ) {
					$themes = wp_get_themes();
					$sites = get_sites( ['number' => 10000] );
					foreach( $sites as $site ) {
						switch_to_blog( $site->blog_id );
						$template_name = get_option( 'template' );
						$style_path = explode( '/', get_stylesheet_directory() );

						unset( $themes[end( $style_path )] );
						unset( $themes[$template_name] );

						restore_current_blog();
					}
					if( $themes ) {
						echo '<p>' . __( 'These Themes are not used on any subsite of this Multisite network:', 'surbma-wp-control' ) . '</p>';
						echo '<ul>';
						foreach ( $themes as $theme ) {
							echo '<li>' . $theme->Name . ' | ' . $theme->Version . ' | <a href="' . $theme->get('ThemeURI') . '" target="_blank">' . __( 'Visit Theme site' ) . '</a></li>';
						}
						echo '</ul>';
					} else {
						_e( 'All themes are in use.', 'surbma-wp-control' );
					}
				} else {
					echo '<p>' . __( 'Sorry, your Multisite install is too large, this plugin is not optimized for such a large network.', 'surbma-wp-control' ) . '</p>';
				}
			?>
		</div>

	</div>
</div>
<?php
}
