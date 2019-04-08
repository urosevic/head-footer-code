<?php
/**
 * @link        https://urosevic.net
 * @since       1.0.0
 * @package     Head_Footer_Code
 *
 * @wordpress-plugin
 * Plugin Name: Head & Footer Code
 * Plugin URI:  https://urosevic.net/wordpress/plugins/head-footer-code/
 * Description: Easy add site-wide and/or article specific custom code to head and/or footer sections (before the &lt;/head&gt; or &lt;/body&gt;) by hooking to <code>wp_head</code> and <code>wp_footer</code>.
 * Version:     1.0.9.1
 * Author:      Aleksandar Urosevic
 * Author URI:  https://urosevic.net
 * License:     GPL-3.0+
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: head-footer-code
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPAU_HEAD_FOOTER_CODE_VER', '1.0.9.1' );
define( 'WPAU_HEAD_FOOTER_CODE_DB_VER', '2' );
define( 'WPAU_HEAD_FOOTER_CODE_FILE', basename( __FILE__ ) );
define( 'WPAU_HEAD_FOOTER_CODE_INC', dirname( __FILE__ ) . '/inc/' );

// Load files.
require_once 'inc/helpers.php';
