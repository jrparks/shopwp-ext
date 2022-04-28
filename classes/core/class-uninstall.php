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
use SWPE\Admin\Settings;
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
			$remove = Settings::removePluginData();
			if ($remove) {
				delete_option('swpe_admin_option_name');

				$meta_type  = 'user';
				$user_id    = 0; // This will be ignored, since we are deleting for all users.
				$meta_key   = '_thumbnail_ext_url';
				$meta_value = ''; // Also ignored. The meta will be deleted regardless of value.
				$delete_all = true;
				delete_metadata( $meta_type, $user_id, $meta_key, $meta_value, $delete_all );
			}

			// Remove the custom value entered into the thumbnail_id
			$args = array(
				'posts_per_page' => -1,
				'post_type' => 'wps_products',
				'suppress_filters' => true
			);
			$posts_array = get_posts( $args );
			foreach($posts_array as $post_array) {
				update_post_meta($post_array->ID, '_thumbnail_id', '');
			}
			
		} catch (Error $e) {
			Log::info('Error during SWPE uninstall.'.$e);
		}
	}
} 
