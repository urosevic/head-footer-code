<?php
/**
 * Category metabox handler.
 *
 * Extends taxonomy edit screens to include code snippet inputs
 * for taxonomy-specific injections.
 *
 * @package Head_Footer_Code
 * @since 1.5.3
 */

namespace Techwebux\Hfc;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Metabox_Taxonomy {
	/** @var Plugin_Info Plugin metadata object. */
	protected $plugin;

	protected $taxonomies;

	/**
	 * Initializes the class and registers frontend hooks.
	 *
	 * @param Plugin_Info $plugin Instance of the plugin info object.
	 */
	public function __construct(
		Plugin_Info $plugin,
		array $taxonomies = array( 'category' )
	) {
		$this->plugin     = $plugin;
		$this->taxonomies = $taxonomies;

		// Check if the current user's role has permission to edit HFC
		if ( ! Common::user_has_allowed_role() ) {
			return;
		}

		foreach ( $this->taxonomies as $taxonomy ) {
			// Dynamic hook: {taxonomy}_edit_form & edit_{taxonomy}
			add_action( "{$taxonomy}_edit_form", array( $this, 'form' ), 10, 1 );
			add_action( "edit_{$taxonomy}", array( $this, 'save' ), 10, 1 );
		}
	}

	/**
	 * Function to prepare variables and render Category metabox fields for Head & Footer Code.
	 *
	 * @param object $term_object Taxonomy term object.
	 * @return void
	 */
	public function form( $term_object ) {
		// Get taxonomy definition
		$taxonomy_obj = get_taxonomy( $term_object->taxonomy );

		// Get taxonomy label
		$taxonomy_label = ( $taxonomy_obj && isset( $taxonomy_obj->labels->singular_name ) )
			? $taxonomy_obj->labels->singular_name
			: esc_html__( 'taxonomy', 'head-footer-code' );

		// Get taxonomy name
		$term_name = $term_object->name; // or $term_object->slug

		/** @var string $form_scope Used in templates/hfc-form.php */
		$auhfc_form_scope = esc_html( "{$term_name} {$taxonomy_label} " )
			. esc_html__( 'specific', 'head-footer-code' );

		$auhfc_security_risk_notice = Common::get_security_risk_notice();

		$term_id  = isset( $term_object->term_id ) ? (int) $term_object->term_id : 0;
		$taxonomy = isset( $term_object->taxonomy ) ? (string) $term_object->taxonomy : 'category';

		// Get taxonomy specific termmeta.
		/** @var array $auhfc_form_data Used in templates/hfc-form.php */
		$auhfc_form_data = array(
			'behavior' => Common::get_term_meta( 'behavior', $term_id ),
			'head'     => Common::get_term_meta( 'head', $term_id ),
			'body'     => Common::get_term_meta( 'body', $term_id ),
			'footer'   => Common::get_term_meta( 'footer', $term_id ),
		);

		// Render nonce and form.
		wp_nonce_field( $this->get_nonce_action( $taxonomy ), $this->get_nonce_name( $taxonomy ) );
		echo '<div id="auhfc-head-footer-code">';
		echo '<h2>' . esc_html( $this->plugin->name ) . '</h2>';
		include_once $this->plugin->dir . '/templates/hfc-form.php';
		echo '</div>';
	}

	/**
	 * Function to update taxonomy meta
	 */
	public function save( $term_id ) {
		if ( ! isset( $_POST['auhfc'] ) ) {
			return;
		}

		// Get taxonomy from form.
		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_text_field( $_POST['taxonomy'] ) : '';

		// Bail if current taxonomy is not among allowed in plugin settings.
		if ( ! in_array( $taxonomy, $this->taxonomies, true ) ) {
			return;
		}

		// Verify nonce.
		$nonce = isset( $_POST[ $this->get_nonce_name( $taxonomy ) ] )
			? sanitize_text_field( wp_unslash( $_POST[ $this->get_nonce_name( $taxonomy ) ] ) )
			: '';

		// Verify nonce and user capabilities
		if (
			empty( $nonce )
			|| ! wp_verify_nonce( $nonce, $this->get_nonce_action( $taxonomy ) )
		) {
			return;
		}

		// Dynamic capability check
		$tax_obj = get_taxonomy( $taxonomy );
		if ( ! $tax_obj || ! current_user_can( $tax_obj->cap->edit_terms, $term_id ) ) {
			return;
		}

		// Maybe delete HFC for this taxonomy?
		if ( ! isset( $_POST['auhfc'] ) ) {
			delete_term_meta( $term_id, '_auhfc' );
			return;
		}

		// Sanitize data and update term meta.
		$data = Common::sanitize_hfc_data( $_POST['auhfc'] );
		update_term_meta( $term_id, '_auhfc', wp_slash( $data ) );
	}

	/**
	 * Generates the nonce field name for a given taxonomy.
	 */
	private function get_nonce_name( $taxonomy ) {
		return "auhfc_{$taxonomy}_nonce";
	}

	/**
	 * Generates the nonce action string for a given taxonomy.
	 */
	private function get_nonce_action( $taxonomy ) {
		return "auhfc_{$taxonomy}_save_action";
	}
}
