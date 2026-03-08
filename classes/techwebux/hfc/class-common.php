<?php
/**
 * Utility functions collection.
 *
 * Provides shared helper methods and reusable logic used across
 * different components of the plugin.
 *
 * @package   Head_Footer_Code
 * @since     1.4.0
 */

namespace Techwebux\Hfc;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Common {
	/** @var array Settings retrieved from the main controller. */
	private static $settings = null;

	/** @var Plugin_Info Plugin metadata object. */
	protected static $plugin;

	/**
	 * Injects the plugin metadata object into the Common utility class.
	 *
	 * This allows static methods to access plugin properties like name,
	 * version, and directory without needing global constants.
	 *
	 * @param Plugin_Info $plugin The plugin metadata container.
	 * @return void
	 */
	public static function init( Plugin_Info $plugin ) {
		self::$plugin = $plugin;
	}

	/**
	 * Initialize settings if not already set.
	 *
	 * @return void
	 */
	private static function init_settings() {
		if ( null === self::$settings ) {
			self::$settings = Main::get_settings();
		}
	}

	/**
	 * Check if the current user has any of the allowed roles.
	 *
	 * @return bool
	 */
	public static function user_has_allowed_role() {
		// Ensure settings are initialized.
		self::init_settings();

		// Always allow Super Admin (Multisite)
		$current_user = wp_get_current_user();
		if ( is_super_admin( $current_user->ID ) ) {
			return true;
		}

		// Get current user roles
		$user_roles = (array) $current_user->roles;

		// Merge fixed always-allowed and configurable allowed roles
		$allowed_roles = array_merge(
			array( 'administrator', 'shop_manager' ),
			self::$settings['article']['allowed_roles']
		);

		// Check if any of user's roles are in the allowed list
		return (bool) array_intersect( $user_roles, $allowed_roles );
	}

	/**
	 * Check if homepage uses Blog mode
	 *
	 * @return bool
	 */
	public static function is_homepage_blog_posts() {
		if ( is_home() && 'posts' === get_option( 'show_on_front', false ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Check if the current singular post type is enabled in plugin settings.
	 *
	 * @return bool
	 */
	public static function is_supported_singular_post_type() {
		// Ensure settings are initialized.
		self::init_settings();

		$singular_post_type = self::get_singular_post_type();

		return $singular_post_type && in_array( $singular_post_type, self::$settings['article']['post_types'], true );
	}

	/**
	 * Check if current queried request is on supported taxonomy page
	 *
	 * @return bool
	 */
	public static function is_supported_taxonomy() {
		// Ensure settings are initialized.
		self::init_settings();

		$queried_object = get_queried_object();

		return ( is_category() || is_tag() || is_tax() )
			&& isset( $queried_object->taxonomy )
			&& in_array( $queried_object->taxonomy, self::$settings['article']['taxonomies'], true );
	}

	/**
	 * Determine should we print site-wide code
	 * or it should be replaced with homepage/article/taxonomy code.
	 *
	 * @param  string  $behavior       Behavior for article specific code (replace/append).
	 * @param  string  $code           Article specific custom code.
	 * @param  string  $post_type      Post type of current article.
	 * @param  array   $post_types     Array of post types where article specific code is enabled.
	 * @param  boolean $is_taxonomy    Indicate if current displayed page is taxonomy or not.
	 * @return boolean                 Boolean that determine should site-wide code be printed (true) or not (false).
	 */
	public static function is_printable_sitewide(
		$behavior = 'append',
		$code = '',
		$post_type = null,
		$post_types = array(),
		$is_taxonomy = false
	) {
		// On homepage print site wide if...
		$is_homepage_blog_posts = self::is_homepage_blog_posts();
		if ( $is_homepage_blog_posts ) {
			// ... homepage behavior is not replace, or...
			// ... homepage behavior is replace but homepage code is empty.
			if (
				'replace' !== $behavior
				|| ( 'replace' === $behavior && empty( $code ) )
			) {
				return true;
			}
		} elseif ( $is_taxonomy ) { // On taxonomy page print site wide if...
			// ... behavior is not replace, or...
			// ... behavior is replace but taxonomy content is empty.
			if (
				'replace' !== $behavior
				|| ( 'replace' === $behavior && empty( $code ) )
			) {
				return true;
			}
		} elseif ( // On Blog Post or Custom Post Type ...
			// ... article behavior is not replace, or...
			// ... article behavior is replace but current Post Type is not in allowed Post Types, or...
			// ... article behavior is replace and current Post Type is in allowed Post Types but article code is empty.
			'replace' !== $behavior
			|| ( 'replace' === $behavior && ! in_array( $post_type, $post_types, true ) )
			|| ( 'replace' === $behavior && in_array( $post_type, $post_types, true ) && empty( $code ) )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Function to check if code should be added on paged homepage in Blog mode
	 *
	 * @param bool  $is_homepage_blog_posts If current page is blog homepage
	 *
	 * @return bool
	 */
	public static function is_addable_to_paged_homepage( $is_homepage_blog_posts ) {
		// Ensure settings are initialized.
		self::init_settings();

		if (
			true === $is_homepage_blog_posts
			&& is_paged()
			&& ! empty( self::$settings['homepage']['paged'] )
			&& 'no' === self::$settings['homepage']['paged']
		) {
			return false;
		}
		return true;
	}

	/**
	 * Sanitizes an HTML classnames to ensure it only contains valid characters.
	 *
	 * Strips the string down to A-Z,a-z,0-9,_,-, . If this results in an empty
	 * string then it will return the alternative value supplied.
	 *
	 * @param string $classes    The classnames to be sanitized (multiple classnames separated by space)
	 * @param string $fallback   Optional. The value to return if the sanitization ends up as an empty string.
	 *                           Defaults to an empty string.
	 *
	 * @return string            The sanitized value
	 */
	public static function sanitize_html_classes( $classes, $fallback = '' ) {
		// Strip out any %-encoded octets.
		$sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $classes );

		// Limit to A-Z, a-z, 0-9, '_', '-' and ' ' (for multiple classes).
		$sanitized = trim( preg_replace( '/[^A-Za-z0-9\_\ \-]/', '', $sanitized ) );

		if ( '' === $sanitized && $fallback ) {
			return self::sanitize_html_classes( $fallback );
		}

		return $sanitized;
	}

	/**
	 * Defines the expanded schema of allowed HTML tags and attributes for KSES.
	 *
	 * Extends the default `post` global with specific attributes required for
	 * modern tracking scripts, preloading (fetchpriority, imagesrcset),
	 * and security (nonce, integrity).
	 *
	 * @return array Map of allowed tags and their permitted attributes.
	 */
	public static function allowed_html() {
		// Allow safe HTML, JS, and CSS.
		return array_replace_recursive(
			wp_kses_allowed_html( 'post' ), // Allow safe HTML for posts.
			array(
				'noscript' => true,
				// Allow <script> tags.
				'script'   => array(
					'type'        => true,
					'async'       => true,
					'defer'       => true,
					'src'         => true, // remote
					'crossorigin' => true, // security
					'nonce'       => true, // security
					'charset'     => true,
				),
				// Allow <style> tags.
				'style'    => array(
					'media'  => true,
					'type'   => true,
					'scoped' => true,
					'nonce'  => true,
				),
				// Allow <link> tags for CSS and preloading.
				'link'     => array(
					'href'           => true,
					'rel'            => true,
					'media'          => true,
					'hreflang'       => true,
					'type'           => true,
					'sizes'          => true,
					'title'          => true,
					'fetchpriority'  => true, // preload
					'as'             => true, // preload
					'imagesrcset'    => true, // preload for images https://developer.mozilla.org/en-US/docs/Web/API/HTMLLinkElement/imageSrcset
					'imagesizes'     => true, // preload for images https://developer.mozilla.org/en-US/docs/Web/API/HTMLLinkElement/imageSizes
					'crossorigin'    => true, // security
					'nonce'          => true, // security
					'itemprop'       => true, // for structured data
					'referrerpolicy' => true, // security
					'integrity'      => true, // security
				),
				// Allow <meta> tags.
				'meta'     => array(
					'name'       => true,
					'http-equiv' => true,
					'content'    => true,
					'charset'    => true,
					'itemprop'   => true,
					'media'      => true,
					'property'   => true,
				),
				// Allow <noscript> and <iframe> for GTag and custom
				'noscript' => true,
				'iframe'   => array(
					// standard
					'src'      => true,
					'srcdoc'   => true,
					'name'     => true,
					'sandbox'  => true,
					'seamless' => true,
					'width'    => true,
					'height'   => true,
					// global
					'class'    => true,
					'hidden'   => true,
					'id'       => true,
					'style'    => true,
					'loading'  => true,
				),
			)
		);
	}

	/**
	 * Define allowed FORM HTML for wp_kses
	 *
	 * @return array
	 */
	public static function form_allowed_html() {
		return array(
			'fieldset' => array(),
			'label'    => array(
				'for' => array(),
			),
			'input'    => array(
				'type'      => array(),
				'name'      => array(),
				'id'        => array(),
				'value'     => array(),
				'class'     => array(),
				'min'       => array(), // number
				'max'       => array(), // number
				'step'      => array(), // number
				'checked'   => array(), // checkbox
				'required'  => true,
				'minlength' => true,
				'maxlength' => true,
				'size'      => true,
			),
			'select'   => array(
				'id'    => array(),
				'name'  => array(),
				'class' => array(),
			),
			'option'   => array(
				'value'    => array(),
				'selected' => array(),
			),
			'textarea' => array(
				'name'  => array(),
				'id'    => array(),
				'rows'  => array(),
				'class' => array(),
				'title' => array(),
				'style' => array(),
			),
			/*
			'div'      => array(
				'class' => true,
			),
			*/
			'p'        => array(
				'class' => true,
			),
			'a'        => array(
				'href'   => array(),
				'target' => array( '_blank' ),
				'class'  => true,
				'title'  => true,
			),
			'code'     => array(), // No attributes for the <code> tag
			'br'       => array(),
			'strong'   => array(),
			'em'       => array(),
			'pre'      => array(),
			'span'     => array(
				'class' => true,
			),
			'i'        => true,
		);
	}

	/**
	 * Sanitizes HTML content while preserving script and style tag integrity.
	 *
	 * This method employs a placeholder strategy: it extracts `<script>` and `<style>`
	 * blocks, sanitizes their attributes, and hides them from `wp_kses()` to prevent
	 * the stripping of valid JS/CSS logic. After the remaining HTML is sanitized,
	 * the blocks are reinstated.
	 *
	 * @param  string $content The raw HTML/JS/CSS content to sanitize.
	 * @return string          Sanitized content with preserved safe scripts/styles.
	 */
	public static function sanitize_html_with_scripts( $content ) {
		$allowed_html = self::allowed_html();
		$placeholders = array();

		$regex = '#<(script|style)\b[^>]*>.*?</\1>#is';

		$content = preg_replace_callback(
			$regex,
			function ( $matches ) use ( &$placeholders, $allowed_html ) {
				$full_tag = $matches[0];
				$tag_name = strtolower( $matches[1] ); // script or style

				// Extract opening tag for improved security, eg. <script onload="…">
				if ( preg_match( '/^<' . $tag_name . '[^>]*>/i', $full_tag, $tag_match ) ) {
					$opening_tag           = $tag_match[0];
					$sanitized_opening_tag = wp_kses( $opening_tag, array( $tag_name => $allowed_html[ $tag_name ] ) );
					if ( ! empty( $sanitized_opening_tag ) ) {
						$full_tag = str_replace( $opening_tag, $sanitized_opening_tag, $full_tag );
					}
				}

				$placeholder                  = '__TWU_' . strtoupper( $tag_name ) . '_PLACEHOLDER_' . count( $placeholders ) . '__';
				$placeholders[ $placeholder ] = $full_tag;

				return $placeholder;
			},
			$content
		);

		// Sanitize rest of content (outside scripts/styles)
		$content = wp_kses( $content, $allowed_html );

		if ( ! empty( $placeholders ) ) {
			$content = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $content );
		}

		return $content;
	}

	/**
	 * Sanitize the whole HFC data array (for posts, terms, settings)
	 *
	 * @since 1.5.0
	 *
	 * @param array $input The raw $_POST['auhfc'] data.
	 * @return array Sanitized data.
	 */
	public static function sanitize_hfc_data( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}

		// Temporarily remove Jetpack filter that may interfere with wp_kses.
		$jetpack_filter = array( 'Filter_Embedded_HTML_Objects', 'maybe_create_links' );
		$has_jetpack    = is_callable( $jetpack_filter );

		if ( $has_jetpack ) {
			remove_filter( 'pre_kses', $jetpack_filter, 100 );
		}

		// Build sanitized data array
		$sanitized = array(
			'behavior' => isset( $input['behavior'] ) ? sanitize_key( $input['behavior'] ) : 'append',
			'head'     => isset( $input['head'] ) ? self::sanitize_html_with_scripts( $input['head'] ) : '',
			'body'     => isset( $input['body'] ) ? self::sanitize_html_with_scripts( $input['body'] ) : '',
			'footer'   => isset( $input['footer'] ) ? self::sanitize_html_with_scripts( $input['footer'] ) : '',
		);

		// Reinstate Jetpack filter
		if ( $has_jetpack ) {
			add_filter( 'pre_kses', $jetpack_filter, 100 );
		}

		return $sanitized;
	}

	/**
	 * Get values of metabox fields for Posts or Terms.
	 *
	 * @param string $field_name Field key.
	 * @param int    $id         Post ID or Term ID.
	 * @param string $type       `post` or `term`.
	 * @return mixed
	 */
	public static function get_meta( $field_name, $id, $type = 'post' ) {
		if ( empty( $field_name ) || empty( $id ) ) {
			return ( 'behavior' === $field_name ) ? 'append' : '';
		}

		$meta_key = '_auhfc';

		// Get meta data based on type
		$data = ( 'post' === $type )
			? get_post_meta( $id, $meta_key, true )
			: get_term_meta( $id, $meta_key, true );

		// Check if we got array and requested key exists
		if ( is_array( $data ) && isset( $data[ $field_name ] ) ) {
			// Remove slashes from escaped value (make value ready to use)
			return stripslashes_deep( $data[ $field_name ] );
		}

		// Default for behavior
		if ( 'behavior' === $field_name ) {
			return 'append';
		}

		return '';
	}

	/**
	 * Helper: Get post meta values.
	 */
	public static function get_post_meta( $field_name, $post_id ) {
		return self::get_meta( $field_name, $post_id, 'post' );
	}

	/**
	 * Helper: Get term meta values.
	 */
	public static function get_term_meta( $field_name, $term_id ) {
		return self::get_meta( $field_name, $term_id, 'term' );
	}

	/**
	 * Smart wrapper: Get meta with auto-detected ID.
	 */
	public static function get_meta_auto( $field_name, $type = 'post' ) {
		return self::get_meta( $field_name, get_queried_object_id(), $type );
	}

	/**
	 * Get Post Type for singular requests.
	 *
	 * @return mixed Post type slug or `false` if not on a singular page.
	 */
	public static function get_singular_post_type() {
		return is_singular() ? get_post_type() : false;
	}

	/**
	 * Format security risk notice for appending to each code textarea description
	 *
	 * @return string
	 */
	public static function get_security_risk_notice() {
		return sprintf(
			'<p class="notice notice-warning"><strong>%1$s</strong> %2$s</p>',
			esc_html__( 'WARNING!', 'head-footer-code' ),
			esc_html__( 'Enter only safe, secure, and code from a trusted source. Unsafe or invalid code may break your site or pose security risks.', 'head-footer-code' )
		);
	}

	/** Helper: Get scope label. */
	private static function get_scope_label( $scope ) {
		$labels = array(
			'h' => 'Homepage',
			's' => 'Site-wide',
			'a' => 'Article specific',
			'c' => 'Category specific',
			't' => 'Taxonomy specific',
		);
		return isset( $labels[ $scope ] ) ? $labels[ $scope ] : 'Unknown';
	}

	/** Helper: Get localtion label. */
	private static function get_location_label( $location ) {
		$labels = array(
			'h' => 'HEAD',
			'b' => 'BODY',
			'f' => 'FOOTER',
		);
		return isset( $labels[ $location ] ) ? $labels[ $location ] : 'UNKNOWN';
	}

	/**
	 * Wraps text in <code> tags and escapes HTML entities.
	 *
	 * @param string $text
	 * @return string
	 */
	public static function format_as_code( $text ) {
		return sprintf( '<code>%s</code>', esc_html( $text ) );
	}

	/**
	 * Wrap code block with debugging info when WP_DEBUG is true.
	 *
	 * @param  string $scope    Scope of output (s - SITE WIDE, a - ARTICLE SPECIFIC, h - HOMEPAGE).
	 * @param  string $location Location of output (h - HEAD, b - BODY, f - FOOTER).
	 * @param  string $message  Output message.
	 * @param  string $code     Code for output.
	 * @return string           Composed string.
	 */
	public static function annotate_code_block(
		$scope = null,
		$location = null,
		$message = null,
		$code = null
	) {
		if ( ! WP_DEBUG ) {
			return $code;
		}

		if ( null === $scope || null === $location || null === $message ) {
			return;
		}

		$scope_label    = self::get_scope_label( $scope );
		$location_label = self::get_location_label( $location );
		return sprintf(
			'<!-- %1$s: %2$s %3$s section start (%4$s) -->%6$s%5$s%6$s<!-- %1$s: %2$s %3$s section end (%4$s) -->%6$s',
			self::$plugin->name,          // 1
			esc_html( $scope_label ),     // 2
			esc_html( $location_label ),  // 3
			esc_html( trim( $message ) ), // 4
			trim( $code ),                // 5 - RAW (Pre-sanitized)
			"\n"                          // 6
		);
	}

	/**
	 * Print security risk notice
	 *
	 * @return void
	 */
	public static function print_security_risk_notice() {
		echo wp_kses(
			self::get_security_risk_notice(),
			array(
				'p'      => array( 'class' => true ),
				'strong' => array(),
			)
		);
	}
}
