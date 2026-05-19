<?php

defined( 'ABSPATH' ) || die;

/**
 * Base admin URL for the Images & thumbnails page.
 *
 * @return string
 */
function surbma_wp_control_get_images_thumbnails_page_url() {
	return admin_url( 'admin.php?page=surbma-wp-control-images' );
}

/**
 * CSS class for positive (green) or negative (red) usage styling.
 *
 * @param bool $is_positive Used / referenced state.
 * @return string
 */
function surbma_wp_control_get_status_class( $is_positive ) {
	return $is_positive ? 'surbma-wp-control-status--ok' : 'surbma-wp-control-status--no';
}

/**
 * CSS class for registration status column (Enabled, Disabled, Not registered).
 *
 * @param string $status enabled, disabled, or not_registered.
 * @return string
 */
function surbma_wp_control_get_registration_status_class( $status ) {
	switch ( $status ) {
		case 'enabled':
			return 'surbma-wp-control-status--ok';
		case 'disabled':
			return 'surbma-wp-control-status--no';
		case 'not_registered':
			return 'surbma-wp-control-status--unregistered';
		default:
			return '';
	}
}

/**
 * Print status color styles once per page load.
 */
function surbma_wp_control_print_images_thumbnails_status_styles() {
	static $printed = false;

	if ( $printed ) {
		return;
	}

	$printed = true;
	?>
	<style>
		.surbma-wp-control-images-thumbnails td.surbma-wp-control-status--ok,
		.surbma-wp-control-images-thumbnails td.column-in-library.surbma-wp-control-status--ok,
		.surbma-wp-control-images-thumbnails td.column-in-content.surbma-wp-control-status--ok {
			color: #00a32a;
			font-weight: 600;
		}
		.surbma-wp-control-images-thumbnails td.surbma-wp-control-status--no,
		.surbma-wp-control-images-thumbnails td.column-in-library.surbma-wp-control-status--no,
		.surbma-wp-control-images-thumbnails td.column-in-content.surbma-wp-control-status--no {
			color: #d63638;
			font-weight: 600;
		}
		.surbma-wp-control-images-thumbnails td.column-status.surbma-wp-control-status--unregistered {
			color: #646970;
			font-weight: 600;
		}
	</style>
	<?php
}

/**
 * Open a per-site card.
 *
 * @param int    $blog_id   Blog ID.
 * @param string $site_name Site display name.
 * @param bool   $is_main   Whether this is the main site.
 */
function surbma_wp_control_render_images_thumbnails_site_card_open( $blog_id, $site_name, $is_main ) {
	?>
	<div class="card" style="max-width: none; margin-top: 1.5em;">
		<h2 class="title">
			<?php echo esc_html( $site_name ); ?>
			<?php if ( $is_main ) : ?>
				<span class="description"><?php esc_html_e( '(Main site)', 'surbma-wp-control' ); ?></span>
			<?php endif; ?>
			<span class="description">
				<?php
				printf(
					/* translators: %d: blog ID */
					esc_html__( 'Blog ID %d', 'surbma-wp-control' ),
					(int) $blog_id
				);
				?>
			</span>
		</h2>
	<?php
}

/**
 * Close a per-site card.
 */
function surbma_wp_control_render_images_thumbnails_site_card_close() {
	echo '</div>';
}

/**
 * Plain-text list of active image sizes (for clipboard copy).
 *
 * @param array<string, array{width: int, height: int, crop: bool}> $active_sizes Active sizes.
 * @return string
 */
function surbma_wp_control_get_active_image_sizes_copy_text( $active_sizes ) {
	$lines = array();

	foreach ( $active_sizes as $name => $dimensions ) {
		$lines[] = sprintf(
			'%s: %s × %s px',
			$name,
			$dimensions['width'],
			$dimensions['height']
		);
	}

	return implode( "\n", $lines );
}

/**
 * Copy button for the active image sizes list.
 *
 * @param array<string, array{width: int, height: int, crop: bool}> $active_sizes Active sizes.
 */
function surbma_wp_control_render_active_image_sizes_copy_button( $active_sizes ) {
	$source_id = 'surbma-wp-control-active-sizes-' . wp_unique_id();
	$text      = surbma_wp_control_get_active_image_sizes_copy_text( $active_sizes );
	?>
	<p class="surbma-wp-control-active-sizes-copy">
		<button type="button" class="button button-secondary surbma-wp-control-copy-active-sizes" data-target="<?php echo esc_attr( $source_id ); ?>">
			<?php esc_html_e( 'Copy active image sizes', 'surbma-wp-control' ); ?>
		</button>
		<span class="surbma-wp-control-copy-feedback description" hidden aria-live="polite"><?php esc_html_e( 'Copied!', 'surbma-wp-control' ); ?></span>
		<textarea id="<?php echo esc_attr( $source_id ); ?>" readonly hidden class="surbma-wp-control-active-sizes-source"><?php echo esc_textarea( $text ); ?></textarea>
	</p>
	<?php
}

/**
 * Print clipboard script once per page load.
 */
function surbma_wp_control_print_active_image_sizes_copy_script() {
	static $printed = false;

	if ( $printed ) {
		return;
	}

	$printed = true;
	?>
	<script>
	( function () {
		document.addEventListener( 'click', function ( event ) {
			var button = event.target.closest( '.surbma-wp-control-copy-active-sizes' );

			if ( ! button ) {
				return;
			}

			var sourceId = button.getAttribute( 'data-target' );
			var source = sourceId ? document.getElementById( sourceId ) : null;
			var wrap = button.closest( '.surbma-wp-control-active-sizes-copy' );
			var feedback = wrap ? wrap.querySelector( '.surbma-wp-control-copy-feedback' ) : null;

			if ( ! source ) {
				return;
			}

			var text = source.value;

			function showFeedback() {
				if ( ! feedback ) {
					return;
				}

				feedback.hidden = false;

				window.setTimeout( function () {
					feedback.hidden = true;
				}, 2000 );
			}

			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( text ).then( showFeedback );
				return;
			}

			source.removeAttribute( 'hidden' );
			source.style.position = 'absolute';
			source.style.left = '-9999px';
			source.select();
			source.setSelectionRange( 0, text.length );

			try {
				document.execCommand( 'copy' );
				showFeedback();
			} catch ( e ) {
				// No-op.
			}

			source.setAttribute( 'hidden', 'hidden' );
		} );
	}() );
	</script>
	<?php
}

/**
 * Format a usage status cell for the In library column.
 *
 * @param int  $count   Reference count.
 * @param bool $is_used Whether the size is used.
 */
function surbma_wp_control_render_media_cleaner_usage_cell( $count, $is_used ) {
	if ( $is_used ) {
		printf(
			/* translators: %s: number of references */
			esc_html__( 'Used (%s)', 'surbma-wp-control' ),
			esc_html( number_format_i18n( $count ) )
		);
	} else {
		esc_html_e( 'Not used', 'surbma-wp-control' );
	}
}

/**
 * Build table rows: registered sizes plus orphans from attachment metadata.
 *
 * @param array<string, array{width: int, height: int, crop: bool}> $all_sizes       Registered sizes.
 * @param array<string, array{width: int, height: int, crop: bool}> $active_sizes    Active sizes.
 * @param array<string, int>                                        $meta_counts     Library counts by slug.
 * @param array<string, array{width: int, height: int, crop: bool}> $size_dimensions Dimensions from metadata.
 * @return array<string, array{dimensions: array{width: int, height: int, crop: bool}, status: string}>
 */
function surbma_wp_control_build_images_thumbnails_table_rows( $all_sizes, $active_sizes, $meta_counts, $size_dimensions ) {
	$rows = array();

	foreach ( $all_sizes as $name => $dimensions ) {
		$rows[ $name ] = array(
			'dimensions' => $dimensions,
			'status'     => isset( $active_sizes[ $name ] ) ? 'enabled' : 'disabled',
		);
	}

	$orphan_slugs = array();

	foreach ( array_keys( $meta_counts ) as $slug ) {
		if ( ! isset( $all_sizes[ $slug ] ) ) {
			$orphan_slugs[] = $slug;
		}
	}

	sort( $orphan_slugs, SORT_STRING );

	foreach ( $orphan_slugs as $slug ) {
		if ( isset( $size_dimensions[ $slug ] ) ) {
			$dimensions = array(
				'width'  => (int) $size_dimensions[ $slug ]['width'],
				'height' => (int) $size_dimensions[ $slug ]['height'],
				'crop'   => false,
			);
		} else {
			$dimensions = array(
				'width'  => 0,
				'height' => 0,
				'crop'   => false,
			);
		}

		$rows[ $slug ] = array(
			'dimensions' => $dimensions,
			'status'     => 'not_registered',
		);
	}

	return $rows;
}

/**
 * Build summary stats for one site's media cleaner report.
 *
 * @param array<string, array{dimensions: array, status: string}> $table_rows     Merged table rows.
 * @param array<string, int>                                       $meta_counts    Library counts by slug.
 * @param array<string, int>                                       $content_counts Content reference counts.
 * @return array{
 *     library_used: int,
 *     library_unused: int,
 *     content_used: int,
 *     content_unused: int,
 *     total_sizes: int
 * }
 */
function surbma_wp_control_get_media_cleaner_site_stats( $table_rows, $meta_counts, $content_counts ) {
	$library_used = 0;
	$content_used = 0;

	foreach ( array_keys( $table_rows ) as $slug ) {
		if ( ! empty( $meta_counts[ $slug ] ) ) {
			++$library_used;
		}
		if ( ! empty( $content_counts[ $slug ] ) ) {
			++$content_used;
		}
	}

	$total_sizes = count( $table_rows );

	return array(
		'library_used'   => $library_used,
		'library_unused' => $total_sizes - $library_used,
		'content_used'   => $content_used,
		'content_unused' => $total_sizes - $content_used,
		'total_sizes'    => $total_sizes,
	);
}

/**
 * Render combined image sizes and usage table for one site.
 *
 * @param array<string, mixed> $site_data Site data from surbma_wp_control_get_images_thumbnails_site_data_for_blog().
 */
function surbma_wp_control_render_images_thumbnails_site_section( $site_data ) {
	$all_sizes = isset( $site_data['all_sizes'] ) && is_array( $site_data['all_sizes'] )
		? $site_data['all_sizes']
		: array();

	$active_sizes = isset( $site_data['active_sizes'] ) && is_array( $site_data['active_sizes'] )
		? $site_data['active_sizes']
		: surbma_wp_control_get_active_image_sizes( $all_sizes );

	$meta_counts         = isset( $site_data['metadata']['counts'] ) ? $site_data['metadata']['counts'] : array();
	$content_counts      = isset( $site_data['content']['counts'] ) ? $site_data['content']['counts'] : array();
	$size_dimensions     = isset( $site_data['metadata']['size_dimensions'] ) ? $site_data['metadata']['size_dimensions'] : array();
	$attachments_scanned = isset( $site_data['metadata']['attachments_scanned'] ) ? (int) $site_data['metadata']['attachments_scanned'] : 0;
	$table_rows          = surbma_wp_control_build_images_thumbnails_table_rows( $all_sizes, $active_sizes, $meta_counts, $size_dimensions );
	$stats               = surbma_wp_control_get_media_cleaner_site_stats( $table_rows, $meta_counts, $content_counts );
	?>
	<p class="description">
		<?php
		echo esc_html(
			sprintf(
				/* translators: 1: attachments scanned, 2: sizes used in library, 3: total sizes, 4: unused in library, 5: sizes referenced in content, 6: not referenced in content */
				__( 'Image attachments scanned: %1$s. In library: %2$d of %3$d sizes used, %4$d unused. In content: %5$d of %3$d sizes referenced, %6$d not referenced.', 'surbma-wp-control' ),
				number_format_i18n( $attachments_scanned ),
				$stats['library_used'],
				$stats['total_sizes'],
				$stats['library_unused'],
				$stats['content_used'],
				$stats['content_unused']
			)
		);
		?>
	</p>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Size name', 'surbma-wp-control' ); ?></th>
				<th scope="col" class="column-dimensions"><?php esc_html_e( 'Dimensions', 'surbma-wp-control' ); ?></th>
				<th scope="col" class="column-crop"><?php esc_html_e( 'Crop', 'surbma-wp-control' ); ?></th>
				<th scope="col" class="column-status"><?php esc_html_e( 'Status', 'surbma-wp-control' ); ?></th>
				<th scope="col" class="column-in-library"><?php esc_html_e( 'In library', 'surbma-wp-control' ); ?></th>
				<th scope="col" class="column-in-content"><?php esc_html_e( 'In content', 'surbma-wp-control' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $table_rows as $name => $row ) : ?>
				<?php
				$dimensions    = $row['dimensions'];
				$registration  = $row['status'];
				$meta_count    = isset( $meta_counts[ $name ] ) ? (int) $meta_counts[ $name ] : 0;
				$content_count = isset( $content_counts[ $name ] ) ? (int) $content_counts[ $name ] : 0;
				$in_library    = $meta_count > 0;
				$in_content    = $content_count > 0;
				$has_dimensions = (int) $dimensions['width'] > 0 && (int) $dimensions['height'] > 0;
				?>
				<tr>
					<td><strong><?php echo esc_html( $name ); ?></strong></td>
					<td class="column-dimensions">
						<?php
						if ( $has_dimensions ) {
							echo esc_html(
								sprintf(
									/* translators: 1: width in pixels, 2: height in pixels */
									__( '%1$s × %2$s px', 'surbma-wp-control' ),
									$dimensions['width'],
									$dimensions['height']
								)
							);
						} else {
							echo '—';
						}
						?>
					</td>
					<td class="column-crop">
						<?php
						if ( 'not_registered' === $registration ) {
							echo '—';
						} else {
							echo $dimensions['crop'] ? esc_html__( 'Yes', 'surbma-wp-control' ) : esc_html__( 'No', 'surbma-wp-control' );
						}
						?>
					</td>
					<td class="column-status <?php echo esc_attr( surbma_wp_control_get_registration_status_class( $registration ) ); ?>">
						<?php
						if ( 'enabled' === $registration ) {
							esc_html_e( 'Enabled', 'surbma-wp-control' );
						} elseif ( 'disabled' === $registration ) {
							esc_html_e( 'Disabled', 'surbma-wp-control' );
						} else {
							esc_html_e( 'Not registered', 'surbma-wp-control' );
						}
						?>
					</td>
					<td class="column-in-library <?php echo esc_attr( surbma_wp_control_get_status_class( $in_library ) ); ?>">
						<?php surbma_wp_control_render_media_cleaner_usage_cell( $meta_count, $in_library ); ?>
					</td>
					<td class="column-in-content <?php echo esc_attr( surbma_wp_control_get_status_class( $in_content ) ); ?>">
						<?php
						if ( $in_content ) {
							printf(
								/* translators: %s: number of references */
								esc_html__( 'Referenced (%s)', 'surbma-wp-control' ),
								esc_html( number_format_i18n( $content_count ) )
							);
						} else {
							esc_html_e( 'Not referenced', 'surbma-wp-control' );
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php surbma_wp_control_render_active_image_sizes_copy_button( $active_sizes ); ?>
	<?php
}

/**
 * Render one site card (single site admin).
 *
 * @param array<string, mixed> $site_data     Site report data.
 * @param bool                 $show_scanned Whether to show last-scanned line inside the card.
 */
function surbma_wp_control_render_images_thumbnails_site_card( $site_data, $show_scanned = true ) {
	$blog_id   = isset( $site_data['blog_id'] ) ? (int) $site_data['blog_id'] : 0;
	$site_name = isset( $site_data['site_name'] ) ? $site_data['site_name'] : '';
	$is_main   = ! empty( $site_data['is_main_site'] );
	$scanned_at = isset( $site_data['scanned_at'] ) ? (int) $site_data['scanned_at'] : 0;

	surbma_wp_control_render_images_thumbnails_site_card_open( $blog_id, $site_name, $is_main );

	if ( $show_scanned && $scanned_at > 0 ) :
		?>
		<p class="description">
			<?php
			printf(
				/* translators: %s: localized date and time */
				esc_html__( 'Last scanned: %s.', 'surbma-wp-control' ),
				esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $scanned_at ) )
			);
			?>
		</p>
		<?php
	endif;

	surbma_wp_control_render_images_thumbnails_site_section( $site_data );
	surbma_wp_control_render_images_thumbnails_site_card_close();
}

/**
 * Render Images & thumbnails for the current site (site admin).
 *
 * @param bool $force_refresh Rescan when true.
 */
function surbma_wp_control_render_images_thumbnails_single_site( $force_refresh ) {
	if ( is_multisite() ) :
		?>
		<div class="notice notice-info inline">
			<p>
				<?php
				printf(
					/* translators: 1: site name, 2: numeric blog ID */
					esc_html__( 'Showing: %1$s (blog ID %2$d).', 'surbma-wp-control' ),
					esc_html( get_bloginfo( 'name' ) ),
					(int) get_current_blog_id()
				);
				?>
			</p>
		</div>
		<?php
	endif;

	$site_data = surbma_wp_control_get_images_thumbnails_site_data_for_blog( get_current_blog_id(), $force_refresh );

	surbma_wp_control_render_images_thumbnails_site_card( $site_data, true );
}

/**
 * Render the Images & thumbnails page.
 */
function surbma_wp_control_render_images_thumbnails() {
	$force_refresh = isset( $_GET['refresh'] ) && '1' === $_GET['refresh'];

	if ( $force_refresh ) {
		surbma_wp_control_delete_media_cleaner_cache();
	}

	$page_url    = surbma_wp_control_get_images_thumbnails_page_url();
	$refresh_url = add_query_arg( 'refresh', '1', $page_url );

	surbma_wp_control_print_images_thumbnails_status_styles();
	?>
	<div class="wrap surbma-wp-control-images-thumbnails">
		<h1><?php esc_html_e( 'Images & thumbnails', 'surbma-wp-control' ); ?></h1>

		<hr class="wp-header-end">

		<p>
			<?php esc_html_e( 'Registered image sizes for this site, whether they are enabled for generation, and whether they appear in the media library or in content. Rows marked Not registered are legacy sizes still stored in attachment metadata. “In library” means at least one attachment has that size in metadata. “In content” means the size slug or dimensions were found in posts or post meta. Counts are references, not unique posts.', 'surbma-wp-control' ); ?>
		</p>

		<p>
			<a href="<?php echo esc_url( $refresh_url ); ?>" class="button button-secondary"><?php esc_html_e( 'Refresh scan', 'surbma-wp-control' ); ?></a>
		</p>

		<?php surbma_wp_control_render_images_thumbnails_single_site( $force_refresh ); ?>

		<p class="description">
			<?php esc_html_e( 'In library reflects metadata only, not guaranteed files on disk. In content uses heuristics and may miss page builders or CDN URLs. Two sizes with the same dimensions can match the same URL pattern. Disabling sizes and regenerating thumbnails is done outside this plugin.', 'surbma-wp-control' ); ?>
		</p>
	</div>
	<?php

	surbma_wp_control_print_active_image_sizes_copy_script();
}
