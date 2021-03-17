jQuery(window).load( function() {

	init_mycred_progress();

	jQuery(document).on( 'um_build_template', function() {
		init_mycred_progress();
	});

});


function init_mycred_progress() {
	setTimeout( function() {
		jQuery('.um-mycred-progress-done').each( function() {
			var pct = jQuery(this).attr('data-pct');
			jQuery(this).animate({ width: pct + '%' }, 300);
		});
	}, 1000 );

	if ( typeof tipsy !== 'undefined' ) {
		jQuery('.um-profile-body.badges .the-badge img').tipsy({gravity: 'n', opacity: 1, live: true, offset: 3 });
	}
}