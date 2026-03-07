<?php
/**
 * Routine to fully uninstall Head & Footer Code plugin.
 *
 * @package   Head_Footer_Code
 * @author    Aleksandar Urošević
 * @link      https://urosevic.net
 * @link      https://www.techwebux.com
 * @since     1.0.5
 */

// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

$auhfc_options = array(
	'auhfc_settings',
	'auhfc_settings_sitewide',
	'auhfc_settings_homepage',
	'auhfc_settings_article',
	'auhfc_db_ver',
);
foreach ( $auhfc_options as $auhfc_option_name ) {
	// Delete option on single site.
	delete_option( $auhfc_option_name );
}

// Delete post meta values.
$auhfc_post_meta_key = '_auhfc';
delete_post_meta_by_key( $auhfc_post_meta_key );

// Delete category meta values.
$auhfc_category_meta_key = '_auhfc';
$auhfc_category_ids      = get_terms(
	array(
		'taxonomy' => 'category',
		'fields'   => 'ids',
		'meta_key' => $auhfc_category_meta_key,
	)
);
foreach ( $auhfc_category_ids as $auhfc_category_id ) {
	delete_term_meta( $auhfc_category_id, $auhfc_category_meta_key );
}
