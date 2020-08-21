<?php
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
if ( isset($auhfc_settings['article']['post_types'] ) ) {
	foreach ($auhfc_settings['article']['post_types'] as $post_type) {
		// Add the custom column to the all post types that have enabled support for custom code.
		add_filter( 'manage_' . $post_type . '_posts_columns', 'auhfc_posts_columns' );
		// And make that column sortable.
		add_filter( 'manage_edit-' . $post_type . '_sortable_columns', 'auhfc_posts_sortable_columns' );
		// Add the data to the custom column for each enabled post types.
		add_action( 'manage_' . $post_type . '_posts_custom_column' , 'auhfc_posts_custom_columns', 10, 2 );
	}
}

function auhfc_posts_columns( $columns ) {
	$columns['hfc'] = __( 'Head & Footer Code', 'head-footer-code' );
	return $columns;
} // END function auhfc_posts_columns( $columns )

function auhfc_posts_sortable_columns( $columns ) {
	$columns['hfc'] = 'hfc';
	return $columns;
} // END function auhfc_posts_sortable_columns( $columns )

function auhfc_posts_custom_columns( $column, $post_id ) {
	if ( 'hfc' !== $column ) {
		return;
	}

	$sections = [];
	if ( !empty( auhfc_get_meta('head', $post_id) ) ) {
		$sections[] = sprintf(
			'<a href="post.php?post=%1$s&action=edit#auhfc_%2$s" class="badge blue %2$s" title="%3$s">%4$s</a>',
			$post_id,
			'head',
			esc_html__( 'Article specific code is defined in HEAD section', 'head-footer-code' ),
			esc_html__( 'HEAD', 'head-footer-code' )
		);
	}
	if ( !empty( auhfc_get_meta('body', $post_id) ) ) {
		$sections[] = sprintf(
			'<a href="post.php?post=%1$s&action=edit#auhfc_%2$s" class="badge blue %2$s" title="%3$s">%4$s</a>',
			$post_id,
			'body',
			esc_html__( 'Article specific code is defined in BODY section', 'head-footer-code' ),
			esc_html__( 'BODY', 'head-footer-code' )
		);
	}
	if ( !empty( auhfc_get_meta('footer', $post_id) ) ) {
		$sections[] = sprintf(
			'<a href="post.php?post=%1$s&action=edit#auhfc_%2$s" class="badge blue %2$s" title="%3$s">%4$s</a>',
			$post_id,
			'footer',
			esc_html__( 'Article specific code is defined in FOOTER section', 'head-footer-code' ),
			esc_html__( 'FOOTER', 'head-footer-code' )
		);
	}
	if ( empty( $sections ) ) {
		printf(
			'<span class="n-a" title="%1$s">%2$s</span>',
			/* translators: This is description for article without defined code */
			esc_html__( 'No article specific code defined in any section', 'head-footer-code' ),
			/* translators: This is label for article without defined code */
			esc_html__( 'Not article code', 'head-footer-code' )
		);
	} else {
		$mode = auhfc_get_meta( 'behavior', $post_id );
		if ( 'append' == $mode ) {
			printf( '<a href="post.php?post=%1$s&action=edit#auhfc_%2$s" class="label" title="%3$s">%4$s</a><br />%5$s',
				$post_id,
				'behavior',
				/* translators: This is description for article specific mode label 'Append' */
				esc_html__( 'Append article specific code to site-wide code', 'head-footer-code' ),
				/* translators: This is label for article specific mode meaning 'Append to site-wide' ) */
				esc_html__( 'Append', 'head-footer-code' ),
				implode( '', $sections )
			);
		} else {
			printf( '<a href="post.php?post=%1$s&action=edit#auhfc_%2$s" class="label" title="%3$s">%4$s</a><br />%5$s',
				$post_id,
				'behavior',
				/* translators: This is description for article specific mode label 'Replace' */
				esc_html__( 'Replace site-wide code with article specific code', 'head-footer-code' ),
				/* translators: This is label for article specific mode meaning 'Replace site-wide with' */
				esc_html__( 'Replace', 'head-footer-code' ),
				implode( '', $sections )
			);
		}
	}

} // END function auhfc_posts_custom_columns( $column, $post_id )
