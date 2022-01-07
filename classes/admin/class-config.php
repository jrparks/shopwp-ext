<?php
namespace SWPE\Admin;

/**
 * Fired during plugin activation to load scripts and plugin config
 *
 * @package    SWPE
 * @subpackage SWPE\Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SWPE\Utils\HookRegistry;

class Config {
	private $registry;
	protected $plugin_name;
	protected $plugin_version;
	protected $plugin_basename;
	protected $plugin_url;
	
	public function __construct($plugin_name, $plugin_version, $plugin_basename, $plugin_url) {
		$this->registry = HookRegistry::get();
		$this->plugin_name = $plugin_name;
		$this->plugin_version = $plugin_version;
		$this->plugin_basename = $plugin_basename;
		$this->plugin_url = $plugin_url;
	}
	
	public function setup() {
        if (is_admin()) {
			$this->registry->add_hook( 'products_featured_script', 'action', 'admin_enqueue_scripts', $this, 'products_featured_script', 10, 1 );
			
			$this->registry->add_hook( 'settings_job_script', 'action', 'admin_enqueue_scripts', $this, 'settings_job_script', 10, 1 );
			
			$this->registry->add_hook( 'enqueue_styles', 'action', 'admin_enqueue_scripts', $this, 'enqueue_styles', 10, 1 );

			$this->registry->add_hook( 'add_settings_links', 'filter', 'plugin_action_links_' . $this->plugin_basename, $this, 'add_settings_links', );
		}
	}
	
	/**
	 * Add settings link to plugin page
	 *
	 * @param $links
	 * @return mixed
	 */
	public function add_settings_links( $links ) {
		$settings_link = array(
			'<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings') . '</a>',
		);
		return array_merge( $settings_link, $links );
	}
	
	/**
	 * Load Custom JS For wps_products post type
	 *
	 */
	public function products_featured_script() {
		global $post_type;
		if( 'wps_products' == $post_type ){
			wp_enqueue_script( 'swpe-products-featured-script', $this->plugin_url . 'assets/js/swpe-featured-image.js', array(), $this->plugin_version, true );
		}
	}
	
	/**
	 * Load Custom JS For settings page ajax job
	 *
	 */
	public function settings_job_script() {
		wp_enqueue_script( 'swpe-job-script', $this->plugin_url . 'assets/js/swpe-update-external-images.js', array(), $this->plugin_version, true );
		wp_localize_script( 'swpe-job-script', 'myObj', array(
			'restURL' => rest_url(),
			'restNonce' => wp_create_nonce( 'wp_rest' )
		));
	}
	
	/**
	 * Load Custom CSS For settings page job
	 *
	 */
	  public function enqueue_styles() {
		 wp_register_style( 'swpe-settings-page-script',
			$this->plugin_url . 'assets/css/swpe_checkbox_toggle_ui.css'
         );
		 wp_enqueue_style( 'swpe-settings-page-script', $this->plugin_url .  'assets/css/swpe_checkbox_toggle_ui.css', array(), $this->plugin_version, 'all' );
	 }
	 
	/**
	 * Unloads Custom CSS For settings page job
	 *
	 */
	 public static function dequeue_styles()  {
		 wp_deregister_style('swpe-settings-page-script');
		 wp_dequeue_style('swpe-settings-page-script');
	 }
	 
	 /**
	 * Unloads Custom CSS For settings page job
	 *
	 */
	 public static function dequeue_scripts()  {
		 wp_dequeue_script( 'swpe-job-script' );
		 wp_dequeue_script( 'swpe-products-featured-script' );
	 }
}
