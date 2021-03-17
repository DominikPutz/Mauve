/**
 * Global
 * @type object
 */
var um_admin_scripts, um_mailchimp_data;

var um_mailchimp_create_template = function (id, data) {
	if (!jQuery('#tmpl-' + id).length) {
		return;
	}

	var template = wp.template(id),
					key = 'um_mailchimp_template_' + (new Date()).getMilliseconds();

	if (typeof window.um_mailchimp_dashboard_data === 'undefined') {
		window.um_mailchimp_dashboard_data = {};
	}

	window.um_mailchimp_dashboard_data[ key ] = {
		template: template,
		data: data
	};
	return key;
};

var um_mailchimp_render_template = function (key, $object) {
	if (typeof window.um_mailchimp_dashboard_data[ key ] !== 'undefined') {
		var content = window.um_mailchimp_dashboard_data[ key ].template(window.um_mailchimp_dashboard_data[key].data);
		if (typeof $object !== 'undefined') {
			window.um_mailchimp_dashboard_data[key].object = $object;
		}
		window.um_mailchimp_dashboard_data[key].object.html(content);
		return true;
	}
	return false;
};

var um_mailchimp_update_template = function (key, data) {
	data = data || {};
	if (typeof window.um_mailchimp_dashboard_data[ key ] !== 'undefined') {
		for (var index in data) {
			window.um_mailchimp_dashboard_data[ key ]['data'][ index ] = data[ index ];
		}

		return um_mailchimp_render_template(key);
	}
	return false;
};

window.um_sync_now = um_mailchimp_create_template('um-mailchimp-sync-metabox', jQuery.extend(um_mailchimp_data, {
	button_disabled: true
}));
if (window.um_sync_now) {
	um_mailchimp_render_template(window.um_sync_now, jQuery('#um-mailchimp-sync-metabox-wrapper'));
}

window.um_scan_now = um_mailchimp_create_template('um-mailchimp-subscribe-metabox', jQuery.extend(um_mailchimp_data, {
	button_disabled: false
}));
if (window.um_scan_now) {
	um_mailchimp_render_template(window.um_scan_now, jQuery('#um-mailchimp-subscribe-metabox-wrapper'));
}

/* Sync Profiles, Bulk Subscribe & Unubscribe */
jQuery(document)
				.on('click', '#btn_um_mailchimp_sync_now:not(.disabled)', um_mc_ajax_sync_now)
				.on('click', '#btn_um_mailchimp_scan_now:not(.disabled)', um_mc_ajax_scan_now)
				.on('click', '#btn_um_mailchimp_bulk_subscribe:not(.disabled)', um_mc_ajax_bulk_action)
				.on('click', '#btn_um_mailchimp_bulk_unsubscribe:not(.disabled)', um_mc_ajax_bulk_action)
				.on('change', '.um_mailchimp_sync_list', function (e) {
					um_mailchimp_update_template(window.um_sync_now, {
						button_disabled: false,
						internal_list: e.target.value
					});
				})
				.on('change', '.um_mailchimp_list', function (e) {
					um_mailchimp_update_template(window.um_scan_now, {
						button_disabled: false,
						internal_list: e.target.value
					});
				});

/**
 *
 * @param {object} e
 * @returns {undefined}
 */
function um_mc_ajax_sync_now(e) {
	e.preventDefault();

	var list_id = jQuery('.um_mailchimp_sync_list').val();

	um_mailchimp_update_template(window.um_sync_now, {
		button_disabled: true, // disable all progress buttons to prevent conflicts
		message: um_mailchimp_data.labels.sync_message,
		loading: true,
		list: list_id
	});

	sync_process(list_id);

	function sync_process(list_id) {

		sync_process.tmpData = sync_process.tmpData || {
			action: 'um_mailchimp_sync_now',
			nonce: um_admin_scripts.nonce,
			key: window.sync_key || '',
			list_id: list_id,
			message: '',
			length: 0,
			offset: 0,
			subtotal: 0,
			total: 0
		};

		jQuery.post(wp.ajax.settings.url, sync_process.tmpData).done(function (json) {

			if (json.success) {
				var tmpData = jQuery.extend(sync_process.tmpData, json.data);

				um_mailchimp_update_template(window.um_sync_now, {
					message: tmpData.message,
					loading: true
				});

				if ((tmpData.total - tmpData.offset) > tmpData.length) {
					sync_process.tmpData.offset = tmpData.offset + tmpData.length;
					sync_process(list_id);
				} else {
					setTimeout(function () {
						window.location = um_mailchimp_data.current_url;
					}, 1000);
				}

			} else {

				um_mailchimp_update_template(window.um_sync_now, {
					message: json.data,
					loading: false
				});
				console.error(json);
			}

		}).fail(function (xhr, status, error) {
			alert(status + ' ' + error);
		});
	}
}

function um_mc_ajax_scan_now(e) {
	e.preventDefault();

	var role = jQuery('.um_mailchimp_user_role').val(),
					status = jQuery('.um_mailchimp_user_status').val();

	um_mailchimp_update_template(window.um_scan_now, {
		button_disabled: true, // disable all progress buttons to prevent conflicts
		message: um_mailchimp_data.labels.scan_message,
		loading: true,
		role: role,
		status: status
	});

	jQuery.post(wp.ajax.settings.url, {
		action: 'um_mailchimp_scan_now',
		role: role,
		status: status,
		nonce: um_admin_scripts.nonce
	}, function (json) {
		if (json.success) {
			window.umMcBulcAction = {
				key: json.data.key,
				role: role,
				status: status,
				batches: []
			};
			if (json.data.total) {
				um_mailchimp_update_template(window.um_scan_now, {
					button_disabled: true,
					loading: false,
					message: json.data.message,
					step: 2
				});
			} else {
				um_mailchimp_update_template(window.um_scan_now, {
					button_disabled: false,
					loading: false,
					message: json.data.message,
					step: 0
				});
			}
		} else {
			alert(json.data);
		}
	}).fail(function (xhr, status, error) {
		alert(status + ' ' + error);
	});
}

function um_mc_ajax_bulk_action(e) {
	e.preventDefault();

	var list_id = jQuery('.um_mailchimp_list').val();

	var $button = jQuery(e.currentTarget);
	var action = '', message = '';
	if ($button.attr('id') === 'btn_um_mailchimp_bulk_subscribe') {
		action = 'um_mailchimp_bulk_subscribe';
		message = um_mailchimp_data.labels.start_bulk_subscribe_process;
	}
	if ($button.attr('id') === 'btn_um_mailchimp_bulk_unsubscribe') {
		action = 'um_mailchimp_bulk_unsubscribe';
		message = um_mailchimp_data.labels.start_bulk_unsubscribe_process;
	}

	um_mailchimp_update_template(window.um_scan_now, {
		button_disabled: true, // disable all progress buttons to prevent conflicts
		loading: true,
		message: message,
		internal_list: list_id
	});

	subscribe_process(list_id);

	function subscribe_process(list_id) {

		subscribe_process.tmpData = subscribe_process.tmpData || jQuery.extend(window.umMcBulcAction, {
			action: action,
			nonce: um_admin_scripts.nonce,
			list_id: list_id,
			message: '',
			length: 0,
			offset: 0,
			subtotal: 0,
			total: 0
		});

		jQuery.post(wp.ajax.settings.url, subscribe_process.tmpData, function (json) {

			if (json.success) {
				var tmpData = jQuery.extend(subscribe_process.tmpData, json.data);
				window.umMcBulcAction.batches.push(tmpData.batch);

				um_mailchimp_update_template(window.um_scan_now, {
					message: tmpData.message,
					loading: true
				});

				if ((tmpData.total - tmpData.offset) > tmpData.length) {
					subscribe_process.tmpData.offset = tmpData.offset + tmpData.length;
					subscribe_process(list_id);
				} else {
					setTimeout(function () {
						window.location = um_mailchimp_data.current_url;
					}, 1000);
				}

			} else {

				um_mailchimp_update_template(window.um_scan_now, {
					message: json.data,
					loading: false
				});
				console.error(json);
			}

		}).fail(function (xhr, status, error) {
			alert(status + ' ' + error);
		});
	}
}