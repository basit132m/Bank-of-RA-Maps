/**
 * Bank of YR Maps — maps archive.
 *
 * Progressive enhancement only. Filters are links and the search box is a GET
 * form, so the page filters correctly with JavaScript disabled; this adds the
 * mobile filter panel and makes the sort control navigate on change.
 */
(function () {
  'use strict';

  // ---------------------------------------------------------- sort select --
  // Each option's value is the URL for that sort order, so changing the
  // control is just a navigation.
  var sort = document.querySelector('[data-byrm-sort]');

  if (sort) {
    sort.addEventListener('change', function () {
      if (this.value) {
        window.location.href = this.value;
      }
    });
  }

  // -------------------------------------------------------- filter panel --
  var toggle = document.querySelector('[data-byrm-filters-toggle]');
  var panel = document.getElementById('byrm-filters');

  if (toggle && panel) {
    toggle.addEventListener('click', function () {
      var open = panel.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');

      if (open) {
        var first = panel.querySelector('input, a');
        if (first) {
          first.focus();
        }
      }
    });
  }

  // --------------------------------------------------------- search reset --
  // Submitting the search should start from page one rather than keeping the
  // page number from the previous result set.
  var form = document.querySelector('[data-byrm-filter-form]');

  if (form) {
    form.addEventListener('submit', function () {
      // Drop empty fields so the resulting URL stays readable.
      form.querySelectorAll('input[type="search"], input[type="hidden"]').forEach(function (field) {
        if (field.value === '' || (field.name === 'sort' && field.value === 'newest')) {
          field.disabled = true;
        }
      });
    });
  }
})();
