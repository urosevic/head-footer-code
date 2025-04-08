<?php
/**
 * Class for Head & Footer Code article metabox
 *
 * @package Head_Footer_Code
 */

namespace Techwebux\Hfc;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Techwebux\Hfc\Main;
use Techwebux\Hfc\Common;

/**
 * Class to compose Head & Footer article metabox
 */
class Metabox_Article {

	private $settings;

	public function __construct() {
		$this->settings = Main::settings();
		if ( is_admin() ) {
			add_action( 'load-post.php', array( $this, 'init_metaboxes' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metaboxes' ) );
		}
	} // END public function __construct

	/**
	 * Initialize metabox on proper backend screens
	 */
	public function init_metaboxes() {
		add_action( 'add_meta_boxes', array( $this, 'add' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	} // END public function init_metaboxes

	/**
	 * This function adds a meta box with a callback function of my_metabox_callback()
	 */
	public function add() {

		if ( empty( $this->settings['article']['post_types'] ) ) {
			return;
		}

		// Check if the current user has permission to edit
		if ( ! Common::user_has_allowed_role() ) {
			return;
		}

		// Add the meta box for allowed post types
		foreach ( $this->settings['article']['post_types'] as $post_type ) {
			add_meta_box(
				'auhfc-head-footer-code',
				esc_html( HFC_PLUGIN_NAME ),
				array( $this, 'html' ),
				$post_type,
				'normal',
				'low'
			);
		}
	} // END public function add

	/**
	 * Save meta box content.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Sanitize the nonce input.
		$nonce = isset( $_POST['head_footer_code_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['head_footer_code_nonce'] ) ) : '';

		/**
		 * To update HFC for the article, make sure:
		 * 1. we have nonce
		 * 2. nonce is valid
		 * 3. user has permission to edit post
		 * 4. we have AUHFC fields as an array
		*/
		if (
			empty( $nonce )
			|| ! wp_verify_nonce( $nonce, '_head_footer_code_nonce' )
			|| ! current_user_can( 'edit_post', $post_id )
			|| empty( $_POST['auhfc'] )
			|| ! is_array( $_POST['auhfc'] )
		) {
			return;
		}

		// Check if the current user has permission to edit
		if ( ! Common::user_has_allowed_role() ) {
			return;
		}

		// Allow safe HTML, JS, and CSS.
		$allowed_html = Common::allowed_html();

		// Sanitize each field separately.
		$data = array(
			'behavior' => isset( $_POST['auhfc']['behavior'] ) ? sanitize_key( $_POST['auhfc']['behavior'] ) : '',
			'head'     => isset( $_POST['auhfc']['head'] ) ? wp_kses( $_POST['auhfc']['head'], $allowed_html ) : '',
			'body'     => isset( $_POST['auhfc']['body'] ) ? wp_kses( $_POST['auhfc']['body'], $allowed_html ) : '',
			'footer'   => isset( $_POST['auhfc']['footer'] ) ? wp_kses( $_POST['auhfc']['footer'], $allowed_html ) : '',
		);
		update_post_meta( $post_id, '_auhfc', wp_slash( $data ) );
	} // END public function save

	/**
	 * Callback function to prepare variables and render article metabox for Head & Footer Code.
	 */
	public function html() {
		/** @var string $form_scope Used in ../templates/hfc-form.php */
		$form_scope = esc_html__( 'article specific', 'head-footer-code' );

		// Get article specific postmeta.
		/** @var array $auhfc_form_data Used in ../templates/hfc-form.php */
		$auhfc_form_data = array(
			'behavior' => Common::get_meta( 'behavior' ),
			'head'     => Common::get_meta( 'head' ),
			'body'     => Common::get_meta( 'body' ),
			'footer'   => Common::get_meta( 'footer' ),
		);

		// Render nonce and form.
		wp_nonce_field( '_head_footer_code_nonce', 'head_footer_code_nonce' );
		include_once HFC_DIR . '/templates/hfc-form.php';
	} // END public function html
} // END class Metabox
