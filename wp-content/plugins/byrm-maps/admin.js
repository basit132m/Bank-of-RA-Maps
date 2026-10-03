/**
 * Bank of YR Maps — Map edit screen.
 *
 * Wires the screenshot gallery to the WordPress media library and makes it
 * sortable by dragging. The map file itself is hosted off-site, so there is no
 * file picker here — just a link typed into the Map download panel.
 */
(function ($) {
  'use strict';

  var strings = window.byrmAdmin || {};

  // ------------------------------------------------------------- gallery ----
  var $list = $('#byrm-gallery-list');
  var $input = $('#byrm-gallery-input');

  /** Write the current thumbnail order back into the hidden field. */
  function syncGallery() {
    var ids = $list.find('.byrm-gallery-item').map(function () {
      return $(this).data('id');
    }).get();

    $input.val(ids.join(','));
  }

  if ($list.length) {
    var galleryFrame;

    $('#byrm-gallery-add').on('click', function (event) {
      event.preventDefault();

      // Reusing one frame keeps previous selections between openings.
      if (!galleryFrame) {
        galleryFrame = wp.media({
          title: strings.galleryTitle || 'Select screenshots',
          button: { text: strings.galleryButton || 'Use these images' },
          library: { type: 'image' },
          multiple: 'add'
        });

        galleryFrame.on('select', function () {
          var existing = ($input.val() || '').split(',').filter(Boolean);

          galleryFrame.state().get('selection').each(function (attachment) {
            var id = String(attachment.id);
            if (existing.indexOf(id) !== -1) {
              return; // Already in the gallery.
            }

            var sizes = attachment.get('sizes') || {};
            var src = (sizes.medium || sizes.thumbnail || sizes.full || {}).url || attachment.get('url');

            $list.append(
              $('<li class="byrm-gallery-item"></li>')
                .attr('data-id', id)
                .append($('<img>').attr('alt', '').attr('src', src))
                .append('<button type="button" class="byrm-gallery-remove" aria-label="Remove image">&times;</button>')
            );
          });

          syncGallery();
        });
      }

      galleryFrame.open();
    });

    $list.on('click', '.byrm-gallery-remove', function (event) {
      event.preventDefault();
      $(this).closest('.byrm-gallery-item').remove();
      syncGallery();
    });

    $('#byrm-gallery-clear').on('click', function (event) {
      event.preventDefault();
      if (window.confirm(strings.confirmClear || 'Remove all screenshots?')) {
        $list.empty();
        syncGallery();
      }
    });

    if ($.fn.sortable) {
      $list.sortable({ items: '> .byrm-gallery-item', update: syncGallery });
    }
  }

})(jQuery);
