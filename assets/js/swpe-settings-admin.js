jQuery(document).ready(function($) {
	/**
	 *	Process request to dismiss our admin notice
	 */
	$('#wp-ajax-swpe-notice .notice-dismiss').click(function() {
		//* Data to make available via the $_POST variable
		data = {
			action: 'wp_ajax_swpe_admin_notice',
			wp_ajax_swpe_admin_nonce: swpe-settings-script.wp_ajax_swpe_admin_nonce
		};

		//* Process the AJAX POST request
		$.post( ajaxurl, data );

		return false;
	});
}); 
