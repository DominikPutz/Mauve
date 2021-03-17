jQuery(document).ready(function() {

	jQuery( document.body ).on('click', '.um-notices-close', function() {
		var wrap = jQuery(this).parents('.um-notices-wrap');
		var notice_id = wrap.data('notice_id');
		var user_id = wrap.data('user_id');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_notices_mark_seen',
				notice_id: notice_id,
				user_id: user_id,
				nonce: um_scripts.nonce
			},
			success: function( data ) {
				if ( wrap.parent('.um-notices-shortcode').length ) {
					wrap.parent().hide();
				} else {
					wrap.animate({'bottom' : '-300px'});
				}
			}
		});
	});

});

jQuery(window).load( function(){
	if ( jQuery('.um-notices-wrap.no-shortcode').length ) {
		setTimeout( function(){
			jQuery('.um-notices-wrap.no-shortcode').animate({
				'bottom' : '0px'
			}, 900);
		},1000);
	}
});