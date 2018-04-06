<?php
/**
 * @link        https://urosevic.net
 * @since       1.0.0
 * @package     Head_Footer_Code
 *
 * @wordpress-plugin
 * Plugin Name: Head & Footer Code
 * Plugin URI:  https://urosevic.net/wordpress/plugins/head-footer-code/
 * Description: Easy add site-wide and/or article specific custom code to head and/or footer sections (before the &lt;/head&gt; or &lt;/body&gt;) by hooking to <code>wp_head</code> and <code>wp_footer</code>. Support Multisite environment.
 * Version:     1.0.7
 * Author:      Aleksandar Urosevic
 * Author URI:  https://urosevic.net
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: head-footer-code
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPAU_HEAD_FOOTER_CODE_VER', '1.0.7' );

// Load files.
require_once 'inc/helpers.php';
if ( is_admin() ) {
	require_once 'inc/settings.php';
	require_once 'inc/metaboxes.php';
} else {
	require_once 'inc/front.php';
}
