jQuery(document).ready(function (e) {

	/* Reviews */
	if (jQuery('.um-woo-review-avg').length) {
		jQuery('.um-woo-review-avg').um_raty({
			half: true,
			starType: 'i',
			number: function () {
				return jQuery(this).attr('data-number');
			},
			score: function () {
				return jQuery(this).attr('data-score');
			},
			hints: ['1 Star', '2 Star', '3 Star', '4 Star', '5 Star'],
			space: false,
			readOnly: true
		});
	}


	/* Country & State */
	jQuery(document.body).on('change', '.um-field #billing_country, .um-field #shipping_country', function () {
		var country = jQuery(this).val();
		var type = jQuery(this).data('key');
		um_wc_refresh_address(country, type);
	});

	if (jQuery('.um-account-tab select.country_select, .um-account-tab select.state_select, .um-custom-shortcode-tab select').length) {
		jQuery('.um-account-tab select.country_select, .um-account-tab select.state_select, .um-custom-shortcode-tab select').select2({
			width: '100%'
		});
	}

	if (jQuery('.um-account-tab .um-field-state .um-field-error').length > 0) {
		var country = jQuery('.um-account-tab .um-field-country:visible select').val();
		var type = jQuery('.um-account-tab .um-field-country:visible select').data('key');
		um_wc_refresh_address(country, type);
	}


	/* Orders */
	jQuery(document.body).on('click', '.um-woo-orders .um-woo-view-order', um_wc_display_order);

	jQuery(document.body).on('click', '.um-woo-order-hide', function (e) {
		e.preventDefault();
		window.history.pushState("string", "Orders", window.location.pathname);
		remove_Modal();
		return false;
	});

	if (window.location.href.indexOf('orders/#') > -1) {
		var order_id = window.location.href.split('orders/#').pop();
		um_wc_display_order(e, order_id);
	}


	/* Subscriptions */
	jQuery(document.body).on('click', '.um-woo-subscriptions a.button.view, .um-woo-subscriptions .subscription-id > a', um_wc_display_subscription);

	jQuery(document.body).on('click', '.back_to_subscriptions', function (e) {
		e.preventDefault();
		window.history.pushState("string", "Subscriptions", window.location.pathname);
		jQuery('.woocommerce_account_subscriptions').removeAttr('style').fadeIn().nextAll('.um_account_subscription').remove();
		return false;
	});

	if (window.location.href.indexOf('subscription/#') > -1) {
		var subscription_id = window.location.href.split('subscription/#').pop();
		um_wc_display_subscription(e, subscription_id);
	}


	/* Payment method */
	jQuery(document.body).on('click', 'a[href*="add-payment-method"]', function (e) {
		e.preventDefault();

		if (!/add-payment-method=1/.test(location.href)) {
			var link = location.href + (/\?/.test(location.href) ? '&' : '?') + 'add-payment-method=1';
			window.history.pushState('string', 'Add payment method', link);
		}

		if (typeof (window.umAddPaymentMethod) === 'undefined') {
			window.umAddPaymentMethod = jQuery('#um_add_payment_method_content').html().trim();
			jQuery('#um_add_payment_method_content').remove();
		}

		if (window.umAddPaymentMethod) {
			prepare_Modal();
			show_Modal(window.umAddPaymentMethod);
			responsive_Modal();
		}
	});

	if (/add-payment-method=1/.test(location.href) && jQuery('a[href*="add-payment-method"]').length) {
		jQuery('a[href*="add-payment-method"]').trigger('click');
	}

});

function um_wc_display_order(e, order_id) {

	if (typeof (order_id) === 'undefined' && typeof (e.currentTarget) !== 'undefined') {
		e.preventDefault(e);
		order_id = jQuery(e.currentTarget).parents('tr').data('order_id');
		window.history.pushState("string", "Orders", jQuery(e.currentTarget).attr('href'));
	}

	return wp.ajax.send('um_woocommerce_get_order', {
		data: {
			order_id: order_id,
			nonce: um_scripts.nonce
		},
		beforeSend: prepare_Modal,
		success: function (data) {
			if (data) {
				show_Modal(data);
				responsive_Modal();
			} else {
				remove_Modal();
			}
		},
		error: function (e) {
			remove_Modal();
			console.log('===UM Woocommerce error===', e);
		}
	});
}

function um_wc_display_subscription(e, subscription_id) {

	if (typeof (subscription_id) === 'undefined' && typeof (e.currentTarget) !== 'undefined') {
		e.preventDefault(e);
		subscription_id = jQuery(e.currentTarget).attr('href').split('subscription/#').pop();
		window.history.pushState("string", "Subscriptions", jQuery(e.currentTarget).attr('href'));
	}

	return wp.ajax.send('um_woocommerce_get_subscription', {
		data: {
			subscription_id: subscription_id,
			nonce: um_scripts.nonce
		},
		beforeSend: function () {
			jQuery('.woocommerce_account_subscriptions').css({cursor: 'wait', opacity: '0.7'});
		},
		success: function (data) {
			jQuery('.woocommerce_account_subscriptions').hide().after(data);
			jQuery('.um_account_subscription').fadeIn();
		},
		error: function (e) {
			console.log('===UM Woocommerce error===', e);
		}
	});
}

function um_wc_refresh_address(country, type) {
	var error;

	wp.ajax.send('um_woocommerce_refresh_address', {
		data: {
			nonce: um_scripts.nonce,
			country: country,
			type: type
		},
		success: function (data) {

			var state_wrap = '.um-field-billing_state';
			if (type === 'shipping_country') {
				state_wrap = '.um-field-shipping_state';
			}
			if (jQuery(state_wrap + ' .um-field-error').length > 0) {
				error = jQuery(state_wrap + ' .um-field-error').clone();
			}

			jQuery(state_wrap).html(data).contents().unwrap();

			if (jQuery(state_wrap + ' select').length > 0) {
				jQuery(state_wrap + ' select').select2({
					width: '100%'
				});
			} else {
				if (jQuery(state_wrap + ' input[type = "hidden"]').length > 0) {
					jQuery(state_wrap).hide();
				} else {
					jQuery(state_wrap).show();
				}
			}
			if (error) {
				jQuery(state_wrap).append(error);
			}

		},
		error: function (e) {
			console.log('===UM Woocommerce error===', e);
		}
	});
}