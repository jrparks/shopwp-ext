<?php

namespace SWPE\Images;

/**
 * External Image Library
 *
 * @package    SWPE
 * @subpackage SWPE\Images
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SWPE\Utils\Log;
use SWPE\Utils\HookRegistry;

/****
 * 
 * ADDING EXTERNAL IMAGE URL FIELD TO THE WPS_PRODUCTS
 *  CUSTOM POST TYPE
 *
 ****/
class External_Images {
	private $registry;
	
	public function __construct() {
		$this->registry = HookRegistry::get();
	}

	public function enable_ext_image_support() {
		Log::debug('SWPE Enabling Default External Image Support.', true);
		$this->registry->add_hook( 
			'thumbnail_url_field', 'filter', 'admin_post_thumbnail_html', $this, 'thumbnail_url_field', 10, PHP_INT_MAX );
		$this->registry->add_hook( 
			'thumbnail_url_field_save', 'action', 'save_post', $this, 'thumbnail_url_field_save', 10, 2 );
		$this->registry->add_hook( 
			'thumbnail_external_replace', 'filter', 'post_thumbnail_html', $this, 'thumbnail_external_replace', 10, PHP_INT_MAX );			
	}
	
	public function disable_ext_image_support() {
		Log::debug('SWPE Disabling Default External Image Support.', true);
		$this->registry->remove_hook( 'thumbnail_url_field', 'filter' );
		$this->registry->remove_hook( 'thumbnail_url_field_save', 'action' );
		$this->registry->remove_hook( 'thumbnail_external_replace', 'filter' );
	}
	 
	/**
	 * Utility Function to check for valif image names.
	 */
	public function contains_valid_image_extension( $url ) {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return FALSE;
		}
		// Supported image format extensions
		$ext = array( 'jpeg', 'jpg', 'gif', 'png', 'svg', 'webp' );
		$info = (array) pathinfo( parse_url( $url, PHP_URL_PATH ) );
		return isset( $info['extension'] )
			&& in_array( strtolower( $info['extension'] ), $ext, TRUE );
	} 
	
	/**  
	 * Helper Function to add in the shopify cdn size or specify a standard wordpress size.
	 *
	 * @param string $post_id - post id
	 * @param string $size - default wordpress sizes such as thumbnail, medium, medium_large, 
	 *  large, full or custom which is the default requiring one or both $width and $height 
	 *  parameters.
	 * @param string $width - width of the image
	 * @param string $height - height of the image
	 *
	 * @return string $ext_url - this is the external image url with cooresponding width and height
	 */
	public function get_ext_image_url($post_id, $size = 'custom', $width = '', $height = ''){
		$ext_url = get_post_meta( $post_id, '_thumbnail_ext_url', TRUE ) ? : "";
		$ext_url = self::get_ext_product_image_thumbnail($ext_url, $size = 'thumbnail');
		return $ext_url;
	}
	
	/**  
	 * Helper Function to add in the shopify cdn size or specify a standard wordpress size.
	 *
	 * @param string $image_src - external image sourse
	 * @param string $size - default wordpress sizes such as thumbnail, medium, medium_large, 
	 *  large, full or custom which is the default requiring one or both $width and $height 
	 *  parameters.
	 * @param string $width - width of the image
	 * @param string $height - height of the image
	 *
	 * @return string $image_src - this is the external image url with cooresponding 
	 * width and height
	 */
	public function get_ext_product_image_thumbnail($image_src, $size = 'custom', $width = '', $height = ''){
		$shopify_size = '';
		if ( !empty($image_src) ) {
			
			if ( ! empty( $size ) ) {
				//Switch case
				switch ($size) {
				  case 'custom':
					if ( !empty($width) && !empty($height)){
						$shopify_size = '_'.$width.'x'.$height;
					} elseif ( !empty($width) && empty($height)){
						$shopify_size = '_'.$width.'x';
					} elseif ( !empty($width) && empty($height)){
						$shopify_size = '_x'.$height;
					}
					break;	
				  case 'thumbnail':
					$shopify_size = '_150x150';
					break;
				  case 'medium':
					$shopify_size = '_300x300';
					break;
				  case 'medium_large':
					$shopify_size = '_768x';
					break;
				  case 'large':
					$shopify_size = '_1024x1024';
					break;
				  case 'full':
					$shopify_size = '';
					break;
				  default:
					$shopify_size = '';
				}
				$image_first_part = substr($image_src, 0, strrpos( $image_src, '.'));
				$image_last_part = substr($image_src, strrpos( $image_src, '.'));
				$image_src = $image_first_part.$shopify_size.$image_last_part;
			}
		}
		return $image_src;
	}

	/**
	 * Overrides the feature image html for the WPS_PRODUCTS custom post type.
	 *
	 * @param string $html for the thumbnail url field
	 *
	 * @return updated custom post type html for the featured image.
	 */
	public function thumbnail_url_field( $html ) {
		if(get_post_type() === 'wps_products') {
			global $post;
			$value = get_post_meta( $post->ID, '_thumbnail_ext_url', TRUE ) ? : "";
			$nonce = wp_create_nonce( 'thumbnail_ext_url_' . $post->ID . get_current_blog_id() );
			$html .= '<input type="hidden" name="thumbnail_ext_url_nonce" value="' 
				. esc_attr( $nonce ) . '">';
			$html .= '<div><p>' . __('Or', 'txtdomain') . '</p>';
			$html .= '<p>' . __( 'External Feature Image URL:', 'txtdomain' ) . '</p>';
			$html .= '<p><input id="ext_img" type="url" name="thumbnail_ext_url" value="' . $value . '"></p>';
			$html .= '<button id="clrBtn" type="button">Clear</button>';
			if ( ! empty($value) && self::contains_valid_image_extension( $value ) ) {
				$html .= '<div id="ext-image"><p><img style="max-width:150px;height:auto;" src="' 
					. esc_url($value) . '"></p>';
				$html .= '<p>' . __( 'Leave url blank to remove.', 'txtdomain' ) . '</p></div>';
			}
			$html .= '</div>';
		}
		return $html;
	}

	/**
	 * Overrides the feature image save for the custom post type.
	 *
	 * @param string $pid, $post for the custom post type
	 *
	 * @return n/a
	 */
	public function thumbnail_url_field_save( $pid, $post ) {
		$cap = $post->post_type === 'page' ? 'edit_page' : 'edit_post';
		if (
			! current_user_can( $cap, $pid )
			|| ! post_type_supports( $post->post_type, 'thumbnail' )
			|| defined( 'DOING_AUTOSAVE' )
		) {
			return;
		}
		$action = 'thumbnail_ext_url_' . $pid . get_current_blog_id();
		$nonce = filter_input( INPUT_POST, 'thumbnail_ext_url_nonce', FILTER_SANITIZE_STRING );
		$url = filter_input( INPUT_POST,  'thumbnail_ext_url', FILTER_VALIDATE_URL );
		if (
			empty( $nonce )
			|| ! wp_verify_nonce( $nonce, $action )
			|| ( ! empty( $url ) && ! self::contains_valid_image_extension( $url ) )
		) {
			return;
		}
		if ( ! empty( $url ) ) {
			update_post_meta( $pid, '_thumbnail_ext_url', esc_url($url) );
			if ( ! get_post_meta( $pid, '_thumbnail_id', TRUE ) ) {
				update_post_meta( $pid, '_thumbnail_id', 'by_url' );
			}
		} elseif ( get_post_meta( $pid, '_thumbnail_ext_url', TRUE ) ) {
			delete_post_meta( $pid, '_thumbnail_ext_url' );
			if ( get_post_meta( $pid, '_thumbnail_id', TRUE ) === 'by_url' ) {
				delete_post_meta( $pid, '_thumbnail_id' );
			}
		}
	}

	/**
	 * Overrides the feature image display for the custom post type.
	 *
	 * @param string $html, $post_id for the custom post type
	 *
	 * @return string $html to display the image for the custom post type
	 */
	public function thumbnail_external_replace( $html, $post_id ) {
		$url =  get_post_meta( $post_id, '_thumbnail_ext_url', TRUE );
		if ( empty( $url ) || ! self::contains_valid_image_extension( $url ) ) {
			return $html;
		}
		$alt = get_post_field( 'post_title', $post_id ) . ' ' .  __( 'thumbnail', 'txtdomain' );
		$attr = array( 'alt' => $alt );
		$attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, NULL );
		$attr = array_map( 'esc_attr', $attr );
		$html = sprintf( '<img src="%s"', esc_url($url) );
		foreach ( $attr as $name => $value ) {
			$html .= " $name=" . '"' . $value . '"';
		}
		$html .= ' />';
		return $html;
	}
}
