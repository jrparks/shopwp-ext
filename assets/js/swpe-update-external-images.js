const MessageType = {
  error: 'notice-error',
  warning: 'notice-warning',
  success : 'notice-success',
  info : 'notice-info',
};

var ajaxLoading = false;
jQuery(document).ready(function($) {
	/**
	 *	Process job trigger request
	 */
	$('#run_job').click(function() {
		if(!ajaxLoading) {
			ajaxLoading = true;
			$.ajax({
				type: 'GET',
				url: myObj.restURL + 'swpe/api/v1/updateExtImages',
				headers: {
					'X-WP-Nonce': myObj.restNonce
				},
				cache: false,
				dataType: "json",
				encode: true,
				beforeSend: function (xhr) {
					xhr.setRequestHeader( 'X-WP-Nonce', myObj.restNonce );
				},
				beforeSend:function(){
					 displayAdminNotice('External Image Update Job request initiated to update the external featured images. Please wait for the job completion message.');
					 $('#spinner').show();
				},
				success: function(response){
					if(response) {
						if (response.success) {
							displayAdminNotice('External Image Update Job request completed sucessfully!', MessageType.success);
						} else {
							displayAdminNotice(response.error, MessageType.error);
						}
					}
				},
				error: function(xhr, status, error) {
					var errorMessage = xhr.status + ': ' + xhr.statusText;
					displayAdminNotice('Error: ' + errorMessage, MessageType.error);
				}
			})
			.done (function(data) { ajaxLoading = false; $('#spinner').hide();})
			.fail (function()     { displayAdminNotice('Error.', MessageType.error); });
		} else {
			displayAdminNotice('Please wait for the previous job request to be completed.', MessageType.error);
		}
	});
});

/**
 * Create and show a dismissible admin notice
 */
function displayAdminNotice( msg, type ) {
 
    var div = document.createElement( 'div' );
	div.setAttribute('style', 'position:relative;');
	switch(type) {
	  case MessageType.error:
		div.classList.add( 'notice', MessageType['error'] );
		break;
	  case MessageType.warning:
		div.classList.add( 'notice', MessageType['warning']);
		break;
	  case MessageType.success:
		div.classList.add( 'notice', MessageType['success'] );
		break;
	  case MessageType.info:
		div.classList.add( 'notice', MessageType['info'] );
		break;
	  default:
		div.classList.add( 'notice', MessageType['info'] );
	}
     
    var p = document.createElement( 'p' );
     
    p.appendChild( document.createTextNode( msg ) );
 
    div.appendChild( p );
 
    var b = document.createElement( 'button' );
    b.setAttribute( 'type', 'button' );
    b.classList.add( 'notice-dismiss' );
	b.setAttribute('style', 'position:absolute; top:0; right:0; ');
 
    var bSpan = document.createElement( 'span' );
    bSpan.classList.add( 'screen-reader-text' );
    bSpan.appendChild( document.createTextNode( 'Dismiss this notice' ) );
    b.appendChild( bSpan );
 
    div.appendChild( b );
 
    var h2 = document.getElementsByTagName( 'h2' )[0];
    h2.parentNode.insertBefore( div, h2.nextSibling);
 
    b.addEventListener( 'click', function () {
        div.parentNode.removeChild( div );
    });
}
