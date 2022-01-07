<?php
namespace SWPE\Api;

/**
 * Fired during plugin activation
 *
 * @package    SWPE
 * @subpackage SWPE\Api
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SWPE\Utils\HookRegistry;
use SWPE\Jobs\Update_External_Featured_Image_Urls as ExtImgLib;

class Endpoint {
	
	private $registry;
	
	public function __construct() {
		$this->registry = HookRegistry::get();
	}
	
	public function setup() {
		$this->registry->add_hook( 
		'register_job_trigger_endpoint', 'action', 'rest_api_init', $this, 'register_job_trigger_endpoint', 10, 2 );
	}
	
	public function register_job_trigger_endpoint() {
		register_rest_route('swpe/api/v1', '/updateExtImages', [
			'methods' => ['GET'],
			'callback' => [$this, 'job_trigger_endpoint_callback'],
			'permission_callback' => [$this, 'verify_admin'] //'__return_true'
		]);
	}
	
	public function verify_admin() {
		return current_user_can('administrator');
	}
	
	public function job_trigger_endpoint_callback() {
		$response = array();
		
		$obj = new ExtImgLib();
		$result =  $obj->run_update_ext_prod_feature_images_cron_job();
		
		if ($result) {
			$response['success'] = 'Success: Job completed sucessfully.';
		} else {
			$response['error'] = 'Error: Job had errors during its execution, please check your error log file.';
		}
		
		echo json_encode($response);
		die;
	}
}
