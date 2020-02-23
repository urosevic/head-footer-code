<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Inject site-wide code to head, body and footer with custom priorty.
 */
$auhfc_defaults = auhfc_defaults();

if ( empty( $auhfc_defaults['priority_h'] ) ) {
	$auhfc_defaults['priority_h'] = 10;
}
if ( empty( $auhfc_defaults['priority_b'] ) ) {
	$auhfc_defaults['priority_b'] = 10;
}
if ( empty( $auhfc_defaults['priority_f'] ) ) {
	$auhfc_defaults['priority_f'] = 10;
}
// Define actions for HEAD and FOOTER
add_action( 'wp_head', 'auhfc_wp_head', $auhfc_defaults['priority_h'] );
add_action( 'wp_body_open', 'auhfc_wp_body', $auhfc_defaults['priority_b'] );
add_action( 'wp_footer', 'auhfc_wp_footer', $auhfc_defaults['priority_f'] );

/**
 * Inject site-wide and Article specific head code before </head>
 */
function auhfc_wp_head() {

	// Get post type
	if ( is_singular() ) {
		global $wp_the_query;
		$auhfc_post_type = $wp_the_query->get_queried_object()->post_type;
	} else {
		$auhfc_post_type = 'not singular';
	}

	// Get variables to test
	$auhfc_settings = auhfc_defaults();

	// Get meta for post only if it's singular
	if ( 'not singular' !== $auhfc_post_type && in_array( $auhfc_post_type, $auhfc_settings['post_types'] ) ) {
		$auhfc_meta = auhfc_get_meta( 'head' );
		$behavior   = auhfc_get_meta( 'behavior' );
		$dbg_set    = "type: {$auhfc_post_type}; bahavior: {$behavior}; priority: {$auhfc_settings['priority_h']}; do_shortcode: {$auhfc_settings['do_shortcode']}";
	} else {
		$auhfc_meta = '';
		$behavior   = '';
		$dbg_set    = $auhfc_post_type;
	}

	// If no code to inject, simple exit
	if ( empty( $auhfc_settings['head'] ) && empty( $auhfc_meta ) ) {
		return;
	}

	// Prepare code output.
	$out = '';

	// Inject site-wide head code
	if (
		! empty( $auhfc_settings['head'] ) &&
		(
			'replace' !== $behavior ||
			( 'replace' == $behavior && ! in_array( $auhfc_post_type, $auhfc_settings['post_types'] ) ) ||
			( 'replace' == $behavior && in_array( $auhfc_post_type, $auhfc_settings['post_types'] ) && empty( $auhfc_meta ) )
		)
	) {
		$out .= auhfc_out( 's', 'h', $dbg_set, $auhfc_settings['head'] );
	}

	// Inject article specific head code if post_type is allowed
	if ( ! empty( $auhfc_meta ) && in_array( $auhfc_post_type, $auhfc_settings['post_types'] ) ) {
		$out .= auhfc_out( 'a', 'h', $dbg_set, $auhfc_meta );
	}

	// Print prepared code.
	echo $out;
	// echo ( 'y' === $auhfc_settings['do_shortcode'] ) ? do_shortcode( $out ) : $out;

	// Free some memory.
	unset( $auhfc_post_type, $auhfc_settings, $auhfc_meta, $behavior, $out );

} // END function auhfc_wp_head()


/**
 * Inject site-wide and Article specific body code right after opening <body>
 */
function auhfc_wp_body() {

	// Get post type
	if ( is_singular() ) {
		global $wp_the_query;
		$auhfc_post_type = $wp_the_query->get_queried_object()->post_type;
	} else {
		$auhfc_post_type = 'not singular';
	}

	// Get variables to test
	$auhfc_settings = auhfc_defaults();

	// Get meta for post only if it's singular
	if ( 'not singular' !== $auhfc_post_type && in_array( $auhfc_post_type, $auhfc_settings['post_types'] ) ) {
		$auhfc_meta = auhfc_get_meta( 'body' );
		$behavior   = auhfc_get_meta( 'behavior' );
		$dbg_set    = "type: {$auhfc_post_type}; bahavior: {$behavior}; priority: {$auhfc_settings['priority_b']}; do_shortcode: {$auhfc_settings['do_shortcode']}";
	} else {
		$auhfc_meta = '';
		$behavior   = '';
		$dbg_set    = $auhfc_post_type;
	}

	// If no code to inject, simple exit
	if ( empty( $auhfc_settings['body'] ) && empty( $auhfc_meta ) ) {
		return;
	}

	// Prepare code output.
	$out = '';

	// Inject site-wide body code
	if (
		! empty( $auhfc_settings['body'] ) &&
		(
			'replace' !== $behavior ||
			( 'replace' == $behavior && ! in_array( $auhfc_post_type, $auhfc_settings['post_types'] ) ) ||
			( 'replace' == $behavior && in_array( $auhfc_post_type, $auhfc_settings['post_types'] ) && empty( $auhfc_meta ) )
		)
	) {
		$out .= auhfc_out( 's', 'b', $dbg_set, $auhfc_settings['body'] );
	}

	// Inject article specific body code if post_type is allowed
	if ( ! empty( $auhfc_meta ) && in_array( $auhfc_post_type, $auhfc_settings['post_types'] ) ) {
		$out .= auhfc_out( 'a', 'b', $dbg_set, $auhfc_meta );
	}

	// Print prepared code.
	echo $out;
	// echo ( 'y' === $auhfc_settings['do_shortcode'] ) ? do_shortcode( $out ) : $out;

	// Free some memory.
	unset( $auhfc_post_type, $auhfc_settings, $auhfc_meta, $behavior, $out );

} // END function auhfc_wp_body()

/**
 * Inject site-wide and Article specific footer code before the </body>
 */
function auhfc_wp_footer() {

	// Get post type
	if ( is_singular() ) {
		global $wp_the_query;
		$auhfc_post_type = $wp_the_query->get_queried_object()->post_type;
	} else {
		$auhfc_post_type = 'not singular';
	}

	// Get variables to test
	$auhfc_settings = auhfc_defaults();

	// Get meta for post only if it's singular
	if ( 'not singular' !== $auhfc_post_type && in_array( $auhfc_post_type, $auhfc_settings['post_types'] ) ) {
		$auhfc_meta = auhfc_get_meta( 'footer' );
		$behavior = auhfc_get_meta( 'behavior' );
		$dbg_set = "type: {$auhfc_post_type}; bahavior: {$behavior}; priority: {$auhfc_settings['priority_f']}; do_shortcode: {$auhfc_settings['do_shortcode']}";
	} else {
		$auhfc_meta = '';
		$behavior = '';
		$dbg_set = $auhfc_post_type;
	}

	// If no code to inject, simple exit
	if ( empty( $auhfc_settings['footer'] ) && empty( $auhfc_meta ) ) {
		return;
	}

	// Prepare code output
	$out = '';

	// Inject site-wide head code
	if (
		! empty( $auhfc_settings['footer'] ) &&
		(
			'replace' !== $behavior ||
			( 'replace' == $behavior && ! in_array( $auhfc_post_type, $auhfc_settings['post_types'] ) ) ||
			( 'replace' == $behavior && in_array( $auhfc_post_type, $auhfc_settings['post_types'] ) && empty( $auhfc_meta ) )
		)
	) {
		$out .= auhfc_out( 's', 'f', $dbg_set, $auhfc_settings['footer'] );
	}

	// Inject article specific head code if post_type is allowed
	if ( ! empty( $auhfc_meta ) && in_array( $auhfc_post_type, $auhfc_settings['post_types'] ) ) {
		$out .= auhfc_out( 'a', 'f', $dbg_set, $auhfc_meta );
	}

	// Print prepared code.
	echo ( 'y' === $auhfc_settings['do_shortcode'] ) ? do_shortcode( $out ) : $out;

	// Free some memory.
	unset( $auhfc_post_type, $auhfc_settings, $auhfc_meta, $behavior, $out );

} // END function auhfc_wp_footer()

/**
 * Add `wp_body_open` backward compatibility for WordPress installations prior 5.2
 */
if ( ! function_exists( 'wp_body_open' ) ) {
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
}
