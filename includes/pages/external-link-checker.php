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
 * Collect all published posts/pages across public post types and return
 * an array of items with external links found in post_content.
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

	$results = array();
	$home    = home_url();

	if ( empty( $posts ) ) {
		return $results;
	}

	foreach ( $posts as $row ) {
		$content = get_post_field( 'post_content', $row->ID );
		preg_match_all( '/<a\s+[^>]*href=["\']([^"\']+)["\']/', $content, $matches );

		$external_links = array_values(
			array_filter(
				$matches[1],
				function ( $url ) use ( $home ) {
					return strpos( $url, $home ) === false
						&& preg_match( '#^https?://#i', $url );
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
				<th scope="col" class="column-type"><?php esc_html_e( 'Type', 'surbma-wp-control' ); ?></th>
				<th scope="col" class="column-links"><?php esc_html_e( 'External links', 'surbma-wp-control' ); ?></th>
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
						<strong>
							<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( get_the_title( $post->ID ) ); ?>
							</a>
						</strong>
					</td>
					<td class="column-type"><?php echo esc_html( $post->post_type ); ?></td>
					<td class="column-links">
						<button
							type="button"
							class="button-link swpc-open-links-modal"
							data-links="<?php echo esc_attr( $links_json ); ?>"
							data-title="<?php echo esc_attr( get_the_title( $post->ID ) ); ?>"
						>
							<?php echo esc_html( number_format_i18n( $link_count ) ); ?>
						</button>
					</td>
					<td class="column-actions">
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
 * Enqueue inline CSS and JS for the External link checker popup — only on its admin page.
 *
 * @param string $hook_suffix The current admin page hook suffix.
 */
function surbma_wp_control_external_link_checker_assets( $hook_suffix ) {
	if ( false === strpos( $hook_suffix, 'surbma-wp-control-external-links' ) ) {
		return;
	}

	$css = '
/* External link checker – modal */
.swpc-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:100000;display:flex;align-items:center;justify-content:center}
.swpc-modal-container{background:#fff;border-radius:4px;box-shadow:0 4px 32px rgba(0,0,0,.3);width:min(760px,94vw);max-height:80vh;display:flex;flex-direction:column;overflow:hidden}
.swpc-modal-header{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #dcdcde;background:#f6f7f7}
.swpc-modal-title{margin:0;font-size:1rem;font-weight:600;color:#1d2327}
.swpc-modal-close{background:none;border:none;font-size:1.5rem;line-height:1;cursor:pointer;color:#50575e;padding:0 4px}
.swpc-modal-close:hover{color:#1d2327}
.swpc-modal-body{overflow-y:auto;padding:16px}
.swpc-modal-links-table td a{word-break:break-all}
.swpc-open-links-modal{color:#2271b1;cursor:pointer;text-decoration:underline;font-weight:600;background:none;border:none;padding:0;font-size:inherit}
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
		var btn=e.target.closest(".swpc-open-links-modal");
		if(!btn)return;
		var links=JSON.parse(btn.dataset.links||"[]");
		openModal(links,btn.dataset.title||"");
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
	$checked = false;
	$items   = array();

	if ( isset( $_POST['surbma_wp_control_check_links'] ) ) {
		check_admin_referer( 'surbma_wp_control_check_links' );
		$checked = true;
		$items   = surbma_wp_control_get_external_links_data();
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'External link checker', 'surbma-wp-control' ); ?></h1>

		<div class="card" style="max-width: none;">
			<h2 class="title"><?php esc_html_e( 'External link checker', 'surbma-wp-control' ); ?></h2>
			<p><?php esc_html_e( 'Scan all published posts and pages for outbound external links.', 'surbma-wp-control' ); ?></p>

			<form method="post" action="<?php echo esc_url( surbma_wp_control_get_external_link_checker_page_url() ); ?>">
				<?php wp_nonce_field( 'surbma_wp_control_check_links' ); ?>
				<input
					type="submit"
					name="surbma_wp_control_check_links"
					class="button button-primary"
					value="<?php esc_attr_e( 'Check posts', 'surbma-wp-control' ); ?>"
				>
			</form>
		</div>

		<?php if ( $checked ) : ?>
			<div class="card" style="max-width: none; margin-top: 1.5em;">
				<h2 class="title"><?php esc_html_e( 'Results', 'surbma-wp-control' ); ?></h2>
				<?php surbma_wp_control_render_external_link_checker_table( $items ); ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}
