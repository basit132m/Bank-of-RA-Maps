/**
 * Bank of YR Maps — progressive enhancement only.
 * Every page works with JavaScript disabled; this adds the mobile menu and
 * submits the filter form when a select changes.
 */
(function () {
  'use strict';

  // ---------------------------------------------------------- mobile menu --
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.getElementById('site-nav');

  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    // Close the menu when focus or a click leaves it.
    document.addEventListener('click', function (event) {
      if (!nav.classList.contains('is-open')) {
        return;
      }
      if (nav.contains(event.target) || toggle.contains(event.target)) {
        return;
      }
      nav.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && nav.classList.contains('is-open')) {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
      }
    });
  }

  // -------------------------------------------------------- filter form ----
  // Applying a filter should not need a second click. The Apply button stays
  // in the markup for anyone without JavaScript.
  var filters = document.querySelector('.filters');

  if (filters) {
    // Keep empty fields out of the query string, so shared and indexed URLs
    // read /maps?players=2 rather than /maps?q=&players=2&theater=&game=.
    filters.addEventListener('submit', function () {
      filters.querySelectorAll('input, select').forEach(function (field) {
        if (field.value === '') {
          field.disabled = true;
        }
      });
    });

    filters.querySelectorAll('select').forEach(function (select) {
      select.addEventListener('change', function () {
        if (typeof filters.requestSubmit === 'function') {
          filters.requestSubmit();
        } else {
          filters.submit();
        }
      });
    });
  }
})();
