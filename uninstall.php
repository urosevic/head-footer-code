<?php
/**
 * @link        https://urosevic.net
 * @since       1.0.5
 * @package     Head_Footer_Code
 */

// If uninstall is not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit();
}

$auhfc_options = array( 'auhfc_settings', 'auhfc_db_ver' );
foreach ( $auhfc_options as $option_name ) {
	// Delete option on single site
	delete_option( $option_name );
	// For site options in Multisite
	delete_site_option( $option_name );
}

// Delete post meta values
$post_meta_key = '_auhfc';
delete_post_meta_by_key( $post_meta_key );
