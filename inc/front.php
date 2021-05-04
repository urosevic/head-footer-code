<?php
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
// Define actions for HEAD and FOOTER
add_action( 'wp_head', 'auhfc_wp_head', $auhfc_settings['sitewide']['priority_h'] );
add_action( 'wp_body_open', 'auhfc_wp_body', $auhfc_settings['sitewide']['priority_b'] );
add_action( 'wp_footer', 'auhfc_wp_footer', $auhfc_settings['sitewide']['priority_f'] );

/**
 * Inject site-wide and Homepage or Article specific head code before </head>
 */
function auhfc_wp_head() {

	// Get post type.
	$auhfc_post_type = auhfc_get_post_type();

	// Get variables to test.
	$auhfc_settings         = auhfc_settings();
	$is_homepage_blog_posts = auhfc_is_homepage_blog_posts();
	$homepage_behavior      = false;
	$homepage_code          = '';

	// Get meta for post only if it's singular.
	if ( 'not singular' !== $auhfc_post_type && in_array( $auhfc_post_type, $auhfc_settings['article']['post_types'] ) ) {
		$article_code     = auhfc_get_meta( 'head' );
		$article_behavior = auhfc_get_meta( 'behavior' );
		$dbg_set          = "type: {$auhfc_post_type}; bahavior: {$article_behavior}; priority: {$auhfc_settings['sitewide']['priority_h']}; do_shortcode: {$auhfc_settings['sitewide']['do_shortcode']}";
	} else {
		$article_code     = '';
		$article_behavior = '';
		$dbg_set          = $auhfc_post_type;
		// Get meta for homepage.
		if ( $is_homepage_blog_posts ) {
			$homepage_code     = $auhfc_settings['homepage']['head'];
			$homepage_behavior = $auhfc_settings['homepage']['behavior'];
			$dbg_set           = "type: homepage; bahavior: {$homepage_behavior}; priority: {$auhfc_settings['sitewide']['priority_h']}; do_shortcode: {$auhfc_settings['sitewide']['do_shortcode']}";
		}
	}

	// If no code to inject, simply exit.
	if ( empty( $auhfc_settings['sitewide']['head'] ) && empty( $article_code ) && empty( $homepage_code ) ) {
		return;
	}

	// Prepare code output.
	$out = '';

	// Inject site-wide head code.
	if (
		! empty( $auhfc_settings['sitewide']['head'] ) &&
		auhfc_print_sitewide( $article_behavior, $auhfc_post_type, $auhfc_settings['article']['post_types'], $article_code, $homepage_behavior, $homepage_code )
	) {
		$out .= auhfc_out( 's', 'h', $dbg_set, $auhfc_settings['sitewide']['head'] );
	}

	// Inject head code for Homepage in Blog Posts omde OR article specific (for allowed post_type) head code.
	if ( $is_homepage_blog_posts && ! empty( $homepage_code ) ) {
		$out .= auhfc_out( 'h', 'h', $dbg_set, $homepage_code );
	} else if ( ! empty( $article_code ) && in_array( $auhfc_post_type, $auhfc_settings['article']['post_types'] ) ) {
		$out .= auhfc_out( 'a', 'h', $dbg_set, $article_code );
	}

	// Print prepared code.
	echo $out;
	// echo ( 'y' === $auhfc_settings['sitewide']['do_shortcode'] ) ? do_shortcode( $out ) : $out;

} // END function auhfc_wp_head()

/**
 * Inject site-wide and Article specific body code right after opening <body>
 */
function auhfc_wp_body() {

	// Get post type.
	$auhfc_post_type = auhfc_get_post_type();

	// Get variables to test.
	$auhfc_settings         = auhfc_settings();
	$is_homepage_blog_posts = auhfc_is_homepage_blog_posts();
	$homepage_behavior      = false;
	$homepage_code          = '';

	// Get meta for post only if it's singular.
	if ( 'not singular' !== $auhfc_post_type && in_array( $auhfc_post_type, $auhfc_settings['article']['post_types'] ) ) {
		$article_code     = auhfc_get_meta( 'body' );
		$article_behavior = auhfc_get_meta( 'behavior' );
		$dbg_set          = "type: {$auhfc_post_type}; bahavior: {$article_behavior}; priority: {$auhfc_settings['sitewide']['priority_b']}; do_shortcode: {$auhfc_settings['sitewide']['do_shortcode']}";
	} else {
		$article_code     = '';
		$article_behavior = '';
		$dbg_set          = $auhfc_post_type;
		// Get meta for homepage.
		if ( $is_homepage_blog_posts ) {
			$homepage_code     = $auhfc_settings['homepage']['body'];
			$homepage_behavior = $auhfc_settings['homepage']['behavior'];
			$dbg_set           = "type: homepage; bahavior: {$homepage_behavior}; priority: {$auhfc_settings['sitewide']['priority_b']}; do_shortcode: {$auhfc_settings['sitewide']['do_shortcode']}";
		}
	}

	// If no code to inject, simple exit.
	if ( empty( $auhfc_settings['sitewide']['body'] ) && empty( $article_code ) && empty( $homepage_code ) ) {
		return;
	}

	// Prepare code output.
	$out = '';

	// Inject site-wide body code.
	if (
		! empty( $auhfc_settings['sitewide']['body'] ) &&
		auhfc_print_sitewide( $article_behavior, $auhfc_post_type, $auhfc_settings['article']['post_types'], $article_code, $homepage_behavior, $homepage_code )
	) {
		$out .= auhfc_out( 's', 'b', $dbg_set, $auhfc_settings['sitewide']['body'] );
	}

	// Inject head code for Homepage in Blog Posts omde OR article specific (for allowed post_type) body code.
	if ( $is_homepage_blog_posts && ! empty( $homepage_code ) ) {
		$out .= auhfc_out( 'h', 'b', $dbg_set, $homepage_code );
	} else if ( ! empty( $article_code ) && in_array( $auhfc_post_type, $auhfc_settings['article']['post_types'] ) ) {
		$out .= auhfc_out( 'a', 'b', $dbg_set, $article_code );
	}

	// Print prepared code.
	echo $out;
	// echo ( 'y' === $auhfc_settings['sitewide']['do_shortcode'] ) ? do_shortcode( $out ) : $out;

} // END function auhfc_wp_body()

/**
 * Inject site-wide and Article specific footer code before the </body>
 */
function auhfc_wp_footer() {

	// Get post type.
	$auhfc_post_type = auhfc_get_post_type();

	// Get variables to test.
	$auhfc_settings         = auhfc_settings();
	$is_homepage_blog_posts = auhfc_is_homepage_blog_posts();
	$homepage_behavior      = false;
	$homepage_code          = '';

	// Get meta for post only if it's singular.
	if ( 'not singular' !== $auhfc_post_type && in_array( $auhfc_post_type, $auhfc_settings['article']['post_types'] ) ) {
		$article_code     = auhfc_get_meta( 'footer' );
		$article_behavior = auhfc_get_meta( 'behavior' );
		$dbg_set          = "type: {$auhfc_post_type}; bahavior: {$article_behavior}; priority: {$auhfc_settings['sitewide']['priority_f']}; do_shortcode: {$auhfc_settings['sitewide']['do_shortcode']}";
	} else {
		$article_code     = '';
		$article_behavior = '';
		$dbg_set          = $auhfc_post_type;
		// Get meta for homepage.
		if ( $is_homepage_blog_posts ) {
			$homepage_code     = $auhfc_settings['homepage']['footer'];
			$homepage_behavior = $auhfc_settings['homepage']['behavior'];
			$dbg_set           = "type: homepage; bahavior: {$homepage_behavior}; priority: {$auhfc_settings['sitewide']['priority_f']}; do_shortcode: {$auhfc_settings['sitewide']['do_shortcode']}";
		}
	}

	// If no code to inject, simple exit.
	if ( empty( $auhfc_settings['sitewide']['footer'] ) && empty( $article_code ) && empty( $homepage_code ) ) {
		return;
	}

	// Prepare code output.
	$out = '';

	// Inject site-wide head code.
	if (
		! empty( $auhfc_settings['sitewide']['footer'] ) &&
		auhfc_print_sitewide( $article_behavior, $auhfc_post_type, $auhfc_settings['article']['post_types'], $article_code, $homepage_behavior, $homepage_code )
	) {
		$out .= auhfc_out( 's', 'f', $dbg_set, $auhfc_settings['sitewide']['footer'] );
	}

	// Inject head code for Homepage in Blog Posts omde OR article specific (for allowed post_type) footer code.
	if ( $is_homepage_blog_posts && ! empty( $homepage_code ) ) {
		$out .= auhfc_out( 'h', 'f', $dbg_set, $homepage_code );
	} else if ( ! empty( $article_code ) && in_array( $auhfc_post_type, $auhfc_settings['article']['post_types'] ) ) {
		$out .= auhfc_out( 'a', 'f', $dbg_set, $article_code );
	}

	// Print prepared code.
	echo ( 'y' === $auhfc_settings['sitewide']['do_shortcode'] ) ? do_shortcode( $out ) : $out;

} // END function auhfc_wp_footer()

/**
 * Add `wp_body_open` backward compatibility for WordPress installations prior 5.2
 */
if ( ! function_exists( 'wp_body_open' ) ) {
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
}
