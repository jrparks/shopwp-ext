<?php
/**
 * The class Autoloader
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Registering our function into spl_autoload_register().
spl_autoload_register('swpe_autoloader');

function swpe_autoloader( $class_name ) {
    // These should be changed for your particular plugin requirements
    $parent_namespace = 'SWPE'; // Case Sensitive
    $classes_subfolder = 'classes';
    if ( false !== strpos( $class_name, $parent_namespace ) ) {
        $classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . $classes_subfolder . DIRECTORY_SEPARATOR;

        // Project namespace
        $project_namespace = $parent_namespace . '\\';
        $length = strlen( $project_namespace );

        // Remove top-level namespace (that is the current dir)
        $class_file = substr( $class_name, $length );
        // Swap underscores for dashes and lowercase
        $class_file = str_replace( '_', '-', strtolower( $class_file ) );

        // Prepend `class-` to the filename (last class part)
        $class_parts = explode( '\\', $class_file );
        $last_index = count( $class_parts ) - 1;
        $class_parts[ $last_index ] = 'class-' . $class_parts[ $last_index ];

        // Join everything back together and add the file extension
        $class_file = implode( DIRECTORY_SEPARATOR, $class_parts ) . '.php';
        $location = $classes_dir . $class_file;

        if ( ! is_file( $location ) ) {
            return;
        }
        require_once $location;
    }
} 
