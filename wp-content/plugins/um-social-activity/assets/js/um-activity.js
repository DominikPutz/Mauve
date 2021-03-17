var Loadwall_ajax = false;

/* Responsive confirm box */
function UM_wall_confirmbox_mobile() {
	var width = jQuery( window ).width();
	var max_width, margin_left, left;
	if ( width <= 500 ) {
		max_width = width;
		margin_left = 0;
		left = 0;
	} else {
		max_width = '400px';
		margin_left = '-200px';
		left = '50%';
	}

	var modal = jQuery('.um-activity-confirm');

	modal.css({
		'top'           : ( jQuery(window).height() - modal.height() ) / 2 + 'px',
		'width'         : max_width,
		'margin-left'   : margin_left,
		'left'          : left
	});
}

/* Show confirm box */
function UM_wall_confirmbox_show(post_id, msg, custclass) {
	var modal = jQuery('.um-activity-confirm');
	if ( ! modal.is( ':visible' ) ) {
		jQuery('.um-activity-confirm-m').html(msg);
		jQuery('.um-activity-confirm-o,.um-activity-confirm').show();
		modal.find('.um-activity-confirm-btn').addClass(custclass).attr('data-post_id', post_id);
	}

	UM_wall_confirmbox_mobile();
}

/* Hides confirm box */
function UM_wall_confirmbox_hide() {
	jQuery('.um-activity-confirm-o,.um-activity-confirm').hide();
}

function um_extractLast( term ) {
	return term.split(" ").pop();
}

function um_extract_string( term ) {
	return term.split(" ");
}

function UM_wall_autocomplete_start() {
	var textareas = jQuery( 'textarea.um-activity-textarea-elem,textarea.um-activity-comment-textarea' );

	if ( textareas.length === 0 ) {
		return;
	}

	textareas.each( function() {
		var el = jQuery(this);

		if (typeof jQuery.ui === 'undefined') {
			return false;
		}

		var el_autocomplete = el.autocomplete({
			minLength: 1,
			source: function( request, response ) {

				if ( um_extractLast( request.term ).charAt(0) === '@' ) {

					jQuery.getJSON( wp.ajax.settings.url + '?action=um_activity_get_user_suggestions&term=' + um_extractLast( request.term )  + '&nonce=' + um_scripts.nonce, function( data ) {
						response( data );
					});

				}

			},
			select: function( event, ui ) {
				ui.item.name = ui.item.name.replace( '<strong>', '' );
				ui.item.name = ui.item.name.replace( '</strong>', '' );

				var terms = um_extract_string( this.value );
				terms.pop();
				terms.push( '@' + ui.item.username );
				terms.push( "" );
				this.value = jQuery.trim( terms.join(" ") );
				return false;
			}
		});

		if ( typeof el_autocomplete.data("ui-autocomplete") !== 'undefined' ) {
			el_autocomplete.data("ui-autocomplete")._renderItem = function( ul, item ) {
				return jQuery("<li />").data("item.autocomplete", item).append(item.photo + item.name + '<span>@' + item.username + '</span>').appendTo(ul);
			}
		}

	});
}

/* Setup image upload */
function UM_wall_img_upload() {
	//jQuery('.ajax-upload-dragdrop').remove();
	var widget;

	jQuery('.um-activity-insert-photo').each( function() {

		if ( jQuery(this).siblings( '.ajax-upload-dragdrop' ).length ) {
			return;
		}

		var apu = jQuery(this);
		widget = apu.parents('.um-activity-widget');

		apu.uploadFile({
			url: wp.ajax.settings.url,
			method: "POST",
			multiple: false,
			formData: {
				action:     'um_imageupload',
				key:        'wall_img_upload',
				set_id:     0,
				set_mode:   'wall',
				timestamp:  apu.data('timestamp'),
				_wpnonce:   apu.data('nonce')
			},
			fileName: 'wall_img_upload',
			allowedTypes: apu.attr('data-allowed'),
			maxFileSize: 9999999,
			dragDropStr: '',
			sizeErrorStr: apu.attr('data-size-err'),
			extErrorStr: apu.attr('data-ext-err'),
			maxFileCountErrorStr: '',
			maxFileCount: 1,
			showDelete: false,
			showAbort: false,
			showDone: false,
			showFileCounter: false,
			showStatusAfterSuccess: true,
			returnType: 'json',
			onSubmit: function ( files ) {
				widget.find('.um-error-block').remove();
				um_disable_post_submit( widget );
				um_clean_photo_fields( widget );
			},
			onSuccess: function ( files, response, xhr ) {

				apu.selectedFiles = 0;

				if ( response.success && response.success === false || typeof response.data.error !== 'undefined' ) {

					um_disable_post_submit( apu.parents('.um-activity-widget') );
					um_post_placeholder( widget.find('.um-activity-textarea-elem') );

					widget.find('.upload-statusbar').prev('div').append(
						'<div class="um-error-block">' + response.data.error + '</div>'
					);
					widget.find('.upload-statusbar').remove();

				} else {

					widget.find('.upload-statusbar').remove();

					um_enable_post_submit( apu.parents('.um-activity-widget') );
					um_photo_placeholder( widget.find('.um-activity-textarea-elem') );

					widget.find('.um-activity-preview img').attr( 'src', response.data[0].url );
					widget.find('.um-activity-preview').show();
					widget.find( 'input[type="hidden"][name="_post_img"]' ).val( response.data[0].file );
					widget.find( 'input[type="hidden"][name="_post_img_url"]' ).val( response.data[0].url );
				}

			}
		});

	});
}


function um_get_activity_post( post_id ) {
	var postdata = {};

	jQuery.ajax({
		url: wp.ajax.settings.url,
		type: 'post',
		data: {
			action: 'um_get_activity_post',
			post_id: post_id,
			nonce: um_scripts.nonce
		},
		success: function( data ) {
			postdata = data;
			return postdata;
		},
		error: function(e) {
			console.log( 'UM Social Activity Error', e );
		}
	});
}

/* Remove image upload */
jQuery( document.body ).on('click', '.um-activity-img-remove', function() {
	var form = jQuery(this).parents('form');
	um_clean_photo_fields( form );

	um_post_placeholder( form.find( '.um-activity-textarea-elem' ) );
	um_check_textarea_length( form.find( 'textarea' ) );
});


function um_clean_photo_fields( form ) {
	form.find('.um-activity-preview').hide();
	form.find('.um-activity-preview img').attr('src', '');
	form.find( 'input[type="hidden"][name="_post_img"]' ).val('');
	form.find( 'input[type="hidden"][name="_post_img_url"]' ).val('');
}


function um_enable_post_submit( form ) {
	form.find( '.um-activity-post' ).removeClass( 'um-disabled' );
}

function um_disable_post_submit( form ) {
	form.find( '.um-activity-post' ).addClass( 'um-disabled' );
}


function um_enable_comment_submit( form ) {
	form.find( '.um-activity-comment-post' ).removeClass( 'um-disabled' );
}

function um_disable_comment_submit( form ) {
	form.find( '.um-activity-comment-post' ).addClass( 'um-disabled' );
}

function um_check_comment_length( textarea ) {
	var form = textarea.parents( '.um-activity-comment-area' );
	if ( textarea.val().trim().length > 0 ) {
		um_enable_comment_submit( form );
	} else {
		um_disable_comment_submit( form );
	}
}


function um_check_textarea_length( textarea ) {
	var form = textarea.parents( '.um-activity-widget' );
	if ( textarea.val().trim().length > 0 ) {
		um_enable_post_submit( form );
	} else {
		if ( form.find( 'input[type="hidden"][name="_post_img"]' ).val().trim().length === 0 ) {
			um_disable_post_submit( form );
		}
	}
}

function um_post_placeholder( obj ) {
	obj.attr( 'placeholder', obj.attr( 'data-ph' ) );
}

function um_photo_placeholder( obj ) {
	obj.attr( 'placeholder', obj.attr( 'data-photoph' ) );
}

function um_getUrlParameter( sParam ) {
	var sPageURL = decodeURIComponent( window.location.search.substring(1) ),
		sURLVariables = sPageURL.split('&'),
		sParameterName,
		i;

	for ( i = 0; i < sURLVariables.length; i++ ) {
		sParameterName = sURLVariables[ i ].split('=');

		if ( sParameterName[0] === sParam ) {
			return sParameterName[1] === undefined ? true : sParameterName[1];
		}
	}
}


function um_wall_ajax_request() {
	var wall = jQuery('.um-activity-wall');
	if ( wall.length > 0 && Loadwall_ajax === false ) {
		Loadwall_ajax = true;
		jQuery('.um-activity-load:last').show();

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_activity_load_wall',
				offset: jQuery('.um-activity-widget:not(.um-activity-new-post):visible').length,
				user_id:  wall.data('user_id'),
				user_wall: wall.data( 'user_wall' ),
				hashtag: wall.data('hashtag'),
				nonce: um_scripts.nonce,
				wall_post: wall.data( 'wall_post' ),
			},
			success: function( data ) {
				jQuery('.um-activity-load').hide();

				if ( data === '' ) {
					Loadwall_ajax = true;
				} else {
					jQuery('.um-activity-wall').append( data );
					if( wall.data( 'wall_post' ) > 0 ){
						Loadwall_ajax = true;
					} else {
						Loadwall_ajax = false;
					}
				}

			},
			error: function (e) {
				console.log('UM Social Activity Error', e);
			}
		});
	}
}

/* Load of posts */
jQuery( window ).scroll( function() {
	var wall = jQuery('.um-activity-wall');
	if ( wall.length > 0 && jQuery(window).scrollTop() + jQuery(window).height() >= wall.offset().top + wall.height() ) {
		um_wall_ajax_request();
	}
});

/* Resize function */
jQuery( window ).resize( function() {
	UM_wall_confirmbox_mobile();
});

jQuery( document ).ready(function () {

	UM_wall_autocomplete_start();
	/* Image upload */
	UM_wall_img_upload();

	// first page loading
	um_wall_ajax_request();

	autosize( jQuery('.um-activity-new-post .um-activity-textarea-elem') );
	autosize( jQuery('.um-activity-widget .um-activity-comment-textarea') );

	var wall_post = um_getUrlParameter('wall_post');
	var wall_comment = um_getUrlParameter('wall_comment_id');

	if ( wall_post > 0 ) {
		if ( ! wall_comment ) {
			/* Scroll to wall post */
			jQuery( 'body' ).scrollTo( '#postid-' + parseInt( wall_post ), 500, {
				offset: 0,
				onAfter: function () {
					jQuery( '#postid-' + parseInt( wall_post ) ).addClass( 'highlighted' );
				}
			});
		} else if ( wall_comment > 0 ) {
			/* Scroll to comments area */
			jQuery( 'body' ).scrollTo( '#commentid-' + parseInt( wall_comment ), 500, {
				offset: -10,
				onAfter: function () {
					jQuery( '#commentid-' + parseInt( wall_comment ) ).addClass( 'highlighted' );
				}
			});
		}
	}


	/* Detect change in textarea content */
	jQuery( document.body ).on( 'input properychange', '.um-activity-textarea-elem', function() {
		um_check_textarea_length( jQuery( this ) );
	});


	/* Detect change in textarea content */
	jQuery( document.body ).on( 'input properychange', '.um-activity-comment-textarea', function() {
		um_check_comment_length( jQuery( this ) );
	});


	/* Post a status */
	jQuery( document.body ).on( 'click', '.um-activity-post', function() {
		if ( jQuery(this).hasClass( 'um-disabled' ) ) {
			return false;
		}

		jQuery(this).parents( '.um-activity-publish' ).submit();
	});


	/* Post publish */
	jQuery( document.body ).on( 'submit', '.um-activity-publish', function(e) {
		e.preventDefault();
		var form = jQuery(this);

		//focus on textarea if empty
		if ( form.find('textarea').val().trim().length === 0 && form.find('input[name="_post_img"]').val().trim().length === 0) {
			form.find('textarea').focus();
			return false;
		}

		um_disable_post_submit( form );

		var formdata = form.serializeArray();
		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			dataType: 'json',
			data: formdata,
			success: function( data ) {

				var widget_template;
				var template_data;

				if ( form.find('input[name="_post_id"]').val() === '0' ) {
					var wall = form.parents('.um').find('.um-activity-wall');

					widget_template = wp.template( 'um-activity-widget' );
					template_data = {
						'content'       : data.content,
						'img_src'       : data.photo_orig_base,
						'img_src_url'   : data.photo_orig_url,
						'modal'         : data.photo,
						/*'img_src'       : form.find('input[name="_post_img"]').val(),
						'img_src_url'   : form.find('input[name="_post_img_url"]').val(),*/
						'wall_id'       : formdata._wall_id,
						'user_id'       : data.user_id,
						'post_id'       : data.postid,
						'post_url'      : data.permalink,
						'photo'         : ( form.find('input[name="_post_img"]').val().trim().length > 0 ),
						'video'         : data.video || data.has_text_video,
						'video_content' : data.video,
						'oembed'        : data.has_oembed,
						'link'          : data.link
					};

					wall.prepend( widget_template( template_data ) );
					wall.find( '.unready' ).removeClass( 'unready um-activity-clone' ).fadeIn();

					form.find('textarea').val('').height('auto');
					um_clean_photo_fields( form );
					um_post_placeholder( form.find( 'textarea' ) );

					UM_wall_autocomplete_start();
				} else {
					form.parents('.um-activity-widget').removeClass( 'editing' );

					widget_template = wp.template( 'um-activity-post' );
					template_data = {
						'content'       : data.content,
						'img_src'       : data.photo_orig_base,
						'img_src_url'   : data.photo_orig_url,
						'modal'         : data.photo,
						/*'img_src'       : form.find('input[name="_post_img"]').val(),
						'img_src_url'   : form.find('input[name="_post_img_url"]').val(),*/
						'wall_id'       : formdata._wall_id,
						'user_id'       : data.user_id,
						'post_id'       : data.postid,
						'post_url'      : data.permalink,
						'photo'         : ( form.find('input[name="_post_img"]').val().trim().length > 0 ),
						'video'         : data.video || data.has_text_video,
						'video_content' : data.video,
						'oembed'        : data.has_oembed,
						'link'          : data.link
					};

					form.parents('.um-activity-body').html( widget_template( template_data ) );
				}
			}
		});
	});


	/* Default behaviour */
	jQuery( document.body ).on('click', '.um-activity-dialog a', function (e) {
		e.preventDefault();
		e.stopPropagation();
		return false;
	});


	/* open dialogs */
	jQuery( document.body ).on( 'click', '.um-activity-start-dialog', function(e) {
		e.stopPropagation();
		e.preventDefault();
		if (!jQuery(this).parents('.um-activity-widget').hasClass('unready')) {
			var to_open = jQuery(this).parent().find('.' + jQuery(this).attr('data-role'));
			if (to_open.is(':visible')) {
				to_open.hide();
			} else {
				to_open.show();
			}
		}
		return false;
	});


	/* Hides dropdown */
	jQuery( document.body ).click( function() {
		jQuery('.um-activity-dialog').hide();
		jQuery('.um-activity-comment-meta').find('.um-activity-editc-d:visible').hide();
	});


	/*Edit Post*/
	jQuery( document.body ).on( 'click', '.um-activity-manage', function() {
		var widget = jQuery(this).parents('.um-activity-widget');

		if ( jQuery(this).parents('.um-activity-dialog').length ) {
			jQuery(this).parents('.um-activity-dialog').hide();
		}

		if ( widget.hasClass( 'editing' ) ) {
			return;
		}

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			dataType: 'json',
			data: {
				action: 'um_get_activity_post',
				post_id: widget.attr('id').replace( 'postid-', '' ),
				nonce: um_scripts.nonce
			},
			success: function( response ) {
				widget.find('.um-activity-bodyinner-txt').hide();
				widget.find('.um-activity-bodyinner-photo').hide();
				widget.find('.um-activity-bodyinner-video').hide();

				var edit_template = wp.template( 'um-edit-post' );
				var edit_data = {
					'textarea' : response.data.orig_content,
					'post_id' : widget.attr('id').replace('postid-', ''),
					'_photo' : widget.find('.um-activity-bodyinner-edit').find('input[name="_photo"]').val(),
					'_photo_url' : widget.find('.um-activity-bodyinner-edit').find('input[name="_photo_url"]').val()
				};

				widget.find('.um-activity-bodyinner-edit').append( edit_template( edit_data ) );
				widget.find('.um-activity-bodyinner-edit').find('textarea:visible').focus();
				if ( widget.find('.um-activity-bodyinner-edit').find('input[name="_photo"]').val() ) {
					widget.find('.um-activity-preview').show();
				}

				autosize( widget.find('.um-activity-bodyinner-edit').find('textarea:visible') );

				UM_wall_img_upload();
				UM_wall_autocomplete_start();

				widget.addClass( 'editing' );
			},
			error: function(e) {
				console.log( 'UM Social Activity Error', e );
			}
		});
	});


	/*Cancel Editing*/
	jQuery( document.body ).on( 'click', '.um-activity-edit-cancel', function(e) {
		var widget = jQuery(this).parents('.um-activity-widget');

		widget.find('.um-activity-bodyinner-txt').show();
		widget.find('.um-activity-bodyinner-photo').show();
		widget.find('.um-activity-bodyinner-video').show();
		widget.find('form').remove();
		widget.removeClass( 'editing' );
	});


	/* Trash post popup */
	jQuery( document.body ).on( 'click', '.um-activity-trash', function() {
		if ( jQuery(this).parents('.um-activity-dialog').length ) {
			jQuery(this).parents('.um-activity-dialog').hide();
		}

		UM_wall_confirmbox_show(
			jQuery(this).parents('.um-activity-widget').attr('id').replace('postid-', ''),
			jQuery(this).attr('data-msg'),
			'um-activity-confirm-removepost'
		);
	});


	/* Removes a post */
	jQuery( document.body ).on( 'click', '.um-activity-confirm-removepost', function() {
		var post_id = jQuery(this).attr('data-post_id');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_activity_remove_post',
				post_id: post_id,
				nonce: um_scripts.nonce
			},
			success: function( data ) {
				jQuery( '.um-activity-widget#postid-' + post_id ).remove();
				UM_wall_confirmbox_hide();
			}
		});
	});


	/* Hides confirm box */
	jQuery( document.body ).on( 'click', '.um-activity-confirm-close,.um-activity-confirm-o', function() {
		UM_wall_confirmbox_hide();
	});


	/* Show hidden post content */
	jQuery( document.body ).on('click', '.um-activity-seemore a', function(e) {
		e.preventDefault();
		p = jQuery(this).parents('.um-activity-bodyinner-txt');
		p.find('.um-activity-seemore').remove();
		p.find('.um-activity-hiddentext').show();
		return false;
	});


	/* Scroll to comments area */
	jQuery( document.body ).on( 'click', '.um-activity-disp-comments a', function() {
		var post_id = jQuery(this).parents('.um-activity-widget').attr('id').replace('postid-', '');
		jQuery('body').scrollTo( '#wallcomments-' + parseInt( post_id ), { duration: 200 });
	});


	/* Focus on comment area */
	jQuery( document.body ).on( 'click', '.um-activity-comment a', function() {
		if ( ! jQuery(this).parents('.um-activity-widget').hasClass( 'unready' ) ) {
			jQuery(this).parents('.um-activity-widget').find( '.um-activity-comments .um-activity-comment-box textarea' ).focus();
		}
	});


	/* posting a comment */
	jQuery( document.body ).on( 'click', '.um-activity-comment-post', function(e) {
		e.preventDefault();

		var obj = jQuery(this);
		var comments_order = jQuery(this).parents('.um-activity-wall').data('comments_order');
		var textarea = jQuery(this).parents('.um-activity-commentl').find( '.um-activity-comment-textarea' );
		var comment = textarea.val();
		var commentbox = textarea.parents( '.um-activity-comments' );
		var replybox = textarea.parents( '.um-activity-commentwrap' );

		var postid = textarea.parents('.um-activity-widget').attr('id').replace('postid-', '');
		var comment_id = textarea.data('commentid');
		var parent_div = jQuery('#commentid-' + comment_id);

		var reply_to = textarea.attr( 'data-reply_to' );

		if ( comment_id && parent_div.length && parent_div.hasClass('editing') ) {
			// if we are editing a reply
			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				dataType: 'json',
				data: {
					action:     'um_activity_wall_comment',
					postid:     postid,
					commentid:  comment_id,
					reply_to:   reply_to,
					comment:    comment,
					nonce: um_scripts.nonce
				},
				success: function (data) {
					parent_div.find('#um-activity-reply-' + comment_id).val(comment);
					parent_div.find('.um-activity-comment-text').html(data.comment_content);
					parent_div.find('.um-activity-editc-d').hide();
					parent_div.find('.um-activity-comment-area').remove();
					parent_div.find('.um-activity-comment-info > div').show();
					parent_div.removeClass('editing');
					parent_div.find('.um-activity-commentl.um-activity-comment-area').show();
				}
			});
		} else {
			// if we are writing a new comment/reply
			textarea.val('');

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				dataType: 'json',
				data: {
					action      : 'um_activity_wall_comment',
					postid      : postid,
					reply_to    : reply_to,
					comment     : comment,
					nonce: um_scripts.nonce
				},
				success: function( data ) {
					//upgrade comments count
					var count = textarea.parents('.um-activity-widget').find('.um-activity-post-comments');
					count.html( parseInt( count.html() ) + 1 );

					var more_count;
					if ( reply_to === '0' ) {

						var comment_template = wp.template( 'um-activity-comment' );
						var comment_data = {
							'comment'           : data.comment_content,
							'comment_id'        : data.commentid,
							'user_hidden'       : data.user_hidden,
							'permalink'         : data.permalink,
							'time'              : data.time,
							'can_edit_comment'  : data.can_edit_comment,
							'user_liked_comment': data.user_liked_comment,
							'likes'             : data.likes
						};

						if ( comments_order === 'desc' ) {
							commentbox.find('.um-activity-comments-loop').prepend( comment_template( comment_data ) );
							commentbox.find( '.unready' ).removeClass( 'unready um-activity-commentl-clone' ).fadeIn();
						} else {
							if ( commentbox.find('.um-activity-comments-loop > .um-activity-commentload').length ) {
								more_count = parseInt( commentbox.find('.um-activity-comments-loop > .um-activity-commentload span.um-activity-more-count').html() );
								commentbox.find('.um-activity-comments-loop > .um-activity-commentload span.um-activity-more-count').html( more_count + 1 );
							} else {
								commentbox.find('.um-activity-comments-loop').append( comment_template( comment_data ) );
								commentbox.find( '.unready' ).removeClass( 'unready um-activity-commentl-clone' ).fadeIn();
							}
						}
					} else {

						var reply_template = wp.template( 'um-activity-comment-reply' );
						var reply_data = {
							'comment'           : data.comment_content,
							'comment_id'        : data.commentid,
							'user_hidden'       : data.user_hidden,
							'permalink'         : data.permalink,
							'time'              : data.time,
							'can_edit_comment'  : data.can_edit_comment,
							'user_liked_comment': data.user_liked_comment,
							'likes'             : data.likes
						};

						if ( ! replybox.find('.um-activity-comment-child').length ) {
							replybox.append( '<div class="um-activity-comment-child"></div>' );
						}

						if ( comments_order === 'desc' ) {
							replybox.find('.um-activity-comment-child').prepend( reply_template( reply_data ) );
							replybox.fadeIn();
						} else {
							if ( replybox.find('.um-activity-comment-child .um-activity-ccommentload').length ) {
								more_count = parseInt( commentbox.find('.um-activity-comment-child .um-activity-ccommentload span.um-activity-more-count').html() );
								commentbox.find('.um-activity-comment-child .um-activity-ccommentload span.um-activity-more-count').html( more_count + 1 );
							} else {
								replybox.find('.um-activity-comment-child').append( reply_template( reply_data ) );
								replybox.fadeIn();
							}
						}

						obj.parents( '.um-activity-commentl.um-activity-comment-area').siblings('.um-activity-comment-meta').find('.um-activity-comment-reply').trigger('click');
					}
				}
			});
		}

		um_check_comment_length( textarea );
	});


	jQuery( document.body ).on( 'keypress', '.um-activity-comment-textarea', function(e) {
		if ( ( e.keyCode === 10 || e.keyCode === 13 ) && ! e.shiftKey && jQuery(this).val().trim().length > 0 ) {
			e.preventDefault();
			jQuery(this).parents('.um-activity-commentl').find( '.um-activity-comment-post' ).trigger( 'click' );
		} else if ( ( e.keyCode === 10 || e.keyCode === 13 ) && ! e.shiftKey ) {
			e.preventDefault();
			return false;
		}
	});


	/* Opens comment edit dropdown */
	jQuery( document.body ).on('click', '.um-activity-editc a', function(e) {
		e.stopPropagation();
		e.preventDefault();
		jQuery('.um-activity-comment-meta').find('.um-activity-editc-d:visible').hide();
		var commentedit = jQuery(this).parents('.um-activity-comment-meta').find('.um-activity-editc-d');

		if (commentedit.is(':visible')) {
			commentedit.hide();
		} else {
			commentedit.show();
		}
		return false;
	});


	/* Edit the comment */
	jQuery( document.body ).on('click', '.um-activity-editc a.edit', function(e) {
		e.preventDefault();

		var obj = jQuery(this);

		if ( obj.parents('.um-activity-commentl').hasClass('unready') ) {
			return false;
		}

		if ( obj.parents('.um-activity-comment-info').find('.um-activity-comment-area').length > 0 ) {
			obj.parents('.um-activity-comment-info').find('.um-activity-comment-area').remove();
		}

		var comment_id = obj.data( 'commentid' );
		var comment_content = jQuery( '#um-activity-reply-' + comment_id ).val();
		var commentbox = jQuery( '#commentid-' + comment_id );
		var reply_to = 0;
		if ( commentbox.hasClass( 'is-child' ) ) {
			reply_to = obj.parents( '.um-activity-commentwrap' ).data( 'comment_id' );
		}

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			dataType: 'json',
			data: {
				action: 'um_get_activity_comment',
				comment_id: comment_id,
				nonce: um_scripts.nonce
			},
			success: function( response ) {
				obj.parents('.um-activity-comment-info').find('.um-activity-editc-d').hide();
				obj.parents('.um-activity-comment-info').find('div').hide();
				commentbox.addClass('editing');

				var comment_edit_template = wp.template( 'um-activity-comment-edit' );
				var comment_data = {
					'comment' : response.data.orig_content,
					'comment_id' : comment_id,
					'reply_to' : reply_to
				};

				obj.parents('.um-activity-comment-info').append( comment_edit_template( comment_data ) ).find('textarea').focus();

				UM_wall_autocomplete_start();
			},
			error: function(e) {
				console.log( 'UM Social Activity Error', e );
			}
		});


		return false;
	});


	/*Cancel Comment Editing*/
	jQuery( document.body ).on( 'click', '.um-activity-comment-edit-cancel', function() {
		var comment_area = jQuery(this).parents('.um-activity-comment-info');

		comment_area.find('.um-activity-comment-data').show();
		comment_area.find('.um-activity-comment-meta').show();

		comment_area.parent().removeClass( 'editing' );

		jQuery(this).parents('.um-activity-commentl.um-activity-comment-area').remove();
	});


	/* Trash comment popup */
	jQuery( document.body ).on('click', '.um-activity-editc a.delete', function(e) {
		e.preventDefault();
		var el = jQuery(this);
		var post_id = el.parent().parent().parent().parent().parent().attr('id').replace('commentid-', '');
		var msg = el.attr('data-msg');
		el.parents('.um-activity-dialog').hide();

		UM_wall_confirmbox_show( post_id, msg, 'um-activity-confirm-removecomment' );

		return false;
	});


	/* Removes a comment */
	jQuery( document.body ).on('click', '.um-activity-confirm-removecomment', function() {
		var comment_id = jQuery(this).attr('data-post_id');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action:'um_activity_remove_comment',
				comment_id: comment_id,
				nonce: um_scripts.nonce
			},
			success: function( data ) {
				var commentbox = jQuery( '.um-activity-commentl#commentid-' + comment_id );
				var count = commentbox.parents('.um-activity-widget').find('.um-activity-post-comments');
				count.html( parseInt( count.html() ) - 1 );
				commentbox.siblings('.um-activity-comment-child').remove();
				commentbox.remove();

				UM_wall_confirmbox_hide();
			}
		});
	});


	/* Reply to comment */
	jQuery( document.body ).on( 'click', '.um-activity-comment-reply', function() {
		if ( jQuery(this).parents('.um-activity-commentl').hasClass('unready') ) {
			return;
		}

		var comment_wrapper = jQuery(this).parents('.um-activity-comment-info');

		if ( comment_wrapper.find('.um-activity-comment-area').length === 0 ) {
			var reply_template = wp.template( 'um-activity-reply' );
			var reply_data = {
				'replyto'       : jQuery(this).attr('data-commentid')
			};

			comment_wrapper.append( reply_template( reply_data ) );
			comment_wrapper.find('textarea').focus();

			autosize( jQuery('.um-activity-widget .um-activity-comment-textarea') );

			UM_wall_autocomplete_start();
		} else {
			comment_wrapper.find('.um-activity-comment-area').remove();
		}
	});


	/* Hide a comment */
	jQuery( document.body ).on('click', '.um-activity-comment-hide', function(e) {
		e.preventDefault();
		el = jQuery(this);
		div = el.parent();

		if ( div.hasClass( 'editing' ) ) {
			div.find('.um-activity-comment-area').remove();
			div.find('.um-activity-comment-info > div').show();
			div.find('.um-activity-editc-d').hide();
			div.removeClass('editing');
		} else {
			var comment_id = div.attr('id').replace('commentid-', '');

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				data: {
					action: 'um_activity_hide_comment',
					comment_id: comment_id,
					nonce: um_scripts.nonce
				},
				success: function( data ) {
					div.find('.um-activity-comment-info').hide();
					div.find('.um-activity-comment-hidden').show();
					div.find('.um-activity-comment-avatar').addClass('hidden-1');

					div.parents('.um-activity-commentwrap[data-comment_id="' + comment_id + '"]').find('.um-activity-commentl.is-child').each( function() {
						jQuery(this).find('.um-activity-comment-info').hide();
						jQuery(this).find('.um-activity-comment-hidden').show();
						jQuery(this).find('.um-activity-comment-avatar').addClass('hidden-1');
						jQuery(this).find('.um-activity-comment-hide').remove();
					});

					el.remove();
				}
			});
		}

		return false;
	});


	/* Unhide a comment */
	jQuery( document.body ).on('click', '.um-activity-comment-hidden a', function(e) {
		e.preventDefault();
		el = jQuery(this);
		var comment_id = el.parent().parent().attr('id').replace('commentid-', '');

		el.parent().parent().find('.um-activity-comment-info').show();
		el.parent().parent().find('.um-activity-comment-hidden').hide();
		el.parent().parent().find('.um-activity-comment-hide').show();
		el.parent().parent().find('.um-activity-comment-avatar').removeClass('hidden-1');

		el.parent().parent().prepend('<a href="#" class="um-activity-comment-hide um-tip-s"><i class="um-icon-close-round"></i></a>');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_activity_unhide_comment',
				comment_id: comment_id,
				nonce: um_scripts.nonce
			},
			success: function (data) {
			}
		});

		return false;
	});


	/* Report post */
	jQuery( document.body ).on('click', '.um-activity-report:not(.flagged)', function() {
		var el = jQuery(this);
		var post_id = el.parents('.um-activity-widget').attr('id').replace('postid-', '');
		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_activity_report_post',
				post_id: post_id,
				nonce: um_scripts.nonce
			},
			success: function (data) {
				el.addClass('flagged').html(el.attr('data-cancel_report'));
			}
		});
	});

	/* Cancel report post */
	jQuery( document.body ).on('click', '.um-activity-report.flagged', function() {
		var el = jQuery(this);
		var post_id = el.parents('.um-activity-widget').attr('id').replace('postid-', '');
		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_activity_unreport_post',
				post_id: post_id,
				nonce: um_scripts.nonce
			},
			success: function (data) {
				el.removeClass('flagged').html(el.attr('data-report'));
			}
		});
	});


	/* load more comments */
	jQuery( document.body ).on('click', '.um-activity-commentload', function(e) {
		e.preventDefault();
		var el = jQuery(this);

		el.hide();
		el.parent().find('.um-activity-commentload-spin').show();

		var offset = el.attr('data-loaded');
		var post_id = el.parents('.um-activity-widget').attr('id').replace('postid-', '');

		el.parents('.um-activity-comments').find('.um-activity-commentload-end').remove();
		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_activity_load_more_comments',
				post_id: post_id,
				offset: offset,
				nonce: um_scripts.nonce
			},
			success: function (data) {
				el.before( data );
				el.attr('data-loaded', el.parents('.um-activity-comments').find('.um-activity-commentl:not(.is-child):not(.um-activity-comment-area):visible').length);
				el.parent().find('.um-activity-commentload-spin').hide();
				if ( el.parents('.um-activity-comments').find('.um-activity-commentload-end').length ) {
					el.show().find('span').html( el.attr('data-load_comments') );
				} else {
					el.remove();
				}
			}
		});

		return false;
	});


	/* load more replies */
	jQuery( document.body ).on('click', '.um-activity-ccommentload', function(e) {
		e.preventDefault();
		var el = jQuery(this);

		el.hide();
		el.parent().find('.um-activity-ccommentload-spin').show();

		var offset = el.attr('data-loaded');
		var post_id = el.parents('.um-activity-widget').attr('id').replace('postid-', '');
		var comment_id = el.parents('.um-activity-commentwrap').attr('data-comment_id');

		el.parents('.um-activity-comments').find('.um-activity-ccommentload-end').remove();
		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_activity_load_more_replies',
				post_id: post_id,
				comment_id: comment_id,
				offset: offset,
				nonce: um_scripts.nonce
			},
			success: function (data) {
				el.before(data);
				el.attr('data-loaded', el.parents('.um-activity-commentwrap').find('.um-activity-commentl.is-child:not(.um-activity-comment-area):visible').length);
				el.parent().find('.um-activity-ccommentload-spin').hide();
				if (el.parents('.um-activity-commentwrap').find('.um-activity-ccommentload-end').length) {
					el.show().find('span').html(el.attr('data-load_replies'));
				}
			}
		});

		return false;
	});



	/* Like of a comment */
	jQuery( document.body ).on('click', '.um-activity-comment-like:not(.active)', function(e) {
		e.preventDefault();
		if (!jQuery(this).parents('.um-activity-commentl').hasClass('unready')) {
			var commentid = jQuery(this).parents('.um-activity-commentl').attr('id').replace('commentid-', '');
			var counter = jQuery(this).parents('.um-activity-commentl').find('.um-activity-ajaxdata-commentlikes');
			var ncount = parseInt(counter.html()) + 1;
			counter.html(ncount);
			jQuery(this).parents('.um-activity-commentl').find('.um-activity-comment-likes').removeClass().addClass('um-activity-comment-likes').addClass('count-' + ncount);
			jQuery(this).addClass('active');
			jQuery(this).html(jQuery(this).attr('data-unlike_text'));
			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'um_activity_like_comment',
					commentid: commentid,
					nonce: um_scripts.nonce
				},
				success: function (data) {

				}
			});
		}
		return false;
	});

	/* Unlike of a comment */
	jQuery( document.body ).on('click', '.um-activity-comment-like.active', function(e) {
		e.preventDefault();
		var commentid = jQuery(this).parents('.um-activity-commentl').attr('id').replace('commentid-', '');
		var counter = jQuery(this).parents('.um-activity-commentl').find('.um-activity-ajaxdata-commentlikes');
		var ncount = parseInt(counter.html()) - 1;
		counter.html(ncount);
		jQuery(this).parents('.um-activity-commentl').find('.um-activity-comment-likes').removeClass().addClass('um-activity-comment-likes').addClass('count-' + ncount);
		jQuery(this).removeClass('active');
		jQuery(this).html(jQuery(this).attr('data-like_text'));
		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			dataType: 'json',
			data: {
				action:'um_activity_unlike_comment',
				commentid: commentid,
				nonce: um_scripts.nonce
			},
			success: function (data) {

			}
		});
		return false;
	});

	/* Like of a post */
	jQuery( document.body ).on('click', '.um-activity-like:not(.active) a', function(e) {
		e.preventDefault();
		if ( ! jQuery(this).parents('.um-activity-widget').hasClass('unready') ) {
			var postid = jQuery(this).parents('.um-activity-widget').attr('id').replace('postid-', '');
			jQuery(this).find('i').addClass('um-effect-pop');
			jQuery(this).parent().addClass('active');
			jQuery(this).find('span').html(jQuery(this).parent().attr('data-unlike_text'));
			jQuery(this).find('i').addClass('um-active-color');
			var count = jQuery(this).parents('.um-activity-widget').find('.um-activity-post-likes');
			count.html(parseInt(count.html()) + 1);
			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				dataType: 'json',
				data: {
					action:'um_activity_like_post',
					postid: postid,
					nonce: um_scripts.nonce
				},
				success: function (data) {

				}
			});
		}
		return false;
	});

	/* Unlike of a post */
	jQuery( document.body ).on('click', '.um-activity-like.active a', function(e) {
		e.preventDefault();
		var postid = jQuery(this).parents('.um-activity-widget').attr('id').replace('postid-', '');
		jQuery(this).find('i').removeClass('um-effect-pop');
		jQuery(this).parent().removeClass('active');
		jQuery(this).find('span').html(jQuery(this).parent().attr('data-like_text'));
		jQuery(this).find('i').removeClass('um-active-color');
		var count = jQuery(this).parents('.um-activity-widget').find('.um-activity-post-likes');
		count.html(parseInt(count.html()) - 1);
		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			dataType: 'json',
			data: {
				action:'um_activity_unlike_post',
				postid: postid,
				nonce: um_scripts.nonce
			},
			success: function (data) {

			}
		});
		return false;
	});

	/* Show post likes in modal */
	jQuery( document.body ).on('click', '.um-activity-show-likes', function(e) {
		e.preventDefault();

		el = jQuery(this);
		var post_id = el.attr('data-post_id');

		if (parseInt(el.find('.um-activity-post-likes').html()) <= 0) {
			return false;
		}

		prepare_Modal();

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_activity_get_post_likes',
				post_id: post_id,
				nonce: um_scripts.nonce
			},
			success: function (data) {
				if (data) {
					show_Modal(data);
					responsive_Modal();
				} else {
					remove_Modal();
				}
			}
		});

		return false;
	});

	/* Show comment likes in modal */
	jQuery( document.body ).on('click', '.um-activity-comment-likes', function(e) {
		e.preventDefault();

		el = jQuery(this);
		var comment_id = el.parent().parent().parent().attr('id').replace('commentid-', '');

		prepare_Modal();

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_activity_get_comment_likes',
				comment_id: comment_id,
				nonce: um_scripts.nonce
			},
			success: function (data) {
				if (data) {
					show_Modal(data);
					responsive_Modal();
				} else {
					remove_Modal();
				}
			}
		});

		return false;
	});


	/* Hide modal with likes */
	jQuery( document.body ).on('click', '.um-activity-modal-hide', function() {
		remove_Modal();
	});
});