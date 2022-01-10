ShopWP Ext
=========================

ShopWP Extensions enable further integration with your existing site and Shopify. 

With the use of the Shop WP plugin I have extended it through a separate plugin to allow for External Image Support using your product Shopify Images as the external external featured images directly from the CDN.

Also there is a CRON available to pull the updated image URLs once a day. I will change this to automatically injest the correct image URL's once the appropriate hook is added to the Shop WP Pro plugin. 

Enjoy!

## How do I use it?

You can install the plugin and enable both settings within the plugin. There is a button to manually run the product image sync so you don't need to wait for a day for the CRON to run.

Once that is completed you can simply modify your theme to utalize your new product featured images.

An example would be to alter your theme to incorporate your Shopify product images as shown below:

    <?php
        if ($show_featured_image && wp_get_attachment_url( get_post_thumbnail_id(get_the_ID())) != false || $searchType == 'wps_products'){
            ?>
        <div class="featured-image-box">

            <div class="featured-image">
                
                <a href="<?php echo esc_url(get_the_permalink()); ?>" title="<?php the_title_attribute(); ?>">
                <img alt="<?php the_title_attribute(); ?>"
                    src="<?php 
                    if ($searchType != 'wps_products') {
                        echo esc_url(
                        wp_get_attachment_url( get_post_thumbnail_id(get_the_ID())));
                    } else {
                        if (is_plugin_active( 'shopwp-ext/shop_wp-ext.php' )) {
                        echo esc_url(get_ext_image_url( get_the_ID(), 'thumbnail' ));
                        }
                    }
                    ?>" title="<?php the_title_attribute(); 
                    
                    ?>"/>
                <span class="post_overlay">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </span>
                </a>
            </div>
            
        </div>
            <?php
        }
    ?>


A convenience function has been added so you can simply call:

get_ext_image_url (<WITH YOUR POST ID>)

Optional parameters include the $size, $width and $height.

    @param string $post_id - post id <REQUIRED>

	@param string $size - <OPTIONAL> default wordpress sizes such as thumbnail, medium, medium_large, large, full or custom which is the default requiring one or both $width and $height parameters.

        'thumbnail'    = 150x150
        'medium'       = 300x300
        'medium-large' = 768x
        'large'        = 1024x1024
        'full'         = original shopify image dimensions
        ''             = default, an empty parameter would also be
                         the original dimensions.

	@param string $width - <OPTIONAL> width of the image in pixels 
	@param string $height - <OPTIONAL> height of the image in pixels

## This template is amazing! How can I ever repay you?
There's no need to credit me in your code. Enjoy!!