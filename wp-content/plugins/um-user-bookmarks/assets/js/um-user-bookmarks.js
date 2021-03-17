jQuery( document ).ready( function($) {


	//Open modal window for select Bookmark
	$( document.body ).on( 'click', '.um-user-bookmarks-add-button', function() {
		var btn = $(this);

		wp.ajax.send( 'um_bookmarks_modal_content', {
			data: {
				bookmark_post: btn.data('um_user_bookmarks_id')
			},
			success: function( data ) {
				$('.um-user-bookmarks-modal').show().find('.um-user-bookmarks-modal-content').html( data );
			},
			error: function( data ) {
				console.log( data );
			}
		});
	});


	//Select Bookmark for post in modal window
	$( document.body ).on( 'change', '.um_user_bookmarks_old_folder-radio', function() {
		var btn = $(this);
		var form = btn.parents('form');
		var post_id = form.find('[name="post_id"]').val();

		wp.ajax.send({
			data: form.serialize(),
			success: function( data ) {
				$('.um-user-bookmarks-modal').hide();
				var target_p = $('body').find('[data-um_user_bookmarks_id="' + post_id + '"]').parents('.um-clear').last();
				target_p.html( data );
			},
			error: function( data ) {
				console.log( data );
			}
		});
	});


	//Add Bookmark via create folder
	$( document.body ).on( 'click', '.um_user_bookmarks_create_folder_btn', function(e) {
		e.preventDefault();

		var btn = $(this);
		var modal = $('.um-user-bookmarks-modal');

		if ( btn.hasClass( 'busy' ) ) {
			return;
		}

		var form = btn.parents( '#form-um-new-folder-bookmark' );
		var title = form.find('[name="_um_user_bookmarks_folder"]');
		form.find('.error').removeClass('error');
		form.find('.error-message').hide();

		if ( $.trim( title.val() ) === '' ) {
			title.addClass( 'error' );
			title.parent( 'td' ).find( '.error-message' ).show();
			return;
		}

		var post_id = form.find('[name="post_id"]').val();

		var temp_html = btn.html();
		btn.addClass( 'busy' ).html( '...' );
		form.find('.form-response').html('');

		wp.ajax.send({
			data: form.serialize(),
			success: function( data ) {
				modal.find('form').remove();
				modal.find('.um-user-bookmarks-modal-content').find('.um-user-bookmarks-modal-heading').remove();
				modal.find('.um-user-bookmarks-modal-content').append('<h1 style="text-align:center;"><i class="um-faicon-check"></i> ' + wp.i18n.__( 'Successful', 'um-user-bookmarks' ) + '</h1>' );

				setTimeout(function(){
					$('.um-user-bookmarks-modal').hide();
				}, 1000);

				var target_p = $('body').find('[data-um_user_bookmarks_id="' + post_id + '"]').parents('.um-clear').last();
				target_p.html( data );
			},
			error: function( data ) {
				console.log( data );
				form.find('.form-response').html( data );
				btn.removeClass('busy').html( temp_html );
			}
		});
	});


	// Remove Bookmark from Post page
	$( document.body ).on( 'click', '.um-user-bookmarks-remove-button', function() {
		var btn = $(this);
		var nonce = btn.data('nonce');
		var post_id = btn.data('post');

		wp.ajax.send( 'um_bookmarks_remove', {
			data: {
				bookmark_post: post_id,
				return_button: true,
				_nonce: nonce
			},
			success: function( data ) {
				btn.attr( 'disabled', true ).html( '<i class="um-user-bookmarks-ajax-loading"></i>' );

				var target_p = $('body').find('[data-um_user_bookmarks_id="' + post_id + '"]').parents('.um-clear').last();
				target_p.html( data );
			},
			error: function( data ) {
				console.log( data );
			}
		});
	});


	// Remove Bookmark from Profile page
	$( document.body ).on( 'click', '.um-user-bookmarks-profile-remove-link', function() {
		var btn = $(this);
		var nonce = btn.data('nonce');
		var post_id = btn.data('id');

		wp.ajax.send( 'um_bookmarks_remove', {
			data: {
				bookmark_post: post_id,
				_nonce: nonce
			},
			success: function( data ) {
				btn.parents('.um-user-bookmarked-item').remove();
			},
			error: function( data ) {
				console.log( data );
			}
		});
	});


	//Hide Modal window
	$( document.body ).on( 'click', '.um-user-bookmarks-cancel-btn', function() {
		var html_content = '<a href="javascript:void(0);" class="um-user-bookmarks-cancel-btn">&times;</a>' + wp.i18n.__( 'Loading..', 'um-user-bookmarks' );
		$('body').find('.um-user-bookmarks-modal').hide().find('.um-user-bookmarks-modal-content').html( html_content );
	});


	// Show/hide add folder block
	$( document.body ).on( 'click', '#um-bookmarks-profile-add-folder', function() {
		$(this).parents('.um-user-bookmarks-profile-add-folder-holder').find('form').toggleClass('show');
		$(this).find('i.icon').toggleClass('um-faicon-angle-down um-faicon-angle-up');
	});


	// Add Folder from Profile page
	$( document.body ).on('click', '.um_user_bookmarks_profile_create_folder_btn', function(e) {
		e.preventDefault();

		var btn = $(this);
		if ( btn.hasClass('busy') ) {
			return;
		}

		var form = btn.parents('#um-user-bookmarks-profile-add-folder-form');
		var title = form.find('[name="_um_user_bookmarks_folder"]');
		form.find('.error').removeClass('error');
		form.find('.error-message').hide();

		if ( $.trim( title.val() ) === '' ) {
			title.addClass( 'error' );
			title.parent('.um_bookmarks_td').find('.error-message').show();
			return;
		}

		var temp_html = btn.html();
		btn.addClass('busy').html('...');
		form.find('.form-response').html('');

		wp.ajax.send({
			data: form.serialize(),
			success: function( data ) {
				location.reload();
			},
			error: function( data ) {
				console.log( data );
				form.find('.form-response').html( data );
				btn.removeClass('busy').html( temp_html );
			}
		});
	});


	//Single folder
	$( document.body ).on('click','.um-user-bookmarks-folder, .um-user-bookmarks-folder-back',function() {
		var btn = $(this);
		var nonce = btn.data('nonce');
		var profile = btn.data('profile');
		var folder_key = btn.data('folder_key');

		var tab_body = btn.parents('.um-profile-body');
		tab_body.html('<div style="text-align:center;"><p class="um-user-bookmarks-ajax-loading"></p></div>');

		wp.ajax.send( 'um_bookmarks_view_folder', {
			data: {
				key: folder_key,
				profile_id: profile,
				_nonce: nonce
			},
			success: function( data ) {
				tab_body.html( data );
			},
			error: function( data ) {
				console.log( data );
			}
		});
	});


	//Single folder edit
	$( document.body ).on('click','.um-user-bookmarks-folder-edit',function() {
		var btn = $(this);
		var folder_key = btn.data('folder_key');
		var user_id = btn.data('profile');
		var nonce = btn.data('nonce');

		var tab_body = btn.parents('.um-profile-body');
		tab_body.html('<div style="text-align:center;"><p class="um-user-bookmarks-ajax-loading"></p></div>');

		wp.ajax.send( 'um_bookmarks_view_edit_folder', {
			data: {
				key: folder_key,
				user: user_id,
				_nonce:nonce
			},
			success: function( data ) {
				tab_body.html( data );
			},
			error: function( data ) {
				console.log( data );
			}
		});
	});


	// Single folder delete
	$( document.body ).on( 'click', '.um-user-bookmarks-folder-delete',function() {
		var btn = $(this);
		var tab_body = btn.parents('.um-profile-body');

		var alert_text = btn.attr('data-alert_text');
		if ( confirm( alert_text ) ) {
			wp.ajax.send( 'um_bookmarks_delete_folder', {
				data: {
					key: btn.data('folder_key'),
					_nonce: btn.data('nonce')
				},
				success: function( data ) {
					tab_body.html('<div style="text-align:center;"><p class="um-user-bookmarks-ajax-loading"></p></div>');

					wp.ajax.send( 'um_bookmarks_get_folder_view', {
						data:{
							profile_id: btn.data('profile'),
							_nonce: btn.data('callback-nonce')
						},
						success: function( data2 ) {
							tab_body.html( data2 );
						},
						error: function( data2 ) {
							console.log( data2 );
						}
					});
				},
				error: function( data ) {
					console.log( data );
				}
			});
		}
	});


	//Back button in header
	$( document.body ).on( 'click', '.um-user-bookmarks-back-btn', function() {
		var btn = $(this);
		var profile = btn.data('profile');
		var nonce = btn.data('nonce');

		var tab_body = btn.parents('.um-profile-body');
		tab_body.html('<div style="text-align:center;"><p class="um-user-bookmarks-ajax-loading"></p></div>');

		wp.ajax.send( 'um_bookmarks_get_folder_view', {
			data:{
				profile_id:profile,
				_nonce:nonce
			},
			success: function( data ) {
				tab_body.html( data );
			},
			error: function( data ) {
				console.log( data );
			}
		});
	});


	//Update bookmarks folder
	$( document.body ).on( 'click', '.um_user_bookmarks_action_folder_update', function(e) {
		e.preventDefault();
		var btn = $(this);
		var form = btn.parents('form.um-user-bookmarks-edit-folder-form');

		var folder_title = form.find('[name="folder_title"]');
		form.find('.error-message').hide();

		if ( $.trim(folder_title.val()) === '') {
			folder_title.parent('p').find('.error-message').show();
			return;
		}

		var tab_body = btn.parents('.um-profile-body');

		wp.ajax.send({
			data: form.serialize(),
			success: function( data ) {
				tab_body.find('.um-user-bookmarks-folder-back').data('folder_key', data.slug).trigger('click');
			},
			error: function( data ) {
				console.log( data );
				form.find('.form-response').html( data );
			}
		});
	});


	//Hide Dropdown
	$(document.body).on('click','.um-profile-edit-folder-a, .um-user-bookmarks-dropdown-hide',function() {
		var btn = $(this);
		var dropdown = btn.parents('header').find('.um-user-bookmarks-dropdown').toggle();
	});
});