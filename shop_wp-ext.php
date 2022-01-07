<?php
/**
 * ShopWP Ext			
 *
 * @package           ShopWP-Ext
 * @author            Jason Parks
 * @copyright         2022 Jason Parks
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       ShopWP Ext
 * Plugin URI:        https://github.com/jrparks/shopwp-ext
 * Description:       ShopWP Ext is an extension plugin created to 
 *                    extend the functionality of ShopWP which enables
 *                    external image support that can be used anywhere
 *                    your Shopify images are needed in your wordpress site.
 *                    Common areas include featured image and search. 
 * Version:           1.0.1
 * Requires at least: 5.8.2
 * Requires PHP:      7.4
 * Author:            Jason Parks
 * Author URI:        https://github.com/jrparks/shopwp-ext
 * Text Domain:       
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        
 */
 
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load the autoloader from it's own file
require_once plugin_dir_path( __FILE__ ) . 'autoloader.php';

// Convenience function to expose class method
function get_ext_image_url($post_id, $size = 'custom', $width = '', $height = ''){
	$lib = new \SWPE\Images\External_Images();
	return $lib->get_ext_image_url($post_id, $size, $width, $height);
}

// Register Activation Hook
register_activation_hook(__FILE__, function(){\SWPE\Core\Activator::activate();});

// Register Deactivation Hook
register_deactivation_hook(__FILE__, function(){\SWPE\Core\Deactivator::deactivate();});

// Register Uninstall Hook
register_uninstall_hook( __FILE__, 'swpe_fn_uninstall' );
function swpe_fn_uninstall() {
    \SWPE\Core\Uninstall::remove_plugin();
}

// Run the plugin.
function swpe_run_plugin() {
	$plugin_basename = plugin_basename(__FILE__);
	$plugin_url = plugin_dir_url(__FILE__);
	$plugin = new\SWPE\SWPE_Plugin($plugin_basename, $plugin_url);
	$plugin->initialize();
}
swpe_run_plugin();
