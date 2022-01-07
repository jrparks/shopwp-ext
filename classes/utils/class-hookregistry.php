<?php
namespace SWPE\Utils;

/**
 * Action and Filter Hook Registry
 *
 * @package    SWPE
 * @subpackage SWPE\Utils
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SWPE\Utils\Log;

class HookRegistry {
	public static $instance;
	private $registry;
	private $hook_types = array(
			'filter' => 'filter',
			'action' => 'action'
			);

	public function __construct() {
		self::$instance = $this;
		$this->registry = array(); 
	}
	
    public static function get() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

	public function add_hook( $id, $type, $name, $object, $method, $priority = 10, $args = 1) {
		$type = strtolower( $type );
		if (!array_key_exists($type, $this->hook_types)) {
			Log::debug('SWPE No proper hook type defined.'.$type, true);
		} 
		
		if ( 'filter' === $type ) {
			if (!array_key_exists($id, $this->registry)) {
				$this->add_filter( $name, $object, $method, $priority, $args );
				Log::debug('SWPE Adding filter hook: '.$name, true);
			}
		} else {
			if (!array_key_exists($id, $this->registry)) {
				$this->add_action( $name, $object, $method, $priority, $args );
				Log::debug('SWPE Adding action hook: '.$name, true);
			}
		}

		$hook_info = array(
			'type'   	=> $type,
			'name'   	=> $name,
			'object' 	=> $object,
			'method' 	=> $method,
			'priority' 	=> $priority,
			'args' 		=> $args,
		);
		$this->registry[ $id ] = $hook_info;
	}
	
	public function remove_all_hooks() {
		error_log(print_r('SWPE Removing all hooks!', true));
		foreach($this->registry as $id => $hook) {
			if ( $hook['type'] === 'filter' ) {
				$this->remove_filter( $hook['name'], $hook['object'], $hook['method'], $hook['priority']);
				Log::debug('SWPE Removing filter hook: '.$id, true);
			} else {
				$this->remove_action( $hook['name'], $hook['object'], $hook['method'], $hook['priority'] );
				Log::debug('SWPE Removing action hook: '.$id, true);
			}
			unset($this->registry[ $id ]);
		}
		return;
	}

	public function remove_hook( $id, $type ) {	
		$type = strtolower( $type );
		if (!array_key_exists($type, $this->hook_types)) {
			Log::debug('SWPE No proper hook type defined.'.$type);
		}
		
		if (array_key_exists($id, $this->registry)) {
			$hook_info = $this->registry[ $id ];

			if ( 'filter' === $type ) {
				$this->remove_filter( $hook_info['name'], $hook_info['object'], $hook_info['method'], $hook_info['priority']);
				Log::debug('SWPE Removing filter hook: '.$id, true);
			} else {
				$this->remove_action( $hook_info['name'], $hook_info['object'], $hook_info['method'], $hook_info['priority'] );
				Log::debug('SWPE Removing action hook: '.$id, true);
			}
			unset($this->registry[ $id ]);
		}
	}

	private function add_filter( $name, $object, $method, $priority, $args ) {
		add_filter( $name, array( $object, $method ), $priority, $args );
	}

	private function add_action( $name, $object, $method, $priority, $args ) {
		add_action( $name, array( $object, $method ), $priority, $args );
	}
	
	private function remove_filter( $name, $object, $method, $priority ) {
		remove_filter( $name, array( $object, $method ), $priority );
	}

	private function remove_action( $name, $object, $method, $priority ) {
		remove_action( $name, array( $object, $method ), $priority );
	}
}
