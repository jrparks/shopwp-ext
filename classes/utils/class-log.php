<?php
namespace SWPE\Utils;

/**
 * Simple Log utility class
 *
 * @package    SWPE
 * @subpackage SWPE\Utils
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Log {
	private $dev_debug_enabled;
	private static $instance;
	
	private function __construct() {
		$this->dev_debug_enabled = false;
	}

	private static function getInstance(){
        if(!self::$instance){
            self::$instance = new Log();
        }
        return self::$instance;
    }
	
	public static function info($message) {
		if (is_array($message) || is_object($message)) {
			error_log(print_r($message, true));
		} else {
			error_log($message);
		}
	}
	
	public static function debug($message, $dev_debug = false) {	
		if (WP_DEBUG === true) {
			if (!$dev_debug || (self::getInstance()->dev_debug_enabled && $dev_debug)) {
				if (is_array($message) || is_object($message)) {
					error_log(print_r($message, true));
				} else {
					error_log($message);
				}
			}
		}
	}
}
