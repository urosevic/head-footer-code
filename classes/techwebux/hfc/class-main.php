<?php
/**
 * Main class for Head & Footer Code
 *
 * @package Head_Footer_Code
 */

namespace Techwebux\Hfc;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Main {
	/**
	 * Cached settings.
	 *
	 * @var array|null
	 */
	private static $settings = null;

	public function __construct() {
		register_activation_hook( HFC_FILE, array( $this, 'activate' ) );
		// Include back-end/front-end resources and maybe update settings.
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Plugin Activation hook function to check for Minimum PHP and WordPress versions
	 */
	public function activate() {
		global $wp_version;

		// Compare PHP and WordPress required and current versions
		if ( version_compare( PHP_VERSION, '5.5', '<' ) ) {
			$scope   = 'PHP';
			$version = '5.5'; // Minimum version of PHP required for this plugin.
		} elseif ( version_compare( $wp_version, '4.9', '<' ) ) {
			$scope   = 'WordPress';
			$version = '4.9'; // Minimum version of WordPress required for this plugin.
		} else {
			return '';
		}

		// First deactivate plutin...
		deactivate_plugins( HFC_FILE );

		// ... then inform user why plugin cannot be activated
		wp_die(
			'<p>' . sprintf(
				/* translators: 1: Plugin name, 2: PHP or WordPress, 3: min version of PHP or WordPress */
				esc_html__( 'The %1$s plugin cannot run on %2$s version older than %3$s. Please contact your host and ask them to upgrade.', 'head-footer-code' ),
				sprintf( '<strong>%s</strong>', esc_html( HFC_PLUGIN_NAME ) ),
				esc_attr( $scope ),
				esc_attr( $version )
			) . '</p>'
		);
	} // END public function activate

	/**
	 * Function to load subclasses, check and update if it has to be done
	 */
	public function plugins_loaded() {
		// Include back-end/front-end resources based on capabilities.
		// https://wordpress.org/documentation/article/roles-and-capabilities/
		if ( is_admin() && current_user_can( 'publish_posts' ) && Common::user_has_allowed_role() ) {
			// Load Settings if the current user can manage options
			if ( current_user_can( 'manage_options' ) ) {
				new Settings();
			}
			// Always load the Grid and Metabox classes for allowed roles.
			new Grid();
			new Metabox_Article();

			// If the user can manage categories, load the Metabox_Category class.
			if ( current_user_can( 'manage_categories' ) ) {
				new Metabox_Category();
			}
		} elseif ( ! is_admin() ) {
			// Load front-end magic.
			new Front();
		}

		// Bail if this plugin data doesn't need updating.
		if ( get_option( 'auhfc_db_ver' ) >= HFC_VER_DB ) {
			return;
		}

		// Require update script and trigger update function.
		require_once HFC_DIR . '/update.php';
		auhfc_update();
	} // END public function plugins_loaded

	/**
	 * Enqueue admin styles and scripts to enable code editor in plugin settings and custom column on article listing
	 *
	 * @param  string $hook Current page hook.
	 */
	public function admin_enqueue_scripts( $hook ) {
		// Admin Stylesheet.
		if ( in_array( $hook, array( 'post.php', 'edit.php', 'tools_page_' . HFC_PLUGIN_SLUG ), true ) ) {
			wp_enqueue_style(
				'head-footer-code-admin',
				HFC_URL . 'assets/css/admin.min.css',
				array(),
				HFC_VER
			);
		}

		// Codemirror Assets.
		$screen = get_current_screen();
		if (
			'tools_page_' . HFC_PLUGIN_SLUG === $hook ||
			'post.php' === $hook ||
			(
				'term.php' === $hook
				&& 'edit-category' === $screen->id
			)
		) {
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
				HFC_URL . 'assets/css/edit.min.css',
				array(),
				HFC_VER
			);
		}
		return;
	} // END public function admin_enqueue_scripts

	/**
	 * Provide global settings with default fallback.
	 *
	 * @return array Arary of defined global values.
	 */
	public static function settings() {
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
	} // END public static function settings
} // END class Main
