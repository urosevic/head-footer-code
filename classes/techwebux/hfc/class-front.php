<?php
/**
 * Frontend magic for Head & Footer Code
 *
 * @package Head_Footer_Code
 */

namespace Techwebux\Hfc;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Techwebux\Hfc\Common;

class Front {
	private $settings;
	public $allowed_html;

	public function __construct() {
		/**
		 * Inject site-wide code to head, body and footer with custom priorty.
		 */
		$this->settings = Main::settings();
		if ( empty( $this->settings['sitewide']['priority_h'] ) ) {
			$this->settings['sitewide']['priority_h'] = 10;
		}
		if ( empty( $this->settings['sitewide']['priority_b'] ) ) {
			$this->settings['sitewide']['priority_b'] = 10;
		}
		if ( empty( $this->settings['sitewide']['priority_f'] ) ) {
			$this->settings['sitewide']['priority_f'] = 10;
		}

		$this->allowed_html = Common::allowed_html();

		// Define actions for HEAD and FOOTER.
		add_action( 'wp_head', array( $this, 'wp_head' ), $this->settings['sitewide']['priority_h'] );
		add_action( 'wp_body_open', array( $this, 'wp_body' ), $this->settings['sitewide']['priority_b'] );
		add_action( 'wp_footer', array( $this, 'wp_footer' ), $this->settings['sitewide']['priority_f'] );
	} // END public function __construct

	/**
	 * Inject site-wide and Homepage or Article specific head code before </head>
	 */
	public function wp_head() {
		// Get variables to test.
		$head_code              = '';
		$head_behavior          = 'none';
		$is_paged               = is_paged() ? 'yes' : 'no';
		$post_type              = Common::get_post_type();
		$is_homepage_blog_posts = Common::is_homepage_blog_posts();

		// Get meta for post only if it's singular.
		if ( 'not singular' !== $post_type && in_array( $post_type, $this->settings['article']['post_types'], true ) ) {
			$head_behavior = Common::get_meta( 'behavior' );
			$head_code     = Common::get_meta( 'head' );
			$dbg_set       = "type: {$post_type}; bahavior: {$head_behavior}; priority: {$this->settings['sitewide']['priority_h']}; do_shortcode_h: {$this->settings['sitewide']['do_shortcode_h']}";
		} elseif ( is_category() ) {
			// Get category (term) meta with get_term_meta().
			$category  = get_queried_object();
			$auhfc_cat = get_term_meta( $category->term_id, '_auhfc', true );
			if ( ! empty( $auhfc_cat ) ) {
				$head_behavior = $auhfc_cat['behavior'];
				$head_code     = $auhfc_cat['head'];
			}
			$dbg_set = "type: category; bahavior: {$head_behavior}; priority: {$this->settings['sitewide']['priority_h']}; do_shortcode_h: {$this->settings['sitewide']['do_shortcode_h']}";
		} else {
			$dbg_set = $post_type;

			// Get meta for homepage.
			if ( $is_homepage_blog_posts ) {
				$add_to_homepage_paged = Common::add_to_homepage_paged( $is_homepage_blog_posts, $this->settings );
				$head_behavior         = $this->settings['homepage']['behavior'];
				$head_code             = $add_to_homepage_paged ? $this->settings['homepage']['head'] : ' ';
				$dbg_set               = "type: homepage; bahavior: {$head_behavior}; is_paged: {$is_paged}; add_on_paged: {$this->settings['homepage']['paged']}; priority: {$this->settings['sitewide']['priority_h']}; do_shortcode_h: {$this->settings['sitewide']['do_shortcode_h']}";
			}
		}

		// If no code to inject, simply exit.
		if ( empty( $this->settings['sitewide']['head'] ) && empty( $head_code ) ) {
			return;
		}

		// Prepare code output.
		$out = '';

		// Inject site-wide head code.
		if (
			! empty( $this->settings['sitewide']['head'] ) &&
			Common::print_sitewide( $head_behavior, $head_code, $post_type, $this->settings['article']['post_types'], is_category() )
		) {
			$out .= Common::out( 's', 'h', $dbg_set, $this->settings['sitewide']['head'] );
		}

		// Inject head code for Homepage in Blog Posts mode OR article specific (for allowed post_type) head code OR category head code.
		if ( ! empty( $head_code ) ) {
			if ( $is_homepage_blog_posts ) {
				$out .= Common::out( 'h', 'h', $dbg_set, $head_code );
			} elseif ( in_array( $post_type, $this->settings['article']['post_types'], true ) ) {
				$out .= Common::out( 'a', 'h', $dbg_set, $head_code );
			} else {
				$out .= Common::out( 'c', 'h', $dbg_set, $head_code );
			}
		}

		// Print prepared code.
		echo 'y' === $this->settings['sitewide']['do_shortcode_h']
			? do_shortcode( $out )
			: $out;
			// We do not use wp_kses( $out, $this->allowed_html );
			// because that mess up <, > and & which is sanitized on entry
	} // END public function wp_head

	/**
	 * Inject site-wide and Article specific body code right after opening <body>
	 */
	public function wp_body() {
		// Get variables to test.
		$body_code              = '';
		$body_behavior          = 'none';
		$is_paged               = is_paged() ? 'yes' : 'no';
		$post_type              = Common::get_post_type();
		$is_homepage_blog_posts = Common::is_homepage_blog_posts();

		// Get meta for post only if it's singular.
		if ( 'not singular' !== $post_type && in_array( $post_type, $this->settings['article']['post_types'], true ) ) {
			$body_behavior = Common::get_meta( 'behavior' );
			$body_code     = Common::get_meta( 'body' );
			$dbg_set       = "type: {$post_type}; bahavior: {$body_behavior}; priority: {$this->settings['sitewide']['priority_b']}; do_shortcode_b: {$this->settings['sitewide']['do_shortcode_b']}";
		} elseif ( is_category() ) {
			// Get category (term) meta with get_term_meta().
			$category  = get_queried_object();
			$auhfc_cat = get_term_meta( $category->term_id, '_auhfc', true );
			if ( ! empty( $auhfc_cat ) ) {
				$body_behavior = $auhfc_cat['behavior'];
				$body_code     = $auhfc_cat['body'];
			}
			$dbg_set = "type: category; bahavior: {$body_behavior}; priority: {$this->settings['sitewide']['priority_b']}; do_shortcode_b: {$this->settings['sitewide']['do_shortcode_b']}";
		} else {
			$dbg_set = $post_type;
			// Get meta for homepage.
			if ( $is_homepage_blog_posts ) {
				$add_to_homepage_paged = Common::add_to_homepage_paged( $is_homepage_blog_posts, $this->settings );
				$body_behavior         = $this->settings['homepage']['behavior'];
				$body_code             = $add_to_homepage_paged ? $this->settings['homepage']['body'] : ' ';
				$dbg_set               = "type: homepage; bahavior: {$body_behavior}; is_paged: {$is_paged}; add_on_paged: {$this->settings['homepage']['paged']}; priority: {$this->settings['sitewide']['priority_b']}; do_shortcode_b: {$this->settings['sitewide']['do_shortcode_b']}";
			}
		}

		// If no code to inject, simple exit.
		if ( empty( $this->settings['sitewide']['body'] ) && empty( $body_code ) ) {
			return;
		}

		// Prepare code output.
		$out = '';

		// Inject site-wide body code.
		if (
			! empty( $this->settings['sitewide']['body'] ) &&
			Common::print_sitewide( $body_behavior, $body_code, $post_type, $this->settings['article']['post_types'], is_category() )
		) {
			$out .= Common::out( 's', 'b', $dbg_set, $this->settings['sitewide']['body'] );
		}

		// Inject body code for Homepage in Blog Posts mode OR article specific (for allowed post_type) body code OR category body code.
		if ( ! empty( $body_code ) ) {
			if ( $is_homepage_blog_posts ) {
				$out .= Common::out( 'h', 'b', $dbg_set, $body_code );
			} elseif ( in_array( $post_type, $this->settings['article']['post_types'], true ) ) {
				$out .= Common::out( 'a', 'b', $dbg_set, $body_code );
			} else {
				$out .= Common::out( 'c', 'b', $dbg_set, $body_code );
			}
		}

		// Print prepared code.
		echo 'y' === $this->settings['sitewide']['do_shortcode_b']
			? do_shortcode( $out )
			: $out;
			// We do not use wp_kses( $out, $this->allowed_html );
			// because that mess up <, > and & which is sanitized on entry
	} // END public function wp_body

	/**
	 * Inject site-wide and Article specific footer code before the </body>
	 */
	public function wp_footer() {
		// Get variables to test.
		$footer_code            = '';
		$footer_behavior        = 'none';
		$is_paged               = is_paged() ? 'yes' : 'no';
		$post_type              = Common::get_post_type();
		$is_homepage_blog_posts = Common::is_homepage_blog_posts();

		// Get meta for post only if it's singular.
		if ( 'not singular' !== $post_type && in_array( $post_type, $this->settings['article']['post_types'], true ) ) {
			$footer_code     = Common::get_meta( 'footer' );
			$footer_behavior = Common::get_meta( 'behavior' );
			$dbg_set         = "type: {$post_type}; bahavior: {$footer_behavior}; priority: {$this->settings['sitewide']['priority_f']}; do_shortcode_f: {$this->settings['sitewide']['do_shortcode_f']}";
		} elseif ( is_category() ) {
			// Get category (term) meta with get_term_meta().
			$category  = get_queried_object();
			$auhfc_cat = get_term_meta( $category->term_id, '_auhfc', true );
			if ( ! empty( $auhfc_cat ) ) {
				$footer_behavior = $auhfc_cat['behavior'];
				$footer_code     = $auhfc_cat['footer'];
			}
			$dbg_set = "type: category; bahavior: {$footer_behavior}; priority: {$this->settings['sitewide']['priority_f']}; do_shortcode_f: {$this->settings['sitewide']['do_shortcode_f']}";
		} else {
			$dbg_set = $post_type;
			// Get meta for homepage.
			if ( $is_homepage_blog_posts ) {
				$add_to_homepage_paged = Common::add_to_homepage_paged( $is_homepage_blog_posts, $this->settings );
				$footer_code           = $add_to_homepage_paged ? $this->settings['homepage']['footer'] : ' ';
				$footer_behavior       = $this->settings['homepage']['behavior'];
				$dbg_set               = "type: homepage; bahavior: {$footer_behavior}; is_paged: {$is_paged}; add_on_paged: {$this->settings['homepage']['paged']}; priority: {$this->settings['sitewide']['priority_f']}; do_shortcode_f: {$this->settings['sitewide']['do_shortcode_f']}";
			}
		}

		// If no code to inject, simple exit.
		if ( empty( $this->settings['sitewide']['footer'] ) && empty( $footer_code ) ) {
			return;
		}

		// Prepare code output.
		$out = '';

		// Inject site-wide footer code.
		if (
			! empty( $this->settings['sitewide']['footer'] ) &&
			Common::print_sitewide( $footer_behavior, $footer_code, $post_type, $this->settings['article']['post_types'], is_category() )
		) {
			$out .= Common::out( 's', 'f', $dbg_set, $this->settings['sitewide']['footer'] );
		}

		// Inject footer code for Homepage in Blog Posts mode OR article specific (for allowed post_type) footer code OR category footer code.
		if ( ! empty( $footer_code ) ) {
			if ( $is_homepage_blog_posts ) {
				$out .= Common::out( 'h', 'f', $dbg_set, $footer_code );
			} elseif ( in_array( $post_type, $this->settings['article']['post_types'], true ) ) {
				$out .= Common::out( 'a', 'f', $dbg_set, $footer_code );
			} else {
				$out .= Common::out( 'c', 'f', $dbg_set, $footer_code );
			}
		}

		// Print prepared code.
		echo 'y' === $this->settings['sitewide']['do_shortcode_f']
			? do_shortcode( $out )
			: $out;
			// We do not use wp_kses( $out, $this->allowed_html );
			// because that mess up <, > and & which is sanitized on entry
	} // END public function wp_footer
} // END class Front
