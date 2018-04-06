<?php
/**
 * Initialize metabox on proper backend screens
 */
function auhfc_init_meta_boxes() {
	add_action( 'add_meta_boxes', 'auhfc_add_meta_boxes' );
	add_action( 'save_post', 'auhfc_save' );
}
if ( is_admin() ) {
	add_action( 'load-post.php',     'auhfc_init_meta_boxes' );
	add_action( 'load-post-new.php', 'auhfc_init_meta_boxes' );
}

/**
 * This function adds a meta box with a callback function of my_metabox_callback()
 */
function auhfc_add_meta_boxes() {

	$auhfc_defaults = auhfc_defaults();

	if ( empty( $auhfc_defaults['post_types'] ) ) {
		return;
	}
	foreach ( $auhfc_defaults['post_types'] as $post_type ) {
		add_meta_box(
			'auhfc-head-footer-code',
			__( 'Head & Footer Code', 'head-footer-code' ),
			'auhfc_display_html',
			$post_type,
			'normal',
			'low'
		);
	}

} // END function auhfc_add_meta_boxes()

/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function auhfc_display_html( $post ) {
	wp_nonce_field( '_head_footer_code_nonce', 'head_footer_code_nonce' ); ?>

	<p>Here you can insert article specific code for Head (before the <code>&lt;/head&gt;</code>) and Footer (before the <code>&lt;/body&gt;</code>) sections. They work in exactly the same way as site-wide code, which you can configure under <a href="tools.php?page=head-footer-code">Tools / Head &amp; Footer Code</a>.</p>

	<table class="form-table">
	<tbody>
	<tr class="widefat">
		<th scope="row">
			<label><?php esc_attr_e( 'Behavior', 'head-footer-code' ); ?></label>
		</th>
		<td>
			<input type="radio" name="auhfc[behavior]" id="auhfc_behavior_append" value="append" <?php echo ( 'append' === auhfc_get_meta( 'behavior' ) ) ? 'checked' : ''; ?>>
			<label for="auhfc_behavior_append">Append to the site-wide code</label><br />
			<input type="radio" name="auhfc[behavior]" id="auhfc_behavior_replace" value="replace" <?php echo ( 'replace' === auhfc_get_meta( 'behavior' ) ) ? 'checked' : ''; ?>>
			<label for="auhfc_behavior_replace">Replace the site-wide code <?php
				if ( is_multisite() ) { ?>
					<em>- you can't remove network-wide code set by network admin</em>
				<?php }
			?></label>
		</td>
	</tr>

	<tr class="widefat">
		<th scope="row">
			<label for="auhfc_head"><?php _e( 'Head Code', 'head-footer-code' ); ?></label>
		</th>
		<td>
			<textarea name="auhfc[head]" id="auhfc_head" class="widefat code" rows="5"><?php echo auhfc_get_meta( 'head' ); ?></textarea>
			<p>Example: <code>&lt;link rel="stylesheet" href="https://domain.com/path/to/style.css" type="text/css" media="all"&gt;</code></p>
		</td>
	</tr>
	<tr class="widefat">
		<th scope="row">
			<label for="auhfc_footer"><?php _e( 'Footer Code', 'head-footer-code' ); ?></label>
		</th>
		<td>
			<textarea name="auhfc[footer]" id="auhfc_footer" class="widefat code" rows="5"><?php echo auhfc_get_meta( 'footer' ); ?></textarea>
			<p>Example: <code>&lt;script type="text/javascript" src="http://domain.com/path/to/script.js"&gt;&lt;/script&gt;</code></p>
		</td>
	</tr>
	</tbody>
	</table>
	<?php
}

/**
 * Save meta box content.
 *
 * @param int $post_id Post ID
 */
function auhfc_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['head_footer_code_nonce'] ) || ! wp_verify_nonce( $_POST['head_footer_code_nonce'], '_head_footer_code_nonce' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( ! empty( $_POST['auhfc'] ) ) {

		$auhfc['head']     = ( ! empty( $_POST['auhfc']['head'] ) ) ? $_POST['auhfc']['head'] : '';
		$auhfc['footer']   = ( ! empty( $_POST['auhfc']['footer'] ) ) ? $_POST['auhfc']['footer'] : '';
		$auhfc['behavior'] = ( ! empty( $_POST['auhfc']['behavior'] ) ) ? $_POST['auhfc']['behavior'] : '';

		if ( ! empty( $auhfc ) ) {
			update_post_meta( $post_id, '_auhfc', $auhfc );
		}
	}

} // END function auhfc_save( $post_id )

/*
	Usage: auhfc_get_meta( 'head' )
	Usage: auhfc_get_meta( 'footer' )
	Usage: auhfc_get_meta( 'behavior' )
*/
