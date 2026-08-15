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
	/** @var array Settings retrieved from the main controller. */
	private $settings;

	/** @var Plugin_Info Plugin metadata object. */
	protected $plugin;

	/** @var string[] Taxonomy slugs this metabox is registered for. */
	protected $taxonomies;

	/**
	 * Initializes the class and registers frontend hooks.
	 *
	 * @param Plugin_Info $plugin Instance of the plugin info object.
	 * @param array       $settings Plugin settings array.
	 */
	public function __construct(
		Plugin_Info $plugin,
		$settings
	) {
		$this->plugin     = $plugin;
		$this->settings   = $settings;
		$this->taxonomies = $this->settings['article']['taxonomies'];

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
		$term_name = $term_object->name;

		/** @var string $form_scope Used in templates/hfc-form.php */
		$auhfc_form_scope = esc_html( "{$term_name} {$taxonomy_label} " )
			. esc_html__( 'specific', 'head-footer-code' );

		/** @var array $auhfc_security_risk_notice Used in templates/hfc-form.php */
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
	 *
	 * @param int $term_id Term ID.
	 */
	public function save( $term_id ) {
		if ( ! isset( $_POST['auhfc'] ) ) {
			return;
		}

		// Get taxonomy from form.
		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) : '';

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

		// Dynamic capability check.
		$tax_obj = get_taxonomy( $taxonomy );
		if ( ! $tax_obj || ! current_user_can( $tax_obj->cap->edit_terms, $term_id ) ) {
			return;
		}

		// Defense-in-depth: the save hook is only wired up for allowed roles in
		// Main::plugins_loaded(), but we re-check here so this handler stays safe
		// on its own, independent of hook wiring.
		if ( ! Common::user_has_allowed_role() ) {
			return;
		}

		// Maybe delete HFC for this taxonomy?
		if ( ! isset( $_POST['auhfc'] ) ) {
			delete_term_meta( $term_id, $this->plugin->meta_key );
			return;
		}

		// Sanitize data and update term meta.
		// Unslash first: WP adds magic-quotes slashes to all superglobals, and this raw
		// custom JS/CSS/HTML content must not carry them into sanitize_hfc_data() or they
		// interfere with its wp_kses()/regex-based sanitization.
		// The update_term_meta() below still needs wp_slash( $data ) - core's update_metadata()
		// and add_metadata() internally call wp_unslash() on the value we pass them and
		// wp_slash() here cancels that out so the stored value matches this sanitized data exactly.
		// See Common::get_meta() for the matching note on the read side (no stripslashes there
		// as it would double-unslash).
		$data = Common::sanitize_hfc_data( wp_unslash( $_POST['auhfc'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized inside Common::sanitize_hfc_data() via sanitize_html_with_scripts()/wp_kses(), not here.
		update_term_meta( $term_id, $this->plugin->meta_key, wp_slash( $data ) );
	}

	/**
	 * Generates the nonce field name for a given taxonomy.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @return string Nonce field name.
	 */
	private function get_nonce_name( $taxonomy ) {
		return "auhfc_{$taxonomy}_nonce";
	}

	/**
	 * Generates the nonce action string for a given taxonomy.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @return string Nonce action name.
	 */
	private function get_nonce_action( $taxonomy ) {
		return "auhfc_{$taxonomy}_save_action";
	}
}
