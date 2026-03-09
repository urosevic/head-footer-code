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
	/** @var array Settings retrieved from the main controller. */
	private $settings;

	/** @var Plugin_Info Plugin metadata object. */
	protected $plugin;

	/** @var array Badges configuration. */
	protected $badges;

	/**
	 * Initializes the class and registers admin hooks.
	 *
	 * @param Plugin_Info $plugin Instance of the plugin info object.
	 * @param array       $settings Plugin settings array.
	 */
	public function __construct( Plugin_Info $plugin, $settings ) {
		$this->plugin   = $plugin;
		$this->settings = $settings;

		add_action( 'admin_init', array( $this, 'admin_manage_columns' ) );
	}

	/**
	 * Register hooks for posts and taxonomies screens.
	 *
	 * @return void
	 */
	public function admin_manage_columns() {
		$this->badges = $this->get_badges_config();

		// Handle columns for post types enabled in plugin settings.
		if ( isset( $this->settings['article']['post_types'] ) ) {
			foreach ( $this->settings['article']['post_types'] as $post_type ) {
				add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_grid_column' ) );
				add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'add_grid_sortable_column' ) );
				// Posts use action hook - content must be echoed, not returned.
				add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'posts_custom_columns' ), 10, 2 );
			}
		}
		// Handle columns for taxonomies enabled in plugin settings.
		if ( isset( $this->settings['article']['taxonomies'] ) ) {
			foreach ( $this->settings['article']['taxonomies'] as $taxonomy ) {
				add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'add_grid_column' ) );
				add_filter( "manage_edit-{$taxonomy}_sortable_columns", array( $this, 'add_grid_sortable_column' ) );
				// Taxonomies use filter hook - content must be returned, not echoed.
				add_filter( "manage_{$taxonomy}_custom_column", array( $this, 'taxonomies_custom_columns' ), 10, 3 );
			}
		}
	}

	/**
	 * Register Head & Footer Code column for posts and taxonomies table.
	 *
	 * @param array $columns  Array of existing columns for table.
	 * @return array $columns Array with custom Head & Footer Code column.
	 */
	public function add_grid_column( $columns ) {
		$columns['hfc'] = sprintf(
			'<span title="%s" class="hfc-column-header">HFC</span>',
			esc_attr( $this->plugin->name )
		);
		return $columns;
	}

	/**
	 * Make Head & Footer Code column sortable.
	 *
	 * @param array $columns  Array of existing columns for table.
	 * @return array $columns Array with custom Head & Footer Code column.
	 */
	public function add_grid_sortable_column( $columns ) {
		$columns['hfc'] = 'hfc';
		return $columns;
	}

	/**
	 * Populate article column with Head & Footer Code indicators.
	 *
	 * @param string  $column Table column name.
	 * @param integer $post_id Current article ID.
	 *
	 * @return void Echo conent for action eg `manage_posts_custom_column`
	 */
	public function posts_custom_columns( $column, $post_id ) {
		if ( 'hfc' !== $column ) {
			return;
		}

		$meta     = get_post_meta( $post_id, $this->plugin->meta_key, true );
		$edit_url = get_edit_post_link( $post_id );

		echo wp_kses_post( $this->render_badges( $meta, $edit_url, 'post' ) );
	}

	/**
	 * Populate taxonomy column with Head & Footer Code indicators.
	 *
	 * @param string  $output
	 * @param string  $column_name Current term column name.
	 * @param integer $term_id     Current taxonomy ID.
	 *
	 * @return string Content for filter eg `manage_category_custom_column`
	 */
	public function taxonomies_custom_columns( $output, $column_name, $term_id ) {
		if ( 'hfc' !== $column_name ) {
			return $output;
		}

		$meta = get_term_meta( $term_id, $this->plugin->meta_key, true );
		// Fallback for WP 5.2
		$taxonomy = filter_input( INPUT_GET, 'taxonomy', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! $taxonomy ) {
			$taxonomy = '';
		}
		$edit_url = get_edit_term_link( $term_id, $taxonomy );

		return $this->render_badges( $meta, $edit_url, 'taxonomy' );
	}

	/**
	 * Renders the badges HTML wrapper and individual badge links.
	 *
	 * @param array  $meta     The stored metadata for the post or term.
	 * @param string $edit_url The URL to the edit screen of the item.
	 * @param string $context  The context: `post` or `taxonomy` (default: `post`).
	 *
	 * @return string The generated HTML badges or empty indicator.
	 */
	private function render_badges( $meta, $edit_url, $context = 'post' ) {
		if ( empty( $meta['head'] ) && empty( $meta['body'] ) && empty( $meta['footer'] ) ) {
			return $this->get_empty_indicator();
		}

		if ( ! $edit_url ) {
			return $this->get_empty_indicator();
		}

		$behavior      = isset( $meta['behavior'] ) ? $meta['behavior'] : 'append';
		$behavior_text = $this->get_behavior_description( $behavior );
		$specific_type = ( 'taxonomy' === $context )
			? __( 'Taxonomy-specific', 'head-footer-code' )
			: __( 'Article-specific', 'head-footer-code' );
		$sections      = $this->badges;

		$output = '<div class="hfc-badges-wrapper ' . esc_attr( 'hfc-' . $behavior ) . '">';

		foreach ( $sections as $section_key => $data ) {
			if ( ! empty( $meta[ $section_key ] ) ) {
				$output .= sprintf(
					'<a href="%s#auhfc_%s" class="badge" title="%s: %s (%s)">%s</a>',
					esc_url( $edit_url ),
					esc_attr( $section_key ),
					esc_attr( $specific_type ),
					esc_attr( $data['title'] ),
					esc_attr( $behavior_text ),
					esc_html( $data['label'] )
				);
			}
		}

		$output .= '</div>';
		return $output;
	}

	/**
	 * Returns the indicator for empty code fields.
	 *
	 * @return string
	 */
	private function get_empty_indicator() {
		return '<span aria-hidden="true">—</span><span class="screen-reader-text">' . esc_attr__( 'No custom code', 'head-footer-code' ) . '</span>';
	}

	/**
	 * Gets a human-readable description of the meta behavior.
	 *
	 * @param string $behavior The behavior slug (`replace` or `append`).
	 * @return string The translated description for the badge title.
	 */
	private function get_behavior_description( $behavior ) {
		return ( 'replace' === $behavior )
			? esc_attr__( 'replace site-wide code with', 'head-footer-code' )
			: esc_attr__( 'append to site-wide code', 'head-footer-code' );
	}

	/**
	 * Returns the configuration array for location badges.
	 *
	 * @return array Multidimensional array of badge labels and titles.
	 */
	private function get_badges_config() {
		return array(
			'head'   => array(
				'label' => 'H',
				'title' => esc_attr__( 'HEAD', 'head-footer-code' ),
			),
			'body'   => array(
				'label' => 'B',
				'title' => esc_attr__( 'BODY', 'head-footer-code' ),
			),
			'footer' => array(
				'label' => 'F',
				'title' => esc_attr__( 'FOOTER', 'head-footer-code' ),
			),
		);
	}
}
