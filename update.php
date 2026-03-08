<?php
/**
 * Routines to update Head & Footer Code database through new versions
 *
 * @package   Head_Footer_Code
 * @since     1.0.1
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run the incremental updates one by one.
 *
 * For example, if the current DB version is 3, and the target DB version is 6,
 * this function will execute update routines if they exist:
 * - auhfc_update_4()
 * - auhfc_update_5()
 * - auhfc_update_6()
 */
function auhfc_update() {
	// Disable PHP timeout for running updates.
	set_time_limit( 0 );

	// Get the current database schema version number.
	$current_db_ver = get_option( 'auhfc_db_ver', 0 );

	// Get the target version that we need to reach.
	$target_db_ver = HFC_VER_DB;

	// Run update routines one by one until the current version number
	// reaches the target version number.
	while ( $current_db_ver < $target_db_ver ) {
		// Increment the current_db_ver by one.
		++$current_db_ver;

		// Each DB version will require a separate update function
		// for example, for db_ver 3, the function name should be auhfc_update_3.
		$func = "auhfc_update_{$current_db_ver}";
		if ( function_exists( $func ) ) {
			call_user_func( $func );
		}

		// Update the option in the database,
		// so that this process can always pick up where it left off.
		update_option( 'auhfc_db_ver', $current_db_ver );
	}
}

/**
 * Initialize updater
 */
function auhfc_update_1() {
	// Get options from DB.
	$defaults = get_option( 'auhfc_settings' );
	if ( ! is_array( $defaults ) ) {
		return;
	}

	// Split priority to priority_h and priority_f.
	if ( isset( $defaults['priority'] ) ) {
		// Split single to specific option values.
		if ( ! isset( $defaults['priority_h'] ) ) {
			$defaults['priority_h'] = $defaults['priority'];
		}
		if ( ! isset( $defaults['priority_f'] ) ) {
			$defaults['priority_f'] = $defaults['priority'];
		}
		// Unset old key value.
		unset( $defaults['priority'] );
		// Save settings to DB.
		update_option( 'auhfc_settings', $defaults );
	}
}

/**
 * Add shortcode processor option
 */
function auhfc_update_2() {
	// Get options from DB.
	$defaults = get_option( 'auhfc_settings' );
	if ( ! is_array( $defaults ) ) {
		return;
	}

	// Add new plugin option.
	if ( ! isset( $defaults['do_shortcode'] ) ) {
		$defaults['do_shortcode'] = 'n';
	}
	// Save settings to DB.
	update_option( 'auhfc_settings', $defaults );
}

/**
 * Initialize updater
 */
function auhfc_update_3() {
	// Get options from DB.
	$defaults = get_option( 'auhfc_settings' );
	if ( ! is_array( $defaults ) ) {
		return;
	}

	// Add empty body field to options.
	if ( ! isset( $defaults['body'] ) ) {
		$defaults['body'] = '';
	}
	// Add body field priority to options.
	if ( ! isset( $defaults['priority_b'] ) ) {
		$defaults['priority_b'] = 10;
	}

	// Save settings to DB.
	update_option( 'auhfc_settings', $defaults );
}

/**
 * Add homepage blog posts code defaults
 */
function auhfc_update_4() {
	// Get options from DB.
	$defaults = get_option( 'auhfc_settings' );
	if ( ! is_array( $defaults ) ) {
		return;
	}

	// Add empty homepage_head field to options.
	if ( ! isset( $defaults['homepage_head'] ) ) {
		$defaults['homepage_head'] = '';
	}
	// Add empty homepage_body field to options.
	if ( ! isset( $defaults['homepage_body'] ) ) {
		$defaults['homepage_body'] = '';
	}
	// Add empty homepage_footer field to options.
	if ( ! isset( $defaults['homepage_footer'] ) ) {
		$defaults['homepage_footer'] = '';
	}
	// Add empty homepage_behavior field to options.
	if ( ! isset( $defaults['homepage_behavior'] ) ) {
		$defaults['homepage_behavior'] = 'append';
	}

	// Save settings to DB.
	update_option( 'auhfc_settings', $defaults );
}

/**
 * Split settings to 3 options (v1.2)
 */
function auhfc_update_5() {
	// Get options from DB.
	$defaults = get_option( 'auhfc_settings' );
	if ( ! is_array( $defaults ) ) {
		return;
	}

	$sitewide = array(
		'head'         => ! empty( $defaults['head'] ) ? $defaults['head'] : '',
		'body'         => ! empty( $defaults['body'] ) ? $defaults['body'] : '',
		'footer'       => ! empty( $defaults['footer'] ) ? $defaults['footer'] : '',
		'do_shortcode' => ! empty( $defaults['do_shortcode'] ) ? $defaults['do_shortcode'] : 'n',
	);
	update_option( 'auhfc_settings_sitewide', $sitewide );

	$homepage = array(
		'head'     => ! empty( $defaults['homepage_head'] ) ? $defaults['homepage_head'] : '',
		'body'     => ! empty( $defaults['homepage_body'] ) ? $defaults['homepage_body'] : '',
		'footer'   => ! empty( $defaults['homepage_footer'] ) ? $defaults['homepage_footer'] : '',
		'behavior' => ! empty( $defaults['homepage_behavior'] ) ? $defaults['homepage_behavior'] : 'append',
	);
	update_option( 'auhfc_settings_homepage', $homepage );

	$article = array(
		'post_types' => ! empty( $defaults['post_types'] ) ? $defaults['post_types'] : array(),
	);
	update_option( 'auhfc_settings_article', $article );

	// Now delete old single option.
	delete_option( 'auhfc_settings' );
}

/**
 * Fix PHP Warning:  in_array() expects parameter 2 to be array, null given in head-footer-code/inc/front.php on line 46, 111, and 176
 */
function auhfc_update_6() {
	$article = get_option( 'auhfc_settings_article' );
	if ( ! is_array( $article ) ) {
		return;
	}

	if ( is_null( $article['post_types'] ) ) {
		$article['post_types'] = array();
		update_option( 'auhfc_settings_article', $article );
	}
}

/**
 * Do Shortcode per location
 */
function auhfc_update_7() {
	// Get options from DB.
	$sitewide = get_option( 'auhfc_settings_sitewide' );
	if ( ! is_array( $sitewide ) ) {
		return;
	}

	if ( ! empty( $sitewide['do_shortcode'] ) ) {
		$sitewide['do_shortcode_h'] = 'n';
		$sitewide['do_shortcode_b'] = 'n';
		$sitewide['do_shortcode_f'] = $sitewide['do_shortcode'];
	} else {
		$sitewide['do_shortcode_h'] = 'n';
		$sitewide['do_shortcode_b'] = 'n';
		$sitewide['do_shortcode_f'] = 'n';
	}
	unset( $sitewide['do_shortcode'] );
	update_option( 'auhfc_settings_sitewide', $sitewide );
}

/**
 * Add or not homepage in Blog Post mode on paged pages
 */
function auhfc_update_8() {
	// Get options from DB.
	$homepage = get_option( 'auhfc_settings_homepage' );
	if ( ! is_array( $homepage ) ) {
		return;
	}

	if ( empty( $homepage['paged'] ) ) {
		$homepage['paged'] = 'yes';
	}
	update_option( 'auhfc_settings_homepage', $homepage );
}

/**
 * Add option to allow unprivileged user roles to manage article-specific HFC
 */
function auhfc_update_9() {
	// Get options from DB.
	$homepage = get_option( 'auhfc_settings_homepage' );

	if ( ! is_array( $homepage ) ) {
		return;
	}

	if ( empty( $homepage['allowed_roles'] ) ) {
		$homepage['allowed_roles'] = array();
	}
	update_option( 'auhfc_settings_homepage', $homepage );
}

/**
 * Migration for v. 1.5.3
 * Clean up double slashes from existing meta data caused by previous double-slashing.
 */
function auhfc_update_10() {
	global $wpdb;

	$meta_key = '_auhfc';

	/**
	 * Strip slashes from Post Metas
	 * We use direct SQL to fetch all IDs at once to avoid N+1 query issues
	 * and memory exhaustion on large databases.
	 */
	$post_metas = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s",
			$meta_key
		)
	);

	if ( ! empty( $post_metas ) ) {
		foreach ( $post_metas as $meta ) {
			$original_data = maybe_unserialize( $meta->meta_value );

			if ( is_array( $original_data ) ) {
				$cleaned_data = stripslashes_deep( $original_data );
				update_post_meta( $meta->post_id, $meta_key, wp_slash( $cleaned_data ) );
			}
		}
	}

	/**
	 * Strip slashes from Taxonomies (Categories at the moment)
	 */
	$term_metas = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT term_id, meta_value FROM $wpdb->termmeta WHERE meta_key = %s",
			$meta_key
		)
	);

	if ( ! empty( $term_metas ) ) {
		foreach ( $term_metas as $meta ) {
			$original_data = maybe_unserialize( $meta->meta_value );

			if ( is_array( $original_data ) ) {
				$cleaned_data = stripslashes_deep( $original_data );
				update_term_meta( $meta->term_id, $meta_key, wp_slash( $cleaned_data ) );
			}
		}
	}
}

/**
 * Migration for v. 1.5.4
 * Add support for taxonomies and pre-select 'category'.
 */
function auhfc_update_11() {
	// Get options from DB.
	$article = get_option( 'auhfc_settings_article' );
	if ( ! is_array( $article ) ) {
		return;
	}

	if ( empty( $article['taxonomies'] ) ) {
		$article['taxonomies'] = array( 'category' );
	}
	update_option( 'auhfc_settings_article', $article );
}
