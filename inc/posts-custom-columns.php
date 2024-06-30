<?php
/**
 * Generate Head & Footer Code indicator columns on article listing
 *
 * @package Head_Footer_Code
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Do this ONLY in admin dashboard!
if ( ! is_admin() ) {
	return;
}

// And do this only for post types enabled on plugin settings page.
$auhfc_settings = auhfc_settings();
if ( isset( $auhfc_settings['article']['post_types'] ) ) {
	foreach ( $auhfc_settings['article']['post_types'] as $post_type ) {
		// Add the custom column to the all post types that have enabled support for custom code.
		add_filter( 'manage_' . $post_type . '_posts_columns', 'auhfc_posts_columns' );
		// And make that column sortable.
		add_filter( 'manage_edit-' . $post_type . '_sortable_columns', 'auhfc_posts_sortable_columns' );
		// Add the data to the custom column for each enabled post types.
		add_action( 'manage_' . $post_type . '_posts_custom_column', 'auhfc_posts_custom_columns', 10, 2 );
	}
}

/**
 * Register Head & Footer Code column for posts table
 *
 * @param array $columns Array of existing columns for table.
 */
function auhfc_posts_columns( $columns ) {
	$columns['hfc'] = esc_html__( 'Head & Footer Code', 'head-footer-code' );
	return $columns;
} // END function auhfc_posts_columns( $columns )

/**
 * Make Head & Footer Code column sortable
 *
 * @param array $columns Array of existing columns for table.
 */
function auhfc_posts_sortable_columns( $columns ) {
	$columns['hfc'] = 'hfc';
	return $columns;
} // END function auhfc_posts_sortable_columns( $columns )

/**
 * Populate Head & Footer Code column with indicators
 *
 * @param string  $column Table column name.
 * @param integer $post_id Current article ID.
 */
function auhfc_posts_custom_columns( $column, $post_id ) {
	if ( 'hfc' !== $column ) {
		return;
	}

	$sections = array();
	if ( ! empty( auhfc_get_meta( 'head', $post_id ) ) ) {
		$sections[] = sprintf(
			'<a href="post.php?post=%1$s&action=edit#auhfc_head" class="badge" title="%2$s">H</a>',
			$post_id,
			esc_html__( 'Article specific code is defined in HEAD section', 'head-footer-code' )
		);
	}
	if ( ! empty( auhfc_get_meta( 'body', $post_id ) ) ) {
		$sections[] = sprintf(
			'<a href="post.php?post=%1$s&action=edit#auhfc_body" class="badge" title="%2$s">B</a>',
			$post_id,
			esc_html__( 'Article specific code is defined in BODY section', 'head-footer-code' )
		);
	}
	if ( ! empty( auhfc_get_meta( 'footer', $post_id ) ) ) {
		$sections[] = sprintf(
			'<a href="post.php?post=%1$s&action=edit#auhfc_footer" class="badge" title="%2$s">F</a>',
			$post_id,
			esc_html__( 'Article specific code is defined in FOOTER section', 'head-footer-code' )
		);
	}

	if ( empty( $sections ) ) {
		printf(
			'<span class="n-a" title="%1$s">%2$s</span>',
			/* translators: This is description for article without defined code */
			esc_html__( 'No article specific code defined in any section', 'head-footer-code' ),
			/* translators: This is label for article without defined code */
			esc_html__( 'No custom code', 'head-footer-code' )
		);
	} else {
		$mode = auhfc_get_meta( 'behavior', $post_id );
		if ( 'append' === $mode ) {
			/* translators: This is description for article specific mode label 'Append' */
			$method_description = esc_html__( 'Append article specific code to site-wide code', 'head-footer-code' );
			/* translators: This is label for article specific mode meaning 'Append to site-wide' ) */
			$method_label = esc_html__( 'Append', 'head-footer-code' );
		} else {
			/* translators: This is description for article specific mode label 'Replace' */
			$method_description = esc_html__( 'Replace site-wide code with article specific code', 'head-footer-code' );
			/* translators: This is label for article specific mode meaning 'Replace site-wide with' */
			$method_label = esc_html__( 'Replace', 'head-footer-code' );
		}
		printf(
			'<a href="post.php?post=%1$s&action=edit#auhfc_behavior" class="label" title="%2$s">%3$s</a><br />%4$s',
			$post_id, // 1
			$method_description, // 3
			$method_label, // 4
			'<div class="badges">' . implode( '', $sections ) . '</div>' // 5
		);
	}
} // END function auhfc_posts_custom_columns( $column, $post_id )
