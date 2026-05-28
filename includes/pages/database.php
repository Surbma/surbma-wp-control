<?php

defined( 'ABSPATH' ) || die;

/**
 * Base admin URL for the Database page.
 *
 * @param string $tab Tab slug (summary, tables, all-options, autoload-yes, autoload-no).
 * @return string
 */
function surbma_wp_control_get_database_page_url( $tab = 'summary' ) {
	return add_query_arg( 'tab', $tab, admin_url( 'admin.php?page=surbma-wp-control-database' ) );
}

/**
 * POST handler: convert all database tables to InnoDB engine.
 * Capability: update_core.
 */
function surbma_wp_control_handle_convert_engine() {
	if ( ! isset( $_POST['surbma_convert_engine'] ) ) {
		return;
	}

	check_admin_referer( 'surbma_wp_control_convert_engine' );

	if ( ! current_user_can( 'update_core' ) ) {
		wp_die( esc_html__( 'You do not have permission to perform this action.', 'surbma-wp-control' ) );
	}

	global $wpdb;

	$tables           = $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );
	$converted_tables = array();
	$failed_tables    = array();
	$skipped_tables   = array();

	if ( $tables ) {
		foreach ( $tables as $table ) {
			$table_name     = $table['Name'];
			$current_engine = $table['Engine'];

			if ( 'InnoDB' === $current_engine ) {
				$skipped_tables[] = $table_name;
				continue;
			}

			if ( 'MyISAM' !== $current_engine ) {
				$skipped_tables[] = $table_name;
				continue;
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$result = $wpdb->query( "ALTER TABLE `{$table_name}` ENGINE=InnoDB" );

			if ( false !== $result ) {
				$converted_tables[] = $table_name;
			} else {
				$failed_tables[] = $table_name;
			}
		}
	}

	$redirect_url = add_query_arg(
		array(
			'page'          => 'surbma-wp-control-database',
			'tab'           => 'tables',
			'engine_result' => 'success',
			'converted'     => count( $converted_tables ),
			'skipped'       => count( $skipped_tables ),
			'failed'        => count( $failed_tables ),
		),
		admin_url( 'admin.php' )
	);

	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'admin_post_surbma_convert_engine', 'surbma_wp_control_handle_convert_engine' );

/**
 * POST handler: convert all database tables to utf8mb4_unicode_ci collation.
 * Capability: update_core.
 */
function surbma_wp_control_handle_convert_collation() {
	if ( ! isset( $_POST['surbma_convert_collation'] ) ) {
		return;
	}

	check_admin_referer( 'surbma_wp_control_convert_collation' );

	if ( ! current_user_can( 'update_core' ) ) {
		wp_die( esc_html__( 'You do not have permission to perform this action.', 'surbma-wp-control' ) );
	}

	global $wpdb;

	$tables           = $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );
	$converted_tables = array();
	$failed_tables    = array();
	$skipped_tables   = array();

	if ( $tables ) {
		foreach ( $tables as $table ) {
			$table_name        = $table['Name'];
			$current_collation = $table['Collation'];

			if ( 'utf8mb4_unicode_ci' === $current_collation ) {
				$skipped_tables[] = $table_name;
				continue;
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$result = $wpdb->query( "ALTER TABLE `{$table_name}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" );

			if ( false !== $result ) {
				$converted_tables[] = $table_name;
			} else {
				$failed_tables[] = $table_name;
			}
		}
	}

	$redirect_url = add_query_arg(
		array(
			'page'             => 'surbma-wp-control-database',
			'tab'              => 'tables',
			'collation_result' => 'success',
			'converted'        => count( $converted_tables ),
			'skipped'          => count( $skipped_tables ),
			'failed'           => count( $failed_tables ),
		),
		admin_url( 'admin.php' )
	);

	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'admin_post_surbma_convert_collation', 'surbma_wp_control_handle_convert_collation' );

/**
 * Render the Database admin page with WP-native tabs (separate URL per tab).
 */
function surbma_wp_control_render_database() {
	global $wpdb;

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'summary';

	$tabs = array(
		'summary'      => __( 'Database Summary', 'surbma-wp-control' ),
		'tables'       => __( 'Database Tables', 'surbma-wp-control' ),
		'all-options'  => __( 'All Options', 'surbma-wp-control' ),
		'autoload-yes' => __( 'autoload = yes', 'surbma-wp-control' ),
		'autoload-no'  => __( 'autoload = no', 'surbma-wp-control' ),
	);

	if ( ! array_key_exists( $current_tab, $tabs ) ) {
		$current_tab = 'summary';
	}

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Database', 'surbma-wp-control' ); ?></h1>

		<?php
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['engine_result'] ) && 'success' === $_GET['engine_result'] ) {
			$converted    = isset( $_GET['converted'] ) ? (int) $_GET['converted'] : 0;
			$skipped      = isset( $_GET['skipped'] ) ? (int) $_GET['skipped'] : 0;
			$failed       = isset( $_GET['failed'] ) ? (int) $_GET['failed'] : 0;
			$notice_class = $failed > 0 ? 'notice-warning' : 'notice-success';
			printf(
				'<div class="notice %s is-dismissible"><p>%s</p></div>',
				esc_attr( $notice_class ),
				esc_html(
					sprintf(
						/* translators: 1: converted, 2: skipped, 3: failed */
						__( 'InnoDB conversion complete. Converted: %1$d, Skipped (already InnoDB): %2$d, Failed: %3$d.', 'surbma-wp-control' ),
						$converted,
						$skipped,
						$failed
					)
				)
			);
		}

		if ( isset( $_GET['collation_result'] ) && 'success' === $_GET['collation_result'] ) {
			$converted    = isset( $_GET['converted'] ) ? (int) $_GET['converted'] : 0;
			$skipped      = isset( $_GET['skipped'] ) ? (int) $_GET['skipped'] : 0;
			$failed       = isset( $_GET['failed'] ) ? (int) $_GET['failed'] : 0;
			$notice_class = $failed > 0 ? 'notice-warning' : 'notice-success';
			printf(
				'<div class="notice %s is-dismissible"><p>%s</p></div>',
				esc_attr( $notice_class ),
				esc_html(
					sprintf(
						/* translators: 1: converted, 2: skipped, 3: failed */
						__( 'Collation conversion complete. Converted: %1$d, Skipped (already utf8mb4_unicode_ci): %2$d, Failed: %3$d.', 'surbma-wp-control' ),
						$converted,
						$skipped,
						$failed
					)
				)
			);
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		?>

		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $tab_slug => $tab_label ) : ?>
				<a href="<?php echo esc_url( surbma_wp_control_get_database_page_url( $tab_slug ) ); ?>"
				   class="nav-tab<?php echo $current_tab === $tab_slug ? ' nav-tab-active' : ''; ?>">
					<?php echo esc_html( $tab_label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="tab-content" style="margin-top: 1.5em;">
			<?php
			switch ( $current_tab ) {
				case 'summary':
					surbma_wp_control_render_database_tab_summary( $wpdb );
					break;
				case 'tables':
					surbma_wp_control_render_database_tab_tables( $wpdb );
					break;
				case 'all-options':
					surbma_wp_control_render_database_tab_options( $wpdb, 'all' );
					break;
				case 'autoload-yes':
					surbma_wp_control_render_database_tab_options( $wpdb, 'autoload-yes' );
					break;
				case 'autoload-no':
					surbma_wp_control_render_database_tab_options( $wpdb, 'autoload-no' );
					break;
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * Render the Database Summary tab.
 *
 * @param wpdb $wpdb WordPress database object.
 */
function surbma_wp_control_render_database_tab_summary( $wpdb ) {
	$options_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options}" );

	$autoload_yes_size = $wpdb->get_var(
		"SELECT SUM(LENGTH(option_value))
		FROM {$wpdb->options}
		WHERE autoload IN ('yes', 'on', 'auto-on', 'auto')"
	);

	$autoload_no_size = $wpdb->get_var(
		"SELECT SUM(LENGTH(option_value))
		FROM {$wpdb->options}
		WHERE autoload NOT IN ('yes', 'on', 'auto-on', 'auto')"
	);

	$total_options_size = $wpdb->get_var(
		"SELECT SUM(LENGTH(option_value))
		FROM {$wpdb->options}"
	);

	$all_tables          = $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );
	$total_tables_count  = count( $all_tables );
	$total_table_size    = 0;
	$total_database_size = 0;

	foreach ( $all_tables as $table ) {
		$table_size           = $table['Data_length'] + $table['Index_length'];
		$total_database_size += $table_size;

		if ( $table['Name'] === $wpdb->options ) {
			$total_table_size = $table_size;
		}
	}

	$autoload_bytes = $autoload_yes_size ? (int) $autoload_yes_size : 0;

	if ( $autoload_bytes < 1024 * 1024 ) {
		$autoload_status_class = 'notice-success';
		$autoload_status_label = __( 'Optimal: < 1 MB', 'surbma-wp-control' );
	} elseif ( $autoload_bytes < 3 * 1024 * 1024 ) {
		$autoload_status_class = 'notice-warning';
		$autoload_status_label = __( 'Warning: 1 MB – 3 MB', 'surbma-wp-control' );
	} else {
		$autoload_status_class = 'notice-error';
		$autoload_status_label = __( 'Danger: > 3 MB', 'surbma-wp-control' );
	}
	?>
	<div class="notice <?php echo esc_attr( $autoload_status_class ); ?> inline" style="margin: 0 0 1em; padding: 6px 12px;">
		<p>
			<?php esc_html_e( 'Autoload options size status:', 'surbma-wp-control' ); ?>
			<strong><?php echo esc_html( $autoload_status_label ); ?></strong>
		</p>
	</div>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Description', 'surbma-wp-control' ); ?></th>
				<th scope="col" style="text-align: right;"><?php esc_html_e( 'Value', 'surbma-wp-control' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php esc_html_e( 'Number of tables in database', 'surbma-wp-control' ); ?></td>
				<td style="text-align: right;"><?php echo number_format( $total_tables_count ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Total size of database', 'surbma-wp-control' ); ?></td>
				<td style="text-align: right;"><?php echo esc_html( size_format( $total_database_size, 2 ) ); ?></td>
			</tr>
			<tr>
				<td>
					<?php
					printf(
						/* translators: %s: table name in code tag */
						esc_html__( 'Number of rows in %s table', 'surbma-wp-control' ),
						'<code>options</code>'
					);
					?>
				</td>
				<td style="text-align: right;"><?php echo number_format( $options_count ); ?></td>
			</tr>
			<tr>
				<td>
					<?php
					printf(
						/* translators: %s: table name in code tag */
						esc_html__( 'Total size of %s with autoload = yes', 'surbma-wp-control' ),
						'<code>options</code>'
					);
					?>
				</td>
				<td style="text-align: right;"><?php echo esc_html( size_format( $autoload_yes_size, 2 ) ); ?></td>
			</tr>
			<tr>
				<td>
					<?php
					printf(
						/* translators: %s: table name in code tag */
						esc_html__( 'Total size of %s with autoload = no', 'surbma-wp-control' ),
						'<code>options</code>'
					);
					?>
				</td>
				<td style="text-align: right;"><?php echo esc_html( size_format( $autoload_no_size, 2 ) ); ?></td>
			</tr>
			<tr>
				<td>
					<?php
					printf(
						/* translators: %s: table name in code tag */
						esc_html__( 'Total size of %s table (data only)', 'surbma-wp-control' ),
						'<code>options</code>'
					);
					?>
				</td>
				<td style="text-align: right;"><?php echo esc_html( size_format( $total_options_size, 2 ) ); ?></td>
			</tr>
			<tr>
				<td>
					<?php
					printf(
						/* translators: %s: table name in code tag */
						esc_html__( 'Total size of %s table (data + index)', 'surbma-wp-control' ),
						'<code>options</code>'
					);
					?>
				</td>
				<td style="text-align: right;"><?php echo esc_html( size_format( $total_table_size, 2 ) ); ?></td>
			</tr>
		</tbody>
	</table>
	<?php
}

/**
 * Render the Database Tables tab with InnoDB and collation conversion buttons.
 *
 * @param wpdb $wpdb WordPress database object.
 */
function surbma_wp_control_render_database_tab_tables( $wpdb ) {
	$tables = $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );
	?>
	<div style="margin-bottom: 1em;">
		<p><?php esc_html_e( 'Type should be InnoDB | Collation should be utf8mb4_unicode_ci', 'surbma-wp-control' ); ?></p>
	</div>

	<div style="margin-bottom: 1em; display: flex; gap: 0.5em; flex-wrap: wrap;">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
		      onsubmit="return confirm('<?php esc_attr_e( 'Are you sure you want to convert all database tables to InnoDB? This operation may take several minutes for large databases.', 'surbma-wp-control' ); ?>');">
			<?php wp_nonce_field( 'surbma_wp_control_convert_engine' ); ?>
			<input type="hidden" name="action" value="surbma_convert_engine" />
			<button class="button button-primary" type="submit" name="surbma_convert_engine">
				<?php esc_html_e( 'Convert to InnoDB', 'surbma-wp-control' ); ?>
			</button>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
		      onsubmit="return confirm('<?php esc_attr_e( 'Are you sure you want to convert all database tables to utf8mb4_unicode_ci? This operation may take several minutes for large databases.', 'surbma-wp-control' ); ?>');">
			<?php wp_nonce_field( 'surbma_wp_control_convert_collation' ); ?>
			<input type="hidden" name="action" value="surbma_convert_collation" />
			<button class="button button-primary" type="submit" name="surbma_convert_collation">
				<?php esc_html_e( 'Convert to utf8mb4_unicode_ci', 'surbma-wp-control' ); ?>
			</button>
		</form>
	</div>

	<?php if ( $tables ) : ?>
		<?php
		foreach ( $tables as &$table ) {
			$table['total_size'] = $table['Data_length'] + $table['Index_length'];
		}
		unset( $table );

		usort(
			$tables,
			function ( $a, $b ) {
				return $b['total_size'] - $a['total_size'];
			}
		);

		$total_db_size = 0;
		?>
		<style>
			.surbma-wp-control-status--no {
				color: #d63638;
				font-weight: 600;
			}
		</style>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Table Name', 'surbma-wp-control' ); ?></th>
					<th scope="col" style="text-align: center;"><?php esc_html_e( 'Rows', 'surbma-wp-control' ); ?></th>
					<th scope="col" style="text-align: center;"><?php esc_html_e( 'Type', 'surbma-wp-control' ); ?></th>
					<th scope="col" style="text-align: center;"><?php esc_html_e( 'Collation', 'surbma-wp-control' ); ?></th>
					<th scope="col" style="text-align: right;"><?php esc_html_e( 'Size', 'surbma-wp-control' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $tables as $table ) : ?>
					<?php
					$table_name      = $table['Name'];
					$table_type      = $table['Engine'];
					$table_collation = $table['Collation'];
					$rows            = number_format( $table['Rows'] );
					$total_size      = $table['total_size'];
					$total_db_size  += $total_size;
					$size            = size_format( $total_size, 2 );
					?>
					<tr>
						<td><?php echo esc_html( $table_name ); ?></td>
						<td style="text-align: center;"><?php echo esc_html( $rows ); ?></td>
						<td style="text-align: center;"<?php echo ( 'InnoDB' !== $table_type ) ? ' class="surbma-wp-control-status--no"' : ''; ?>><?php echo esc_html( $table_type ); ?></td>
						<td style="text-align: center;"<?php echo ( 'utf8mb4_unicode_ci' !== $table_collation ) ? ' class="surbma-wp-control-status--no"' : ''; ?>><?php echo esc_html( $table_collation ); ?></td>
						<td style="text-align: right;"><?php echo esc_html( $size ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<th><?php esc_html_e( 'Total', 'surbma-wp-control' ); ?></th>
					<th></th>
					<th></th>
					<th></th>
					<th style="text-align: right;"><?php echo esc_html( size_format( $total_db_size, 2 ) ); ?></th>
				</tr>
			</tfoot>
		</table>
	<?php endif; ?>
	<?php
}

/**
 * Render one of the options tabs: All Options, autoload-yes, autoload-no.
 *
 * @param wpdb   $wpdb WordPress database object.
 * @param string $mode all | autoload-yes | autoload-no.
 */
function surbma_wp_control_render_database_tab_options( $wpdb, $mode ) {
	switch ( $mode ) {
		case 'autoload-yes':
			$where   = "WHERE autoload IN ('yes', 'on', 'auto-on', 'auto')";
			$heading = __( 'Options with autoload = yes', 'surbma-wp-control' );
			break;
		case 'autoload-no':
			$where   = "WHERE autoload NOT IN ('yes', 'on', 'auto-on', 'auto')";
			$heading = __( 'Options with autoload = no', 'surbma-wp-control' );
			break;
		default:
			$where   = '';
			$heading = __( 'All Options', 'surbma-wp-control' );
			break;
	}

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$options = $wpdb->get_results(
		"SELECT option_name, autoload, LENGTH(option_value) as option_size
		FROM {$wpdb->options}
		{$where}
		ORDER BY option_size DESC",
		ARRAY_A
	);

	echo '<h2>' . esc_html( $heading ) . '</h2>';

	if ( ! $options ) {
		echo '<p>' . esc_html__( 'No options found.', 'surbma-wp-control' ) . '</p>';
		return;
	}

	$total = 0;
	?>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Option Name', 'surbma-wp-control' ); ?></th>
				<th scope="col" style="text-align: center;"><?php esc_html_e( 'Autoload', 'surbma-wp-control' ); ?></th>
				<th scope="col" style="text-align: right;"><?php esc_html_e( 'Size', 'surbma-wp-control' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $options as $option ) : ?>
				<?php
				$option_name  = $option['option_name'];
				$autoload_val = $option['autoload'];
				$option_size  = (int) $option['option_size'];
				$total       += $option_size;
				$size         = size_format( $option_size, 2 );
				?>
				<tr>
					<td><?php echo esc_html( $option_name ); ?></td>
					<td style="text-align: center;"><?php echo esc_html( $autoload_val ); ?></td>
					<td style="text-align: right;"><?php echo esc_html( $size ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<th><?php esc_html_e( 'Total', 'surbma-wp-control' ); ?></th>
				<th></th>
				<th style="text-align: right;"><?php echo esc_html( size_format( $total, 2 ) ); ?></th>
			</tr>
		</tfoot>
	</table>
	<?php
}
