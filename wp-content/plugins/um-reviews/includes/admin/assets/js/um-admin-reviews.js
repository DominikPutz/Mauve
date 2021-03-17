jQuery(document).ready(function () {

    jQuery('.um-reviews-avg').um_raty({
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

    jQuery('.um-reviews-rate').um_raty({
        half: false,
        starType: 'i',
        number: function () {
            return jQuery(this).attr('data-number');
        },
        score: function () {
            return jQuery(this).attr('data-score');
        },
        scoreName: function () {
            return jQuery(this).attr('data-key');
        },
        hints: ['1 Star', '2 Star', '3 Star', '4 Star', '5 Star'],
        space: false
    });
    resizeDoc();
});
jQuery(window).on('resize orientationchange', function () {
    resizeDoc();
});


/**
 * Get user avatar for "From" and "To" fields in the "This Review" metabox
 * @author Alex
 * @since 2019-08-01
 */
jQuery('.um-form-review .um-forms-line select').on('change', function (e) {
	var $img = jQuery(e.target).parent().find('img').first();

	jQuery.ajax({
		url: wpUmReviewsApiSettings.root + 'wp/v2/users/' + e.target.value,
		method: 'GET',
		beforeSend: function (xhr) {
			xhr.setRequestHeader('X-WP-Nonce', wpUmReviewsApiSettings.nonce);
			$img.add(e.target).css({'cursor': 'wait'});
		},
		data: {
			'size': $img.attr('width') || 40
		}
	}).done(function (response) {
		if (typeof (response) === 'object' && typeof (response.um_photo) === 'object') {
			$img.attr('alt', response.um_photo.alt || '');
			$img.attr('src', response.um_photo.url || response.um_photo.default);
			$img.attr('title', response.um_photo.alt || e.target.selectedOptions[0].text);
			$img.add(e.target).removeAttr('style');
		}
	}).fail(function (response) {
		console.dir(response);
	});
});


/**
 * This function works at boot time "jQuery(document).ready()" and at events "resize orientationchange"
 **/
function resizeDoc() {
    var bw = jQuery("body").width();
    /**
     * It puts the "Name" column to the top and in the end
     **/
    if (bw <= 782) {
        jQuery('.post-type-um_review thead tr,.post-type-um_review tbody tr,.post-type-um_review tfoot tr').each(function (i, element) {
            jQuery('.column-review_from', jQuery(element)).before(jQuery('.column-title', jQuery(element)));
        });
    } else {
        jQuery('.post-type-um_review thead tr,.post-type-um_review tbody tr,.post-type-um_review tfoot tr').each(function (i, element) {
            jQuery(".column-review_flag", jQuery(element)).after(jQuery('.column-title', jQuery(element)));
        });
    }

}