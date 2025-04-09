<?php
/**
 * Various common functions for Head & Footer Code
 *
 * @package Head_Footer_Code
 */

namespace Techwebux\Hfc;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Common {

	private static $settings = null;

	/**
	 * Initialize settings if not already set.
	 *
	 * @return void
	 */
	private static function init_settings() {
		if ( null === self::$settings ) {
			self::$settings = Main::settings();
		}
	}

	/**
	 * Check if the current user has any of the allowed roles.
	 *
	 * @return bool
	 */
	public static function user_has_allowed_role() {
		// Always allow Super Admin (Multisite)
		$current_user = wp_get_current_user();
		if ( is_super_admin( $current_user->ID ) ) {
			return true;
		}

		// Get current user roles
		$user_roles = (array) $current_user->roles;

		// Initialize settings if not already initialized
		self::init_settings();

		// Merge fixed always-allowed and configurable allowed roles
		$allowed_roles = array_merge(
			array( 'administrator', 'shop_manager' ),
			self::$settings['article']['allowed_roles']
		);

		// Check if any of user's roles are in the allowed list
		return (bool) array_intersect( $user_roles, $allowed_roles );
	}

	/**
	 * Function to check if homepage uses Blog mode
	 */
	public static function is_homepage_blog_posts() {
		if ( is_home() && 'posts' === get_option( 'show_on_front', false ) ) {
			return true;
		}
		return false;
	} // END public static function is_homepage_blog_posts

	/**
	 * Function to check if code should be added on paged homepage in Blog mode
	 *
	 * @param bool  $is_homepage_blog_posts If current page is blog homepage
	 *
	 * @return bool
	 */
	public static function add_to_homepage_paged( $is_homepage_blog_posts ) {
		// Ensure settings are initialized.
		self::init_settings();

		if (
			true === $is_homepage_blog_posts
			&& ! empty( self::$settings['homepage']['paged'] )
			&& 'no' === self::$settings['homepage']['paged']
			&& is_paged()
		) {
			return false;
		}
		return true;
	} // END public static function add_to_homepage_paged

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

		/**
		 * Filters a sanitized HTML class string.
		 *
		 * @param string $sanitized The sanitized HTML class.
		 * @param string $classse   HTML class before sanitization.
		 * @param string $fallback  The fallback string.
		 */
		return apply_filters( 'sanitize_html_classes', $sanitized, $classes, $fallback );
	} // END public static function sanitize_html_classes

	/**
	 * Prepare allowed code for KSES filtering
	 *
	 * @return array
	 */
	public static function allowed_html() {
		// Allow safe HTML, JS, and CSS.
		return array_merge(
			wp_kses_allowed_html( 'post' ), // Allow safe HTML for posts.
			self::form_allowed_html(),
			array(
				'script' => array(
					'type'        => true,
					'async'       => true,
					'defer'       => true,
					'src'         => true, // remote
					'crossorigin' => true, // security
					'nonce'       => true, // security
				), // Allow <script> tags.
				'style'  => array(
					'nonce' => true,
				),        // Allow <style> tags.
				'link'   => array(          // Allow <link> tags for CSS and preloading.
					'rel'           => true,
					'href'          => true,
					'type'          => true,
					'media'         => true,
					'fetchpriority' => true, // preload
					'as'            => true, // preload
				),
				'meta'   => array(
					'name'   => true,
					'conent' => true,
				),
			)
		);
	} // END public static function allowed_html

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
				'type'    => array(),
				'name'    => array(),
				'id'      => array(),
				'value'   => array(),
				'class'   => array(),
				'min'     => array(), // number
				'max'     => array(), // number
				'step'    => array(), // number
				'checked' => array(), // checkbox
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
			'div'      => array(
				'class' => true,
			),
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
	} // END public static function form_allowed_html

	/**
	 * Get values of metabox fields
	 *
	 * @param  string $field_name Post meta field key.
	 * @param  string $post_id    Post ID (optional).
	 * @return string             Post meta field value.
	 */
	public static function get_meta( $field_name = '', $post_id = null ) {
		if ( empty( $field_name ) ) {
			return false;
		}

		if ( empty( $post_id ) || intval( $post_id ) !== $post_id ) {
			if ( is_admin() ) {
				global $post;

				// If $post has not an object, return false.
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
		} else {
			$post_id = (int) $post_id;
		}

		if ( empty( $post_id ) ) {
			return false;
		}

		$field = get_post_meta( $post_id, '_auhfc', true );

		if ( ! empty( $field ) && is_array( $field ) && ! empty( $field[ $field_name ] ) ) {
			return stripslashes_deep( $field[ $field_name ] );
		} elseif ( 'behavior' === $field_name ) {
			return 'append';
		} else {
			return false;
		}
	} // END public static function get_meta

	/**
	 * Function to get Post Type
	 */
	public static function get_post_type() {
		$auhfc_post_type = 'not singular';
		// Get post type.
		if ( is_singular() ) {
			global $wp_the_query;
			$auhfc_query = $wp_the_query->get_queried_object();
			if ( is_object( $auhfc_query ) ) {
				$auhfc_post_type = $auhfc_query->post_type;
			}
		}
		return $auhfc_post_type;
	} // END public static function get_post_type

	/**
	 * Function to convert code to HTML special chars
	 *
	 * @param string $text RAW content.
	 */
	public static function html2code( $text ) {
		return '<code>' . htmlspecialchars( $text ) . '</code>';
	} // END public static function html2code

	/**
	 * Return debugging string if WP_DEBUG constant is true.
	 *
	 * @param  string $scope    Scope of output (s - SITE WIDE, a - ARTICLE SPECIFIC, h - HOMEPAGE).
	 * @param  string $location Location of output (h - HEAD, b - BODY, f - FOOTER).
	 * @param  string $message  Output message.
	 * @param  string $code     Code for output.
	 * @return string           Composed string.
	 */
	public static function out(
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
		switch ( $scope ) {
			case 'h':
				$scope = 'Homepage';
				break;
			case 's':
				$scope = 'Site-wide';
				break;
			case 'a':
				$scope = 'Article specific';
				break;
			case 'c':
				$scope = 'Category specific';
				break;
			default:
				$scope = 'Unknown';
		}
		switch ( $location ) {
			case 'h':
				$location = 'HEAD';
				break;
			case 'b':
				$location = 'BODY';
				break;
			case 'f':
				$location = 'FOOTER';
				break;
			default:
				$location = 'UNKNOWN';
				break;
		}
		return sprintf(
			'<!-- %1$s: %2$s %3$s section start (%4$s) -->%6$s%5$s%6$s<!-- %1$s: %2$s %3$s section end (%4$s) -->%6$s',
			HFC_PLUGIN_NAME,  // 1
			$scope,           // 2
			$location,        // 3
			trim( $message ), // 4
			trim( $code ),    // 5
			"\n"              // 6
		);
	} // END public static function out

	/**
	 * Determine should we print site-wide code
	 * or it should be replaced with homepage/article/category code.
	 *
	 * @param  string  $behavior       Behavior for article specific code (replace/append).
	 * @param  string  $code           Article specific custom code.
	 * @param  string  $post_type      Post type of current article.
	 * @param  array   $post_types     Array of post types where article specific code is enabled.
	 * @param  boolean $is_category    Indicate if current displayed page is category or not.
	 * @return boolean                 Boolean that determine should site-wide code be printed (true) or not (false).
	 */
	public static function print_sitewide(
		$behavior = 'append',
		$code = '',
		$post_type = null,
		$post_types = array(),
		$is_category = false
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
		} elseif ( $is_category ) { // On category page print site wide if...
			// ... behavior is not replace, or...
			// ... behavior is replace but category content is empty.
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
	} // END public static function print_sitewide

	/**
	 * Format security risk notice for appending to each code textarea description
	 *
	 * @return string
	 */
	public static function security_risk_notice() {
		return '<p class="notice notice-warning">'
			. '<strong>' . esc_html__( 'WARNING!', 'head-footer-code' ) . '</strong> '
			. esc_html__( 'Enter only safe, secure, and code from a trusted source. Unsafe or invalid code may break your site or pose security risks.', 'head-footer-code' )
			. '</p>';
	}
}
