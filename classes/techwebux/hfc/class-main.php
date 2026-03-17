<?php
/**
 * Main plugin orchestrator.
 *
 * Handles plugin bootstrap, hook registration, admin asset management,
 * and initializes core components.
 *
 * @package    Head_Footer_Code
 * @since      1.4.0
 */

namespace Techwebux\Hfc;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Main {
	/** @var array Settings retrieved from the main controller. */
	private static $settings = null;

	/** @var Plugin_Info Plugin metadata object. */
	protected $plugin;

	/**
	 * Initializes the class and registers hooks.
	 */
	public function __construct() {
		$this->plugin = new Plugin_Info();
		Common::init( $this->plugin );

		add_filter( 'safe_style_css', array( $this, 'extend_safe_css' ) );

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Checks environment compatibility during plugin activation.
	 *
	 * Aborts activation and informs the user if the server PHP version
	 * or WordPress version does not meet the minimum requirements.
	 *
	 * @return void
	 */
	public static function plugin_activation() {
		$plugin = Plugin_Info::get_static_data();

		$requirements = array(
			'PHP'       => array(
				'min'     => $plugin->min_php,
				'current' => PHP_VERSION,
			),
			'WordPress' => array(
				'min'     => $plugin->min_wp,
				'current' => $GLOBALS['wp_version'],
			),
		);

		foreach ( $requirements as $type => $ver ) {
			if ( version_compare( $ver['current'], $ver['min'], '<' ) ) {

				deactivate_plugins( $plugin->file );

				wp_die(
					'<p>' . sprintf(
						/* translators: 1: Plugin name, 2: PHP or WordPress, 3: current version, 4: minimum version */
						esc_html__( '%1$s activation error: %2$s %3$s is outdated. Minimum required: %4$s.', 'head-footer-code' ),
						'<strong>' . esc_html( $plugin->name ) . '</strong>',
						esc_html( $type ),
						esc_html( $ver['current'] ),
						esc_html( $ver['min'] )
					) . '</p>'
				);
			}
		}
	}

	/**
	 * Function to load subclasses, check and update if it has to be done
	 */
	public function plugins_loaded() {
		$settings = self::get_settings();

		// Include back-end/front-end resources based on capabilities.
		// https://wordpress.org/documentation/article/roles-and-capabilities/
		if ( is_admin() && current_user_can( 'publish_posts' ) && Common::user_has_allowed_role() ) {
			// Load Settings if the current user can manage options
			if ( current_user_can( 'manage_options' ) ) {
				new Settings( $this->plugin, $settings );
			}
			// Always load the Grid and Metabox classes for allowed roles.
			new Grid( $this->plugin, $settings );
			new Metabox_Article( $this->plugin, $settings );
			new Metabox_Taxonomy( $this->plugin, $settings );

		} elseif ( ! is_admin() ) {
			// Load front-end magic.
			new Front( $this->plugin, $settings );
		}

		// Bail if this plugin data doesn't need updating.
		if ( get_option( 'auhfc_db_ver' ) >= $this->plugin->db_ver ) {
			return;
		}

		// Lock initiated updating
		if ( get_transient( 'auhfc_updating' ) ) {
			return;
		}
		set_transient( 'auhfc_updating', true, 5 * MINUTE_IN_SECONDS );

		// Require update script and trigger update function.
		require_once $this->plugin->dir . '/update.php';
		auhfc_update();

		// Remove updating lock
		delete_transient( 'auhfc_updating' );
	}

	/**
	 * Enqueue admin styles and scripts to enable code editor in plugin settings and custom column on article and taxonomy listings
	 *
	 * @param  string $hook Current page hook.
	 */
	public function admin_enqueue_scripts( $hook ) {
		// Admin Stylesheet.
		if ( in_array( $hook, array( 'post.php', 'post-new.php', 'edit.php', 'edit-tags.php', 'tools_page_' . $this->plugin->slug ), true ) ) {
			wp_enqueue_style(
				'head-footer-code-admin',
				$this->plugin->url . 'assets/css/admin.min.css',
				array(),
				$this->plugin->version
			);
		}

		// Codemirror Assets.
		$screen = get_current_screen();

		// Prepare conditions
		$is_hfc_settings = ( 'tools_page_' . $this->plugin->slug === $hook );
		$is_post_edit    = in_array( $hook, array( 'post.php', 'post-new.php' ), true );
		$is_term_edit    = ( 'term.php' === $hook && in_array( $screen->taxonomy, self::$settings['article']['taxonomies'], true ) );

		if ( $is_hfc_settings || $is_post_edit || $is_term_edit ) {
			// Define $cm_settings to prevent undefined variable error.
			$cm_settings = array(
				'codeEditor' => wp_enqueue_code_editor(
					array(
						'type'       => 'text/html',
						'codemirror' => array(
							'autoRefresh' => true, // Deal with metaboxes rendering
							'extraKeys'   => array(
								'Tab' => 'indentMore', // Enable TAB indent
							),
						),
					)
				),
			);
			wp_localize_script( 'code-editor', 'cm_settings', $cm_settings );
			wp_enqueue_style( 'wp-codemirror' );
			wp_enqueue_script( 'wp-codemirror' );
			wp_enqueue_style(
				'head-footer-code-edit',
				$this->plugin->url . 'assets/css/edit.min.css',
				array(),
				$this->plugin->version
			);
		}
		return;
	}

	/**
	 * Allow widely used style properties for KSES, eg. `display` in WP prior 7.0
	 * and `visibility` used in GTM noscript.
	 *
	 * @param array $styles The current array of allowed CSS properties.
	 * @return array Modified array of allowed CSS properties.
	 */
	public function extend_safe_css( $styles ) {
		return array_unique( array_merge( (array) $styles, array( 'display', 'visibility' ) ) );
	}

	/**
	 * Retrieves and parses plugin settings with default fallback values.
	 *
	 * @return array {
	 * Array of settings.
	 * @type array $sitewide Site-wide settings (head, body, footer, priorities).
	 * @type array $homepage Homepage-specific settings.
	 * @type array $article  Post type and role-based access settings.
	 * }
	 */
	public static function get_settings() {
		// If settings are already cached, return them.
		if ( null !== self::$settings ) {
			return self::$settings;
		}

		// Define default settings.
		$settings = array(
			'sitewide' => array(
				'head'           => '',
				'body'           => '',
				'footer'         => '',
				'priority_h'     => 10,
				'priority_b'     => 10,
				'priority_f'     => 10,
				'do_shortcode_h' => 'n',
				'do_shortcode_b' => 'n',
				'do_shortcode_f' => 'n',
			),
			'homepage' => array(
				'head'     => '',
				'body'     => '',
				'footer'   => '',
				'behavior' => 'append',
				'paged'    => 'yes',
			),
			'article'  => array(
				'post_types'    => array(),
				'taxonomies'    => array(),
				'allowed_roles' => array(),
			),
		);

		// Fetch and merge settings for each group.
		foreach ( $settings as $key => $default_values ) {
			$saved_settings   = get_option( "auhfc_settings_{$key}", $default_values );
			$settings[ $key ] = wp_parse_args( $saved_settings, $default_values );
		}

		// Cache the results.
		self::$settings = $settings;

		return $settings;
	}
}
