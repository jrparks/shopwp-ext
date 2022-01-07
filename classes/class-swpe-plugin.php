<?php
namespace SWPE;

/**
 * The main plugin class
 *
 * @package    SWPE
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SWPE\Admin\Config;
use SWPE\Utils\HookRegistry;
use SWPE\Images\External_Images;
use SWPE\Admin\Settings;
use SWPE\Jobs\Update_External_Featured_Image_Urls as Job;
use SWPE\Api\Endpoint as Api;

class SWPE_Plugin {
	private $registry;
	protected $plugin_name;
	protected $plugin_version;
	protected $plugin_basename;
	protected $plugin_url;
	protected $plugin_path;
	
	public function __construct($plugin_basename, $plugin_url) {
		// Loads the HookRegistry
		$this->registry = HookRegistry::get();
		// Set Plugin Properties
		$this->plugin_name = 'shopwp_ext';
		$this->plugin_version = '1.0.1';
		$this->plugin_basename = $plugin_basename;
		$this->plugin_url = $plugin_url;
	}
	
	public function initialize() {
			// Loads the plugin config and javascript
			$config = new Config( $this->plugin_name, $this->plugin_version, $this->plugin_basename, $this->plugin_url);
			$config->setup();
			
			// Init Library
			$extImgLib = new External_Images();

			// Loads the plugin settings page
			$plugin_settings_page = new Settings();
			$plugin_settings_page->setup();

			if ($plugin_settings_page->isExtImageSupportEnabled()){
				$extImgLib->enable_ext_image_support(); 
			} else {
				$extImgLib->disable_ext_image_support();
			}
			if ($plugin_settings_page->isDailyCronEnabled()){
				Job::schedule_external_product_images_cron_job();
			} else {
				Job::unschedule_external_product_images_cron_job();
			}	
			
			// Loads the plugin settings page
			$api = new Api();
			$api->setup();
	}
}
