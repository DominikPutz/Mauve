;(function($) {

    /**
     * Plugin
     */

    $.fn.imagesGrid = function(options) {

        var args = arguments;

        return this.each(function() {

            // If options is plain object - destroy previous instance and create new
            if ($.isPlainObject(options)) {

                if (this._imgGrid instanceof ImagesGrid) {
                    this._imgGrid.destroy();
                    delete this._imgGrid;
                }

                var opts = $.extend({}, $.fn.imagesGrid.defaults, options);
                opts.element = $(this);
                this._imgGrid = new ImagesGrid(opts);

                return;
            }

            // If options is string - execute method
            if (typeof options === 'string' && this._imgGrid instanceof ImagesGrid) {
                switch (options) {
                    case 'modal.open':
                        this._imgGrid.modal.open(args[1]);
                        break;
                    case 'modal.close':
                        this._imgGrid.modal.close();
                        jQuery('body').trigger( 'um_user_photos_modal_close' );
                        jQuery('body').removeClass('um-user-photos-modal-open');
                        break;
                    case 'destroy':
                        this._imgGrid.destroy();
                        delete this._imgGrid;
                        jQuery('body').removeClass('um-user-photos-modal-open');
                        break;
                }
            }

        });

    };

    /**
     * Plugin default options
     */

    $.fn.imagesGrid.defaults = {
        
        images: [],
        cells: 5,
        align: false,
        nextOnClick: true,
        showViewAll: 'more',
        viewAllStartIndex: 'auto',
        loading: 'loading...',
        getViewAllText: function(imagesCount) {
            return 'View all ' + imagesCount + ' images';
        },
        onGridRendered: $.noop,
        onGridItemRendered: $.noop,
        onGridLoaded: $.noop,
        onGridImageLoaded: $.noop,
        onModalOpen: $.noop,
        onModalClose: $.noop,
        onModalImageClick: $.noop,
        onModalImageUpdate: $.noop
        
    };

    /**
     * ImagesGrid
     *   opts                    - Grid options
     *   opts.element            - Element where to render images grid
     *   opts.images             - Array of images. Array item can be string or object { src, alt, title, caption, thumbnail }
     *   opts.align              - Align images with different height
     *   opts.cells              - Maximum number of cells (from 1 to 6)
     *   opts.showViewAll        - Show view all text:
     *                                'more'   - show if number of images greater than number of cells
     *                                'always' - always show
     *                                false    - never show
     *   opts.viewAllStartIndex  - Start image index when view all link clicked
     *   opts.getViewAllText     - Callback function returns text for "view all images" link
     *   opts.onGridRendered     - Callback function fired when grid items added to the DOM
     *   opts.onGridItemRendered - Callback function fired when grid item added to the DOM
     *   opts.onGridLoaded       - Callback function fired when grid images loaded
     *   opts.onGridImageLoaded  - Callback function fired when grid image loaded
     */

    function ImagesGrid(opts) {

        this.opts = opts || {};

        this.$window = $(window);
        this.$element = this.opts.element;
        this.$gridItems = [];

        this.modal = null;
        this.imageLoadCount = 0;

        var cells = this.opts.cells;
        this.opts.cells = (cells < 1)? 1: (cells > 6)? 6: cells;

        this.onWindowResize = this.onWindowResize.bind(this);
        this.onImageClick = this.onImageClick.bind(this);

        this.init();
    }

    ImagesGrid.prototype.init = function(){

        this.setGridClass();
        this.renderGridItems();
        this.createModal();

        this.$window.on('resize', this.onWindowResize);
    }

    ImagesGrid.prototype.createModal = function() {

        var opts = this.opts;

        this.modal = new ImagesGridModal({
            loading: opts.loading,
            images: opts.images,
            nextOnClick: opts.nextOnClick,
            onModalOpen: opts.onModalOpen,
            onModalClose: opts.onModalClose,
            onModalImageClick: opts.onModalImageClick,
            onModalImageUpdate: opts.onModalImageUpdate
        });
    }

    ImagesGrid.prototype.setGridClass = function() {

        var opts = this.opts,
            imgsLen = opts.images.length,
            cellsCount = (imgsLen < opts.cells)? imgsLen: opts.cells;

        this.$element.addClass('imgs-grid imgs-grid-' + cellsCount);
    }

    ImagesGrid.prototype.renderGridItems = function() {

        var opts = this.opts,
            imgs = opts.images,
            imgsLen = imgs.length;

        if (!imgs) {
            return;
        }

        this.$element.empty();
        this.$gridItems = [];

        for (var i = 0; i < imgsLen; ++i) {
            if (i === opts.cells) {
                break;
            }
            this.renderGridItem(imgs[i], i);
        }

        if (opts.showViewAll === 'always' ||
            (opts.showViewAll === 'more' && imgsLen > opts.cells)
        ) {
            this.renderViewAll();
        }

        opts.onGridRendered(this.$element);
    }

    ImagesGrid.prototype.renderGridItem = function(image, index) {

        var src = image,
            alt = '',
            title = '',
            id = '',
            author = '',
            opts = this.opts,
            _this = this;

        if ($.isPlainObject(image)) {
            
            src = image.thumbnail || image.src;
            alt = image.alt || '';
            title = image.title || '';
            id = image.id || '';
            author = image.author || '';
            
        }

        var item = $('<div>', {
            class: 'imgs-grid-image',
            click: this.onImageClick,
            data: { index: index }
        });

        item.append(
            $('<div>', {
                class: 'image-wrap'
            }).append(
                $('<img>', {
                    src: src,
                    alt: alt,
                    title: title,
                    author: author,
                    id: parseInt(id.replace("user_photo-",'')),
                    on: {
                        load: function(event) {
                            _this.onImageLoaded(event, $(this), image);
                        }
                    }
                })
            )
        );

        this.$gridItems.push(item);
        this.$element.append(item);

        opts.onGridItemRendered(item, image);
    }

    ImagesGrid.prototype.renderViewAll = function() {

        var opts = this.opts;

        this.$element.find('.imgs-grid-image:last .image-wrap').append(
            $('<div>', {
                class: 'view-all'
            }).append(
                $('<span>', {
                    class: 'view-all-cover',
                }),
                $('<span>', {
                    class: 'view-all-text',
                    text: opts.getViewAllText(opts.images.length)
                })
            )
        );
    }

    ImagesGrid.prototype.onWindowResize = function(event) {
        if (this.opts.align) {
            this.align();
        }
    }

    ImagesGrid.prototype.onImageClick = function(event) {

        var opts = this.opts,
            img = $(event.currentTarget),
            imageIndex;

        if (img.find('.view-all').length > 0 &&
            typeof opts.viewAllStartIndex === 'number' ) {
            imageIndex = opts.viewAllStartIndex;
        } else {
            imageIndex = img.data('index');
        }
        
        this.modal.open(imageIndex);
        jQuery('body').trigger( 'um_user_photos_modal_open' );
        jQuery('body').addClass('um-user-photos-modal-open');
    }

    ImagesGrid.prototype.onImageLoaded = function(event, imageEl, image) {

        var opts = this.opts;

        ++this.imageLoadCount;

        opts.onGridImageLoaded(event, imageEl, image);

        if (this.imageLoadCount === this.$gridItems.length) {
            this.imageLoadCount = 0;
            this.onAllImagesLoaded()
        }
    }

    ImagesGrid.prototype.onAllImagesLoaded = function() {

        var opts = this.opts;

        if (opts.align) {
            this.align();
        }

        opts.onGridLoaded(this.$element);
    }

    ImagesGrid.prototype.align = function() {

        var itemsLen = this.$gridItems.length;

        switch (itemsLen) {
            case 2:
            case 3:
                this.alignItems(this.$gridItems);
                break;
            case 4:
                this.alignItems(this.$gridItems.slice(0, 2));
                this.alignItems(this.$gridItems.slice(2));
                break;
            case 5:
            case 6:
                this.alignItems(this.$gridItems.slice(0, 3));
                this.alignItems(this.$gridItems.slice(3));
                break;
        }
    }

    ImagesGrid.prototype.alignItems = function(items) {

        var itemsHeight = items.map(function(item) {
            return item.find('img').height();
        });

        var normalizedHeight = Math.min.apply(null, itemsHeight);

        $(items).each(function() {

            var item = $(this),
                imgWrap = item.find('.image-wrap'),
                img = item.find('img'),
                imgHeight = img.height();

            imgWrap.height(normalizedHeight);

            if (imgHeight > normalizedHeight) {
                var top = Math.floor((imgHeight - normalizedHeight) / 2);
                img.css({ top: -top });
            }
        });
    }

    ImagesGrid.prototype.destroy = function() {

        this.$window.off('resize',this.onWindowResize);

        this.$element.empty()
            .removeClass('imgs-grid imgs-grid-' + this.$gridItems.length);

        this.modal.destroy();
        jQuery('body').removeClass('um-user-photos-modal-open');
    }

    /**
     * ImagesGridModal
     *  opts                    - Modal options
     *  opts.images             - Array of images
     *  opts.nextOnClick        - Show next image when click on modal image
     *  opts.loading            - Image loading text
     *  opts.onModalOpen        - Callback function called when modal opened
     *  opts.onModalClose       - Callback function called when modal closed
     *  opts.onModalImageClick  - Callback function called on modal image click
     */

    function ImagesGridModal(opts) {

        this.opts = opts || {};

        this.imageIndex = null;

        this.$document = $(document);
        this.$modal = null;
        this.$indicator = null;

        this.close = this.close.bind(this);
        this.prev = this.prev.bind(this);
        this.next = this.next.bind(this);
        this.onIndicatorClick = this.onIndicatorClick.bind(this);
        this.onImageLoaded = this.onImageLoaded.bind(this);
        this.onKeyUp = this.onKeyUp.bind(this);

        this.$document.on('keyup', this.onKeyUp);
    }

    ImagesGridModal.prototype.open = function(imageIndex) {

        if (this.isOpened()) {
            return;
        }

        this.imageIndex = parseInt(imageIndex) || 0;
        this.render();
    }

    ImagesGridModal.prototype.close = function(event) {

        if (!this.$modal) {
            return;
        }

        var opts = this.opts;

        this.$modal.animate({
            opacity: 0
        }, {
            duration: 100,
            complete: function() {
                this.$modal.remove();
                this.$modal = null;
                this.$indicator = null;
                this.imageIndex = null;
                opts.onModalClose();
            }.bind(this)
        });
        
        jQuery('body').removeClass('um-user-photos-modal-open');
    }

    ImagesGridModal.prototype.isOpened = function() {
        return (this.$modal && this.$modal.is(':visible'));
    }

    ImagesGridModal.prototype.render = function() {

        var opts = this.opts;

        this.renderModal();
        this.renderCloseButton();
        this.renderInnerContainer();
        this.renderCaption();
        //this.renderIndicatorContainer();

        this.$modal.animate({
            opacity: 1
        }, {
            duration: 100,
            complete: function() {
                opts.onModalOpen(this.$modal, opts.images[this.imageIndex]);
            }.bind(this)
        });
    }

    ImagesGridModal.prototype.renderModal = function() {
        this.$modal = $('<div>', {
            class: 'imgs-grid-modal'
        }).appendTo('body');
    }

    // caption

    ImagesGridModal.prototype.renderCloseButton = function() {
        this.$modal.append($('<div>', {
            class: 'modal-close',
            html: '&times;',
            click: this.close
        }));
    }

    ImagesGridModal.prototype.renderInnerContainer = function() {

        var opts = this.opts,
            image = this.getImage(this.imageIndex);
        
        this.$modal.append($('<div>', {
            class: 'modal-close',
            html: '&times;',
            click: this.close
        }));

        this.$modal.append(
            $('<div>', {
                class: 'modal-inner'
            }).append(
                $('<div>', {
                    class: 'modal-image'
                }).append(
                    $('<img>', {
                        src: image.src,
                        alt: image.alt,
                        title: image.title,
                        id: image.id,
                        on: {
                            load: this.onImageLoaded,
                            click: function(event) {
                                this.onImageClick(event, $(this), image);
                            }.bind(this)
                        }
                    }),
                    $('<div>', {
                        class: 'modal-loader',
                        html: opts.loading
                    })
                ),
                $('<div>', {
                    class: 'modal-control left',
                    click: this.prev
                }).append(
                    $('<div>', {
                        class: 'arrow left'
                    })
                ),
                $('<div>', {
                    class: 'modal-control right',
                    click: this.next
                }).append(
                    $('<div>', {
                        class: 'arrow right'
                    })
                )
            )
        );

        if (opts.images.length <= 1) {
            this.$modal.find('.modal-control').hide();
        }
    }
    
    
    
    

    ImagesGridModal.prototype.renderIndicatorContainer = function() {

        var opts = this.opts,
            imgsLen = opts.images.length;

        if (imgsLen == 1) {
            return;
        }

        this.$indicator = $('<div>', {
            class: 'modal-indicator'
        });

        var list = $('<ul>'), i;
        for (i = 0; i < imgsLen; ++i) {
            list.append($('<li>', {
                class: this.imageIndex == i? 'selected': '',
                click: this.onIndicatorClick,
                data: { index: i }
            }));
        }

        this.$indicator.append(list);
        this.$modal.append(this.$indicator);
    }

    ImagesGridModal.prototype.prev = function() {

        var imgsLen = this.opts.images.length;

        if (this.imageIndex > 0) {
            --this.imageIndex;
        } else {
            this.imageIndex = imgsLen - 1;
        }

        this.updateImage();
    }

    ImagesGridModal.prototype.next = function() {

        var imgsLen = this.opts.images.length;

        if (this.imageIndex < imgsLen - 1) {
            ++this.imageIndex;
        } else {
            this.imageIndex = 0;
        }

        this.updateImage();
    }

    ImagesGridModal.prototype.updateImage = function() {

        var opts = this.opts,
            image = this.getImage(this.imageIndex),
            imageEl = this.$modal.find('.modal-image img');
        
           // console.log('updateImage '+image);

        imageEl.attr({
            src: image.src,
            alt: image.alt,
            title: image.title,
            author: image.author,
            id: image.id
        });

        this.$modal.find('.modal-caption').text(
            this.getImageCaption(image.id) );

        if (this.$indicator) {
            var indicatorList = this.$indicator.find('ul');
            indicatorList.children().removeClass('selected');
            indicatorList.children().eq(this.imageIndex).addClass('selected');
        }

        this.showLoader();

        opts.onModalImageUpdate(imageEl, image);
    }
    
    
    
    
    
    /*add caption div*/
    ImagesGridModal.prototype.renderCaption = function() {
        
        
        var image = this.getImage(this.imageIndex);
        var caption_text = this.getImageCaption(image.id);
        
        this.$caption = $('<div>', {
        class: 'modal-caption',
        style: 'background:#fff;',
        html:'<div style="text-align:center;padding-top:50px;"><div class="um-user-photos-ajax-loading"></div></div>'
        }).appendTo(this.$modal);
        
        if( caption_text ){
            $('body').find('.imgs-grid-modal').find('modal-caption').html(caption_text);
        }
        
    }
    
    

    ImagesGridModal.prototype.onImageClick = function(event, imageEl, image) {

        var opts = this.opts;

        if (opts.nextOnClick) {
            this.next();
        }

        opts.onModalImageClick(event, imageEl, image);
    }

    ImagesGridModal.prototype.onImageLoaded = function() {
        this.hideLoader();
    }

    ImagesGridModal.prototype.onIndicatorClick = function(event) {
        var index = $(event.target).data('index');
        this.imageIndex = index;
        this.updateImage();
    }

    ImagesGridModal.prototype.onKeyUp = function(event) {

        if (!this.$modal) {
            return;
        }

        switch (event.keyCode) {
            case 27: // Esc
                this.close();
                break;
            case 37: // Left arrow
                this.prev();
                break;
            case 39: // Right arrow
                this.next();
                break;
        }
    }

    // only returning img url
    ImagesGridModal.prototype.getImage = function(index) {

        var opts = this.opts,
            image = opts.images[index];

        if ($.isPlainObject(image)) {
            return image;
        } else {
            return { src: image, alt: '', title: '' }
        }
    }

    ImagesGridModal.prototype.getImageCaption = function(imgId) {
        //var img = this.getImage(imgIndex);
        
        jQuery('body').find('.imgs-grid-modal').find('.modal-caption').html( '<div style="text-align:center;padding-top:50px;"><div class="um-user-photos-ajax-loading"></div></div>' );
        
        var caption = get_caption_text(imgId)
        if(caption){
            return caption;
        }
        
        
    }

    ImagesGridModal.prototype.showLoader = function() {
        if (this.$modal) {
            this.$modal.find('.modal-image img').hide();
            this.$modal.find('.modal-loader').show();
        }
    }

    ImagesGridModal.prototype.hideLoader = function() {
        if (this.$modal) {
            this.$modal.find('.modal-image img').show();
            this.$modal.find('.modal-loader').hide();
        }
    }

    ImagesGridModal.prototype.destroy = function() {
        this.$document.off('keyup', this.onKeyUp);
        this.close();
    }

})(jQuery);

function get_caption_text( imageId ){
    
    imageId = parseInt(imageId.replace("user_photo-",''));
    
    jQuery('body').find('.imgs-grid-modal').find('.modal-caption').html( '<div style="text-align:center;padding-top:50px;"><div class="um-user-photos-ajax-loading"></div></div>' );
    
    wp.ajax.send( 'um_user_photos_get_comment_section', {
			data: {
				image_id: imageId
			},
			success: function( data ) {
                jQuery('body').find('.imgs-grid-modal').find('.modal-caption').html( data );
               // return data;
			},
			error: function( data ) {
				console.log( data );
                 return data;
			}
	});
    
    return '';
}


jQuery(function($) {
  var albums = $('body').find('.um_user_photos_activity_view');

  if(albums.length){
      $.each( albums, function( i, val ) {

        var element = $(val);
        var img = element.attr('data-images');
        var img = element.find('img');
        var img_arr = [];
        $.each( img, function( index, ele ) {
          img_arr.push({
              'src' : ele.src,
              'title' : ele.title,
              'alt' : ele.alt,
              'author' : ele.author,
              'id' : ele.id,
          });
        });
        //console.log(img);
        //if(img && img !=''){

        //var img_arr = img.split(',');
        var album_id = '#'+element.attr('id');
        $(album_id).imagesGrid({
              images: img_arr,
              align: true,
              getViewAllText: function(imgsCount) {
                  var extra_pic = imgsCount - 5;
                  return '+ '+extra_pic+' more';

              }
          });

        //} // endif
      }); // end each
  } // endif
});




jQuery(function($) {
    
    
    
    
  $(document).on('click','[data-umaction="open_modal"]',function(e){
      e.preventDefault();
      var btn = $(this);
      var id = btn.attr('data-id');
      var src = btn.attr('href');
      var alt = btn.attr('alt');
      var title = btn.attr('title');
      open_modal({
          id:id,
          src:src,
          alt:alt,
          title:title
      });
      
    }); // image click
    
    
    $(document).on('click','.um-user-photos-modal-close',function(e){
        e.preventDefault();
        var btn = $(this);
        var modal = btn.parents('.imgs-grid-modal');
        modal.animate({opacity: 0}, {
            duration: 100,
            complete: function() {
                modal.remove();
                $('body').removeClass('um-user-photos-modal-open');
            }
        });
    });
    
    // next
    $(document).on('click','.um-photo-modal-next',function(e){
        e.preventDefault();
        
        $('body').find('.imgs-grid-modal').find('.modal-caption').html('<div style="text-align:center;padding-top:50px;"><div class="um-user-photos-ajax-loading"></div></div>');
        
        var btn = $(this);
        var next_image = null;
        var modal = btn.parents('.imgs-grid-modal');
        var current_image_id = modal.find('.modal-image').find('img').attr('id');
        
        var current_image = $('body').find('a[data-id="'+current_image_id+'"][data-umaction="open_modal"]').first();
        
        var current_block = current_image.parents('.um-user-photos-image').parent('.um-user-photos-image-block');
        
        var next_block = current_block.nextAll(".um-user-photos-image-block").filter(':first');
        
        if(! next_block.length){
            next_block = $('body').find(".um-user-photos-image-block").first();
        }
        
        next_image = next_block.find('a[data-umaction="open_modal"]');
        
        open_modal({
          id:next_image.attr('data-id'),
          src:next_image.attr('href'),
          alt:'',
          title:''
        });
        
    });
    
    // prev
    $(document).on('click','.um-photo-modal-prev',function(e){
        e.preventDefault();
        
        $('body').find('.imgs-grid-modal').find('.modal-caption').html('<div style="text-align:center;padding-top:50px;"><div class="um-user-photos-ajax-loading"></div></div>');
        
        var btn = $(this);
        var next_image = null;
        var modal = btn.parents('.imgs-grid-modal');
        modal.find('.modal-caption').html('<div style="text-align:center;padding-top:50px;"><div class="um-user-photos-ajax-loading"></div></div>');
        var current_image_id = modal.find('.modal-image').find('img').attr('id');
        var current_image = $('body').find('a[data-id="'+current_image_id+'"][data-umaction="open_modal"]').first();
        var current_block = current_image.parent('.um-user-photos-image').parent('.um-user-photos-image-block');
        var prev_block = current_block.prevAll(".um-user-photos-image-block").filter(':first');
        
        if(! prev_block.length){
            prev_block = $('body').find(".um-user-photos-image-block").last();
        }
        
        prev_image = prev_block.find('a[data-umaction="open_modal"]');
        
        open_modal({
          id:prev_image.attr('data-id'),
          src:prev_image.attr('href'),
          alt:'',
          title:''
        });
        
    });
    
});



function open_modal(image){
    
    //console.log(image);
    
    var caption = get_caption_text(image.id);
    var html = jQuery('body').find('.imgs-grid-modal').first();
    
    if( ! html.length){
        
        var html = '';
          html +='<div class="imgs-grid-modal" style="opacity: 1;">';

                html += '<div class="modal-close um-user-photos-modal-close">&times;</div>';

                html += '<div class="modal-inner">';

                    html += '<div class="modal-image">';

                        html +='<img src="'+image.src+'" alt="" title="'+image.title+'" id="'+image.id+'">';

                        html += '<div class="modal-loader" style="display: none;">loading...</div>';

                    html += '</div>';


                    html +='<div class="modal-control left"><div class="arrow left um-photo-modal-prev"></div></div>';

                    html += '<div class="modal-control right"><div class="arrow right um-photo-modal-next"></div></div>';

                html += '</div>';
        
            html +='<div class="modal-caption"><div style="text-align:center;padding-top:50px;"><div class="um-user-photos-ajax-loading"></div></div></div>';

            html +='</div>';

          jQuery('body').append(html);
        
    }else{
        
       // console.log('modal already open');
        html.find('.modal-inner').find('.modal-image').html('<div class="um-user-photos-ajax-loading"></div>');
        html.find('.modal-caption').html( '<div style="text-align:center;padding-top:50px;"><div class="um-user-photos-ajax-loading"></div></div>' );
        
        html.find('.modal-inner').find('.modal-image').html('<img src="'+image.src+'" alt="" title="'+image.title+'" id="'+image.id+'">');
        
        if(caption){
            html.find('.modal-caption').html( caption );
        }
        
        
    }
    
    jQuery('body').addClass('um-user-photos-modal-open');
    
}







/*ajax calls*/
jQuery( document ).ready(function () {

	/* Like of a post */
	jQuery( document.body ).on('click', '.um-user-photos-like:not(.active) a', function(e) {
		e.preventDefault();
		
		
			var postid = jQuery(this).parents('.um-user-photos-widget').attr('id').replace('postid-', '');
        
			jQuery(this).find('i').addClass('um-effect-pop');
        
			jQuery(this).parent().addClass('active');
        
			jQuery(this).find('span').html(jQuery(this).parent().attr('data-unlike_text'));
			jQuery(this).find('i').addClass('um-active-color');
			
			var count = jQuery(this).parents('.um-user-photos-widget').find('.um-user-photos-post-likes');
			
			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				dataType: 'json',
				data: {
					action:'um_user_photos_like_photo',
					postid: postid
				},
				success: function (response) {
					
					//console.log(data);
					count.html(response.data);
				}
			});
		
	});

	/* Unlike of a post */
	jQuery( document.body ).on('click', '.um-user-photos-like.active a', function(e) {
		e.preventDefault();
		
		
			var postid = jQuery(this).parents('.um-user-photos-widget').attr('id').replace('postid-', '');
        
			jQuery(this).find('i').removeClass('um-effect-pop');
        
			jQuery(this).parent().removeClass('active');
        
			jQuery(this).find('span').html(jQuery(this).parent().attr('data-like_text'));
			jQuery(this).find('i').removeClass('um-active-color');
			
			var count = jQuery(this).parents('.um-user-photos-widget').find('.um-user-photos-post-likes');
			
			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				dataType: 'json',
				data: {
					action:'um_user_photos_unlike_photo',
					postid: postid
				},
				success: function (response) {
					
					//console.log(data);
					count.html(response.data);
				}
			});
		
	});
    
    
    
    
    // focus on comment
    jQuery( document.body ).on( 'click', '.um-user-photos-comment a', function() {
		if ( ! jQuery(this).parents('.um-user-photos-widget').hasClass( 'unready' ) ) {
			jQuery(this).parents('.um-user-photos-widget').find( '.um-user-photos-comments .um-user-photos-comment-box textarea' ).focus();
		}
	});
    
    // enable comment button
    jQuery( document.body ).on( 'keypress', '.um-user-photos-comment-textarea', function(e) {
		
       if(  jQuery(this).val().trim().length > 0 ) {
            
			//e.preventDefault();
			jQuery(this).parents('.um-user-photos-commentl').find( '.um-user-photos-comment-post' ).removeClass( 'um-disabled' );
            
		} else {
            
			jQuery(this).parents('.um-user-photos-commentl').find( '.um-user-photos-comment-post' ).addClass( 'um-disabled' );
            
		}
        
	});
    
    
    
    
    // submit comment
    	/* posting a comment */
	jQuery( document.body ).on( 'click', '.um-user-photos-comment-post', function(e) {
        
        e.preventDefault();
        
        var btn = jQuery(this);
        
		var textarea = btn.parents('.um-user-photos-commentl').find( '.um-user-photos-comment-textarea' );
        
		var comment = textarea.val();
		var postid = textarea.parents('.um-user-photos-widget').attr('id').replace('postid-', '');
		var comment_holder = textarea.parents('.um-user-photos-widget').find('.um-user-photos-comments-loop');
        
        var count = jQuery(this).parents('.um-user-photos-widget').find('.um-user-photos-post-comments');
        
        wp.ajax.send( 'um_user_photos_post_comment', {
			data: {
				image_id: postid,
                comment: comment
			},
			success: function( response ) {
                
                //console.log(response);
                comment_holder.prepend( response.content );
                count.html( response.count );
                textarea.val('');
                btn.addClass('um-disabled');
              
			},
			error: function( data ) {
				console.log( data );
                 return data;
			}
	   });
        
        
        
	}); // submit comment
    
    
    
    
    /* Opens comment edit dropdown */
	jQuery( document.body ).on('click', '.um-user-photos-editc a', function(e) {
		
		e.preventDefault();
        var btn = jQuery(this);
        
		
		var dropdown = btn.parents('.um-user-photos-comment-meta').find('.um-user-photos-editc-d');
        dropdown.toggle();
        //btn.find('i').toggleClass('um-icon-close um-icon-edit');	
		
	});
    
    
    /* Like comment */
	jQuery( document.body ).on('click', '.um-user-photos-comment-like', function(e) {
		
		e.preventDefault();
        var btn = jQuery(this);
		var comment_id = btn.attr('data-id');
        var new_text = btn.attr('data-unlike_text');
        var like_count = btn.parents('.um-user-photos-comment-meta').find('.um-user-photos-ajaxdata-commentlikes');
        var current_count = parseInt(like_count.html());
        var action = 'um_user_photos_like_comment';
        
        if( btn.hasClass('active') ){
            
            new_text = btn.attr('data-like_text');
            action = 'um_user_photos_unlike_comment';
            btn.removeClass('active');
            //btn.text(new_text);
            //like_count.text( current_count - 1);
            
        } else {
            
            btn.addClass('active');
            
        }
        
        wp.ajax.send(action, {
                    data: {
                        commentid: comment_id
                    },
                    success: function( response ) {
                       btn.text(new_text);
                       like_count.text( response.count );
                    },
                    error: function( data ) {
                        console.log( data );
                    }
            });
		
	});
    
    
    
    // show photo likes
    jQuery(document).on('click','.um-user-photos-show-likes',function(e){
        e.preventDefault();
        
        var btn = jQuery(this);
        var target = jQuery('body').find('[data-scope="um-user-photos-modal"]');
        
        if( ! target.length ){
            
            jQuery('body').append('<div class="um-user-photos-modal" data-scope="um-user-photos-modal"><div class="um-user-photos-modal-body"><div class="um-user-photos-modal-head"><div class="um-user-photos-modal-title"></div></div><div class="um-user-photos-modal-content"></div></div></div>');
            
            target = jQuery('body').find('[data-scope="um-user-photos-modal"]');
        }
        
        var modal_title = btn.attr('data-modal_title');
        var modal_content = '<div class="text-center"><div class="um-user-photos-ajax-loading"></div></div>';
        var modal_content_div = target.find('.um-user-photos-modal-content');
        modal_content_div.html(modal_content);

        target.show();

        var template_path = btn.attr('data-template');


        wp.ajax.send('get_um_user_photo_likes', {
                    data: {
                        image_id: btn.attr('data-id')
                    },
                    success: function( response ) {
                       modal_content_div.html(response.content);
                       target.find('.um-user-photos-modal-title').text(modal_title);
                    },
                    error: function( data ) {
                        console.log( data );
                    }
            });
        
    });
    
    
    // show comment likes
    jQuery(document).on('click','.um-user-photos-comment-likes a',function(e){
        e.preventDefault();
        
        var btn = jQuery(this);
        var target = jQuery('body').find('[data-scope="um-user-photos-modal"]');
        
        if( ! target.length ){
            
            jQuery('body').append('<div class="um-user-photos-modal" data-scope="um-user-photos-modal"><div class="um-user-photos-modal-body"><div class="um-user-photos-modal-head"><div class="um-user-photos-modal-title"></div></div><div class="um-user-photos-modal-content"></div></div></div>');
            
            target = jQuery('body').find('[data-scope="um-user-photos-modal"]');
        }
        
        var modal_title = btn.attr('data-modal_title');
        var modal_content = '<div class="text-center"><div class="um-user-photos-ajax-loading"></div></div>';
        var modal_content_div = target.find('.um-user-photos-modal-content');
        modal_content_div.html(modal_content);

        target.show();

        var template_path = btn.attr('data-template');


        wp.ajax.send('get_um_user_photos_comment_likes', {
                    data: {
                        comment_id: btn.attr('data-id')
                    },
                    success: function( response ) {
                       modal_content_div.html(response.content);
                       target.find('.um-user-photos-modal-title').text(modal_title);
                    },
                    error: function( data ) {
                        console.log( data );
                    }
            });
        
    });
    
    
    // Edit comment
    jQuery(document).on('click','.um-user-photos-editc-d .edit',function(e){
        e.preventDefault();
        
        var btn = jQuery(this);
        var target = jQuery('body').find('[data-scope="um-user-photos-modal"]');
        
        if( ! target.length ){
            
            jQuery('body').append('<div class="um-user-photos-modal" data-scope="um-user-photos-modal"><div class="um-user-photos-modal-body"><div class="um-user-photos-modal-head"><div class="um-user-photos-modal-title"></div></div><div class="um-user-photos-modal-content"></div></div></div>');
            
            target = jQuery('body').find('[data-scope="um-user-photos-modal"]');
        }
        
        var modal_title = btn.attr('data-modal_title');
        var modal_content = '<div class="text-center"><div class="um-user-photos-ajax-loading"></div></div>';
        
        var modal_content_div = target.find('.um-user-photos-modal-content');
        modal_content_div.html(modal_content);

        target.show();

        var template_path = btn.attr('data-template');


        wp.ajax.send('get_um_user_photos_comment_edit', {
                    data: {
                        comment_id: btn.attr('data-commentid')
                    },
                    success: function( response ) {
                       modal_content_div.html(response.content);
                       target.find('.um-user-photos-modal-title').text(modal_title);
                    },
                    error: function( data ) {
                        console.log( data );
                    }
            });
        
    });
    
    // Edit comment
    jQuery(document).on('click','.um-user-photos-editc-d .delete',function(e){
        e.preventDefault();
        
        var btn = jQuery(this);
        var target = jQuery('body').find('[data-scope="um-user-photos-modal"]');
        
        if( ! target.length ){
            
            jQuery('body').append('<div class="um-user-photos-modal" data-scope="um-user-photos-modal"><div class="um-user-photos-modal-body"><div class="um-user-photos-modal-head"><div class="um-user-photos-modal-title"></div></div><div class="um-user-photos-modal-content"></div></div></div>');
            
            target = jQuery('body').find('[data-scope="um-user-photos-modal"]');
        }
        
        var modal_title = btn.attr('data-modal_title');
        var modal_content = '<div class="text-center"><div class="um-user-photos-ajax-loading"></div></div>';
        
        var modal_content_div = target.find('.um-user-photos-modal-content');
        modal_content_div.html(modal_content);

        target.show();

        var template_path = btn.attr('data-template');


        wp.ajax.send('get_um_user_photos_comment_delete', {
                    data: {
                        comment_id: btn.attr('data-commentid'),
                        msg : btn.attr('data-msg')
                    },
                    success: function( response ) {
                       modal_content_div.html(response.content);
                       target.find('.um-user-photos-modal-title').text(modal_title);
                    },
                    error: function( data ) {
                        console.log( data );
                    }
            });
        
    });
    
    
    
    jQuery(document).on('click','#delete-um-user-photos-comment',function(e){
        e.preventDefault();
        
        var btn = jQuery(this);
        
        if(btn.hasClass('busy')){
            return;
        }
        
        btn.addClass('busy');
        
        var btn_init = btn.text();
        btn.html('<i class="um-user-photos-ajax-loading"></i>');
        // delete comment here
        wp.ajax.send('um_user_photos_comment_delete', {
                    data: {
                        comment_id: btn.attr('data-id')
                    },
                    success: function( response ) {
                        
                       btn.html(btn_init);
                       btn.removeClass('busy');
                        
                       jQuery('body').find('.um-user-photos-commentwrap[data-comment_id="'+btn.attr('data-id')+'"]').remove();
                        
                        btn.parents('.um-user-photos-modal').hide();
                        
                    },
                    error: function( data ) {
                        
                        btn.html(btn_init);
                        btn.removeClass('busy');
                        
                        console.log( data );
                    }
        });
    });
    
    
    
    jQuery(document).on('click','#um-user-photos-comment-update-btn',function(e){
        e.preventDefault();
        
        var btn = jQuery(this);
        
        if(btn.hasClass('busy')){
            return;
        }
        
        btn.addClass('busy');
        
        var btn_init = btn.text();
        btn.html('<i class="um-user-photos-ajax-loading"></i>');
        
        var form = btn.parents('form');
        
        wp.ajax.send('um_user_photos_comment_update', {
                    data: {
                        comment_id: btn.attr('data-commentid'),
                        comment_content : btn.parents('form').find('[name="comment_text"]').val()
                    },
                    success: function( response ) {
                        
                        btn.html(btn_init);
                        btn.removeClass('busy');
                        
                       form.find('.um-galley-form-response').html(response.message);
                        
                        jQuery('body').find('.modal-caption').find('[data-comment_id="'+response.comment_id+'"]').find('.um-user-photos-comment-text').html(response.comment);
                        
                       setTimeout(function(){
                           
                           btn.parents('.um-user-photos-modal').hide();
                           
                       }, 2000);
                        
                        

                    },
                    error: function( data ) {
                        
                        btn.html(btn_init);
                        btn.removeClass('busy');
                        
                        console.log( data );
                    }
        });
        
        
        
        //btn.parents('.um-user-photos-modal').hide();
       
    });
    
    

});