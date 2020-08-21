<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

abstract class AUHfc_Meta_Box {

	/**
	 * This function adds a meta box with a callback function of my_metabox_callback()
	 */
	public static function add() {

		$auhfc_settings = auhfc_settings();

		if ( empty( $auhfc_settings['article']['post_types'] ) ) {
			return;
		}
		foreach ( $auhfc_settings['article']['post_types'] as $post_type ) {
			add_meta_box(
				'auhfc-head-footer-code',
				esc_html__( 'Head & Footer Code', 'head-footer-code' ),
				[ self::class, 'html' ],
				$post_type,
				'normal',
				'low'
			);
		}

	} // END public static function add()

	/**
	 * Save meta box content.
	 *
	 * @param int $post_id Post ID
	 */
	public static function save( $post_id ) {
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
			$auhfc['body']     = ( ! empty( $_POST['auhfc']['body'] ) ) ? $_POST['auhfc']['body'] : '';
			$auhfc['footer']   = ( ! empty( $_POST['auhfc']['footer'] ) ) ? $_POST['auhfc']['footer'] : '';
			$auhfc['behavior'] = ( ! empty( $_POST['auhfc']['behavior'] ) ) ? $_POST['auhfc']['behavior'] : '';

			if ( ! empty( $auhfc ) ) {
				update_post_meta( $post_id, '_auhfc', wp_slash( $auhfc ) );
			}
		}

	} // END fpublic static function save( $post_id )

	/**
	 * Meta box display callback.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public static function html( $post ) {
		wp_nonce_field( '_head_footer_code_nonce', 'head_footer_code_nonce' ); ?>
		<p><?php
		printf(
			/* translators: %1$s will be replaced with preformatted HTML tag </head>
			%2$s will be replaced with preformatted HTML tag <body>
			%3$s will be replaced with preformatted HTML tag </body>
			%4$s will be replaced with a link to Head & Footer Code Settings page */
			esc_html__( 'Here you can insert article specific code for Head (before the %1$s), Body (after the %2$s) and Footer (before the %3$s) sections. They work in exactly the same way as site-wide code, which you can configure under %4$s.', 'head-footer-code'),
			'<code>&lt;/head&gt;</code>',
			'<code>&lt;body&gt;</code>',
			'<code>&lt;/body&gt;</code>',
			sprintf( '<a href="tools.php?page=head_footer_code">%s</a>', esc_html__( 'Tools / Head &amp; Footer Code', 'head-footer-code' ) 
			)
		);
		?></p>
		<label><?php esc_html_e( 'Behavior', 'head-footer-code' ); ?></label><br />
		<select name="auhfc[behavior]" id="auhfc_behavior">
			<option value="append" <?php echo ( 'append' === auhfc_get_meta( 'behavior' ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Append to the site-wide code', 'head-footer-code' ); ?></option>
			<option value="replace" <?php echo ( 'replace' === auhfc_get_meta( 'behavior' ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Replace the site-wide code', 'head-footer-code' ); ?></option>
		</select>
		<br /><br />
		<label for="auhfc_head"><?php esc_html_e( 'Head Code', 'head-footer-code' ); ?></label><br />
		<textarea name="auhfc[head]" id="auhfc_head" class="widefat code" rows="5"><?php echo auhfc_get_meta( 'head' ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Example', 'head-footer-code'); ?>: <code>&lt;link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/style.css" type="text/css" media="all"&gt;</code></p>
		<br />
		<label for="auhfc_body"><?php esc_html_e( 'Body Code', 'head-footer-code' ); ?></label><br />
		<textarea name="auhfc[body]" id="auhfc_body" class="widefat code" rows="5"><?php echo auhfc_get_meta( 'body' ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Example', 'head-footer-code'); ?>: <code>&lt;script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/body-start.js" type="text/css" media="all"&gt;&lt;/script&gt;</code></p>
		<br />
		<label for="auhfc_footer"><?php esc_html_e( 'Footer Code', 'head-footer-code' ); ?></label><br />
		<textarea name="auhfc[footer]" id="auhfc_footer" class="widefat code" rows="5"><?php echo auhfc_get_meta( 'footer' ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Example', 'head-footer-code'); ?>: <code>&lt;script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/script.js"&gt;&lt;/script&gt;</code></p>
		<?php
	} // END public static function html()

} // END class AUHfc_Meta_Box

/**
 * Initialize metabox on proper backend screens
 */
function auhfc_init_meta_boxes() {
	add_action( 'add_meta_boxes', [ 'AUHfc_Meta_Box', 'add' ] );
	add_action( 'save_post', [ 'AUHfc_Meta_Box', 'save' ] );
}
if ( is_admin() ) {
	add_action( 'load-post.php', 'auhfc_init_meta_boxes' );
	add_action( 'load-post-new.php', 'auhfc_init_meta_boxes' );
}
