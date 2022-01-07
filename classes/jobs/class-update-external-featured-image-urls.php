<?php
namespace SWPE\Jobs;

/**
 * Displays admin notices
 *
 * @package    SWPE
 * @subpackage SWPE\Jobs
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SWPE\Utils\Log;

class Update_External_Featured_Image_Urls {

	/**
	 * Cron job to update the Wordpress Shopify posts so they store the external feature image 
	 * url. 
	 *
	 * return - true for complete, false for failure
	 */
	public function run_update_ext_prod_feature_images_cron_job(){
		$args = array(  
			'post_type' => 'wps_products',
			'post_status' => 'publish',
			'posts_per_page' => -1, // getting all posts of a post type
			'no_found_rows' => true,
		); 

		$Products = \ShopWP\Factories\API\Items\Products_Factory::build();
		$loop = new \WP_Query( $args );
		
		$fail = false;
		$complete = false;
		while ( $loop->have_posts() ) : $loop->the_post();
			$post_id = get_the_ID();
			$product_id = get_post_custom_values('product_id', $post_id);
			if (!empty($product_id[0])) {
				Log::debug('Product ID: '.$product_id[0], true);
				$result = $Products->get_product([
				   'product_id' => $product_id[0],
					'schema' => '
						images(first: 250) {
							edges {
								node {
									width
									height
									altText
									id
									originalSrc
									transformedSrc
								}
							}
						}
					'
				]);
				$image_src = $result -> product -> images -> edges[0] -> node -> originalSrc;
				Log::debug('Product Image URL: '.$image_src);
				if ( ! empty( $image_src ) ) {
					$obj = new \SWPE\Images\External_Images();
					$ext_image = $obj->get_ext_product_image_thumbnail($image_src, 'full');

					$image_src = $ext_image;
					$old_value = get_post_meta($post_id, '_thumbnail_ext_url', true);
					if ($old_value != esc_url($image_src)) {
						$result = update_post_meta( $post_id, '_thumbnail_ext_url', esc_url($image_src) );
						if (!$result) {
							$fail = true;
							Log::debug('Unable to update url.');
						}
						if ( ! get_post_meta( $post_id, '_thumbnail_id', TRUE ) ) {
							update_post_meta( $post_id, '_thumbnail_id', 'by_url' );
						}
					}
				} elseif ( get_post_meta( $post_id, '_thumbnail_ext_url', TRUE ) ) {
					delete_post_meta( $post_id, '_thumbnail_ext_url' );
					if ( get_post_meta( $post_id, '_thumbnail_id', TRUE ) === 'by_url' ) {
						delete_post_meta( $post_id, '_thumbnail_id' );
					}
				}
			} else {
				Log::debug('No product id found for: '.$post_id);
			}
		endwhile;
		
		if (!$fail) {
			$complete = true;
		}
		return $complete;
	}
	
	// Schedule External Image Update Cron Job Event
	public static function schedule_external_product_images_cron_job() {
		if (!\wp_next_scheduled('run_update_ext_prod_feature_images_cron_job')) {
			\wp_schedule_event(current_time('timestamp'), 'daily', 'run_update_ext_prod_feature_images_cron_job');
		}
	}
	
	// Unschedule External Image Update Cron Job Event
	public static function unschedule_external_product_images_cron_job() {
		$event_timestamp = wp_next_scheduled('run_update_ext_prod_feature_images_cron_job');
		\wp_unschedule_event($event_timestamp, 'run_update_ext_prod_feature_images_cron_job');
	}
}
