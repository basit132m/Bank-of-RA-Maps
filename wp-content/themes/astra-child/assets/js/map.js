/**
 * Bank of YR Maps — map page gallery and image viewer.
 *
 * Two behaviours:
 *  - hovering a thumbnail magnifies the image inside its frame, following the
 *    pointer, so detail can be inspected without leaving the page;
 *  - clicking opens a full-screen viewer with prev/next, a thumbnail strip,
 *    keyboard control, swipe, and a 1:1 inspect mode for high-resolution art.
 *
 * Without JavaScript every thumbnail is still a plain link to the full image.
 */
(function () {
  'use strict';

  var shots = Array.prototype.slice.call(document.querySelectorAll('[data-byrm-shot]'));
  if (!shots.length) {
    return;
  }

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var canHover = window.matchMedia('(hover: hover)').matches;

  // ====================================================== hover magnifier ====
  // Moving transform-origin under the pointer makes the frame behave like a
  // loupe: the part being pointed at is the part that enlarges.
  if (canHover) {
    shots.forEach(function (shot) {
      var img = shot.querySelector('.byrm-shot__img');
      if (!img) {
        return;
      }

      shot.addEventListener('pointermove', function (event) {
        if (event.pointerType !== 'mouse') {
          return;
        }

        var box = shot.getBoundingClientRect();
        var x = ((event.clientX - box.left) / box.width) * 100;
        var y = ((event.clientY - box.top) / box.height) * 100;

        img.style.transformOrigin =
          Math.max(0, Math.min(100, x)) + '% ' + Math.max(0, Math.min(100, y)) + '%';
      });

      shot.addEventListener('pointerleave', function () {
        img.style.transformOrigin = '50% 50%';
      });
    });
  }

  // ============================================================== viewer ====
  var lb = document.getElementById('byrm-lightbox');
  if (!lb) {
    return;
  }

  var stage = lb.querySelector('[data-byrm-lb-stage]');
  var img = lb.querySelector('[data-byrm-lb-img]');
  var caption = lb.querySelector('[data-byrm-lb-caption]');
  var counter = lb.querySelector('[data-byrm-lb-count]');
  var strip = lb.querySelector('[data-byrm-lb-strip]');
  var prevBtn = lb.querySelector('[data-byrm-lb-prev]');
  var nextBtn = lb.querySelector('[data-byrm-lb-next]');
  var zoomBtn = lb.querySelector('[data-byrm-lb-zoom]');
  var closers = lb.querySelectorAll('[data-byrm-lb-close]');

  var items = shots.map(function (shot) {
    var thumb = shot.querySelector('img');
    return {
      full: shot.getAttribute('data-full'),
      width: parseInt(shot.getAttribute('data-width'), 10) || 0,
      height: parseInt(shot.getAttribute('data-height'), 10) || 0,
      caption: shot.getAttribute('data-caption') || '',
      alt: shot.getAttribute('data-alt') || '',
      thumb: thumb ? thumb.currentSrc || thumb.src : ''
    };
  });

  var current = 0;
  var opener = null;
  var zoomed = false;

  // ---------------------------------------------------------- thumb strip
  items.forEach(function (item, index) {
    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'byrm-lb__thumb';
    button.setAttribute('aria-label', 'Image ' + (index + 1));

    var thumbImg = document.createElement('img');
    thumbImg.src = item.thumb;
    thumbImg.alt = '';
    thumbImg.loading = 'lazy';
    button.appendChild(thumbImg);

    button.addEventListener('click', function () {
      show(index);
    });

    strip.appendChild(button);
  });

  var thumbs = Array.prototype.slice.call(strip.querySelectorAll('.byrm-lb__thumb'));

  // ------------------------------------------------------------- display
  function setZoom(on) {
    zoomed = !!on;
    lb.classList.toggle('is-zoomed', zoomed);
    zoomBtn.setAttribute('aria-pressed', zoomed ? 'true' : 'false');

    if (!zoomed) {
      img.style.transform = '';
      img.style.width = '';
      img.style.height = '';
    } else {
      // Native pixel size, so "ultra HD" uploads can actually be inspected.
      img.style.width = items[current].width ? items[current].width + 'px' : 'auto';
      img.style.height = 'auto';
      centreZoom();
    }
  }

  /** Keep the middle of the image in view when inspect mode opens. */
  function centreZoom() {
    if (!zoomed) {
      return;
    }
    stage.scrollLeft = (stage.scrollWidth - stage.clientWidth) / 2;
    stage.scrollTop = (stage.scrollHeight - stage.clientHeight) / 2;
  }

  function show(index) {
    if (index < 0 || index >= items.length) {
      return;
    }

    current = index;
    var item = items[index];

    setZoom(false);
    img.classList.add('is-loading');

    var loader = new Image();
    loader.onload = function () {
      img.src = item.full;
      img.alt = item.alt;
      img.classList.remove('is-loading');
    };
    loader.onerror = function () {
      img.classList.remove('is-loading');
    };
    loader.src = item.full;

    // If it is already cached the handler may not fire, so set it anyway.
    if (loader.complete) {
      img.src = item.full;
      img.alt = item.alt;
      img.classList.remove('is-loading');
    }

    caption.textContent = item.caption;
    counter.textContent = (index + 1) + ' / ' + items.length;

    thumbs.forEach(function (thumb, i) {
      thumb.classList.toggle('is-current', i === index);
      if (i === index) {
        thumb.setAttribute('aria-current', 'true');
      } else {
        thumb.removeAttribute('aria-current');
      }
    });

    if (thumbs[index]) {
      thumbs[index].scrollIntoView({ block: 'nearest', inline: 'center',
        behavior: reduced ? 'auto' : 'smooth' });
    }

    prevBtn.disabled = index === 0;
    nextBtn.disabled = index === items.length - 1;

    preload(index + 1);
    preload(index - 1);
  }

  /** Warm the neighbouring image so stepping through feels instant. */
  function preload(index) {
    if (index < 0 || index >= items.length) {
      return;
    }
    var pre = new Image();
    pre.src = items[index].full;
  }

  // --------------------------------------------------------- open / close
  function open(index, trigger) {
    opener = trigger || null;
    lb.hidden = false;
    document.body.classList.add('byrm-lb-open');

    // Next frame, so the opacity transition has a starting point to animate from.
    window.requestAnimationFrame(function () {
      lb.classList.add('is-open');
    });

    show(index);
    nextBtn.focus();
  }

  function close() {
    lb.classList.remove('is-open');
    setZoom(false);

    var finish = function () {
      lb.hidden = true;
      document.body.classList.remove('byrm-lb-open');
      if (opener) {
        opener.focus();
        opener = null;
      }
    };

    if (reduced) {
      finish();
    } else {
      window.setTimeout(finish, 240);
    }
  }

  function step(delta) {
    var next = current + delta;
    if (next >= 0 && next < items.length) {
      show(next);
    }
  }

  // ------------------------------------------------------------- wiring
  shots.forEach(function (shot, index) {
    shot.addEventListener('click', function (event) {
      event.preventDefault();
      open(index, shot);
    });
  });

  prevBtn.addEventListener('click', function () { step(-1); });
  nextBtn.addEventListener('click', function () { step(1); });

  Array.prototype.forEach.call(closers, function (button) {
    button.addEventListener('click', close);
  });

  zoomBtn.addEventListener('click', function () { setZoom(!zoomed); });
  img.addEventListener('click', function () { setZoom(!zoomed); });

  // Pan the zoomed image by dragging.
  var dragging = false;
  var dragX = 0;
  var dragY = 0;

  stage.addEventListener('pointerdown', function (event) {
    if (!zoomed) {
      return;
    }
    dragging = true;
    dragX = event.clientX;
    dragY = event.clientY;
    stage.setPointerCapture(event.pointerId);
  });

  stage.addEventListener('pointermove', function (event) {
    if (!dragging) {
      return;
    }
    stage.scrollLeft -= event.clientX - dragX;
    stage.scrollTop -= event.clientY - dragY;
    dragX = event.clientX;
    dragY = event.clientY;
  });

  stage.addEventListener('pointerup', function () { dragging = false; });
  stage.addEventListener('pointercancel', function () { dragging = false; });

  // ---------------------------------------------------------- keyboard
  document.addEventListener('keydown', function (event) {
    if (lb.hidden) {
      return;
    }

    switch (event.key) {
      case 'Escape':
        event.preventDefault();
        if (zoomed) { setZoom(false); } else { close(); }
        break;
      case 'ArrowLeft':
        event.preventDefault();
        step(-1);
        break;
      case 'ArrowRight':
        event.preventDefault();
        step(1);
        break;
      case 'Home':
        event.preventDefault();
        show(0);
        break;
      case 'End':
        event.preventDefault();
        show(items.length - 1);
        break;
      case 'Tab':
        trapFocus(event);
        break;
      default:
        break;
    }
  });

  /** Keep keyboard focus inside the dialog while it is open. */
  function trapFocus(event) {
    var focusable = lb.querySelectorAll('button:not([disabled])');
    if (!focusable.length) {
      return;
    }

    var first = focusable[0];
    var last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }

  // ------------------------------------------------------------- swipe
  var touchX = null;

  lb.addEventListener('touchstart', function (event) {
    if (zoomed || event.touches.length !== 1) {
      touchX = null;
      return;
    }
    touchX = event.touches[0].clientX;
  }, { passive: true });

  lb.addEventListener('touchend', function (event) {
    if (touchX === null) {
      return;
    }
    var delta = event.changedTouches[0].clientX - touchX;
    if (Math.abs(delta) > 50) {
      step(delta < 0 ? 1 : -1);
    }
    touchX = null;
  }, { passive: true });

  window.addEventListener('resize', centreZoom);
})();
