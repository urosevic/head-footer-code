<?php
/**
 * Provide global defaults
 * @return array Arary of defined global values
 */
function auhfc_defaults() {
	$defaults = array(
		'head'       => '',
		'footer'     => '',
		'priority_h' => 10,
		'priority_f' => 10,
		'post_types' => array(),
	);
	$auhfc_settings = get_option( 'auhfc_settings', $defaults );
	$auhfc_settings = wp_parse_args( $auhfc_settings, $defaults );
	return $auhfc_settings;
} // END function auhfc_defaults()

/**
 * Go through activation hook on single and network wide activation
 * Credits: https://developer.wordpress.org/reference/functions/get_sites/#comment-1842
 */
function auhfc_activate( $networkwide ) {
	// Multisite Network Activate
	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		error_log( 'it is multisite' );
		// Check if it is a network activation so we run activation for each site
		if ( $networkwide ) {
			error_log( 'it is networkwide' );
			if ( function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ) {
				$sites = get_sites();
				foreach ( $sites as $site ) {
					error_log( 'it is site ID ' . $site->blog_id );
					switch_to_blog( $site->blog_id );
					_auhfc_activate();
					restore_current_blog();
				}
				return;
			}
		}
	}
	_auhfc_activate();
} // END function auhfc_activate()

/**
 * Activate plugin and run update if required
 * 
 */
function _auhfc_activate() {
	auhfc_defaults();
	auhfc_maybe_update();
} // END function _auhfc_activate() {

/**
 * Check do we need to migrate options
 */
function auhfc_maybe_update() {

	// bail if this plugin data doesn't need updating
	if ( get_option( 'auhfc_db_ver' ) >= WPAU_HEAD_FOOTER_CODE_DB_VER ) {
		return;
	}

	require_once( dirname( __FILE__ ) . '/update.php' );
	auhfc_update();

} // END function auhfc_maybe_update()

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
