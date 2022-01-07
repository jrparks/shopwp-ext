<?php
namespace SWPE\Core;

/**
 * Fired during plugin deactivation
 *
 * @package    SWPE
 * @subpackage SWPE\Core
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Exit if uninstall constant is not defined
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

use SWPE\Utils\Log;
use SWPE\Admin\Config;
use SWPE\Utils\HookRegistry;
use SWPE\Jobs\Update_External_Featured_Image_Urls as Job;

class Uninstall {
	public static function remove_plugin() {
		try {
			Log::info('Uninstalling the SWPE Plugin.');
			
			// Remove styles and scripts
			Config::dequeue_styles();
			Config::dequeue_scripts();
			
			// Remove all hooks from the registry
			$reg = HookRegistry::get();
			$reg->remove_all_hooks();
			
			// Disable the cron Job
			Job::unschedule_external_product_images_cron_job();
			
			// Remove plugin options
			delete_option('swpe_admin_option_name');
			
		} catch (Error $e) {
			Log::info('Error during SWPE uninstall.'.$e);
		}
	}
}
