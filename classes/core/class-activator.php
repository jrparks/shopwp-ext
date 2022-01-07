<?php
namespace SWPE\Core;

/**
 * Fired during plugin activation
 *
 * @package    SWPE
 * @subpackage SWPE\Core
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SWPE\Utils\Log;
use SWPE\Admin\Settings;

class Activator {
	public static function activate() {
		try {
			Log::info('Activating SWPE Plugin.');
			
			// Require parent plugin
			if ( ! is_plugin_active( 'shopwp-pro/shopwp.php' ) and current_user_can( 'activate_plugins' ) ) {
				// Stop activation redirect and show error
				wp_die('Sorry, but this plugin requires the ShopWP Pro Plugin to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
			} else {
				// Set the initial plugin setting page options
				if ( is_admin() ) {	
					// Sets the default settings values on activation
					Settings::set_default_on_activate();
				}
			} 
		} catch (Error $e) {
			Log::info('Error during SWPE activation: '.$e);
		}
	}
}
