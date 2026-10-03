/**
 * Bank of YR Maps — footer behaviour.
 *
 * Only the back-to-top button. Everything else in the footer is plain markup
 * and CSS, and works with JavaScript disabled.
 */
(function () {
  'use strict';

  var button = document.querySelector('[data-byrm-totop]');
  if (!button) {
    return;
  }

  button.addEventListener('click', function () {
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' });

    // As the page nears the top the header un-condenses and grows taller.
    // Scroll anchoring compensates for that height change mid-animation and
    // can leave the page a few dozen pixels short of the top, so nudge it once
    // the motion has settled. Guarded by a distance check, so a visitor who
    // scrolls away during the animation is never yanked back.
    window.setTimeout(function () {
      if (window.scrollY > 0 && window.scrollY < 300) {
        window.scrollTo({ top: 0, behavior: 'auto' });
      }
    }, 800);

    // Move focus to the top of the document too, so keyboard and screen reader
    // users actually land there rather than staying in the footer.
    var target = document.querySelector('.byrm-header') || document.body;

    target.setAttribute('tabindex', '-1');
    target.focus({ preventScroll: true });

    // Keep the tabindex until focus leaves, otherwise removing it immediately
    // blurs the element and drops focus back to the body.
    target.addEventListener('blur', function onBlur() {
      target.removeAttribute('tabindex');
      target.removeEventListener('blur', onBlur);
    });
  });
})();
