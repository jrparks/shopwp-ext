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

use SWPE\Utils\Log;
use SWPE\Admin\Config;
use SWPE\Utils\HookRegistry;
use SWPE\Jobs\Update_External_Featured_Image_Urls as Job;

class Deactivator {

	public static function deactivate() {
		try {
			Log::info('Deactivating SWPE Plugin.');
			
			//Remove styles and scripts
			Config::dequeue_styles();
			Config::dequeue_scripts();
			
			// Remove all hooks (actions and filters) from the registry
			$reg = HookRegistry::get();
			$reg->remove_all_hooks();
			
			// Disable the cron Job
			Job::unschedule_external_product_images_cron_job();

		} catch (Error $e) {
			Log::info('Error during SWPE deactivation: '.$e);
		}
	}
} 
