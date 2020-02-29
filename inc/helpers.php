<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Include back-end/front-end resources.
if ( is_admin() ) {
	require_once WPAU_HEAD_FOOTER_CODE_INC . 'settings.php';
	require_once WPAU_HEAD_FOOTER_CODE_INC . 'class-auhfc-meta-box.php';
} else {
	require_once WPAU_HEAD_FOOTER_CODE_INC . 'front.php';
}

register_activation_hook( WPAU_HEAD_FOOTER_CODE_FILE, 'auhfc_activate' );
/**
 * Plugin Activation hook function to check for Minimum PHP and WordPress versions
 */
function auhfc_activate() {
	global $wp_version;
	$php_req = '5.6'; // Minimum version of PHP required for this plugin
	$wp_req  = '4.9'; // Minimum version of WordPress required for this plugin

	if ( version_compare( PHP_VERSION, $php_req, '<' ) ) {
		$flag = 'PHP';
	} elseif ( version_compare( $wp_version, $wp_req, '<' ) ) {
		$flag = 'WordPress';
	} else {
		return;
	}
	$version = 'PHP' == $flag ? $php_req : $wp_req;
	deactivate_plugins( WPAU_HEAD_FOOTER_CODE_FILE );
	wp_die(
		'<p>The <strong>Head & Footer Code</strong> plugin requires' . $flag . ' version ' . $version . ' or greater.</p>',
		'Plugin Activation Error',
		[
			'response'  => 200,
			'back_link' => true,
		]
	);

	// Trigger updater function.
	auhfc_maybe_update();
} // END function auhfc_activate()

// Regular update trigger.
add_action( 'plugins_loaded', 'auhfc_maybe_update' );
function auhfc_maybe_update() {
	// Bail if this plugin data doesn't need updating.
	if ( get_option( 'auhfc_db_ver' ) >= WPAU_HEAD_FOOTER_CODE_DB_VER ) {
		return;
	}
	// Require update script.
	require_once( dirname( __FILE__ ) . '/update.php' );
	// Trigger update function.
	auhfc_update();
} // END function auhfc_maybe_update()

add_action( 'admin_enqueue_scripts', 'auhfc_codemirror_enqueue_scripts' );
/**
 * CodeMirror enqueue hoot function to enable code editor in plugin settings
 * @param  string $hook Current page hook
 */
function auhfc_codemirror_enqueue_scripts( $hook ) {
	if ( 'tools_page_head_footer_code' !== $hook ) {
		return;
	}
	$cm_settings['codeEditor'] = wp_enqueue_code_editor( [ 'type' => 'text/html' ] );
	wp_localize_script( 'jquery', 'cm_settings', $cm_settings );
	wp_enqueue_script( 'wp-codemirror' );
	wp_enqueue_style( 'wp-codemirror' );
} // END function auhfc_codemirror_enqueue_scripts( $hook )

/**
 * Provide global defaults
 * @return array Arary of defined global values
 */
function auhfc_defaults() {
	$defaults = [
		'head'         => '',
		'footer'       => '',
		'priority_h'   => 10,
		'priority_f'   => 10,
		'post_types'   => [],
		'do_shortcode' => 'n',
	];
	$auhfc_settings = get_option( 'auhfc_settings', $defaults );
	$auhfc_settings = wp_parse_args( $auhfc_settings, $defaults );
	return $auhfc_settings;
} // END function auhfc_defaults()

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

/**
 * Return debugging string if WP_DEBUG constant is true.
 * @param  string $scope    Scope of output (s - SITE WIDE, a - ARTICLE SPECIFIC)
 * @param  string $location Location of output (h - HEAD, b - BODY, f - FOOTER)
 * @param  string $message  Output message
 * @param  string $code     Code for output
 * @return string           Composed string
 */
function auhfc_out( $scope = null, $location = null, $message = null, $code = null ) {
	if ( ! WP_DEBUG ) {
		return $code;
	}
	if ( null == $scope || null == $location || null == $message ) {
		return;
	}
	$scope = 's' == $scope ? 'Site-wide' : 'Article specific';
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
		'<!-- Head & Footer Code: %1$s %2$s section start (%3$s) -->%5$s %4$s%5$s<!-- Head & Footer Code: %1$s %2$s section end (%3$s) -->%5$s',
		$scope,            // 1
		$location,         // 2
		trim( $message ),  // 3
		trim( $code ),     // 4
		"\n"               // 5
	);
} // END function auhfc_out( $scope = null, $location = null, $message = null, $code = null )

function auhfc_body_note() {
	return '<p class="notice"><strong>Please note!</strong> Usage of this hook should be reserved for output of <em>unseen elements</em> like <code>&lt;script&gt;</code> tags or additional metadata. It should not be used to add arbitrary HTML content to a page that <em>could break layouts or lead to unexpected situations</em>. Make sure that your active theme support <a href="https://developer.wordpress.org/reference/hooks/wp_body_open/" target="_hook">wp_body_open</a> hook.</p>';
}
