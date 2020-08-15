<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Run the incremental updates one by one.
 *
 * For example, if the current DB version is 3, and the target DB version is 6,
 * this function will execute update routines if they exist:
 *  - auhfc_update_4()
 *  - auhfc_update_5()
 *  - auhfc_update_6()
 */

function auhfc_update() {
	// Disable PHP timeout for running updates.
	set_time_limit( 0 );

	// Get the current database schema version number.
	$current_db_ver = get_option( 'auhfc_db_ver', 0 );

	// Get the target version that we need to reach.
	$target_db_ver = WPAU_HEAD_FOOTER_CODE_DB_VER;

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

} // END function auhfc_update()

/**
 * Initialize updater
 */
function auhfc_update_1() {

	// Get options from DB.
	$defaults = get_option( 'auhfc_settings' );

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

} // END function auhfc_update_1()

/**
 * Add shortcode processor option
 */
function auhfc_update_2() {

	// Get options from DB.
	$defaults = get_option( 'auhfc_settings' );

	// Add new plugin option.
	if ( ! isset( $defaults['do_shortcode'] ) ) {
		$defaults['do_shortcode'] = 'n';
	}
	// Save settings to DB.
	update_option( 'auhfc_settings', $defaults );

} // END function auhfc_update_2()


/**
 * Initialize updater
 */
function auhfc_update_3() {

	// Get options from DB.
	$defaults = get_option( 'auhfc_settings' );

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

} // END function auhfc_update_3()

/**
 * Add homepage blog posts code defaults
 */
function auhfc_update_4() {

	// Get options from DB.
	$defaults = get_option( 'auhfc_settings' );

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

} // END function auhfc_update_4()


/**
 * Split settings to 3 options (v1.2)
 */
function auhfc_update_5() {

	// Get options from DB.
	$defaults = get_option( 'auhfc_settings' );

	$sitewide = [
		'head'         => $defaults['head'],
		'body'         => $defaults['body'],
		'footer'       => $defaults['footer'],
		'do_shortcode' => $defaults['do_shortcode'],
	];
	update_option( 'auhfc_settings_sitewide', $sitewide );

	$homepage = [
		'head'         => $defaults['homepage_head'],
		'body'         => $defaults['homepage_body'],
		'footer'       => $defaults['homepage_footer'],
		'behavior'     => $defaults['homepage_behavior'],
	];
	update_option( 'auhfc_settings_homepage', $homepage );

	$article = [
		'post_types'   => $defaults['post_types'],
	];
	update_option( 'auhfc_settings_article', $article );

	// Now delete old single option.
	delete_option( 'auhfc_settings' );

} // END function auhfc_update_5()
