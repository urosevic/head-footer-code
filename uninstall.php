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

// Delete term meta values across all taxonomies (category, tags, or any custom
// taxonomy the user selected in settings), including empty terms.
$auhfc_term_meta_key = '_auhfc';
$auhfc_term_ids       = get_terms(
	array(
		'taxonomy'   => get_taxonomies( array(), 'names' ),
		'fields'     => 'ids',
		'hide_empty' => false,
		'meta_key'   => $auhfc_term_meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One-time uninstall cleanup; no cheaper way to find terms carrying this meta key.
	)
);
if ( ! is_wp_error( $auhfc_term_ids ) ) {
	foreach ( $auhfc_term_ids as $auhfc_term_id ) {
		delete_term_meta( $auhfc_term_id, $auhfc_term_meta_key );
	}
}
