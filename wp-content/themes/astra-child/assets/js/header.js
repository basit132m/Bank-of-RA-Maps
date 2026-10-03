/**
 * Bank of YR Maps — header behaviour.
 *
 * Progressive enhancement: the header is fully usable with JavaScript off.
 * This adds the condensed-on-scroll state, the mobile menu panel and the
 * search drawer, with focus handling for all three.
 */
(function () {
  'use strict';

  var header = document.querySelector('[data-byrm-header]');
  if (!header) {
    return;
  }

  var nav = document.getElementById('byrm-nav');
  var burger = header.querySelector('.byrm-burger');
  var searchPanel = document.getElementById('byrm-search');
  var searchToggle = header.querySelector('.byrm-search-toggle');
  var searchField = document.getElementById('byrm-search-field');
  var backdrop = document.querySelector('[data-byrm-backdrop]');

  var DESKTOP = 981;

  // Separate thresholds for condensing and expanding. Condensing removes ~58px
  // of header, which shortens the page and can nudge the scroll position back
  // across a single threshold — the header then flips state repeatedly and
  // visibly shakes. The gap between these two values is wider than that shift,
  // so each change settles on the first try.
  var CONDENSE_AT = 140;
  var EXPAND_AT = 60;

  // ------------------------------------------------- publish header height
  // The mobile menu panel and its backdrop start directly below the header,
  // so they need its current height — which changes when it condenses.
  function publishHeight() {
    header.style.setProperty('--byrm-header-h', header.offsetHeight + 'px');
  }

  // ------------------------------------------------------ condense on scroll
  // rAF-throttled so scrolling stays cheap on low-end machines.
  var ticking = false;

  function applyScrollState() {
    var y = window.scrollY;
    var condensed = header.classList.contains('is-stuck');

    if (!condensed && y > CONDENSE_AT) {
      header.classList.add('is-stuck');
    } else if (condensed && y < EXPAND_AT) {
      header.classList.remove('is-stuck');
    }

    // Read back after the class change so the panel follows the new height.
    window.setTimeout(publishHeight, 300);
    publishHeight();
    ticking = false;
  }

  window.addEventListener('scroll', function () {
    if (!ticking) {
      window.requestAnimationFrame(applyScrollState);
      ticking = true;
    }
  }, { passive: true });

  applyScrollState();
  publishHeight();

  if (typeof ResizeObserver === 'function') {
    new ResizeObserver(publishHeight).observe(header);
  }

  // ------------------------------------------------------------ mobile menu
  function setMenu(open) {
    if (!nav || !burger) {
      return;
    }

    nav.classList.toggle('is-open', open);
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');

    if (backdrop) {
      backdrop.classList.toggle('is-open', open);
      if (open) {
        backdrop.removeAttribute('hidden');
      } else {
        backdrop.setAttribute('hidden', '');
      }
    }

    // Stop the page scrolling behind the open panel.
    document.documentElement.style.overflow = open ? 'hidden' : '';

    if (open) {
      var first = nav.querySelector('a');
      if (first) {
        first.focus();
      }
    }
  }

  function menuIsOpen() {
    return !!nav && nav.classList.contains('is-open');
  }

  if (burger) {
    burger.addEventListener('click', function () {
      setMenu(!menuIsOpen());
    });
  }

  if (backdrop) {
    backdrop.addEventListener('click', function () {
      setMenu(false);
    });
  }

  // ---------------------------------------------------------- search drawer
  function setSearch(open) {
    if (!searchPanel || !searchToggle) {
      return;
    }

    searchPanel.classList.toggle('is-open', open);
    searchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');

    if (open && searchField) {
      // Wait for the drawer to start opening before focusing, so the browser
      // does not scroll the page to reach a zero-height field.
      window.setTimeout(function () {
        searchField.focus();
      }, 60);
    }
  }

  function searchIsOpen() {
    return !!searchPanel && searchPanel.classList.contains('is-open');
  }

  if (searchToggle) {
    searchToggle.addEventListener('click', function () {
      setSearch(!searchIsOpen());
    });
  }

  // --------------------------------------------------------------- keyboard
  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
      return;
    }

    if (menuIsOpen()) {
      setMenu(false);
      if (burger) {
        burger.focus();
      }
    }

    if (searchIsOpen()) {
      setSearch(false);
      if (searchToggle) {
        searchToggle.focus();
      }
    }
  });

  // Close the menu when a click lands outside it.
  document.addEventListener('click', function (event) {
    if (!menuIsOpen()) {
      return;
    }
    if (nav.contains(event.target) || (burger && burger.contains(event.target))) {
      return;
    }
    setMenu(false);
  });

  // ------------------------------------------------------------- viewport
  // Leaving mobile width with the panel open would strand it open.
  var resizeTimer;
  window.addEventListener('resize', function () {
    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(function () {
      if (window.innerWidth >= DESKTOP && menuIsOpen()) {
        setMenu(false);
      }
    }, 150);
  });
})();
