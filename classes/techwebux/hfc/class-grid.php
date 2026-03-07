<?php
/**
 * Admin list view helper.
 *
 * Adds and populates custom columns in post/page/CPT listings
 * to provide visual indicators of attached code snippets.
 *
 * @package   Head_Footer_Code
 * @since     1.2.1
 */

namespace Techwebux\Hfc;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Grid {
	private $settings;

	public function __construct() {
		// Do this ONLY in admin dashboard!
		if ( ! is_admin() ) {
			return;
		}
		$this->settings = Main::settings();
		if ( ! Common::user_has_allowed_role() ) {
			return;
		}
		add_action( 'admin_init', array( $this, 'admin_post_manage_columns' ) );
	}

	public function admin_post_manage_columns() {
		// And do this only for post types enabled on plugin settings page.
		if ( isset( $this->settings['article']['post_types'] ) ) {
			foreach ( $this->settings['article']['post_types'] as $post_type ) {
				// Add the custom column to the all post types that have enabled support for custom code.
				add_filter( 'manage_' . $post_type . '_posts_columns', array( $this, 'posts_columns' ) );
				// And make that column sortable.
				add_filter( 'manage_edit-' . $post_type . '_sortable_columns', array( $this, 'posts_sortable_columns' ) );
				// Add the data to the custom column for each enabled post types.
				add_action( 'manage_' . $post_type . '_posts_custom_column', array( $this, 'posts_custom_columns' ), 10, 2 );
			}
		}
	}

	/**
	 * Register Head & Footer Code column for posts table
	 *
	 * @param array $columns Array of existing columns for table.
	 */
	public function posts_columns( $columns ) {
		$columns['hfc'] = esc_html( HFC_PLUGIN_NAME );
		return $columns;
	}

	/**
	 * Make Head & Footer Code column sortable
	 *
	 * @param array $columns Array of existing columns for table.
	 */
	public function posts_sortable_columns( $columns ) {
		$columns['hfc'] = 'hfc';
		return $columns;
	}

	/**
	 * Populate Head & Footer Code column with indicators
	 *
	 * @param string  $column Table column name.
	 * @param integer $post_id Current article ID.
	 */
	public function posts_custom_columns( $column, $post_id ) {
		if ( 'hfc' !== $column ) {
			return;
		}

		$meta = get_post_meta( $post_id, '_auhfc', true );
		if ( empty( $meta['head'] ) && empty( $meta['body'] ) && empty( $meta['footer'] ) ) {
			echo '<span class="n-a">' . esc_html__( 'No custom code', 'head-footer-code' ) . '</span>';
			return;
		}

		$behavior       = isset( $meta['behavior'] ) ? $meta['behavior'] : 'append';
		$behavior_class = ( 'replace' === $behavior ) ? 'hfc-replace' : 'hfc-append';
		$behavior_text  = ( 'replace' === $behavior )
			? esc_attr__( 'replace site-wide code with', 'head-footer-code' )
			: esc_attr__( 'append to site-wide code', 'head-footer-code' );

		$sections = array(
			'head'   => array(
				'label' => 'H',
				'key'   => 'head',
				'title' => esc_attr__( 'Article-specific HEAD', 'head-footer-code' ),
			),
			'body'   => array(
				'label' => 'B',
				'key'   => 'body',
				'title' => esc_attr__( 'Article-specific BODY', 'head-footer-code' ),
			),
			'footer' => array(
				'label' => 'F',
				'key'   => 'footer',
				'title' => esc_attr__( 'Article-specific FOOTER', 'head-footer-code' ),
			),
		);

		echo '<div class="hfc-badges-wrapper ' . esc_attr( $behavior_class ) . '">';

		foreach ( $sections as $id => $data ) {
			if ( ! empty( $meta[ $data['key'] ] ) ) {
				printf(
					'<a href="post.php?post=%1$s&action=edit#auhfc_%2$s" class="badge" title="%3$s">%4$s</a>',
					$post_id,
					$id,
					esc_attr( "{$data['title']} ({$behavior_text})" ),
					esc_html( $data['label'] )
				);
			}
		}

		echo '</div>';
	}
}
