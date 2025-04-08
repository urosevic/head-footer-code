<?php
/**
 * Generate Head & Footer Code indicator columns on article listing
 *
 * @package Head_Footer_Code
 */

namespace Techwebux\Hfc;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Techwebux\Hfc\Main;
use Techwebux\Hfc\Common;

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
	} // END public function __construct

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
	} // END public function admin_post_manage_columns

	/**
	 * Register Head & Footer Code column for posts table
	 *
	 * @param array $columns Array of existing columns for table.
	 */
	public function posts_columns( $columns ) {
		$columns['hfc'] = esc_html( HFC_PLUGIN_NAME );
		return $columns;
	} // END public function posts_columns

	/**
	 * Make Head & Footer Code column sortable
	 *
	 * @param array $columns Array of existing columns for table.
	 */
	public function posts_sortable_columns( $columns ) {
		$columns['hfc'] = 'hfc';
		return $columns;
	} // END public function posts_sortable_columns

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

		$sections = array();
		if ( ! empty( Common::get_meta( 'head', $post_id ) ) ) {
			$sections[] = sprintf(
				'<a href="post.php?post=%1$s&action=edit#auhfc_head" class="badge" title="%2$s">H</a>',
				$post_id,
				esc_html__( 'Article specific code is defined in HEAD section', 'head-footer-code' )
			);
		}
		if ( ! empty( Common::get_meta( 'body', $post_id ) ) ) {
			$sections[] = sprintf(
				'<a href="post.php?post=%1$s&action=edit#auhfc_body" class="badge" title="%2$s">B</a>',
				$post_id,
				esc_html__( 'Article specific code is defined in BODY section', 'head-footer-code' )
			);
		}
		if ( ! empty( Common::get_meta( 'footer', $post_id ) ) ) {
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
			$mode = Common::get_meta( 'behavior', $post_id );
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
				absint( $post_id ),              // 1
				esc_html( $method_description ), // 3
				esc_html( $method_label ),       // 4
				'<div class="badges">'
				. wp_kses( implode( '', $sections ), Common::allowed_html() )
				. '</div>'                       // 5
			);
		}
	} // END public function posts_custom_columns
} // END class Grid
