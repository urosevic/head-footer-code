<?php
/**
 * Routine to fully uninstall Head & Footer Code plugin.
 *
 * @link        https://urosevic.net
 * @since       1.0.5
 * @package     Head_Footer_Code
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

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
foreach ( $auhfc_options as $option_name ) {
	// Delete option on single site.
	delete_option( $option_name );
}

// Delete post meta values.
$post_meta_key = '_auhfc';
delete_post_meta_by_key( $post_meta_key );

// Delete category meta values.
$category_meta_key  = '_auhfc';
$auhfc_category_ids = get_terms(
	array(
		'taxonomy' => 'category',
		'fields'   => 'ids',
		'meta_key' => $category_meta_key,
	)
);
foreach ( $auhfc_category_ids as $category_id ) {
	$ret = delete_term_meta( $category_id, $category_meta_key );
}
