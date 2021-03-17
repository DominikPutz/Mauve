(function ($) {

	$(document).on('click', '[data-trigger="um-user-photos-modal"]', function (e) {
		e.preventDefault();
		var target = $('body').find('[data-scope="um-user-photos-modal"]');
		var modal_title = $(this).attr('data-modal_title');
		var modal_content = '<div class="text-center"><div class="um-user-photos-ajax-loading"></div></div>';
		var modal_content_div = target.find('.um-user-photos-modal-content');
		modal_content_div.html(modal_content);

		target.show();

		var template_path = $(this).attr('data-template');

		var postData = {
			template: template_path
		};

		if ($(this).attr('data-scope') == 'edit') {
			var id = $(this).attr('data-id');

			if ($(this).attr('data-edit') == 'album') {
				postData = {
					template: template_path,
					album_id: id
				};
			}

			if ($(this).attr('data-edit') == 'image') {
				postData = {
					template: template_path,
					image_id: id,
					album_id: $(this).attr('data-album')
				};
			}
		}



		$.post(
						$(this).attr('data-action'),
						postData, function (response) {
							modal_content_div.html(response);
						});

		target.find('.um-user-photos-modal-title').text(modal_title);
	});


	$(document).on('click', '.um-user-photos-modal-close-link', function (e) {
		e.preventDefault();
		$(this).parents('.um-user-photos-modal').hide();
		$(this).parents('.um-user-photos-modal').find('.um-user-photos-modal-title').text('');
		$(this).parents('.um-user-photos-modal').find('.um-user-photos-modal-content').html('');
	});


	$(document).on('click', '.um-galley-modal-submit', function (e) {
		var button = $(this);
		var btn_init = button.text();
		var form = button.parents('.um-user-photos-modal-content').find('form.um-user-photos-modal-form');
		real_form = form[0];
		data = '{}';
		var action = form.attr('action');
		var formData = new FormData(real_form);

		var response_div = form.find('.um-galley-form-response');
		response_div.removeClass('success').removeClass('error');
		response_div.html('');

		button.html('<i class="um-user-photos-ajax-loading"></i>');
		button.attr('disabled', true);

		$.ajax({
			type: 'POST',
			url: action,
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			success: function (data) {
				button.html(btn_init);
				button.attr('disabled', false);
				if (data == 'success') {
					location.reload();
				} else {
					try {

						json = $.parseJSON(data);
						response_div.addClass(json.type);
						var html = '<ul>';
						$.each(json.messages, function (index, message) {
							html += '<li>' + message + '</li>';
						});
						html += '</ul>';
						response_div.html(html);

					} catch (e) {
						console.log(data);
						if (data.statusText) {
							response_div.html('<span>Error:' + data.statusText + '</span>').addClass('error');
						}
					}
				}
			},
			error: function (data) {
				button.html(btn_init);
				button.attr('disabled', false);
				console.log("error");
				console.log(data);
				if (data.statusText) {
					response_div.html('<span>Error:' + data.statusText + '</span>').addClass('error');
				}
			}
		});



	});


	/* album ajax view */
	$(document).on('click', '.um-user-photos-album-block', function (e) {
		e.preventDefault();

		var profile_body = $(this).parents('.um-profile-body.photos');
		profile_body.html('<div class="text-center"><div class="um-user-photos-ajax-loading"></div></div>');

		var album_id = $(this).attr('data-id');
		var url = $(this).attr('data-action');

		// process ajax request

		$.post(url, {id: album_id}, function (response) {
			profile_body.html(response);
		});
	});


	// back to gallery
	$(document).on('click', '.back-to-um-user-photos', function (e) {
		e.preventDefault();
		var profile_body = $(this).parents('.um-profile-body.photos');
		var album_owner = $(this).attr('data-profile');
		profile_body.html('');
		profile_body.html('<div class="text-center"><div class="um-user-photos-ajax-loading"></div></div>');

		$.post(
						$(this).attr('data-action'),
						{
							template: $(this).attr("data-template"),
							user_id: album_owner
						},
						function (response) {
							profile_body.html(response);
						});

	});


	// toggle album/photos view
	$(document).on('click', '.um-user-photos-view-toggle', function (e) {
		e.preventDefault();

		if ($(this).hasClass('active')) {
			return;
		}

		var profile_body = $(this).parents('.um-profile-body.photos');
		var album_owner = $(this).attr('data-profile');
		profile_body.html('');
		profile_body.html('<div class="text-center"><div class="um-user-photos-ajax-loading"></div></div>');

		$.post(
						$(this).attr('data-action'),
						{
							template: $(this).attr("data-template"),
							user_id: album_owner
						},
						function (response) {
							profile_body.html(response);
						});

	});


	// delete album
	$(document).on('click', '#delete-um-album', function (e) {
		e.preventDefault();

		var button = $(this);
		var form = button.parents('form');
		var btn_init = button.text();
		var action = button.attr('data-action');
		var album_id = button.attr('data-id');
		var nonce = button.attr('data-wpnonce');
		var profile_body = $(this).parents('.um-profile-body.photos');

		button.html('<i class="um-user-photos-ajax-loading"></i>');
		button.attr('disabled', true);

		var response_div = form.find('.um-galley-form-response');
		response_div.removeClass('success').removeClass('error');
		response_div.html('');

		$.post(action, {
			id: album_id,
			_wpnonce: nonce
		}, function (response) {
			if (response == 'success') {
				profile_body.find('.back-to-um-user-photos').trigger('click');
			} else {

				button.html(btn_init);
				button.attr('disabled', false);

				try {
					json = $.parseJSON(response);
					response_div.addClass(json.type);
					var html = '<ul>';
					$.each(json.messages, function (index, message) {
						html += '<li>' + message + '</li>';
					});
					html += '</ul>';
					response_div.html(html);

				} catch (e) {
					console.log(response);
					if (response.statusText) {
						response_div.html('<span>Error:' + response.statusText + '</span>').addClass('error');
					}
				}
			}
		});

	});


	// update album
	$(document).on('click', '#um-user-photos-album-update', function (e) {
		e.preventDefault();

		var button = $(this);
		var btn_init = button.text();
		var form = button.parents('form');
		var action = form.attr('action');
		var album_action = button.attr('data-album_action');
		var profile_body = $(this).parents('.um-profile-body.photos');
		var album_id = form.find('[name="album_id"]').val();
		button.html('<i class="um-user-photos-ajax-loading"></i>');
		button.attr('disabled', true);
		var real_form = form[0];
		var formData = new FormData(real_form);

		var response_div = form.find('.um-galley-form-response');
		response_div.removeClass('success').removeClass('error');
		response_div.html('');

		$.ajax({
			type: 'POST',
			url: action,
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			success: function (data) {
				button.html(btn_init);
				button.attr('disabled', false);
				if (data == 'success') {
					profile_body.html('');
					profile_body.html('<div class="text-center"><div class="um-user-photos-ajax-loading"></div></div>');
					$.post(album_action, {id: album_id}, function (response) {
						profile_body.html(response);
					});
				} else {
					try {

						json = $.parseJSON(data);
						response_div.addClass(json.type);
						var html = '<ul>';
						$.each(json.messages, function (index, message) {
							html += '<li>' + message + '</li>';
						});
						html += '</ul>';
						response_div.html(html);

					} catch (e) {
						console.log(data);
						if (data.statusText) {
							response_div.html('<span>Error:' + data.statusText + '</span>').addClass('error');
						}
					}
				}
			},
			error: function (data) {
				button.html(btn_init);
				button.attr('disabled', false);
				console.log("Error");
				console.log(data);
				if (data.statusText) {
					response_div.html('<span>Error:' + data.statusText + '</span>').addClass('error');
				}
			}
		});

	});


	$(document).on('click', '#um-user-photos-image-update-btn', function (e) {
		e.preventDefault();

		var button = $(this);
		var btn_init = button.text();
		var form = button.parents('form');
		var action = form.attr('action');
		var profile_body = $(this).parents('.um-profile-body.photos');
		button.html('<i class="um-user-photos-ajax-loading"></i>');
		button.attr('disabled', true);

		var response_div = form.find('.um-galley-form-response');
		response_div.removeClass('success').removeClass('error');
		response_div.html('');


		$.post(action, form.serialize(), function (response) {

			button.html(btn_init);
			button.attr('disabled', false);

			try {
				json = $.parseJSON(response);
				response_div.addClass(json.type);
				var html = '<ul>';
				$.each(json.messages, function (index, message) {
					html += '<li>' + message + '</li>';
				});
				html += '</ul>';
				response_div.html(html);

				if (json.type === 'success') {
					setTimeout(function () {
						response_div.parents('.um-user-photos-modal').hide();
					}, 2000);
				} // close modal after success response

			} catch (e) {
				console.log(response);
				if (response.statusText) {
					response_div.html('<span>Error:' + response.statusText + '</span>').addClass('error');
				}
			}
		});

	});


	$(document).on('click', '[data-delete_photo]', function (e) {
		e.preventDefault();
		$(this).hide();
		var confirmation_text = $(this).attr('data-confirmation');
		var target = $(this).attr('data-delete_photo');
		var id = $(this).attr('data-id');
		var nonce = $(this).attr('data-wpnonce');
		var url = $(this).attr('href');
		var album = $(this).attr('data-album');
		if (confirm(confirmation_text)) {

			$.post(
							url,
							{
								image_id: id,
								album_id: album,
								_wpnonce: nonce,
							},
							function (response) {

								if (response == 'success') {
									$(target).remove();
								} else {
									console.log(response);
									if (response.statusText) {
										response_div.html('<span>Error:' + response.statusText + '</span>').addClass('error');
									}
								}

							});

		} else {
			$(this).show();
		}
	});


	$(document).on('click', '#um-user-photos-toggle-view-photos-load-more', function (e) {
		e.preventDefault();
		var btn = $(this);
		var btn_parent = btn.parents('.um-load-more');
		var btn_init = btn.text();
		var url = btn.attr('data-href');
		var profile_id = btn.attr('data-profile');
		var data_per_page = btn.attr('data-per_page');
		var data_current_page = btn.attr('data-current_page');
		var parent = btn.parents('.um-user-photos-albums');
		var photo_holder = parent.find('.photos-container');

		btn.attr('data-current_page', parseInt(data_current_page) + 1);

		btn.text('Loading');
		btn.attr('disabled', true);
		parent.css('opacity', '0.5');

		//photo_holder.append('<p>Display response</p>');
		$.post(url,
						{
							profile: profile_id,
							per_page: data_per_page,
							page: data_current_page
						}, function (response) {

			var count = (response.match(/data-umaction/g) || []).length;

			btn.text(btn_init);
			btn.attr('disabled', false);
			parent.css('opacity', '1');

			if (response == 'empty') {
				btn_parent.remove();
			} else {
				photo_holder.append(response);
				if (count < parseInt(data_per_page)) {
					btn_parent.remove();
				}
			}

		});

	});


	$(document).on('change', '#um-user-photos-input-album-cover', function (e) {
		var target_element = $(this).parents('h1.album-poster-holder');
		var reader = new FileReader();
		reader.onload = function (e) {
			var bg = e.target.result;
			target_element.css('background-image', 'url("' + bg + '")');
			target_element.css('background-size', 'contain');
		};
		reader.readAsDataURL(this.files[0]);
	});


	$(document).on('change', '#um-user-photos-input-album-images', function (e) {
		var target_element = $(this).parents('form').find('#um-user-photos-images-uploaded');
		var files = e.target.files;
		target_element.html('');
		for (var i = 0; i < files.length; i++) {
			var file = files[i];
			//Only pics
			if (!file.type.match('image'))
				continue;

			var picReader = new FileReader();
			picReader.addEventListener("load", function (e) {
				var picFile = e.target;
				target_element.append('<img style="display:inline;height:70px;margin:2px;border:1px solid #000;" src="' + picFile.result + '" data-index=""/>');
			});
			//Read the image
			picReader.readAsDataURL(file);
		}
	});


	/* Button "Delete my all albums & photos" */
	$(document).on('click', '#um_user_photos_delete_all', function (e) {
		e.preventDefault();
		var btn = $(this);
		if (btn.hasClass('inactive')) {
			return;
		}

		var text = btn.attr('data-alert_message');
		var user_id = btn.attr('data-profile');
		var nonce = btn.attr('data-wpnonce');
		var url = btn.attr('href');

		if (confirm(text)) {
			btn.addClass('inactive');
			btn.text('Processing...Please wait.');

			$.post(url, {
				profile_id: user_id,
				_wpnonce: nonce
			}, function (response) {
				if (typeof (response) === 'object' && response.success) {
					btn.parent('p').append('<font style="color:green;display:block;text-align:center;">Deleted Successfully !</font>');
					btn.remove();
				} else {
					console.log(response);
				}
			}, 'json');
		}
	});


	$(document).on('change', '#um-user-photos-input-album-cover', function (e) {
		e.preventDefault();
		var field = $(this);
		var form = field.parents('form');
		var max_size = form.attr('data-max_size');
		var max_size_error_msg = form.attr('data-max_size_error');
		var footer = form.find('.um-user-photos-modal-footer');
		var response_div = form.find('.um-galley-form-response');

		response_div.html('').removeClass('error');
		footer.show();

		var allowed_size = max_size / 1000000;

		if (field[0]['files']) {
			var size = field[0]['files'][0]['size'];
			if (size >= max_size) {
				var name = field[0]['files'][0]['name'];
				var err_txt = name + ' ' + max_size_error_msg + ' ' + allowed_size + ' MB';
				field.trigger('reset');
				response_div.html('<span>' + err_txt + '</span>').addClass('error');
				footer.hide();
				return false;
			}
		}
	});


	$(document).on('change', '#um-user-photos-input-album-images', function (e) {
		e.preventDefault();
		var field = $(this);
		var form = field.parents('form');
		var max_size = form.attr('data-max_size');
		var max_size_error_msg = form.attr('data-max_size_error');
		var footer = form.find('.um-user-photos-modal-footer');
		var response_div = form.find('.um-galley-form-response');

		response_div.html('').removeClass('error');
		footer.show();

		var allowed_size = max_size / 1000000;

		if (field[0]['files']) {

			var error = false;
			var error_html = '';
			var img_size = 0;
			var img_name = '';
			var files = field[0]['files'];
			var err_txt = '';

			for (var i = 0, f; f = files[i]; i++) {
				//console.log(field[0]['files'][i]);
				img_size = field[0]['files'][i]['size'];
				img_name = field[0]['files'][i]['name'];
				if (img_size >= max_size) {
					error = true;
					err_txt = img_name + ' ' + max_size_error_msg + ' ' + allowed_size + ' MB';
					error_html += '<p style="margin:0;">' + err_txt + '</p>';
				}
			}

			if (error) {
				response_div.html('<span>' + err_txt + '</span>').addClass('error');
				footer.hide();
				return false;
			}
		}
	});


	$(document).on('click', '.um-user-photos-album-options', function (e) {
		e.preventDefault();
		$(this).next('.um-dropdown').show();
	});


	$(document).on('click', '.um-dropdown-hide', function (e) {
		e.preventDefault();
		$(this).parents('.um-dropdown').hide();
	});


})(jQuery);