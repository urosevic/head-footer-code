<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Inject site-wide code to head and footer with custom priorty.
 */
$auhfc_defaults = auhfc_defaults();

if ( empty( $auhfc_defaults['priority_h'] ) ) {
	$auhfc_defaults['priority_h'] = 10;
}
if ( empty( $auhfc_defaults['priority_f'] ) ) {
	$auhfc_defaults['priority_f'] = 10;
}
// Define actions for HEAD and FOOTER
add_action( 'wp_head', 'auhfc_wp_head', $auhfc_defaults['priority_h'] );
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
		$behavior = auhfc_get_meta( 'behavior' );
		if ( WP_DEBUG ) {
			$dbg_set = "(type: {$auhfc_post_type}; bahavior: {$behavior}; priority: {$auhfc_settings['priority_h']}; do_shortcode: {$auhfc_settings['do_shortcode']})";
		}
	} else {
		$auhfc_meta = '';
		$behavior = '';
		if ( WP_DEBUG ) {
			$dbg_set = "({$auhfc_post_type})";
		}
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
		$out .= WP_DEBUG ? "<!-- Head & Footer Code: Site-wide head section start {$dbg_set} -->\n" : '';
		$out .= $auhfc_settings['head'];
		$out .= WP_DEBUG ? $out .= "<!-- Head & Footer Code: Site-wide head section end {$dbg_set} -->\n" : '';
	}

	// Inject article specific head code if post_type is allowed
	if ( ! empty( $auhfc_meta ) && in_array( $auhfc_post_type, $auhfc_settings['post_types'] ) ) {
		$out .= WP_DEBUG ? "<!-- Head & Footer Code: Article specific head section start {$dbg_set} -->\n" : '';
		$out .= $auhfc_meta;
		$out .= WP_DEBUG ? "<!-- Head & Footer Code: Article specific head section end {$dbg_set} -->\n" : '';
	}

	// Print prepared code.
	echo ( 'y' === $auhfc_settings['do_shortcode'] ) ? do_shortcode( $out ) : $out;

	// Free some memory.
	unset( $auhfc_post_type, $auhfc_settings, $auhfc_meta, $behavior, $out );

} // END function auhfc_wp_head()

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
		if ( WP_DEBUG ) {
			$dbg_set = "(type: {$auhfc_post_type}; bahavior: {$behavior}; priority: {$auhfc_settings['priority_f']}; do_shortcode: {$auhfc_settings['do_shortcode']})";
		}
	} else {
		$auhfc_meta = '';
		$behavior = '';
		if ( WP_DEBUG ) {
			$dbg_set = "({$auhfc_post_type})";
		}
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
		$out .= WP_DEBUG ? "<!-- Head & Footer Code: Site-wide footer section start {$dbg_set} -->\n" : '';
		$out .= $auhfc_settings['footer'];
		$out .= WP_DEBUG ? "<!-- Head & Footer Code: Site-wide footer section end {$dbg_set} -->\n" : '';
	}

	// Inject article specific head code if post_type is allowed
	if ( ! empty( $auhfc_meta ) && in_array( $auhfc_post_type, $auhfc_settings['post_types'] ) ) {
		$out .= WP_DEBUG ? "<!-- Head & Footer Code: Article specific footer section start {$dbg_set} -->\n" : '';
		$out .= trim( $auhfc_meta );
		$out .= WP_DEBUG ? "<!-- Head & Footer Code: Article specific footer section end {$dbg_set} -->\n" : '';
	}

	// Print prepared code.
	echo ( 'y' === $auhfc_settings['do_shortcode'] ) ? do_shortcode( $out ) : $out;

	// Free some memory.
	unset( $auhfc_post_type, $auhfc_settings, $auhfc_meta, $behavior, $out );

} // END function auhfc_wp_footer()
