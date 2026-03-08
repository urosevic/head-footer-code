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

	/** @var array Cached page context to avoid repeated function calls. */
	private $page_context = null;

	/**
	 * Location constants
	 */
	const LOCATION_HEAD   = 'head';
	const LOCATION_BODY   = 'body';
	const LOCATION_FOOTER = 'footer';

	/**
	 * Scope constants
	 */
	const SCOPE_SITEWIDE = 's';
	const SCOPE_ARTICLE  = 'a';
	const SCOPE_HOMEPAGE = 'h';
	const SCOPE_TAXONOMY = 't';

	/**
	 * Initializes the class and registers frontend hooks.
	 *
	 * @param Plugin_Info $plugin Instance of the plugin info object.
	 * @param array       $settings Plugin settings array.
	 */
	public function __construct( Plugin_Info $plugin, $settings ) {
		$this->plugin       = $plugin;
		$this->settings     = $settings;
		$this->allowed_html = Common::allowed_html();

		// Set default priorities if not defined.
		$this->initialize_priorities();

		// Define actions for HEAD, BODY and FOOTER.
		add_action( 'wp_head', array( $this, 'wp_head' ), $this->settings['sitewide']['priority_h'] );
		add_action( 'wp_body_open', array( $this, 'wp_body' ), $this->settings['sitewide']['priority_b'] );
		add_action( 'wp_footer', array( $this, 'wp_footer' ), $this->settings['sitewide']['priority_f'] );
	}

	/**
	 * Initialize priority settings with defaults.
	 */
	private function initialize_priorities() {
		if ( empty( $this->settings['sitewide']['priority_h'] ) ) {
			$this->settings['sitewide']['priority_h'] = 10;
		}
		if ( empty( $this->settings['sitewide']['priority_b'] ) ) {
			$this->settings['sitewide']['priority_b'] = 10;
		}
		if ( empty( $this->settings['sitewide']['priority_f'] ) ) {
			$this->settings['sitewide']['priority_f'] = 10;
		}
	}

	/**
	 * Get and cache page context information.
	 *
	 * @return array Page context data.
	 */
	private function get_page_context() {
		if ( null !== $this->page_context ) {
			return $this->page_context;
		}

		$this->page_context = array(
			'singular_post_type'     => Common::get_singular_post_type(),
			'is_homepage_blog_posts' => Common::is_homepage_blog_posts(),
			'is_supported_post_type' => Common::is_supported_singular_post_type(),
			'is_supported_taxonomy'  => Common::is_supported_taxonomy(),
			'is_paged'               => is_paged(),
		);

		return $this->page_context;
	}

	/**
	 * Inject site-wide, Homepage, Article specific and Taxonomy specific head code before `</head>`
	 */
	public function wp_head() {
		$this->inject_code( self::LOCATION_HEAD );
	}

	/**
	 * Inject site-wide, Homepage, Article specific and Taxonomy specific body code after opening `<body>`
	 */
	public function wp_body() {
		$this->inject_code( self::LOCATION_BODY );
	}

	/**
	 * Inject site-wide, Homepage, Article specific and Taxonomy specific footer code before the `</body>`
	 */
	public function wp_footer() {
		$this->inject_code( self::LOCATION_FOOTER );
	}

	/**
	 * Generic code injection handler for all locations.
	 *
	 * @param string $location The location to inject code (head, body, or footer).
	 */
	private function inject_code( $location ) {
		$context = $this->get_page_context();

		// Get location-specific configuration.
		$config = $this->get_location_config( $location );

		// Determine what code to inject based on context.
		$injection_data = $this->determine_injection_data( $location, $context, $config );

		// Early exit if nothing to inject.
		if ( empty( $this->settings['sitewide'][ $location ] ) && empty( $injection_data['code'] ) ) {
			return;
		}

		// Build output.
		$output = $this->build_output( $location, $injection_data, $context );

		// Print with optional shortcode processing.
		echo 'y' === $config['do_shortcode']
			? do_shortcode( $output )
			: $output;
			// We do not use wp_kses( $output, $this->allowed_html );
			// because that would escape <, > and & which is already sanitized on entry.
	}

	/**
	 * Get location-specific configuration.
	 *
	 * @param string $location The location (head, body, or footer).
	 * @return array Configuration array.
	 */
	private function get_location_config( $location ) {
		$location_char = substr( $location, 0, 1 ); // h, b, or f

		return array(
			'priority'     => $this->settings['sitewide'][ 'priority_' . $location_char ],
			'do_shortcode' => $this->settings['sitewide'][ 'do_shortcode_' . $location_char ],
		);
	}

	/**
	 * Determine what code should be injected based on current context.
	 *
	 * @param string $location The location (head, body, or footer).
	 * @param array  $context Page context data.
	 * @param array  $config Location configuration.
	 * @return array Injection data with behavior, code, debug info, and scope.
	 */
	private function determine_injection_data( $location, $context, $config ) {
		$behavior = 'none';
		$code     = '';
		$dbg_set  = $context['singular_post_type'];
		$scope    = '';

		// Singular post (post, page, CPT).
		if ( $context['singular_post_type'] && $context['is_supported_post_type'] ) {
			$behavior = Common::get_meta_auto( 'behavior' );
			$code     = Common::get_meta_auto( $location );
			$scope    = self::SCOPE_ARTICLE;
			$dbg_set  = sprintf(
				'type: %s; behavior: %s; priority: %s; do_shortcode_%s: %s',
				$context['singular_post_type'],
				$behavior,
				$config['priority'],
				substr( $location, 0, 1 ),
				$config['do_shortcode']
			);
		} elseif ( $context['is_supported_taxonomy'] ) {
			// Taxonomy (category, tag, custom taxonomy).
			$tax_object = get_queried_object();
			$behavior   = Common::get_meta_auto( 'behavior', 'term' );
			$code       = Common::get_meta_auto( $location, 'term' );
			$scope      = self::SCOPE_TAXONOMY;
			$dbg_set    = sprintf(
				'type: %s; behavior: %s; priority: %s; do_shortcode_%s: %s',
				$tax_object->taxonomy,
				$behavior,
				$config['priority'],
				substr( $location, 0, 1 ),
				$config['do_shortcode']
			);
		} elseif ( $context['is_homepage_blog_posts'] ) {
			// Homepage in blog posts mode.
			$add_to_homepage_paged = Common::is_addable_to_paged_homepage(
				$context['is_homepage_blog_posts'],
				$this->settings
			);
			$behavior              = $this->settings['homepage']['behavior'];
			$code                  = $add_to_homepage_paged ? $this->settings['homepage'][ $location ] : ' ';
			$scope                 = self::SCOPE_HOMEPAGE;
			$dbg_set               = sprintf(
				'type: homepage; behavior: %s; is_paged: %s; add_on_paged: %s; priority: %s; do_shortcode_%s: %s',
				$behavior,
				$context['is_paged'] ? 'yes' : 'no',
				$this->settings['homepage']['paged'],
				$config['priority'],
				substr( $location, 0, 1 ),
				$config['do_shortcode']
			);
		}

		return array(
			'behavior' => $behavior,
			'code'     => $code,
			'dbg_set'  => $dbg_set,
			'scope'    => $scope,
		);
	}

	/**
	 * Build the final output string.
	 *
	 * @param string $location The location (head, body, or footer).
	 * @param array  $injection_data Injection data from determine_injection_data().
	 * @param array  $context Page context data.
	 * @return string The composed output.
	 */
	private function build_output( $location, $injection_data, $context ) {
		$output       = '';
		$location_key = substr( $location, 0, 1 ); // h, b, or f

		// Inject site-wide code if appropriate.
		if (
			! empty( $this->settings['sitewide'][ $location ] ) &&
			Common::is_printable_sitewide(
				$injection_data['behavior'],
				$injection_data['code'],
				$context['singular_post_type'],
				$this->settings['article']['post_types'],
				$context['is_supported_taxonomy']
			)
		) {
			$output .= Common::annotate_code_block(
				self::SCOPE_SITEWIDE,
				$location_key,
				$injection_data['dbg_set'],
				$this->settings['sitewide'][ $location ]
			);
		}

		// Inject context-specific code (homepage, article, or taxonomy).
		if ( ! empty( $injection_data['code'] ) && ! empty( $injection_data['scope'] ) ) {
			$output .= Common::annotate_code_block(
				$injection_data['scope'],
				$location_key,
				$injection_data['dbg_set'],
				$injection_data['code']
			);
		}

		return $output;
	}
}
