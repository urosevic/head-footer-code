<?php
/**
 * Frontend magic for Head & Footer Code
 *
 * @package Head_Footer_Code
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Inject site-wide code to head, body and footer with custom priorty.
 */
$auhfc_settings = auhfc_settings();
if ( empty( $auhfc_settings['sitewide']['priority_h'] ) ) {
	$auhfc_settings['sitewide']['priority_h'] = 10;
}
if ( empty( $auhfc_settings['sitewide']['priority_b'] ) ) {
	$auhfc_settings['sitewide']['priority_b'] = 10;
}
if ( empty( $auhfc_settings['sitewide']['priority_f'] ) ) {
	$auhfc_settings['sitewide']['priority_f'] = 10;
}

// Define actions for HEAD and FOOTER.
add_action( 'wp_head', 'auhfc_wp_head', $auhfc_settings['sitewide']['priority_h'] );
add_action( 'wp_body_open', 'auhfc_wp_body', $auhfc_settings['sitewide']['priority_b'] );
add_action( 'wp_footer', 'auhfc_wp_footer', $auhfc_settings['sitewide']['priority_f'] );

/**
 * Inject site-wide and Homepage or Article specific head code before </head>
 */
function auhfc_wp_head() {

	// Get variables to test.
	$auhfc_settings         = auhfc_settings();
	$auhfc_post_type        = auhfc_get_post_type();
	$is_homepage_blog_posts = auhfc_is_homepage_blog_posts();
	$head_behavior          = 'none';
	$head_code              = '';

	// Get meta for post only if it's singular.
	if ( 'not singular' !== $auhfc_post_type && in_array( $auhfc_post_type, $auhfc_settings['article']['post_types'], true ) ) {
		$head_behavior = auhfc_get_meta( 'behavior' );
		$head_code     = auhfc_get_meta( 'head' );
		$dbg_set       = "type: {$auhfc_post_type}; bahavior: {$head_behavior}; priority: {$auhfc_settings['sitewide']['priority_h']}; do_shortcode_h: {$auhfc_settings['sitewide']['do_shortcode_h']}";
	} elseif ( is_category() ) {
		// Get category (term) meta with get_term_meta().
		$category  = get_queried_object();
		$auhfc_cat = get_term_meta( $category->term_id, '_auhfc', true );
		if ( ! empty( $auhfc_cat ) ) {
			$head_behavior = $auhfc_cat['behavior'];
			$head_code     = $auhfc_cat['head'];
		}
		$dbg_set = "type: category; bahavior: {$head_behavior}; priority: {$auhfc_settings['sitewide']['priority_h']}; do_shortcode_h: {$auhfc_settings['sitewide']['do_shortcode_h']}";
	} else {
		$dbg_set = $auhfc_post_type;
		// Get meta for homepage.
		if ( $is_homepage_blog_posts ) {
			$head_behavior = $auhfc_settings['homepage']['behavior'];
			$head_code     = $auhfc_settings['homepage']['head'];
			$dbg_set       = "type: homepage; bahavior: {$head_behavior}; priority: {$auhfc_settings['sitewide']['priority_h']}; do_shortcode_h: {$auhfc_settings['sitewide']['do_shortcode_h']}";
		}
	}

	// If no code to inject, simply exit.
	if ( empty( $auhfc_settings['sitewide']['head'] ) && empty( $head_code ) ) {
		return;
	}

	// Prepare code output.
	$out = '';

	// Inject site-wide head code.
	if (
		! empty( $auhfc_settings['sitewide']['head'] ) &&
		auhfc_print_sitewide( $head_behavior, $head_code, $auhfc_post_type, $auhfc_settings['article']['post_types'], is_category() )
	) {
		$out .= auhfc_out( 's', 'h', $dbg_set, $auhfc_settings['sitewide']['head'] );
	}

	// Inject head code for Homepage in Blog Posts mode OR article specific (for allowed post_type) head code OR category head code.
	if ( ! empty( $head_code ) ) {
		if ( $is_homepage_blog_posts ) {
			$out .= auhfc_out( 'h', 'h', $dbg_set, $head_code );
		} elseif ( in_array( $auhfc_post_type, $auhfc_settings['article']['post_types'], true ) ) {
			$out .= auhfc_out( 'a', 'h', $dbg_set, $head_code );
		} else {
			$out .= auhfc_out( 'c', 'h', $dbg_set, $head_code );
		}
	}

	// Print prepared code.
	echo ( 'y' === $auhfc_settings['sitewide']['do_shortcode_h'] ) ? do_shortcode( $out ) : $out;

} // END function auhfc_wp_head()

/**
 * Inject site-wide and Article specific body code right after opening <body>
 */
function auhfc_wp_body() {

	// Get variables to test.
	$auhfc_settings         = auhfc_settings();
	$auhfc_post_type        = auhfc_get_post_type();
	$is_homepage_blog_posts = auhfc_is_homepage_blog_posts();
	$body_behavior          = 'none';
	$body_code              = '';

	// Get meta for post only if it's singular.
	if ( 'not singular' !== $auhfc_post_type && in_array( $auhfc_post_type, $auhfc_settings['article']['post_types'], true ) ) {
		$body_behavior = auhfc_get_meta( 'behavior' );
		$body_code     = auhfc_get_meta( 'body' );
		$dbg_set       = "type: {$auhfc_post_type}; bahavior: {$body_behavior}; priority: {$auhfc_settings['sitewide']['priority_b']}; do_shortcode_b: {$auhfc_settings['sitewide']['do_shortcode_b']}";
	} elseif ( is_category() ) {
		// Get category (term) meta with get_term_meta().
		$category  = get_queried_object();
		$auhfc_cat = get_term_meta( $category->term_id, '_auhfc', true );
		if ( ! empty( $auhfc_cat ) ) {
			$body_behavior = $auhfc_cat['behavior'];
			$body_code     = $auhfc_cat['body'];
		}
		$dbg_set = "type: category; bahavior: {$body_behavior}; priority: {$auhfc_settings['sitewide']['priority_b']}; do_shortcode_b: {$auhfc_settings['sitewide']['do_shortcode_b']}";
	} else {
		$dbg_set = $auhfc_post_type;
		// Get meta for homepage.
		if ( $is_homepage_blog_posts ) {
			$body_behavior = $auhfc_settings['homepage']['behavior'];
			$body_code     = $auhfc_settings['homepage']['body'];
			$dbg_set       = "type: homepage; bahavior: {$body_behavior}; priority: {$auhfc_settings['sitewide']['priority_b']}; do_shortcode_b: {$auhfc_settings['sitewide']['do_shortcode_b']}";
		}
	}

	// If no code to inject, simple exit.
	if ( empty( $auhfc_settings['sitewide']['body'] ) && empty( $body_code ) ) {
		return;
	}

	// Prepare code output.
	$out = '';

	// Inject site-wide body code.
	if (
		! empty( $auhfc_settings['sitewide']['body'] ) &&
		auhfc_print_sitewide( $body_behavior, $body_code, $auhfc_post_type, $auhfc_settings['article']['post_types'], is_category() )
	) {
		$out .= auhfc_out( 's', 'b', $dbg_set, $auhfc_settings['sitewide']['body'] );
	}

	// Inject body code for Homepage in Blog Posts mode OR article specific (for allowed post_type) body code OR category body code.
	if ( ! empty( $body_code ) ) {
		if ( $is_homepage_blog_posts ) {
			$out .= auhfc_out( 'h', 'b', $dbg_set, $body_code );
		} elseif ( in_array( $auhfc_post_type, $auhfc_settings['article']['post_types'], true ) ) {
			$out .= auhfc_out( 'a', 'b', $dbg_set, $body_code );
		} else {
			$out .= auhfc_out( 'c', 'b', $dbg_set, $body_code );
		}
	}

	// Print prepared code.
	echo ( 'y' === $auhfc_settings['sitewide']['do_shortcode_b'] ) ? do_shortcode( $out ) : $out;

} // END function auhfc_wp_body()

/**
 * Inject site-wide and Article specific footer code before the </body>
 */
function auhfc_wp_footer() {

	// Get variables to test.
	$auhfc_settings         = auhfc_settings();
	$auhfc_post_type        = auhfc_get_post_type();
	$is_homepage_blog_posts = auhfc_is_homepage_blog_posts();
	$footer_behavior        = 'none';
	$footer_code            = '';

	// Get meta for post only if it's singular.
	if ( 'not singular' !== $auhfc_post_type && in_array( $auhfc_post_type, $auhfc_settings['article']['post_types'], true ) ) {
		$footer_code     = auhfc_get_meta( 'footer' );
		$footer_behavior = auhfc_get_meta( 'behavior' );
		$dbg_set         = "type: {$auhfc_post_type}; bahavior: {$footer_behavior}; priority: {$auhfc_settings['sitewide']['priority_f']}; do_shortcode_f: {$auhfc_settings['sitewide']['do_shortcode_f']}";
	} elseif ( is_category() ) {
		// Get category (term) meta with get_term_meta().
		$category  = get_queried_object();
		$auhfc_cat = get_term_meta( $category->term_id, '_auhfc', true );
		if ( ! empty( $auhfc_cat ) ) {
			$footer_behavior = $auhfc_cat['behavior'];
			$footer_code     = $auhfc_cat['footer'];
		}
		$dbg_set = "type: category; bahavior: {$footer_behavior}; priority: {$auhfc_settings['sitewide']['priority_f']}; do_shortcode_f: {$auhfc_settings['sitewide']['do_shortcode_f']}";
	} else {
		$dbg_set = $auhfc_post_type;
		// Get meta for homepage.
		if ( $is_homepage_blog_posts ) {
			$footer_code     = $auhfc_settings['homepage']['footer'];
			$footer_behavior = $auhfc_settings['homepage']['behavior'];
			$dbg_set         = "type: homepage; bahavior: {$footer_behavior}; priority: {$auhfc_settings['sitewide']['priority_f']}; do_shortcode_f: {$auhfc_settings['sitewide']['do_shortcode_f']}";
		}
	}

	// If no code to inject, simple exit.
	if ( empty( $auhfc_settings['sitewide']['footer'] ) && empty( $footer_code ) ) {
		return;
	}

	// Prepare code output.
	$out = '';

	// Inject site-wide footer code.
	if (
		! empty( $auhfc_settings['sitewide']['footer'] ) &&
		auhfc_print_sitewide( $footer_behavior, $footer_code, $auhfc_post_type, $auhfc_settings['article']['post_types'], is_category() )
	) {
		$out .= auhfc_out( 's', 'f', $dbg_set, $auhfc_settings['sitewide']['footer'] );
	}

	// Inject footer code for Homepage in Blog Posts mode OR article specific (for allowed post_type) footer code OR category footer code.
	if ( ! empty( $footer_code ) ) {
		if ( $is_homepage_blog_posts ) {
			$out .= auhfc_out( 'h', 'f', $dbg_set, $footer_code );
		} elseif ( in_array( $auhfc_post_type, $auhfc_settings['article']['post_types'], true ) ) {
			$out .= auhfc_out( 'a', 'f', $dbg_set, $footer_code );
		} else {
			$out .= auhfc_out( 'c', 'f', $dbg_set, $footer_code );
		}
	}

	// Print prepared code.
	echo ( 'y' === $auhfc_settings['sitewide']['do_shortcode_f'] ) ? do_shortcode( $out ) : $out;

} // END function auhfc_wp_footer()

/**
 * Add `wp_body_open` backward compatibility for WordPress installations prior 5.2
 */
if ( ! function_exists( 'wp_body_open' ) ) {
	/**
	 * Fire the wp_body_open action.
	 */
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
}
