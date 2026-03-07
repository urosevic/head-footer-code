<?php
/**
 * Plugin Metadata Container
 *
 * This file defines the Plugin_Info class, which serves as a central
 * repository for all plugin-specific metadata (name, version, paths).
 * It replaces the reliance on global constants for improved testability
 * and cleaner dependency injection.
 *
 * @package   Head_Footer_Code
 * @since     1.5.4
 */

namespace Techwebux\Hfc;

/**
 * Class Plugin_Info
 *
 * A Data Transfer Object that holds immutable plugin metadata.
 * Centralizes properties like name, version, and paths to avoid
 * reliance on global constants across the plugin suite.
 */
class Plugin_Info {
	/** @var string Minial WordPress version (eg. `5.2`) */
	public $min_wp;

	/** @var string Minial PHP version (eg. `5.6`) */
	public $min_php;

	/** @var string Plugin version (eg. `1.5.4`) */
	public $version;

	/** @var int Plugin database version. */
	public $db_ver;

	/** @var string Absolute path to main plugin file. */
	public $file;

	/** @var string Plugin directory path without trailing slash. */
	public $dir;

	/** @var string Plugin directory URL with trailing slash. */
	public $url;

	/** @var string Basename of the plugin (eg. `head-footer-code/head-footer-code.php`). */
	public $basename;

	/** @var string Full plugin name. */
	public $name;

	/** @var string Plugin slug. */
	public $slug;

	public function __construct() {
		$this->min_wp   = HFC__MIN_WP;
		$this->min_php  = HFC__MIN_PHP;
		$this->version  = HFC_VER;
		$this->db_ver   = absint( HFC_VER_DB );
		$this->file     = HFC_FILE;
		$this->dir      = dirname( HFC_FILE );
		$this->url      = plugin_dir_url( HFC_FILE );
		$this->basename = plugin_basename( HFC_FILE );
		$this->name     = 'Head & Footer Code';
		$this->slug     = 'head-footer-code';
	}

	public static function get_static_data() {
		return new self();
	}

	public static function get_supported_taxonomies() {
		return array( 'category', 'product_cat' );
	}
}
