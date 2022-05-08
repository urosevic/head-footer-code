<?php
/**
 * Head & Footer Code plugin for WordPress
 *
 * @link        https://urosevic.net/
 * @since       1.0.0
 * @package     Head_Footer_Code
 *
 * Plugin Name: Head & Footer Code
 * Plugin URI:  https://urosevic.net/wordpress/plugins/head-footer-code/
 * Description: Easy add site-wide, category or article specific custom code before the closing <strong>&lt;/head&gt;</strong> and <strong>&lt;/body&gt;</strong> or after opening <strong>&lt;body&gt;</strong> HTML tag.
 * Version:     1.3.0
 * Author:      Aleksandar Urošević
 * Author URI:  https://urosevic.net/
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: head-footer-code
 * Domain Path: /languages
 * Requires at elast: 4.9
 * Tested up to: 6.0
 * Requires PHP: 5.6
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPAU_HEAD_FOOTER_CODE_VER', '1.3.0' );
define( 'WPAU_HEAD_FOOTER_CODE_DB_VER', '7' );
define( 'WPAU_HEAD_FOOTER_CODE_FILE', __FILE__ );
define( 'WPAU_HEAD_FOOTER_CODE_DIR', dirname( WPAU_HEAD_FOOTER_CODE_FILE ) . '/' );
define( 'WPAU_HEAD_FOOTER_CODE_INC', WPAU_HEAD_FOOTER_CODE_DIR . 'inc/' );

// Load files.
require_once 'inc/helpers.php';
