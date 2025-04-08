<?php

namespace Techwebux\Hfc;

spl_autoload_register( __NAMESPACE__ . '\autoload' );

/**
 * Autoload function for Techwebux\Hfc classes.
 *
 * @param string $class_name The fully qualified class name.
 * @return bool True if class was loaded, false otherwise.
 */
function autoload( $class_name ) {
	// Ensure the class belongs to the current namespace.
	if (
		empty( $class_name )
		|| 0 !== strpos( $class_name, __NAMESPACE__ . '\\' )
	) {
		// Not our namespace, bail out.
		return false;
	}

	// Replace underscores with dashes and convert class name to lowercase, then split.
	$components = explode(
		'\\',
		str_replace( '_', '-', strtolower( $class_name ) )
	);

	// Replace last component with composed class filename.
	$components[] = 'class-' . array_pop( $components ) . '.php';

	// Define class real path.
	$class_path = realpath( __DIR__ . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $components ) );

	// Check if the class file exists within the plugin directory before including.
	if ( ! empty( $class_path ) && file_exists( $class_path ) ) {
		// We already making sure that file is exists and valid.
		require_once $class_path; // phpcs:ignore
		return true;
	}
	return false;
} // END function autoload
