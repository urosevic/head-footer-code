<?php
/**
 * Routine to handle Category metabox for Head & Footer Code
 *
 * @package Head_Footer_Code
 * @since 1.3.0
 */

namespace Techwebux\Hfc;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Techwebux\Hfc\Common;

class Metabox_Category {
	public function __construct() {
		// Check if the current user's role has permission to edit HFC
		if ( ! Common::user_has_allowed_role() ) {
			return;
		}

		// Inject Head & Footer Code to Category Edit form.
		add_action( 'category_edit_form', array( $this, 'form' ), 10, 1 );

		// Save changes when a category is updated/edited.
		add_action( 'edit_category', array( $this, 'save' ), 10, 1 );
	} // END public function __construct

	/**
	 * Function to prepare variables and render Category metabox fields for Head & Footer Code.
	 *
	 * @param object $term_object Taxonomy term object.
	 */
	public function form( $term_object ) {
		/** @var string $form_scope Used in templates/hfc-form.php */
		$form_scope = esc_html__( 'category specific', 'head-footer-code' );

		$security_risk_notice = Common::security_risk_notice();

		// Get existing HFC meta for known Category or use defaults.
		/** @var array $auhfc_form_data Used in templates/hfc-form.php */
		$auhfc_form_data = ! empty( $term_object->term_id )
			? get_term_meta( $term_object->term_id, '_auhfc', true )
			: array(
				'init'     => 'default',
				'behavior' => 'append',
				'head'     => '',
				'body'     => '',
				'footer'   => '',
			);

		// Render nonce and form.
		wp_nonce_field( 'auhfc_category_save_action', 'auhfc_category_nonce' );
		echo '<h2>' . esc_html( HFC_PLUGIN_NAME ) . '</h2>';
		include_once HFC_DIR . '/templates/hfc-form.php';
	} // END public function form

	/**
	 * Function to update category meta
	 */
	public function save() {
		// Sanitize the nonce input.
		$nonce = isset( $_POST['auhfc_category_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['auhfc_category_nonce'] ) ) : '';

		// Verify nonce and user capabilities
		if (
			empty( $nonce )
			|| ! wp_verify_nonce( $nonce, 'auhfc_category_save_action' )
			|| ! current_user_can( 'manage_categories' )
		) {
			return;
		}

		// Escape if our value is ot present in $_POST array.
		if ( empty( $_POST['auhfc'] ) ) {
			return null;
		}

		// Ensure the necessary data is present.
		if ( empty( $_POST['tag_ID'] ) || ! isset( $_POST['auhfc'] ) ) {
			return;
		}

		// Sanitize the term_id of Category which is provided in $_POST key `tag_ID`
		$term_id = absint( $_POST['tag_ID'] );

		// Allow safe HTML, JS, and CSS.
		$allowed_html = Common::allowed_html();

		// Sanitize the `auhfc` data.
		if ( is_array( $_POST['auhfc'] ) ) {
			$data = array(
				'behavior' => ! empty( $_POST['auhfc']['behavior'] ) ? sanitize_key( $_POST['auhfc']['behavior'] ) : '',
				// Use wp_kses to preserve allowed tags and attributes.
				'head'     => ! empty( $_POST['auhfc']['head'] ) ? wp_kses( $_POST['auhfc']['head'], $allowed_html ) : '',
				'body'     => ! empty( $_POST['auhfc']['body'] ) ? wp_kses( $_POST['auhfc']['body'], $allowed_html ) : '',
				'footer'   => ! empty( $_POST['auhfc']['footer'] ) ? wp_kses( $_POST['auhfc']['footer'], $allowed_html ) : '',
			);
		}

		/**
		 * Save category metabox form values using update_term_meta()
		 * https://developer.wordpress.org/reference/functions/update_term_meta/
		 *
		 * The term_id of Category is provided in $_POST key `tag_ID`
		 */
		update_term_meta( $term_id, '_auhfc', $data );
	} // END public function save
} // END class Metabox_Category
