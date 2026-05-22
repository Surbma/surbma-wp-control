<?php

defined( 'ABSPATH' ) || die;

/**
 * Base admin URL for the External link checker page.
 *
 * @return string
 */
function surbma_wp_control_get_external_link_checker_page_url() {
	return admin_url( 'admin.php?page=surbma-wp-control-external-links' );
}

/**
 * Return the saved domain whitelist as an array of normalised host strings.
 * Accepts one domain (or URL) per line; strips scheme and trailing slash.
 *
 * @return string[]
 */
function surbma_wp_control_get_link_checker_whitelist() {
	$raw   = get_option( 'surbma_wp_control_link_checker_whitelist', '' );
	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	$hosts = array();
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		// Strip scheme so users can paste full URLs.
		$line = preg_replace( '#^https?://#i', '', $line );
		// Strip trailing slash.
		$line = rtrim( $line, '/' );
		if ( '' !== $line ) {
			$hosts[] = strtolower( $line );
		}
	}
	return $hosts;
}

/**
 * Return whether the "Exclude these links" checkbox is enabled.
 * Defaults to true (checked) even before the user has ever saved the form.
 *
 * @return bool
 */
function surbma_wp_control_link_checker_exclude_enabled() {
	return '1' === get_option( 'surbma_wp_control_link_checker_exclude_enabled', '1' );
}

/**
 * Check whether a URL's host matches any entry in the whitelist.
 * Supports exact match and wildcard (*) matching.
 *
 * If an entry contains *, fnmatch() is used so that:
 *   - *.example.com  matches any subdomain of example.com (not example.com itself)
 *   - *-sub.example.com matches any host ending with -sub.example.com
 * If an entry has no *, only an exact match is accepted.
 *
 * @param string   $url       The URL to test.
 * @param string[] $whitelist Normalised host list from surbma_wp_control_get_link_checker_whitelist().
 * @return bool
 */
function surbma_wp_control_url_is_whitelisted( $url, $whitelist ) {
	if ( empty( $whitelist ) ) {
		return false;
	}
	$host = strtolower( wp_parse_url( $url, PHP_URL_HOST ) );
	if ( ! $host ) {
		return false;
	}
	foreach ( $whitelist as $entry ) {
		if ( strpos( $entry, '*' ) !== false ) {
			// Wildcard entry: use fnmatch for glob-style matching.
			if ( fnmatch( $entry, $host ) ) {
				return true;
			}
		} else {
			// No wildcard: exact match only.
			if ( $host === $entry ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Handle saving whitelist settings.
 */
function surbma_wp_control_save_whitelist_settings() {
	if ( ! isset( $_POST['surbma_wp_control_save_whitelist_nonce'] ) ) {
		return;
	}
	check_admin_referer( 'surbma_wp_control_save_whitelist', 'surbma_wp_control_save_whitelist_nonce' );

	if ( ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do this.', 'surbma-wp-control' ) );
	}

	$whitelist = isset( $_POST['surbma_wp_control_link_checker_whitelist'] )
		? sanitize_textarea_field( wp_unslash( $_POST['surbma_wp_control_link_checker_whitelist'] ) )
		: '';
	update_option( 'surbma_wp_control_link_checker_whitelist', $whitelist );

	$exclude = isset( $_POST['surbma_wp_control_link_checker_exclude_enabled'] ) ? '1' : '0';
	update_option( 'surbma_wp_control_link_checker_exclude_enabled', $exclude );

	wp_safe_redirect(
		add_query_arg( 'swpc_settings_saved', '1', surbma_wp_control_get_external_link_checker_page_url() )
	);
	exit;
}
add_action( 'admin_post_surbma_wp_control_save_whitelist', 'surbma_wp_control_save_whitelist_settings' );

/**
 * Collect all published posts/pages across public post types and return
 * an array of items with external links found in post_content.
 * Whitelisted domains are excluded when the "Exclude these links" option is on.
 *
 * @return array<int, array{post: WP_Post, links: string[]}> Posts that have at least one external link.
 */
function surbma_wp_control_get_external_links_data() {
	global $wpdb;

	$post_types = get_post_types( array( 'public' => true ) );

	// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.QuotedDynamicPlaceholderGeneration
	$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$query = $wpdb->prepare(
		"SELECT ID, post_title, post_type FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ({$placeholders})",
		array_values( $post_types )
	);

	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$posts = $wpdb->get_results( $query );

	$results   = array();
	$home      = home_url();
	$exclude   = surbma_wp_control_link_checker_exclude_enabled();
	$whitelist = $exclude ? surbma_wp_control_get_link_checker_whitelist() : array();

	if ( empty( $posts ) ) {
		return $results;
	}

	foreach ( $posts as $row ) {
		$content = get_post_field( 'post_content', $row->ID );
		preg_match_all( '/<a\s+[^>]*href=["\']([^"\']+)["\']/', $content, $matches );

		$external_links = array_values(
			array_filter(
				$matches[1],
				function ( $url ) use ( $home, $exclude, $whitelist ) {
					if ( strpos( $url, $home ) !== false ) {
						return false;
					}
					if ( ! preg_match( '#^https?://#i', $url ) ) {
						return false;
					}
					if ( $exclude && surbma_wp_control_url_is_whitelisted( $url, $whitelist ) ) {
						return false;
					}
					return true;
				}
			)
		);

		if ( count( $external_links ) > 0 ) {
			$results[] = array(
				'post'  => get_post( $row->ID ),
				'links' => $external_links,
			);
		}
	}

	return $results;
}

/**
 * Render the summary line above the results table.
 *
 * @param int $total_posts Number of posts with external links.
 * @param int $total_links Total number of external links found.
 */
function surbma_wp_control_render_external_link_checker_summary( $total_posts, $total_links ) {
	?>
	<p class="description">
		<?php
		echo esc_html(
			sprintf(
				/* translators: 1: number of posts with external links, 2: total external links found */
				__( 'Posts with external links: %1$d. Total external links found: %2$d.', 'surbma-wp-control' ),
				$total_posts,
				$total_links
			)
		);
		?>
	</p>
	<?php
}

/**
 * Render the results table listing posts and their external link counts.
 *
 * @param array<int, array{post: WP_Post, links: string[]}> $items Scan results.
 */
function surbma_wp_control_render_external_link_checker_table( $items ) {
	if ( empty( $items ) ) {
		?>
		<p><?php esc_html_e( 'No posts with external links found.', 'surbma-wp-control' ); ?></p>
		<?php
		return;
	}

	$total_posts = count( $items );
	$total_links = 0;
	foreach ( $items as $item ) {
		$total_links += count( $item['links'] );
	}

	surbma_wp_control_render_external_link_checker_summary( $total_posts, $total_links );
	?>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Title', 'surbma-wp-control' ); ?></th>
				<th scope="col" class="column-type" style="text-align:center;"><?php esc_html_e( 'Type', 'surbma-wp-control' ); ?></th>
				<th scope="col" class="column-links" style="text-align:center;"><?php esc_html_e( 'External links', 'surbma-wp-control' ); ?></th>
				<th scope="col" class="column-actions"><?php esc_html_e( 'Actions', 'surbma-wp-control' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $items as $index => $item ) : ?>
				<?php
				$post       = $item['post'];
				$link_count = count( $item['links'] );
				$links_json = wp_json_encode( $item['links'] );
				?>
				<tr>
					<td>
						<strong><?php echo esc_html( get_the_title( $post->ID ) ); ?></strong>
					</td>
					<td class="column-type" style="text-align:center;"><?php echo esc_html( $post->post_type ); ?></td>
					<td class="column-links" style="text-align:center;"><?php echo esc_html( number_format_i18n( $link_count ) ); ?></td>
					<td class="column-actions">
						<a
							href="#"
							class="swpc-open-links-modal"
							data-links="<?php echo esc_attr( $links_json ); ?>"
							data-title="<?php echo esc_attr( get_the_title( $post->ID ) ); ?>"
						>
							<?php esc_html_e( 'Show links', 'surbma-wp-control' ); ?>
						</a>
						<?php echo ' | '; ?>
						<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'View', 'surbma-wp-control' ); ?>
						</a>
						<?php echo ' | '; ?>
						<a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Edit', 'surbma-wp-control' ); ?>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<!-- Links popup modal (single shared instance) -->
	<div id="swpc-links-modal-overlay" class="swpc-modal-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="swpc-modal-title" aria-hidden="true">
		<div class="swpc-modal-container">
			<div class="swpc-modal-header">
				<h3 id="swpc-modal-title" class="swpc-modal-title"></h3>
				<button type="button" class="swpc-modal-close" aria-label="<?php esc_attr_e( 'Close', 'surbma-wp-control' ); ?>">&times;</button>
			</div>
			<div class="swpc-modal-body">
				<table class="wp-list-table widefat fixed striped swpc-modal-links-table">
					<thead>
						<tr>
							<th scope="col">#</th>
							<th scope="col"><?php esc_html_e( 'URL', 'surbma-wp-control' ); ?></th>
						</tr>
					</thead>
					<tbody id="swpc-modal-links-tbody"></tbody>
				</table>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Enqueue inline CSS and JS for the External link checker popup -- only on its admin page.
 *
 * @param string $hook_suffix The current admin page hook suffix.
 */
function surbma_wp_control_external_link_checker_assets( $hook_suffix ) {
	if ( false === strpos( $hook_suffix, 'surbma-wp-control-external-links' ) ) {
		return;
	}

	$css = '
/* External link checker - modal */
.swpc-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:100000;display:flex;align-items:center;justify-content:center}
.swpc-modal-container{background:#fff;border-radius:4px;box-shadow:0 4px 32px rgba(0,0,0,.3);width:min(760px,94vw);max-height:80vh;display:flex;flex-direction:column;overflow:hidden}
.swpc-modal-header{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #dcdcde;background:#f6f7f7}
.swpc-modal-title{margin:0;font-size:1rem;font-weight:600;color:#1d2327}
.swpc-modal-close{background:none;border:none;font-size:1.5rem;line-height:1;cursor:pointer;color:#50575e;padding:0 4px}
.swpc-modal-close:hover{color:#1d2327}
.swpc-modal-body{overflow-y:auto;padding:16px}
.swpc-modal-links-table td a{word-break:break-all}
.swpc-open-links-modal{color:#2271b1;cursor:pointer;text-decoration:underline}
.swpc-open-links-modal:hover{color:#135e96}
';

	$js = '
(function(){
	var overlay=document.getElementById("swpc-links-modal-overlay");
	if(!overlay)return;
	var titleEl=document.getElementById("swpc-modal-title");
	var tbody=document.getElementById("swpc-modal-links-tbody");
	var closeBtn=overlay.querySelector(".swpc-modal-close");

	function openModal(links,postTitle){
		titleEl.textContent=postTitle;
		tbody.innerHTML="";
		links.forEach(function(url,i){
			var tr=document.createElement("tr");
			var tdNum=document.createElement("td");tdNum.textContent=i+1;
			var tdUrl=document.createElement("td");
			var a=document.createElement("a");
			a.href=url;a.textContent=url;a.target="_blank";a.rel="noopener noreferrer";
			tdUrl.appendChild(a);tr.appendChild(tdNum);tr.appendChild(tdUrl);tbody.appendChild(tr);
		});
		overlay.style.display="flex";overlay.setAttribute("aria-hidden","false");closeBtn.focus();
	}
	function closeModal(){overlay.style.display="none";overlay.setAttribute("aria-hidden","true");}

	document.addEventListener("click",function(e){
		var link=e.target.closest(".swpc-open-links-modal");
		if(!link)return;
		e.preventDefault();
		var links=JSON.parse(link.dataset.links||"[]");
		openModal(links,link.dataset.title||"");
	});
	closeBtn.addEventListener("click",closeModal);
	overlay.addEventListener("click",function(e){if(e.target===overlay)closeModal();});
	document.addEventListener("keydown",function(e){if(e.key==="Escape"&&overlay.style.display!=="none")closeModal();});
}());
';

	wp_register_style( 'swpc-external-links', false, array(), '1' );
	wp_enqueue_style( 'swpc-external-links' );
	wp_add_inline_style( 'swpc-external-links', $css );

	wp_register_script( 'swpc-external-links', false, array(), '1', true );
	wp_enqueue_script( 'swpc-external-links' );
	wp_add_inline_script( 'swpc-external-links', $js );
}
add_action( 'admin_enqueue_scripts', 'surbma_wp_control_external_link_checker_assets' );

/**
 * Render the External link checker page.
 */
function surbma_wp_control_render_external_link_checker() {
	$items           = surbma_wp_control_get_external_links_data();
	$whitelist_raw   = get_option( 'surbma_wp_control_link_checker_whitelist', '' );
	$exclude_enabled = surbma_wp_control_link_checker_exclude_enabled();
	$saved           = isset( $_GET['swpc_settings_saved'] ) && '1' === $_GET['swpc_settings_saved']; // phpcs:ignore WordPress.Security.NonceVerification
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'External link checker', 'surbma-wp-control' ); ?></h1>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Settings saved.', 'surbma-wp-control' ); ?></p>
			</div>
		<?php endif; ?>

		<div class="card" style="max-width: none;">
			<h2 class="title"><?php esc_html_e( 'External link checker', 'surbma-wp-control' ); ?></h2>
			<p><?php esc_html_e( 'This tool detects suspicious or unintentional outbound links in your published content. It scans all published posts and pages for external links — links pointing to domains outside your site. Use the whitelist below to exclude trusted domains (e.g. your own affiliate partners, CDN providers, or social profiles) so only truly unexpected links are flagged.', 'surbma-wp-control' ); ?></p>
		</div>

		<div class="card" style="max-width: none; margin-top: 1.5em;">
			<h2 class="title"><?php esc_html_e( 'Whitelist settings', 'surbma-wp-control' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="surbma_wp_control_save_whitelist">
				<?php wp_nonce_field( 'surbma_wp_control_save_whitelist', 'surbma_wp_control_save_whitelist_nonce' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="surbma_wp_control_link_checker_whitelist">
								<?php esc_html_e( 'Whitelisted domains', 'surbma-wp-control' ); ?>
							</label>
						</th>
						<td>
							<textarea
								id="surbma_wp_control_link_checker_whitelist"
								name="surbma_wp_control_link_checker_whitelist"
								rows="6"
								cols="50"
								class="large-text code"
							><?php echo esc_textarea( $whitelist_raw ); ?></textarea>
							<p class="description">
								<?php esc_html_e( 'Add one domain per line. Use * as a wildcard (e.g. *.example.com, *-sub.example.com). Plain domains match exactly. You may also paste full URLs — the scheme will be stripped.', 'surbma-wp-control' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Exclude these links', 'surbma-wp-control' ); ?></th>
						<td>
							<label>
								<input
									type="checkbox"
									name="surbma_wp_control_link_checker_exclude_enabled"
									value="1"
									<?php checked( $exclude_enabled ); ?>
								>
								<?php esc_html_e( 'Exclude links to whitelisted domains from the results', 'surbma-wp-control' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'When checked, links pointing to any domain in the whitelist above will not appear in the results table.', 'surbma-wp-control' ); ?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save settings', 'surbma-wp-control' ) ); ?>
			</form>
		</div>

		<div class="card" style="max-width: none; margin-top: 1.5em;">
			<h2 class="title"><?php esc_html_e( 'Results', 'surbma-wp-control' ); ?></h2>
			<?php surbma_wp_control_render_external_link_checker_table( $items ); ?>
		</div>
	</div>
	<?php
}
