<?php
/**
 * Class for Head & Footer Code article metabox
 *
 * @package Head_Footer_Code
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to compose Head & Footer article metabox
 */
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
				esc_html( HFC_PLUGIN_NAME ),
				array( self::class, 'html' ),
				$post_type,
				'normal',
				'low'
			);
		}
	} // END public static function add()

	/**
	 * Save meta box content.
	 *
	 * @param int $post_id Post ID.
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

			$auhfc['head']     = ! empty( $_POST['auhfc']['head'] ) ? $_POST['auhfc']['head'] : '';
			$auhfc['body']     = ! empty( $_POST['auhfc']['body'] ) ? $_POST['auhfc']['body'] : '';
			$auhfc['footer']   = ! empty( $_POST['auhfc']['footer'] ) ? $_POST['auhfc']['footer'] : '';
			$auhfc['behavior'] = ! empty( $_POST['auhfc']['behavior'] ) ? $_POST['auhfc']['behavior'] : '';

			if ( ! empty( $auhfc ) ) {
				update_post_meta( $post_id, '_auhfc', wp_slash( $auhfc ) );
			}
		}
	} // END fpublic static function save( $post_id )

	/**
	 * Meta box display callback.
	 */
	public static function html() {
		wp_nonce_field( '_head_footer_code_nonce', 'head_footer_code_nonce' ); ?>
		<p>
		<?php
		printf(
			/* translators: 1: translated 'article specific', 2: </head>, 3: <body>, 4: </body> */
			esc_html__( 'Here you can insert %1$s code for HEAD (before the %2$s), BODY (after the %3$s) and FOOTER (before the %4$s) sections.', 'head-footer-code' ),
			__( 'article specific', 'head-footer-code' ),
			'<code>&lt;/head&gt;</code>',
			'<code>&lt;body&gt;</code>',
			'<code>&lt;/body&gt;</code>'
		);
		echo '<br>';

		// One who can manage options and modify category settings
		if ( ! current_user_can( 'manage_options' ) ) {
			$allowed_managers  = is_multisite() ? __( 'Super Admin', 'head-footer-code' ) . ' ' . __( 'and', 'head-footer-code' ) : '';
			$allowed_managers .= __( 'Administrator', 'head-footer-code' );
			printf(
				/* translators: 1: User role(s) that can manage options (Super Admin and/or Administrator), 2: Path/Name of Plugin Settings page */
				esc_html__( 'They work in exactly the same way as site-wide code, which %1$s can configure under %2$s.', 'head-footer-code' ),
				__( 'Tools', 'head-footer-code' ) . ' > ' . HFC_PLUGIN_NAME,
				$allowed_managers
			);
		} else {
			printf(
				/* translators: Link to Plugin Settings page */
				esc_html__( 'They work in exactly the same way as site-wide code, which you can configure under %s.', 'head-footer-code' ),
				'<a href="tools.php?page=' . HFC_PLUGIN_SLUG . '">' . __( 'Tools', 'head-footer-code' ) . ' > ' . HFC_PLUGIN_NAME . '</a>'
			);
		}

		echo '<br>';
		printf(
			/* translators: 1: translated 'article specific', 2: HTML comment code */
			esc_html__( 'Please note, if you leave empty any of %1$s fields and choose replace behavior, site-wide code will not be removed until you add empty space or empty HTML comment %2$s here.', 'head-footer-code' ),
			__( 'article specific', 'head-footer-code' ),
			'<code>&lt;!-- --&gt;</code>'
		);
		?>
		</p>

		<label><?php esc_html_e( 'Behavior', 'head-footer-code' ); ?></label><br />
		<select name="auhfc[behavior]" id="auhfc_behavior">
			<option value="append" <?php echo ( 'append' === auhfc_get_meta( 'behavior' ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Append to the site-wide code', 'head-footer-code' ); ?></option>
			<option value="replace" <?php echo ( 'replace' === auhfc_get_meta( 'behavior' ) ) ? 'selected' : ''; ?>><?php esc_html_e( 'Replace the site-wide code', 'head-footer-code' ); ?></option>
		</select>
		<br /><br />
		<label for="auhfc_head"><?php esc_html_e( 'HEAD Code', 'head-footer-code' ); ?></label><br />
		<textarea name="auhfc[head]" id="auhfc_head" class="widefat code" rows="5"><?php echo esc_textarea( auhfc_get_meta( 'head' ) ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Example', 'head-footer-code' ); ?>: <code>&lt;link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/style.css" type="text/css" media="all"&gt;</code></p>
		<br />
		<label for="auhfc_body"><?php esc_html_e( 'BODY Code', 'head-footer-code' ); ?></label><br />
		<textarea name="auhfc[body]" id="auhfc_body" class="widefat code" rows="5"><?php echo esc_textarea( auhfc_get_meta( 'body' ) ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Example', 'head-footer-code' ); ?>: <code>&lt;script src="<?php echo get_stylesheet_directory_uri(); ?>/body-start.js" type="text/javascript"&gt;&lt;/script&gt;</code></p>
		<br />
		<label for="auhfc_footer"><?php esc_html_e( 'FOOTER Code', 'head-footer-code' ); ?></label><br />
		<textarea name="auhfc[footer]" id="auhfc_footer" class="widefat code" rows="5"><?php echo esc_textarea( auhfc_get_meta( 'footer' ) ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Example', 'head-footer-code' ); ?>: <code>&lt;script src="<?php echo get_stylesheet_directory_uri(); ?>/script.js" type="text/javascript"&gt;&lt;/script&gt;</code></p>
		<script type="text/javascript">
		(function(){
			'use strict';
			var auhfc_cm_head = wp.codeEditor.initialize(document.getElementById('auhfc_head'), cm_settings);
			auhfc_cm_head.codemirror.on('change', function(el) { el.save(); });
			var auhfc_cm_body = wp.codeEditor.initialize(document.getElementById('auhfc_body'), cm_settings);
			auhfc_cm_body.codemirror.on('change', function(el) { el.save(); });
			var auhfc_cm_footer = wp.codeEditor.initialize(document.getElementById('auhfc_footer'), cm_settings);
			auhfc_cm_footer.codemirror.on('change', function(el) { el.save(); });
		})();
		</script>
		<?php
	} // END public static function html()
} // END class AUHfc_Meta_Box

/**
 * Initialize metabox on proper backend screens
 */
function auhfc_init_meta_boxes() {
	add_action( 'add_meta_boxes', array( 'AUHfc_Meta_Box', 'add' ) );
	add_action( 'save_post', array( 'AUHfc_Meta_Box', 'save' ) );
}
if ( is_admin() ) {
	add_action( 'load-post.php', 'auhfc_init_meta_boxes' );
	add_action( 'load-post-new.php', 'auhfc_init_meta_boxes' );
}
