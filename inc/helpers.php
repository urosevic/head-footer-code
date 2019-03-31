<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

register_activation_hook( __FILE__, 'auhfc_activate' );
/**
 * Plugin Activation hook function to check for Minimum PHP and WordPress versions
 */
function auhfc_activate() {
	global $wp_version;
	$php_req = '5.5'; // Minimum version of PHP required for this plugin
	$wp_req  = '4.9'; // Minimum version of WordPress required for this plugin

	if ( version_compare( PHP_VERSION, $php_req, '<' ) ) {
		$flag = 'PHP';
	} elseif ( version_compare( $wp_version, $wp_req, '<' ) ) {
		$flag = 'WordPress';
	} else {
		return;
	}
	$version = 'PHP' == $flag ? $php_req : $wp_req;
	deactivate_plugins( WPAU_HEAD_FOOTER_CODE_FILE );
	wp_die(
		'<p>The <strong>Head & Footer Code</strong> plugin requires' . $flag . ' version ' . $version . ' or greater.</p>',
		'Plugin Activation Error',
		array(
			'response' => 200,
			'back_link' => true,
		)
	);
} // END function auhfc_activate()

add_action( 'admin_enqueue_scripts', 'auhfc_codemirror_enqueue_scripts' );
/**
 * CodeMirror enqueue hoot function to enable code editor in plugin settings
 * @param  string $hook Current page hook
 */
function auhfc_codemirror_enqueue_scripts( $hook ) {
	if ( 'tools_page_head_footer_code' !== $hook ) {
		return;
	}
	$cm_settings['codeEditor'] = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
	wp_localize_script( 'jquery', 'cm_settings', $cm_settings );
	wp_enqueue_script( 'wp-codemirror' );
	wp_enqueue_style( 'wp-codemirror' );
} // END function auhfc_codemirror_enqueue_scripts( $hook )

/**
 * Provide global defaults
 * @return array Arary of defined global values
 */
function auhfc_defaults() {
	$defaults = array(
		'head'       => '',
		'footer'     => '',
		'priority'   => 10,
		'post_types' => array(),
	);
	$auhfc_settings = get_option( 'auhfc_settings', $defaults );
	$auhfc_settings = wp_parse_args( $auhfc_settings, $defaults );
	return $auhfc_settings;
} // END function auhfc_defaults()

/**
 * Get values of metabox fields
 * @param  string $field_name Post meta field key
 * @return string             Post meta field value
 */
function auhfc_get_meta( $field_name = '' ) {

	if ( empty( $field_name ) ) {
		return false;
	}

	if ( is_admin() ) {
		global $post;

		// If $post has not an object, return false
		if ( empty( $post ) || ! is_object( $post ) ) {
			return false;
		}

		$post_id = $post->ID;
	} else {
		if ( is_singular() ) {
			global $wp_the_query;
			$post_id = $wp_the_query->get_queried_object_id();
		} else {
			$post_id = false;
		}
	}

	if ( empty( $post_id ) ) {
		return false;
	}

	$field = get_post_meta( $post_id, '_auhfc', true );

	if ( ! empty( $field ) && is_array( $field ) && ! empty( $field[ $field_name ] ) ) {
		return stripslashes_deep( $field[ $field_name ] );
	} elseif ( 'behavior' == $field_name ) {
		return 'append';
	} else {
		return false;
	}
} // END function auhfc_get_meta( $field_name )
