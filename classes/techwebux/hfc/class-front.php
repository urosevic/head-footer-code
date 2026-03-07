<?php
/**
 * Frontend code injector.
 *
 * Handles output of scripts/styles in `<head>`, start of `<body>`, and before
 * `</body>` across various site contexts (site-wide, singular, archive, home).
 *
 * @package   Head_Footer_Code
 * @since     1.0.0
 */

namespace Techwebux\Hfc;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Front
 *
 * Conditionaly output code snippets on frontend
 */
class Front {
	/** @var array Settings retrieved from the main controller. */
	private $settings;

	/** @var Plugin_Info Plugin metadata object. */
	protected $plugin;

	/** @var array Allowed HTML tags for sanitization. */
	public $allowed_html;

	/**
	 * Initializes the class and registers frontend hooks.
	 *
	 * @param Plugin_Info $plugin Instance of the plugin info object.
	 */
	public function __construct( Plugin_Info $plugin ) {
		$this->plugin       = $plugin;
		$this->settings     = Main::settings();
		$this->allowed_html = Common::allowed_html();

		/**
		 * Inject site-wide code to head, body and footer with custom priorty.
		 */
		if ( empty( $this->settings['sitewide']['priority_h'] ) ) {
			$this->settings['sitewide']['priority_h'] = 10;
		}
		if ( empty( $this->settings['sitewide']['priority_b'] ) ) {
			$this->settings['sitewide']['priority_b'] = 10;
		}
		if ( empty( $this->settings['sitewide']['priority_f'] ) ) {
			$this->settings['sitewide']['priority_f'] = 10;
		}

		// Define actions for HEAD, BODY and FOOTER.
		add_action( 'wp_head', array( $this, 'wp_head' ), $this->settings['sitewide']['priority_h'] );
		add_action( 'wp_body_open', array( $this, 'wp_body' ), $this->settings['sitewide']['priority_b'] );
		add_action( 'wp_footer', array( $this, 'wp_footer' ), $this->settings['sitewide']['priority_f'] );
	}

	/**
	 * Inject site-wide and Homepage or Article specific head code before </head>
	 */
	public function wp_head() {
		// Get variables to test.
		$head_behavior          = 'none';
		$head_code              = '';
		$is_paged               = is_paged() ? 'yes' : 'no';
		$post_type              = Common::get_post_type();
		$is_homepage_blog_posts = Common::is_homepage_blog_posts();

		$dbg_set = $post_type;

		if ( 'not singular' !== $post_type && in_array( $post_type, $this->settings['article']['post_types'], true ) ) {
			// Get meta for singular article.
			$head_behavior = Common::get_meta_auto( 'behavior' );
			$head_code     = Common::get_meta_auto( 'head' );
			$dbg_set       = "type: {$post_type}; bahavior: {$head_behavior}; priority: {$this->settings['sitewide']['priority_h']}; do_shortcode_h: {$this->settings['sitewide']['do_shortcode_h']}";
		} elseif ( is_category() ) {
			// Get meta for category.
			$head_behavior = Common::get_meta_auto( 'behavior', 'term' );
			$head_code     = Common::get_meta_auto( 'head', 'term' );
			$dbg_set       = "type: category; bahavior: {$head_behavior}; priority: {$this->settings['sitewide']['priority_h']}; do_shortcode_h: {$this->settings['sitewide']['do_shortcode_h']}";
		} else {
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
	}

	/**
	 * Inject site-wide and Article specific body code right after opening <body>
	 */
	public function wp_body() {
		// Get variables to test.
		$body_behavior          = 'none';
		$body_code              = '';
		$is_paged               = is_paged() ? 'yes' : 'no';
		$post_type              = Common::get_post_type();
		$is_homepage_blog_posts = Common::is_homepage_blog_posts();

		$dbg_set = $post_type;

		if ( 'not singular' !== $post_type && in_array( $post_type, $this->settings['article']['post_types'], true ) ) {
			// Get meta for singular article.
			$body_behavior = Common::get_meta_auto( 'behavior' );
			$body_code     = Common::get_meta_auto( 'body' );
			$dbg_set       = "type: {$post_type}; bahavior: {$body_behavior}; priority: {$this->settings['sitewide']['priority_b']}; do_shortcode_b: {$this->settings['sitewide']['do_shortcode_b']}";
		} elseif ( is_category() ) {
			// Get meta for category.
			$body_behavior = Common::get_meta_auto( 'behavior', 'term' );
			$body_code     = Common::get_meta_auto( 'body', 'term' );
			$dbg_set       = "type: category; bahavior: {$body_behavior}; priority: {$this->settings['sitewide']['priority_b']}; do_shortcode_b: {$this->settings['sitewide']['do_shortcode_b']}";
		} else {
			// Get meta for homepage.
			if ( $is_homepage_blog_posts ) {
				$add_to_homepage_paged = Common::add_to_homepage_paged( $is_homepage_blog_posts, $this->settings );
				$body_behavior         = $this->settings['homepage']['behavior'];
				$body_code             = $add_to_homepage_paged ? $this->settings['homepage']['body'] : ' ';
				$dbg_set               = "type: homepage; bahavior: {$body_behavior}; is_paged: {$is_paged}; add_on_paged: {$this->settings['homepage']['paged']}; priority: {$this->settings['sitewide']['priority_b']}; do_shortcode_b: {$this->settings['sitewide']['do_shortcode_b']}";
			}
		}

		// If no code to inject, exit.
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
	}

	/**
	 * Inject site-wide and Article specific footer code before the </body>
	 */
	public function wp_footer() {
		// Get variables to test.
		$footer_behavior        = 'none';
		$footer_code            = '';
		$is_paged               = is_paged() ? 'yes' : 'no';
		$post_type              = Common::get_post_type();
		$is_homepage_blog_posts = Common::is_homepage_blog_posts();

		$dbg_set = $post_type;

		if ( 'not singular' !== $post_type && in_array( $post_type, $this->settings['article']['post_types'], true ) ) {
			// Get meta for singular article.
			$footer_behavior = Common::get_meta_auto( 'behavior' );
			$footer_code     = Common::get_meta_auto( 'footer' );
			$dbg_set         = "type: {$post_type}; bahavior: {$footer_behavior}; priority: {$this->settings['sitewide']['priority_f']}; do_shortcode_f: {$this->settings['sitewide']['do_shortcode_f']}";
		} elseif ( is_category() ) {
			// Get met for category.
			$footer_behavior = Common::get_meta_auto( 'behavior', 'term' );
			$footer_code     = Common::get_meta_auto( 'footer', 'term' );
			$dbg_set         = "type: category; bahavior: {$footer_behavior}; priority: {$this->settings['sitewide']['priority_f']}; do_shortcode_f: {$this->settings['sitewide']['do_shortcode_f']}";
		} else {
			// Get meta for homepage.
			if ( $is_homepage_blog_posts ) {
				$add_to_homepage_paged = Common::add_to_homepage_paged( $is_homepage_blog_posts, $this->settings );
				$footer_behavior       = $this->settings['homepage']['behavior'];
				$footer_code           = $add_to_homepage_paged ? $this->settings['homepage']['footer'] : ' ';
				$dbg_set               = "type: homepage; bahavior: {$footer_behavior}; is_paged: {$is_paged}; add_on_paged: {$this->settings['homepage']['paged']}; priority: {$this->settings['sitewide']['priority_f']}; do_shortcode_f: {$this->settings['sitewide']['do_shortcode_f']}";
			}
		}

		// If no code to inject, exit.
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
	}
}
