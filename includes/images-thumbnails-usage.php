<?php

defined( 'ABSPATH' ) || die;

/**
 * Bump when the persisted image size registry shape or semantics change.
 */
const SURBMA_WP_CONTROL_IMAGE_SIZES_REGISTRY_VERSION = 2;

/**
 * All registered image sizes (standard, additional, and core subsizes).
 *
 * @return array<string, array{width: int, height: int, crop: bool}>
 */
function surbma_wp_control_get_registered_image_sizes() {
	global $_wp_additional_image_sizes;

	$all_sizes      = array();
	$standard_sizes = array( 'thumbnail', 'medium', 'medium_large', 'large' );

	foreach ( $standard_sizes as $size ) {
		$all_sizes[ $size ] = array(
			'width'  => (int) get_option( "{$size}_size_w" ),
			'height' => (int) get_option( "{$size}_size_h" ),
			'crop'   => (bool) get_option( "{$size}_crop" ),
		);
	}

	if ( ! empty( $_wp_additional_image_sizes ) && is_array( $_wp_additional_image_sizes ) ) {
		$all_sizes = array_merge( $all_sizes, $_wp_additional_image_sizes );
	}

	if ( function_exists( 'wp_get_registered_image_subsizes' ) ) {
		foreach ( wp_get_registered_image_subsizes() as $name => $data ) {
			if ( ! isset( $all_sizes[ $name ] ) ) {
				$all_sizes[ $name ] = array(
					'width'  => (int) $data['width'],
					'height' => (int) $data['height'],
					'crop'   => $data['crop'],
				);
			}
		}
	}

	return $all_sizes;
}

/**
 * Image sizes currently enabled for generation on upload.
 *
 * @param array<string, array{width: int, height: int, crop: bool}> $all_sizes Registered sizes.
 * @return array<string, array{width: int, height: int, crop: bool}>
 */
function surbma_wp_control_get_active_image_sizes( $all_sizes ) {
	$active_sizes = array();

	foreach ( get_intermediate_image_sizes() as $_size ) {
		if ( isset( $all_sizes[ $_size ] ) ) {
			$active_sizes[ $_size ] = $all_sizes[ $_size ];
		}
	}

	return $active_sizes;
}

/**
 * Transient key for a site's persisted image size registry (from subsite admin).
 *
 * @param int $blog_id Blog ID.
 * @return string
 */
function surbma_wp_control_get_image_sizes_registry_transient_key( $blog_id ) {
	return 'surbma_wp_control_image_sizes_' . (int) $blog_id;
}

/**
 * Save the full registered/active size list for one site (subsite admin only).
 *
 * @param int                                                          $blog_id      Blog ID.
 * @param array<string, array{width: int, height: int, crop: bool}> $all_sizes    Registered sizes.
 * @param array<string, array{width: int, height: int, crop: bool}> $active_sizes Active sizes.
 */
function surbma_wp_control_persist_image_sizes_registry( $blog_id, $all_sizes, $active_sizes ) {
	$blog_id = (int) $blog_id;

	if ( get_current_blog_id() !== $blog_id ) {
		return;
	}

	$data = array(
		'registry_version' => SURBMA_WP_CONTROL_IMAGE_SIZES_REGISTRY_VERSION,
		'all_sizes'        => $all_sizes,
		'active_sizes'     => $active_sizes,
		'stylesheet'       => get_stylesheet(),
		'template'         => get_template(),
		'saved_at'         => time(),
	);

	$ttl = (int) apply_filters( 'surbma_wp_control_image_sizes_registry_cache_ttl', MONTH_IN_SECONDS );

	if ( $ttl > 0 ) {
		set_transient( surbma_wp_control_get_image_sizes_registry_transient_key( $blog_id ), $data, $ttl );
	}
}

/**
 * Load a previously persisted registry for the current blog context.
 *
 * @param int $blog_id Blog ID.
 * @return array{all_sizes: array<string, array>, active_sizes: array<string, array>}|null
 */
function surbma_wp_control_get_persisted_image_sizes_registry( $blog_id ) {
	$cached = get_transient( surbma_wp_control_get_image_sizes_registry_transient_key( $blog_id ) );

	if ( ! is_array( $cached ) || empty( $cached['all_sizes'] ) || ! is_array( $cached['all_sizes'] ) ) {
		return null;
	}

	if ( get_stylesheet() !== ( $cached['stylesheet'] ?? '' ) ) {
		return null;
	}

	if ( (int) ( $cached['registry_version'] ?? 0 ) !== SURBMA_WP_CONTROL_IMAGE_SIZES_REGISTRY_VERSION ) {
		return null;
	}

	return array(
		'all_sizes'    => $cached['all_sizes'],
		'active_sizes' => isset( $cached['active_sizes'] ) && is_array( $cached['active_sizes'] ) ? $cached['active_sizes'] : array(),
	);
}

/**
 * Keep only active slugs that exist in the registered size list.
 *
 * @param array<string, array{width: int, height: int, crop: bool}> $all_sizes    Registered sizes.
 * @param array<string, array{width: int, height: int, crop: bool}> $active_sizes Active sizes.
 * @return array<string, array{width: int, height: int, crop: bool}>
 */
function surbma_wp_control_filter_active_sizes_for_registry( $all_sizes, $active_sizes ) {
	$filtered = array();

	foreach ( $active_sizes as $slug => $dimensions ) {
		if ( isset( $all_sizes[ $slug ] ) ) {
			$filtered[ $slug ] = $all_sizes[ $slug ];
		}
	}

	return $filtered;
}

/**
 * Resolve registered and active image sizes for one site.
 *
 * Site wp-admin saves a theme-complete registry snapshot. Both site and Network
 * Admin read that snapshot so Status (Enabled/Disabled) matches. Network Admin
 * never overwrites the snapshot (main site included).
 *
 * @param int $blog_id Blog ID.
 * @return array{
 *     all_sizes: array<string, array{width: int, height: int, crop: bool}>,
 *     active_sizes: array<string, array{width: int, height: int, crop: bool}>
 * }
 */
function surbma_wp_control_resolve_image_sizes_for_blog( $blog_id ) {
	$blog_id = (int) $blog_id;
	$context = surbma_wp_control_begin_blog_image_sizes_context( $blog_id );

	$runtime_all    = surbma_wp_control_get_registered_image_sizes();
	$runtime_active = surbma_wp_control_get_active_image_sizes( $runtime_all );

	if ( empty( $context['switched'] ) ) {
		surbma_wp_control_persist_image_sizes_registry( $blog_id, $runtime_all, $runtime_active );
	}

	$persisted = surbma_wp_control_get_persisted_image_sizes_registry( $blog_id );

	surbma_wp_control_end_blog_image_sizes_context( $context );

	if ( null !== $persisted ) {
		$all_sizes    = $persisted['all_sizes'];
		$active_sizes = surbma_wp_control_filter_active_sizes_for_registry( $all_sizes, $persisted['active_sizes'] );

		return array(
			'all_sizes'    => $all_sizes,
			'active_sizes' => $active_sizes,
		);
	}

	return array(
		'all_sizes'    => $runtime_all,
		'active_sizes' => $runtime_active,
	);
}

/**
 * Switch blog context and reset additional image sizes (multisite Network Admin).
 *
 * Does not load theme functions.php — many themes (e.g. Divi) require a full
 * bootstrap and fatally error when included from admin. Per-site options and
 * core subsizes are read after switch_to_blog(); theme add_image_size() slugs
 * still appear via attachment metadata as Not registered rows.
 *
 * @param int $blog_id Target site ID.
 * @return array{backup: array<string, mixed>|null, switched: bool}
 */
function surbma_wp_control_begin_blog_image_sizes_context( $blog_id ) {
	global $_wp_additional_image_sizes;

	$blog_id      = (int) $blog_id;
	$needs_switch = get_current_blog_id() !== $blog_id;
	$backup_sizes = $_wp_additional_image_sizes;

	if ( $needs_switch ) {
		$_wp_additional_image_sizes = array();
		switch_to_blog( $blog_id );

		if ( function_exists( 'wp_get_registered_image_subsizes' ) ) {
			wp_get_registered_image_subsizes();
		}
	}

	return array(
		'backup'   => $backup_sizes,
		'switched' => $needs_switch,
	);
}

/**
 * Restore blog and $_wp_additional_image_sizes after begin_blog_image_sizes_context().
 *
 * @param array{backup: array<string, mixed>|null, switched: bool} $context Context from begin_blog_image_sizes_context().
 */
function surbma_wp_control_end_blog_image_sizes_context( $context ) {
	global $_wp_additional_image_sizes;

	if ( empty( $context['switched'] ) ) {
		return;
	}

	restore_current_blog();
	$_wp_additional_image_sizes = isset( $context['backup'] ) ? $context['backup'] : array();
}

/**
 * Registered and active image sizes for one site (switches blog context when needed).
 *
 * Resets $_wp_additional_image_sizes when switching blogs so sizes from other
 * sites do not leak into this site's report (multisite Network Admin).
 *
 * @param int $blog_id Site ID.
 * @return array{
 *     blog_id: int,
 *     site_name: string,
 *     is_main_site: bool,
 *     all_sizes: array<string, array{width: int, height: int, crop: bool}>,
 *     active_sizes: array<string, array{width: int, height: int, crop: bool}>
 * }
 */
function surbma_wp_control_get_image_sizes_data_for_blog( $blog_id ) {
	$blog_id = (int) $blog_id;
	$context = surbma_wp_control_begin_blog_image_sizes_context( $blog_id );

	$all_sizes = surbma_wp_control_get_registered_image_sizes();

	$data = array(
		'blog_id'      => $blog_id,
		'site_name'    => get_bloginfo( 'name' ),
		'is_main_site' => is_main_site( $blog_id ),
		'all_sizes'    => $all_sizes,
		'active_sizes' => surbma_wp_control_get_active_image_sizes( $all_sizes ),
	);

	surbma_wp_control_end_blog_image_sizes_context( $context );

	return $data;
}

/**
 * Image sizes registry, active sizes, and media cleaner usage for one site.
 *
 * @param int  $blog_id       Site ID.
 * @param bool $force_refresh Skip usage cache and rescan.
 * @return array<string, mixed>
 */
function surbma_wp_control_get_images_thumbnails_site_data_for_blog( $blog_id, $force_refresh = false ) {
	$blog_id = (int) $blog_id;
	$context = surbma_wp_control_begin_blog_image_sizes_context( $blog_id );

	$usage = surbma_wp_control_get_media_cleaner_usage_data( $force_refresh, $blog_id );

	$usage['blog_id']      = $blog_id;
	$usage['site_name']    = get_bloginfo( 'name' );
	$usage['site_url']     = get_home_url( $blog_id, '/' );
	$usage['is_main_site'] = is_main_site( $blog_id );

	surbma_wp_control_end_blog_image_sizes_context( $context );

	$sizes = surbma_wp_control_resolve_image_sizes_for_blog( $blog_id );

	return array_merge(
		$usage,
		array(
			'all_sizes'        => $sizes['all_sizes'],
			'active_sizes'     => $sizes['active_sizes'],
			'registered_sizes' => $sizes['all_sizes'],
		)
	);
}

/**
 * Transient key for media cleaner scan cache (per site).
 *
 * @param int|null $blog_id Blog ID; defaults to current blog.
 * @return string
 */
function surbma_wp_control_get_media_cleaner_transient_key( $blog_id = null ) {
	if ( null === $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	return 'surbma_wp_control_media_cleaner_' . (int) $blog_id;
}

/**
 * Scan attachment metadata for generated image size usage.
 *
 * @return array{
 *     counts: array<string, int>,
 *     attachments_scanned: int,
 *     size_dimensions: array<string, array{width: int, height: int, crop: bool}>
 * }
 */
function surbma_wp_control_scan_image_size_metadata_usage() {
	global $wpdb;

	$counts                = array();
	$size_dimensions       = array();
	$attachments_scanned   = 0;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$rows = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT pm.meta_value
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = %s
			AND p.post_type = %s
			AND p.post_mime_type LIKE %s",
			'_wp_attachment_metadata',
			'attachment',
			'image/%'
		)
	);

	if ( ! is_array( $rows ) ) {
		return array(
			'counts'                => $counts,
			'attachments_scanned'   => 0,
			'size_dimensions'       => $size_dimensions,
		);
	}

	$attachments_scanned = count( $rows );

	foreach ( $rows as $meta_value ) {
		$meta = maybe_unserialize( $meta_value );

		if ( ! is_array( $meta ) || empty( $meta['sizes'] ) || ! is_array( $meta['sizes'] ) ) {
			continue;
		}

		foreach ( array_keys( $meta['sizes'] ) as $slug ) {
			if ( ! isset( $counts[ $slug ] ) ) {
				$counts[ $slug ] = 0;
			}
			++$counts[ $slug ];

			if ( ! isset( $size_dimensions[ $slug ] ) && ! empty( $meta['sizes'][ $slug ]['width'] ) && ! empty( $meta['sizes'][ $slug ]['height'] ) ) {
				$size_dimensions[ $slug ] = array(
					'width'  => (int) $meta['sizes'][ $slug ]['width'],
					'height' => (int) $meta['sizes'][ $slug ]['height'],
					'crop'   => false,
				);
			}
		}
	}

	return array(
		'counts'                => $counts,
		'attachments_scanned'   => $attachments_scanned,
		'size_dimensions'       => $size_dimensions,
	);
}

/**
 * Recursively collect sizeSlug references from parsed blocks.
 *
 * @param array<int, array<string, mixed>> $blocks  Parsed blocks.
 * @param array<string, int>              $counts  Size slug => reference count (by reference).
 */
function surbma_wp_control_collect_block_size_slugs( $blocks, &$counts ) {
	$size_blocks = array(
		'core/image',
		'core/cover',
		'core/media-text',
	);

	foreach ( $blocks as $block ) {
		$block_name = isset( $block['blockName'] ) ? $block['blockName'] : '';

		if ( in_array( $block_name, $size_blocks, true ) ) {
			$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();

			if ( ! empty( $attrs['sizeSlug'] ) ) {
				$slug = (string) $attrs['sizeSlug'];

				if ( ! isset( $counts[ $slug ] ) ) {
					$counts[ $slug ] = 0;
				}
				++$counts[ $slug ];
			}
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			surbma_wp_control_collect_block_size_slugs( $block['innerBlocks'], $counts );
		}
	}
}

/**
 * Count substring occurrences in haystack.
 *
 * @param string $haystack Content to search.
 * @param string $needle   Substring.
 * @return int
 */
function surbma_wp_control_substr_count( $haystack, $needle ) {
	if ( '' === $needle || '' === $haystack ) {
		return 0;
	}

	return substr_count( $haystack, $needle );
}

/**
 * Scan posts and meta for image size references in content.
 *
 * @param array<string, array{width: int, height: int, crop: bool}> $registered_sizes Registered sizes.
 * @return array{counts: array<string, int>}
 */
function surbma_wp_control_scan_image_size_content_usage( $registered_sizes ) {
	global $wpdb;

	$counts         = array();
	$exclude_types  = array(
		'attachment',
		'revision',
		'nav_menu_item',
		'custom_css',
		'customize_changeset',
		'oembed_cache',
		'user_request',
		'wp_block',
		'wp_template',
		'wp_template_part',
		'wp_navigation',
	);
	$post_statuses  = array( 'publish', 'draft', 'private', 'pending', 'future' );
	$status_placeholders = implode( ', ', array_fill( 0, count( $post_statuses ), '%s' ) );
	$type_placeholders   = implode( ', ', array_fill( 0, count( $exclude_types ), '%s' ) );

	$query_args = array_merge( $post_statuses, $exclude_types );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$posts = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID, post_content
			FROM {$wpdb->posts}
			WHERE post_status IN ({$status_placeholders})
			AND post_type NOT IN ({$type_placeholders})
			AND post_content != ''",
			$query_args
		),
		ARRAY_A
	);

	$slug_patterns = array();
	$dim_patterns  = array();

	foreach ( $registered_sizes as $slug => $dimensions ) {
		$width  = (int) $dimensions['width'];
		$height = (int) $dimensions['height'];

		$slug_patterns[ $slug ] = array(
			'sizeSlug_json'   => '"sizeSlug":"' . $slug . '"',
			'sizeSlug_json_sp' => '"sizeSlug": "' . $slug . '"',
			'size_attr'       => 'size="' . $slug . '"',
			'size_attr_single' => "size='" . $slug . "'",
		);

		if ( $width > 0 && $height > 0 ) {
			$dim_patterns[ $slug ] = '-' . $width . 'x' . $height . '.';
		}
	}

	if ( is_array( $posts ) ) {
		foreach ( $posts as $post ) {
			$content = $post['post_content'];

			if ( false !== strpos( $content, '<!-- wp:' ) && function_exists( 'parse_blocks' ) ) {
				$blocks = parse_blocks( $content );
				surbma_wp_control_collect_block_size_slugs( $blocks, $counts );
			}

			foreach ( $slug_patterns as $slug => $patterns ) {
				$slug_hits = 0;

				foreach ( $patterns as $pattern ) {
					$slug_hits += surbma_wp_control_substr_count( $content, $pattern );
				}

				if ( isset( $dim_patterns[ $slug ] ) ) {
					$slug_hits += surbma_wp_control_substr_count( $content, $dim_patterns[ $slug ] );
				}

				if ( $slug_hits > 0 ) {
					if ( ! isset( $counts[ $slug ] ) ) {
						$counts[ $slug ] = 0;
					}
					$counts[ $slug ] += $slug_hits;
				}
			}
		}
	}

	$meta_like_parts = array( '%sizeSlug%' );

	foreach ( $dim_patterns as $dim_suffix ) {
		$meta_like_parts[] = '%' . $wpdb->esc_like( $dim_suffix ) . '%';
	}

	$meta_like_sql = array();
	$meta_like_args = array();

	foreach ( $meta_like_parts as $like ) {
		$meta_like_sql[] = 'pm.meta_value LIKE %s';
		$meta_like_args[] = $like;
	}

	$meta_like_clause = implode( ' OR ', $meta_like_sql );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$meta_rows = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT pm.meta_value
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE p.post_status IN ({$status_placeholders})
			AND p.post_type NOT IN ({$type_placeholders})
			AND ( {$meta_like_clause} )",
			array_merge( $query_args, $meta_like_args )
		)
	);

	if ( is_array( $meta_rows ) ) {
		foreach ( $meta_rows as $meta_value ) {
			if ( ! is_string( $meta_value ) || '' === $meta_value ) {
				continue;
			}

			foreach ( $slug_patterns as $slug => $patterns ) {
				$slug_hits = 0;

				foreach ( $patterns as $pattern ) {
					$slug_hits += surbma_wp_control_substr_count( $meta_value, $pattern );
				}

				if ( isset( $dim_patterns[ $slug ] ) ) {
					$slug_hits += surbma_wp_control_substr_count( $meta_value, $dim_patterns[ $slug ] );
				}

				if ( $slug_hits > 0 ) {
					if ( ! isset( $counts[ $slug ] ) ) {
						$counts[ $slug ] = 0;
					}
					$counts[ $slug ] += $slug_hits;
				}
			}
		}
	}

	return array(
		'counts' => $counts,
	);
}

/**
 * Scan post content and meta for slug/dimension patterns only (no block parse).
 *
 * Used for orphan sizes not included in the registered-sizes content pass.
 *
 * @param array<string, array{width: int, height: int, crop: bool}> $sizes Sizes to search for.
 * @return array{counts: array<string, int>}
 */
function surbma_wp_control_scan_content_patterns_for_sizes( $sizes ) {
	global $wpdb;

	$counts        = array();
	$exclude_types = array(
		'attachment',
		'revision',
		'nav_menu_item',
		'custom_css',
		'customize_changeset',
		'oembed_cache',
		'user_request',
		'wp_block',
		'wp_template',
		'wp_template_part',
		'wp_navigation',
	);
	$post_statuses       = array( 'publish', 'draft', 'private', 'pending', 'future' );
	$status_placeholders = implode( ', ', array_fill( 0, count( $post_statuses ), '%s' ) );
	$type_placeholders   = implode( ', ', array_fill( 0, count( $exclude_types ), '%s' ) );
	$query_args          = array_merge( $post_statuses, $exclude_types );

	if ( empty( $sizes ) ) {
		return array(
			'counts' => $counts,
		);
	}

	$slug_patterns = array();
	$dim_patterns  = array();

	foreach ( $sizes as $slug => $dimensions ) {
		$width  = (int) $dimensions['width'];
		$height = (int) $dimensions['height'];

		$slug_patterns[ $slug ] = array(
			'sizeSlug_json'    => '"sizeSlug":"' . $slug . '"',
			'sizeSlug_json_sp' => '"sizeSlug": "' . $slug . '"',
			'size_attr'        => 'size="' . $slug . '"',
			'size_attr_single'  => "size='" . $slug . "'",
		);

		if ( $width > 0 && $height > 0 ) {
			$dim_patterns[ $slug ] = '-' . $width . 'x' . $height . '.';
		}
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
	$posts = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT post_content
			FROM {$wpdb->posts}
			WHERE post_status IN ({$status_placeholders})
			AND post_type NOT IN ({$type_placeholders})
			AND post_content != ''",
			$query_args
		),
		ARRAY_A
	);

	if ( is_array( $posts ) ) {
		foreach ( $posts as $post ) {
			$content = $post['post_content'];

			foreach ( $slug_patterns as $slug => $patterns ) {
				$slug_hits = 0;

				foreach ( $patterns as $pattern ) {
					$slug_hits += surbma_wp_control_substr_count( $content, $pattern );
				}

				if ( isset( $dim_patterns[ $slug ] ) ) {
					$slug_hits += surbma_wp_control_substr_count( $content, $dim_patterns[ $slug ] );
				}

				if ( $slug_hits > 0 ) {
					if ( ! isset( $counts[ $slug ] ) ) {
						$counts[ $slug ] = 0;
					}
					$counts[ $slug ] += $slug_hits;
				}
			}
		}
	}

	$meta_like_parts = array();
	$meta_like_args  = array();

	foreach ( array_keys( $slug_patterns ) as $slug ) {
		$meta_like_parts[] = '%' . $wpdb->esc_like( '"sizeSlug":"' . $slug . '"' ) . '%';
		$meta_like_parts[] = '%' . $wpdb->esc_like( 'size="' . $slug . '"' ) . '%';

		if ( isset( $dim_patterns[ $slug ] ) ) {
			$meta_like_parts[] = '%' . $wpdb->esc_like( $dim_patterns[ $slug ] ) . '%';
		}
	}

	if ( ! empty( $meta_like_parts ) ) {
		$meta_like_sql = array();

		foreach ( $meta_like_parts as $like ) {
			$meta_like_sql[] = 'pm.meta_value LIKE %s';
			$meta_like_args[] = $like;
		}

		$meta_like_clause = implode( ' OR ', $meta_like_sql );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$meta_rows = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT pm.meta_value
				FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE p.post_status IN ({$status_placeholders})
				AND p.post_type NOT IN ({$type_placeholders})
				AND ( {$meta_like_clause} )",
				array_merge( $query_args, $meta_like_args )
			)
		);

		if ( is_array( $meta_rows ) ) {
			foreach ( $meta_rows as $meta_value ) {
				if ( ! is_string( $meta_value ) || '' === $meta_value ) {
					continue;
				}

				foreach ( $slug_patterns as $slug => $patterns ) {
					$slug_hits = 0;

					foreach ( $patterns as $pattern ) {
						$slug_hits += surbma_wp_control_substr_count( $meta_value, $pattern );
					}

					if ( isset( $dim_patterns[ $slug ] ) ) {
						$slug_hits += surbma_wp_control_substr_count( $meta_value, $dim_patterns[ $slug ] );
					}

					if ( $slug_hits > 0 ) {
						if ( ! isset( $counts[ $slug ] ) ) {
							$counts[ $slug ] = 0;
						}
						$counts[ $slug ] += $slug_hits;
					}
				}
			}
		}
	}

	return array(
		'counts' => $counts,
	);
}

/**
 * Merge content reference counts for orphan size slugs (not in registered list).
 *
 * @param array{counts: array<string, int>}                         $content          Main content scan.
 * @param array<string, array{width: int, height: int, crop: bool}> $registered_sizes Registered sizes.
 * @param array{counts: array<string, int>, size_dimensions?: array<string, array{width: int, height: int, crop: bool}>} $metadata Metadata scan.
 * @return array{counts: array<string, int>}
 */
function surbma_wp_control_merge_orphan_content_counts( $content, $registered_sizes, $metadata ) {
	$meta_counts      = isset( $metadata['counts'] ) ? $metadata['counts'] : array();
	$size_dimensions  = isset( $metadata['size_dimensions'] ) ? $metadata['size_dimensions'] : array();
	$orphan_sizes     = array();

	foreach ( array_keys( $meta_counts ) as $slug ) {
		if ( isset( $registered_sizes[ $slug ] ) ) {
			continue;
		}

		if ( isset( $size_dimensions[ $slug ] ) ) {
			$orphan_sizes[ $slug ] = $size_dimensions[ $slug ];
		} else {
			$orphan_sizes[ $slug ] = array(
				'width'  => 0,
				'height' => 0,
				'crop'   => false,
			);
		}
	}

	if ( empty( $orphan_sizes ) ) {
		return $content;
	}

	$pattern_counts = surbma_wp_control_scan_content_patterns_for_sizes( $orphan_sizes );
	$merged_counts  = isset( $content['counts'] ) ? $content['counts'] : array();

	foreach ( $pattern_counts['counts'] as $slug => $count ) {
		if ( ! isset( $merged_counts[ $slug ] ) ) {
			$merged_counts[ $slug ] = $count;
		} else {
			$merged_counts[ $slug ] += $count;
		}
	}

	$content['counts'] = $merged_counts;

	return $content;
}

/**
 * Run full media cleaner scan (metadata + content), with transient cache.
 *
 * @param bool $force_refresh Skip cache and rescan.
 * @return array{
 *     metadata: array{counts: array<string, int>, attachments_scanned: int},
 *     content: array{counts: array<string, int>},
 *     registered_sizes: array<string, array{width: int, height: int, crop: bool}>,
 *     scanned_at: int
 * }
 */
function surbma_wp_control_get_media_cleaner_usage_data( $force_refresh = false, $blog_id = null ) {
	$transient_key = surbma_wp_control_get_media_cleaner_transient_key( $blog_id );

	if ( ! $force_refresh ) {
		$cached = get_transient( $transient_key );

		if ( false !== $cached && is_array( $cached ) && surbma_wp_control_is_media_cleaner_cache_valid( $cached ) ) {
			return $cached;
		}
	}

	if ( function_exists( 'set_time_limit' ) ) {
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@set_time_limit( 120 );
	}

	$registered_sizes = surbma_wp_control_get_registered_image_sizes();
	$metadata         = surbma_wp_control_scan_image_size_metadata_usage();
	$content          = surbma_wp_control_scan_image_size_content_usage( $registered_sizes );
	$content          = surbma_wp_control_merge_orphan_content_counts( $content, $registered_sizes, $metadata );

	$data = array(
		'metadata'          => $metadata,
		'content'           => $content,
		'registered_sizes'  => $registered_sizes,
		'scanned_at'        => time(),
	);

	$ttl = (int) apply_filters( 'surbma_wp_control_media_cleaner_cache_ttl', HOUR_IN_SECONDS );

	if ( $ttl > 0 ) {
		set_transient( $transient_key, $data, $ttl );
	}

	return $data;
}

/**
 * Whether cached media cleaner data has the fields required by the current UI.
 *
 * @param array<string, mixed> $cached Cached scan payload.
 * @return bool
 */
function surbma_wp_control_is_media_cleaner_cache_valid( $cached ) {
	return isset( $cached['metadata']['counts'] )
		&& is_array( $cached['metadata']['counts'] )
		&& isset( $cached['metadata']['size_dimensions'] )
		&& is_array( $cached['metadata']['size_dimensions'] );
}

function surbma_wp_control_get_media_cleaner_usage_data_for_blog( $blog_id, $force_refresh = false ) {
	$blog_id = (int) $blog_id;
	$context = surbma_wp_control_begin_blog_image_sizes_context( $blog_id );

	$usage = surbma_wp_control_get_media_cleaner_usage_data( $force_refresh, $blog_id );

	$usage['blog_id']      = $blog_id;
	$usage['site_name']    = get_bloginfo( 'name' );
	$usage['site_url']     = get_home_url( $blog_id, '/' );
	$usage['is_main_site'] = is_main_site( $blog_id );

	surbma_wp_control_end_blog_image_sizes_context( $context );

	return $usage;
}

/**
 * Delete media cleaner scan cache for the current site.
 */
function surbma_wp_control_delete_media_cleaner_cache() {
	delete_transient( surbma_wp_control_get_media_cleaner_transient_key() );
}
