<?php
/**
 * Head & Footer Code plugin for WordPress
 *
 * @link        https://urosevic.net/
 * @link        https://www.techwebux.com/
 * @since       1.0.0
 * @package     Head_Footer_Code
 *
 * Plugin Name: Head & Footer Code
 * Plugin URI:  https://urosevic.net/wordpress/plugins/head-footer-code/
 * Description: Easy add site-wide, category or article specific custom code before the closing <strong>&lt;/head&gt;</strong> and <strong>&lt;/body&gt;</strong> or after opening <strong>&lt;body&gt;</strong> HTML tag.
 * Version:     1.4.3
 * Author:      Aleksandar Urošević
 * Author URI:  https://urosevic.net/
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: head-footer-code
 * Domain Path: /languages
 * Requires at least: 4.9
 * Tested up to: 6.8
 * Requires PHP: 5.5
 */

namespace Techwebux\Hfc;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'HFC_VER', '1.4.3' );
define( 'HFC_VER_DB', '9' );
define( 'HFC_FILE', __FILE__ );
define( 'HFC_DIR', __DIR__ );
define( 'HFC_URL', plugin_dir_url( __FILE__ ) );
define( 'HFC_PLUGIN_NAME', 'Head & Footer Code' );
define( 'HFC_PLUGIN_SLUG', 'head-footer-code' );

// Load files.
require_once HFC_DIR . '/classes/autoload.php';
new Main();

/**
 * Add `wp_body_open` backward compatibility for WordPress installations prior 5.2
 */
if ( ! function_exists( 'wp_body_open' ) ) {
	/**
	 * Fire the wp_body_open action.
	 */
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
}
