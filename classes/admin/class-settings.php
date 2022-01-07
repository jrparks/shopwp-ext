<?php
namespace SWPE\Admin;

/**
 * Displays plugin settings page
 *
 * @package    SWPE
 * @subpackage SWPE\Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SWPE\Utils\Log;
use SWPE\Utils\HookRegistry;
use SWPE\Images\External_Images;
use SWPE\Jobs\Update_External_Featured_Image_Urls as Job;

class Settings {
	private $admin_options;
	private $registry;
	private $extImgLib;

	public function __construct() {
		$this->registry = HookRegistry::get();
		$this->extImgLib = new External_Images();
	}
	
	public function setup() {
		$this->registry->add_hook( 'admin_add_plugin_page', 'action', 'admin_menu', $this, 'admin_add_plugin_page', 10, 2 );
		$this->registry->add_hook( 
		'admin_page_init', 'action', 'admin_init', $this, 'admin_page_init', 10, 2 );

		// Updates actions when values changed
		$this->registry->add_hook( 
		'trigger_action_options', 'action', 'update_option_swpe_admin_option_name', $this, 'trigger_action_options', 10, 2 );	
	}
	
	public function trigger_action_options( $new_value, $old_value ) {
		$inp1 = 'enable_external_feature_image_url';
		$inp2 = 'enable_external_image_update_daily_cron';
		if ( $new_value !== $old_value && ! empty( $new_value ) ) {
			if ( isset($new_value[$inp1]) && !isset($old_value[$inp1]) ) {
				Log::debug('SWPE Enable Img Support', true);
				$this->extImgLib->enable_ext_image_support(); // enable
			} elseif ( !isset($new_value[$inp1]) && isset($old_value[$inp1]) ) {
				Log::debug('SWPE Disable Img Support', true);
				$this->extImgLib->disable_ext_image_support(); // disable 
			}
			
			if ( isset($new_value[$inp2]) && !isset($old_value[$inp2]) ) {
				Log::debug('SWPE Enable Daily Cron Support', true);
				Job::schedule_external_product_images_cron_job();
			} elseif ( !isset($new_value[$inp2]) && isset($old_value[$inp2]) ) {
				Log::debug('SWPE Disable Daily Cron Support', true);
				Job::unschedule_external_product_images_cron_job(); 
			}
		}
		return $new_value;
	}
	
	public function isExtImageSupportEnabled() {
		$enabled = false;
		$opt = get_option('swpe_admin_option_name');
		if (isset($opt['enable_external_feature_image_url']) && $opt['enable_external_feature_image_url'] === 'enable_external_feature_image_url') {
			$enabled = true;
		}
		return $enabled;
	}
	
	public function isDailyCronEnabled() {
		$enabled = false;
		$opt = get_option('swpe_admin_option_name');
		if (isset($opt['enable_external_image_update_daily_cron']) && $opt['enable_external_image_update_daily_cron'] === 'enable_external_image_update_daily_cron') {
			$enabled = true;
		}
		return $enabled;
	}
	
	public function configure_setup() {
		Log::debug('SWPE Toggle Triggers', true);
		$opt = get_option('swpe_admin_option_name');
		
		if (isset($this->admin_options['enable_external_feature_image_url']) && $this->admin_options['enable_external_feature_image_url'] === 'enable_external_feature_image_url') {
			$this->extImgLib->enable_ext_image_support(); // enable
		} else {
			$this->extImgLib->disable_ext_image_support(); // disable 
		}
		if (isset($this->admin_options['enable_external_image_update_daily_cron']) && $this->admin_options['enable_external_image_update_daily_cron'] === 'enable_external_image_update_daily_cron') {
			Job::schedule_external_product_images_cron_job();
		} else {
			Job::unschedule_external_product_images_cron_job();
		}
	}
	
	public function toggle_ext_image_support() {
		$opt = get_option('swpe_admin_option_name');
		if ($opt['enable_external_feature_image_url'] === 'enable_external_feature_image_url') {
			$this->extImgLib->enable_ext_image_support(); // enable
		} else {
			$this->extImgLib->disable_ext_image_support(); // disable 
		}
	}

	public function toggle_cron_job_support() {
		$opt = get_option('swpe_admin_option_name');
		if ($opt['enable_external_image_update_daily_cron'] === 'enable_external_image_update_daily_cron') {
			Job::schedule_external_product_images_cron_job();
		} else {
			Job::unschedule_external_product_images_cron_job();
		}
	}
	
	public static function set_default_on_activate() {
		$defaults = array(
		'enable_external_feature_image_url' => 'enable_external_feature_image_url', 'enable_external_image_update_daily_cron' => 'enable_external_image_update_daily_cron');
		try {
			$option_exists = (get_option('swpe_admin_option_name', null) !== null);
			if ( !$option_exists ){
				Log::debug('Adding SWPE default options');
				
				// Add defaults to the database
				add_option( 'swpe_admin_option_name', $defaults );
				
				// Add External Image Support
				$extImgLib = new External_Images();
				$extImgLib->enable_ext_image_support();
				
				// Add Cron
				Job::schedule_external_product_images_cron_job();
				
			} 
		} catch (exception $e) {
			Log::info('Error during SWPE activation: '.$e);
		}
	}
		
	public function admin_add_plugin_page() {
		add_menu_page(
			'ShopWP Extensions', // page_title
			'ShopWP Ext', // menu_title
			'manage_options', // capability
			'shopwp_ext', // menu_slug
			array( $this, 'admin_create_admin_page' ), // function
			'dashicons-admin-plugins', // icon_url
			80 // position
		);
	}

	public function admin_create_admin_page() {
		$this->admin_options = get_option( 'swpe_admin_option_name' ); ?>

		<div class="wrap">
			
			<h2><svg width="30px" height="30px" viewBox="0 0 200 200" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><g transform="matrix(0.8,0,0,0.8,23.0271,31.2238)"><g><g transform="matrix(0.91,0,0,0.91,0,0)"><g><path d="M117.808,140C117.808,148.798 111.396,156.126 103,157.556L103,177L157.656,177C168.322,177 177,168.322 177,157.656L177,103L151.808,103L151.808,100C151.808,93.488 146.512,88.19 140,88.19C133.488,88.19 128.192,93.488 128.192,100L128.192,103L103,103L103,122.444C111.396,123.874 117.808,131.202 117.808,140Z" style="fill:rgb(61,77,101);fill-rule:nonzero;"/></g></g><g transform="matrix(0.91,0,0,0.91,-0.728524,-0.759017)"><g><path d="M140,82.19C148.796,82.19 156.126,88.602 157.556,97L177,97L177,42.344C177,31.678 168.322,23 157.656,23L103,23L103,48.19L100,48.19C93.488,48.19 88.192,53.488 88.192,60C88.192,66.512 93.488,71.81 100,71.81L103,71.81L103,97L122.444,97C123.876,88.602 131.204,82.19 140,82.19Z" style="fill:rgb(55,67,85);fill-rule:nonzero;"/></g></g><g transform="matrix(0.91,0,0,0.91,-20.0893,-24.1815)"><g><path d="M82.192,60C82.192,51.202 88.604,43.874 97,42.444L97,23L42.344,23C31.678,23 23,31.678 23,42.344L23,97L48.192,97L48.192,100C48.192,106.512 53.488,111.81 60,111.81C66.512,111.81 71.808,106.512 71.808,100L71.808,97L97,97L97,77.556C88.604,76.126 82.192,68.798 82.192,60Z" style="fill:rgb(131,147,167);fill-rule:nonzero;"/></g></g><g transform="matrix(0.91,0,0,0.91,0,0)"><g><path d="M60,117.81C51.204,117.81 43.874,111.398 42.444,103L23,103L23,157.656C23,168.322 31.678,177 42.344,177L97,177L97,151.81L100,151.81C106.512,151.81 111.808,146.512 111.808,140C111.808,133.488 106.512,128.19 100,128.19L97,128.19L97,103L77.556,103C76.124,111.398 68.796,117.81 60,117.81Z" style="fill:rgb(83,101,125);fill-rule:nonzero;"/></g></g></g></g></svg>ShopWP Extensions</h2>
			<p>ShopWP Extensions to enable further integration with your existing site. I have currently added in External Image Support for the Shopify Images as external featured images. Also set a CRON to pull the updated images once a day. I will change this once appropriate hook is added to the Shop WP Pro plugin. Enjoy!</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'swpe_admin_option_group' );
					do_settings_sections( 'swpe_admin' );
					submit_button();
				?>
			</form>
		</div>
		<?php 
	}

	public function admin_page_init() {	
		register_setting(
			'swpe_admin_option_group', // option_group
			'swpe_admin_option_name', // option_name
			array( $this, 'admin_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'admin_setting_section', // id
			'Settings', // title
			array( $this, 'admin_section_info' ), // callback
			'swpe_admin' // page
		);

		add_settings_field(
			'enable_external_feature_image_url', // id
			'Enable External Feature Image URL', // title
			array( $this, 'enable_external_feature_image_url_callback' ), // callback
			'swpe_admin', // page
			'admin_setting_section' // section
		);

		add_settings_field(
			'enable_external_image_update_daily_cron', // id
			'Enable External Image Update Daily Cron', // title
			array( $this, 'enable_external_image_update_daily_cron_callback' ), // callback
			'swpe_admin', // page
			'admin_setting_section' // section
		);
	}

	public function admin_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['enable_external_feature_image_url'] ) ) {
			$sanitary_values['enable_external_feature_image_url'] = $input['enable_external_feature_image_url'];
		}

		if ( isset( $input['enable_external_image_update_daily_cron'] ) ) {
			$sanitary_values['enable_external_image_update_daily_cron'] = $input['enable_external_image_update_daily_cron'];
		}
		return $sanitary_values;
	}
	
	public function admin_section_info() {
		
	}

	public function enable_external_feature_image_url_callback() {
		printf(
			'<input type="checkbox" class="swpe-ui-toggle" name="swpe_admin_option_name[enable_external_feature_image_url]" id="enable_external_feature_image_url" value="enable_external_feature_image_url" %s>',
			( isset( $this->admin_options['enable_external_feature_image_url'] ) && $this->admin_options['enable_external_feature_image_url'] === 'enable_external_feature_image_url' ) ? 'checked' : ''
		);
	}

	public function enable_external_image_update_daily_cron_callback() {
		$spinner = '<div id="spinner" style="margin-top:-1.85em; padding-left:6.25em; display:none;"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 50 50"><path fill="#6cb847" d="M25,5A20.14,20.14,0,0,1,45,22.88a2.51,2.51,0,0,0,2.49,2.26h0A2.52,2.52,0,0,0,50,22.33a25.14,25.14,0,0,0-50,0,2.52,2.52,0,0,0,2.5,2.81h0A2.51,2.51,0,0,0,5,22.88,20.14,20.14,0,0,1,25,5Z"><animateTransform attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.5s" repeatCount="indefinite"/></path></svg></div>';
		printf(
			'<input type="checkbox" class="swpe-ui-toggle" name="swpe_admin_option_name[enable_external_image_update_daily_cron]" id="enable_external_image_update_daily_cron" value="enable_external_image_update_daily_cron" %s> <label for="enable_external_image_update_daily_cron">* Requires WP-Cron</label><div class="wrap"><button type="button" id="run_job">Run Job</button>'.$spinner.'</div>',
			( isset( $this->admin_options['enable_external_image_update_daily_cron'] ) && $this->admin_options['enable_external_image_update_daily_cron'] === 'enable_external_image_update_daily_cron' ) ? 'checked' : ''
		);
	}
}
