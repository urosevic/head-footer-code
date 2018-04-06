<?php
/**
 * Run the incremental updates one by one.
 *
 * For example, if the current DB version is 3, and the target DB version is 6,
 * this function will execute update routines if they exist:
 *  - auhfc_update_routine_4()
 *  - auhfc_update_routine_5()
 *  - auhfc_update_routine_6()
 */

function auhfc_update() {
	// no PHP timeout for running updates
	set_time_limit( 0 );

	// this is the current database schema version number
	$current_db_ver = get_option( 'auhfc_db_ver', 0 );

	// this is the target version that we need to reach
	$target_db_ver = WPAU_HEAD_FOOTER_CODE_DB_VER;

	// run update routines one by one until the current version number
	// reaches the target version number
	while ( $current_db_ver < $target_db_ver ) {
		// increment the current db_ver by one
		++$current_db_ver;

		// each db version will require a separate update function
		// for example, for db_ver 3, the function name should be solis_update_routine_3
		$func = "auhfc_update_routine_{$current_db_ver}";
		if ( function_exists( $func ) ) {
			call_user_func( $func );
		}

		// update the option in the database, so that this process can always
		// pick up where it left off
		update_option( 'auhfc_db_ver', $current_db_ver );
	}

} // END function auhfc_update()

/**
 * Initialize updater
 */
function auhfc_update_routine_1() {

	// get options from DB
	$defaults = get_option( 'auhfc_settings' );

	// split pre-1.0.8 priority to priority_h and priority_f
	if ( isset( $defaults['priority'] ) ) {
		// Split single to separate option values
		if ( ! isset( $defaults['priority_h'] ) ) {
			$defaults['priority_h'] = $defaults['priority'];
		}
		if ( ! isset( $defaults['priority_f'] ) ) {
			$defaults['priority_f'] = $defaults['priority'];
		}
		// Unset old key value
		unset( $defaults['priority'] );
		// Update option
		update_option( 'auhfc_settings', $defaults );
	}

} // END function auhfc_update_routine_1()

/**
 * Add network wide head and footer defaults to main site
 */
function auhfc_update_routine_2() {
	if ( is_multisite() && is_main_site() ) {
		$defaults = get_option( 'auhfc_settings' );
		if ( ! isset( $defaults['network_head'] ) ) {
			$defaults['network_head'] = '';
		}
		if ( ! isset( $defaults['network_footer'] ) ) {
			$defaults['network_footer'] = '';
		}
		update_option( 'auhfc_settings', $defaults );
	}
} // END function auhfc_update_routine_2()
