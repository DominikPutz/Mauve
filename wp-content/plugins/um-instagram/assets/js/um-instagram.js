if (typeof (window.UM) !== 'object') {
	window.UM = {};
}

window.UM.instagram = {
	ajax: {
		get_photos: function () {

			var request = {
				action: 'um_instagram_get_photos',
				dataType: 'json',
				metakey: UM.instagram.block.wrap.attr('data-metakey'),
				um_user_id: UM.instagram.block.wrap.attr('data-user_id'),
				viewing: UM.instagram.block.wrap.attr('data-viewing'),
				nonce: um_scripts.nonce
			};

			return jQuery.ajax({
				method: 'POST',
				url: wp.ajax.settings.url,
				data: request,
				dataType: 'json',
				beforeSend: function (xhr) {
					UM.instagram.block.preload.css({'backgroundImage': 'url(' + um_instagram.image_loader + ')'}).fadeIn();
				},
				success: function (response) {
					if (typeof (response) === 'object') {

						UM.instagram.data = jQuery.extend(UM.instagram.data, {
							offset: 0,
							shownPhotos: response.photos.slice(0, 6),
							photos: response.photos,
							total: response.photos.length,
							viewing: 'true'
						});

						UM.instagram.template.paginate(UM.instagram.data);
						UM.instagram.template.photos(UM.instagram.data);
						UM.instagram.preload();
					}
				},
				error: function (e) {
					console.log(e);
				}
			});
		}
	},
	block: {
		content: jQuery('#um-ig-content'),
		gallery: jQuery('#um-ig-show_photos'),
		paginate: jQuery('#um-ig-content .um-ig-paginate'),
		preload: jQuery('#um-ig-preload'),
		wrap: jQuery('#um-ig-photo-wrap')
	},
	data: {
		offset: 0,
		photos: {},
		total: 0
	},
	preload: function () {

		// Show preload
		UM.instagram.block.content.css({display: 'block', height: '1px', overflowY: 'hidden'});
		UM.instagram.block.preload.css({display: 'block'});

		// Add photo opacity effects on hover
		UM.instagram.block.content.find('a img').hover(function (e) {
			jQuery(e.currentTarget).stop().animate({"opacity": 0.7});
		}, function (e) {
			jQuery(e.currentTarget).stop().animate({"opacity": 1});
		});

		// Make square thumbnails
		setTimeout(function () {
			var size = UM.instagram.block.gallery.children('li:first').width();
			UM.instagram.block.gallery.children('li').each(function (i, item) {
				var $li = jQuery(item);
				if ($li.height() >= size) {
					$li.css({height: size + 'px', overflowY: 'hidden'});
				}
				if ($li.height() < size) {
					$li.css({overflowX: 'hidden'}).find('img').css({height: size + 'px'});
				}
			});
			UM.instagram.block.content.removeAttr('style');
		}, 600);

		// Remove preload
		setTimeout(function () {
			UM.instagram.block.preload.css({display: 'none'});
			UM.instagram.block.content.fadeIn();
		}, 800);
	},
	setup: function () {

		if (!UM.instagram.block.gallery.length) {
			UM.instagram.ajax.get_photos().done(function (response) {
				if (typeof (response) === 'object' && !response.has_error) {
					UM.instagram.setup();
				}
			});
			return;
		}

		// Previous
		UM.instagram.block.nav_previous = UM.instagram.block.content.find('a.nav-left');
		UM.instagram.block.nav_previous.on('click', UM.instagram.showPrev);
		if (UM.instagram.data.offset - 6 < 0) {
			UM.instagram.block.nav_previous.hide();
		}

		// Next
		UM.instagram.block.nav_next = UM.instagram.block.content.find('a.nav-right');
		UM.instagram.block.nav_next.on('click', UM.instagram.showNext);
		if (UM.instagram.data.offset + 6 > UM.instagram.data.total) {
			UM.instagram.block.nav_next.hide();
		}

		// Disconnect Instagram account
		UM.instagram.block.disconnect = UM.instagram.block.content.siblings('.um-ig-photos_disconnect');
		UM.instagram.block.disconnect.on('click', function () {
			jQuery('.um-ig-photos_metakey').val('');
			jQuery('.um-form form').submit();
		});
	},
	showNext: function () {

		var offset = Math.min(UM.instagram.data.offset + 6, UM.instagram.data.photos.length);

		UM.instagram.block.preload.css({height: UM.instagram.block.content.height() + 'px'});
		UM.instagram.data = jQuery.extend(UM.instagram.data, {
			offset: offset,
			shownPhotos: UM.instagram.data.photos.slice(offset, offset + 6)
		});

		UM.instagram.template.paginate(UM.instagram.data);
		UM.instagram.template.photos(UM.instagram.data);
		UM.instagram.preload();

		if (offset + 6 >= UM.instagram.data.total) {
			UM.instagram.block.nav_next.hide();
		}
		UM.instagram.block.nav_previous.show();
	},
	showPrev: function () {
		var offset = Math.max(UM.instagram.data.offset - 6, 0);

		UM.instagram.block.preload.css({height: UM.instagram.block.content.height() + 'px'});
		UM.instagram.data = jQuery.extend(UM.instagram.data, {
			offset: offset,
			shownPhotos: UM.instagram.data.photos.slice(offset, offset + 6)
		});

		UM.instagram.template.paginate(UM.instagram.data);
		UM.instagram.template.photos(UM.instagram.data);
		UM.instagram.preload();

		if (offset - 6 < 0) {
			UM.instagram.block.nav_previous.hide();
		}
		UM.instagram.block.nav_next.show();
	},
	template: {
		paginate: function (data, wrapper) {
			wrapper = wrapper || UM.instagram.block.paginate;
			data = jQuery.extend(UM.instagram.data, {
				firstSeen: data.offset + 1,
				lastSeen: Math.min(data.offset + 6, data.total)
			});

			var html = '<span>' + data.firstSeen + ' - ' + data.lastSeen + '/' + data.total + '</span>';

			wrapper.html(html);
		},
		photos: function (data, wrapper) {
			wrapper = wrapper || UM.instagram.block.wrap;
			data = jQuery.extend(UM.instagram.data, data || {});

			var html = '';

			html += '<ul id="um-ig-show_photos" data-offset="' + data.offset + '" data-photos-count="' + data.total + '" data-viewing="' + data.viewing + '">';

			for (var i in data.shownPhotos) {
				var photo = data.shownPhotos[i];

				html += '<li>';
				html += '	<a class="um-photo-modal" href="' + photo.images.standard_resolution.url + '" data-src="' + photo.images.standard_resolution.url + '">';
				html += '		<img class="um-lazy" src="' + photo.images.thumbnail.url + '" data-original="' + photo.images.standard_resolution.url + '" />';
				html += '	</a>';
				html += '</li>';
			}

			while (++i < 6 || i % 6 !== 0) {
				html += '<li class="um-ig-photo-placeholder"></li>';
			}

			html += '</ul>';

			return UM.instagram.block.gallery = wrapper.html(html).find('#um-ig-show_photos');
		}
	}
};

jQuery(function () {
	UM.instagram.setup();
});