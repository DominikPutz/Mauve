/*
 Plugin Name: Ultimate Member - MailChimp
 Description: Admin panel scripts
 Version: 2.2.0
 Author: Ultimate Member
 Author URI: http://ultimatemember.com/
 */

/**
 * Globals
 * @object type
 */
var um_admin_scripts;

jQuery(function () {

	/**
	 * Button "Clear log"
	 */
	jQuery(document.body).on('click', '#um_mailchimp_clear_log', function (e) {
		e.preventDefault();
		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_mailchimp_clear_log',
				nonce: um_admin_scripts.nonce
			},
			success: function () {
				window.location.reload();
			}
		});
	});

});