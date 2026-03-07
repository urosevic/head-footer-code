<?php
/**
 * Article metabox handler.
 *
 * Manages the creation and data persistence of custom code snippet
 * metaboxes for posts, pages, and custom post types.
 *
 * @package   Head_Footer_Code
 * @since     1.0.0
 */

namespace Techwebux\Hfc;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to compose Head & Footer article metabox
 */
class Metabox_Article {
	/** @var array Settings retrieved from the main controller. */
	private $settings;

	/** @var Plugin_Info Plugin metadata object. */
	protected $plugin;

	/**
	 * Initializes the class and registers frontend hooks.
	 *
	 * @param Plugin_Info $plugin Instance of the plugin info object.
	 */
	public function __construct( Plugin_Info $plugin ) {
		$this->plugin   = $plugin;
		$this->settings = Main::get_settings();

		// Check if the current user's role has permission to edit HFC
		if ( ! Common::user_has_allowed_role() ) {
			return;
		}

		add_action( 'load-post.php', array( $this, 'init_metaboxes' ) );
		add_action( 'load-post-new.php', array( $this, 'init_metaboxes' ) );
	}

	/**
	 * Initialize metabox on proper backend screens
	 */
	public function init_metaboxes() {
		add_action( 'add_meta_boxes', array( $this, 'add' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	/**
	 * This function adds a meta box with a callback function of my_metabox_callback()
	 */
	public function add() {

		if ( empty( $this->settings['article']['post_types'] ) ) {
			return;
		}

		// Add the meta box for allowed post types
		foreach ( $this->settings['article']['post_types'] as $post_type ) {
			add_meta_box(
				'auhfc-head-footer-code',
				esc_html( $this->plugin->name ),
				array( $this, 'form' ),
				$post_type,
				'normal',
				'low'
			);
		}
	}

	/**
	 * Callback function to prepare variables and render article metabox for Head & Footer Code.
	 *
	 * @param object $post WP_Post object.
	 * @return void
	 */
	public function form( $post ) {
		/** @var string $form_scope Used in ../templates/hfc-form.php */
		$form_scope = esc_html__( 'article specific', 'head-footer-code' );

		$auhfc_security_risk_notice = Common::get_security_risk_notice();

		$post_id = $post->ID;

		// Get article specific postmeta.
		/** @var array $auhfc_form_data Used in ../templates/hfc-form.php */
		$auhfc_form_data = array(
			'behavior' => Common::get_post_meta( 'behavior', $post_id ),
			'head'     => Common::get_post_meta( 'head', $post_id ),
			'body'     => Common::get_post_meta( 'body', $post_id ),
			'footer'   => Common::get_post_meta( 'footer', $post_id ),
		);

		// Render nonce and form.
		wp_nonce_field( '_head_footer_code_nonce', 'head_footer_code_nonce' );
		include_once $this->plugin->dir . '/templates/hfc-form.php';
	}

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
		$nonce = isset( $_POST['head_footer_code_nonce'] )
		? sanitize_text_field( wp_unslash( $_POST['head_footer_code_nonce'] ) )
		: '';

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

		// Sanitize data and update post meta.
		$data = Common::sanitize_hfc_data( $_POST['auhfc'] );
		update_post_meta( $post_id, '_auhfc', wp_slash( $data ) );
	}
}
