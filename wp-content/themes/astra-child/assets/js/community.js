/**
 * Community page — board enhancements.
 *
 * Everything here is an enhancement and nothing more: with JavaScript off, the
 * board still reads, the form still posts, and WordPress's own threading links
 * still work. The one thing that hides itself without JS is the row of starter
 * prompts, because prefilling the box is all they do.
 */
(function () {
	'use strict';

	var form = document.querySelector('.byrm-cform');
	var box = form ? form.querySelector('textarea') : null;

	/* ------------------------------------------------------------ autogrow */

	/**
	 * Grow the message box to fit what has been typed, up to a sensible cap, so
	 * a long post is not written through a five-line window.
	 */
	function fit() {
		if (!box) {
			return;
		}

		box.style.height = 'auto';

		var wanted = box.scrollHeight;
		var cap = Math.round(window.innerHeight * 0.6);

		box.style.height = Math.min(wanted, cap) + 'px';
		box.style.overflowY = wanted > cap ? 'auto' : 'hidden';
	}

	if (box) {
		box.addEventListener('input', fit);
		fit();
	}

	/* --------------------------------------------------------- draft saving */

	/**
	 * Keep an unsent message in this browser.
	 *
	 * People type a long reply, click a map to check its name, and lose the lot.
	 * The draft is stored locally — it never leaves the browser, it is dropped
	 * the moment the message is posted, and it is ignored once a week old.
	 */
	var KEY = 'byrm-draft:' + window.location.pathname;
	var WEEK = 7 * 24 * 60 * 60 * 1000;

	function store() {
		if (!box) {
			return;
		}

		try {
			if (box.value.trim() === '') {
				window.localStorage.removeItem(KEY);

				return;
			}

			window.localStorage.setItem(
				KEY,
				JSON.stringify({ text: box.value, at: Date.now() })
			);
		} catch (e) {
			/* Private browsing, or storage full. Not worth bothering anyone about. */
		}
	}

	function restore() {
		if (!box || box.value.trim() !== '') {
			return;
		}

		try {
			var raw = window.localStorage.getItem(KEY);

			if (!raw) {
				return;
			}

			var saved = JSON.parse(raw);

			if (!saved || !saved.text || Date.now() - (saved.at || 0) > WEEK) {
				window.localStorage.removeItem(KEY);

				return;
			}

			box.value = saved.text;
			fit();
		} catch (e) {
			/* A malformed entry is simply discarded. */
		}
	}

	/**
	 * Has a message just been posted?
	 *
	 * WordPress sends you back to #comment-123 when it accepts one, and adds
	 * ?unapproved=&moderation-hash= when it holds one for review. Either way the
	 * message is safely on the server and the draft can go.
	 *
	 * Deliberately not cleared on submit: the server can still reject a post
	 * (spam guard, missing name, an expired form), and throwing away what
	 * somebody wrote at the exact moment they are told to try again would be
	 * the worst possible time to do it.
	 */
	function posted() {
		return /^#comment-\d+$/.test(window.location.hash) ||
			window.location.search.indexOf('unapproved=') > -1;
	}

	if (box) {
		if (posted()) {
			try {
				window.localStorage.removeItem(KEY);
			} catch (e) {
				/* Nothing stored. */
			}
		}

		restore();

		var pending;

		box.addEventListener('input', function () {
			window.clearTimeout(pending);
			pending = window.setTimeout(store, 400);
		});

		window.addEventListener('pagehide', store);
	}

	/* ------------------------------------------------------ starter prompts */

	var starters = document.querySelector('[data-byrm-starters]');

	if (starters && box) {
		starters.classList.add('is-ready');

		starters.addEventListener('click', function (event) {
			var button = event.target.closest('[data-byrm-prompt]');

			if (!button) {
				return;
			}

			var prompt = button.getAttribute('data-byrm-prompt') || '';

			// Only ever added to an empty box: nobody wants their half-written
			// message overwritten by a button they brushed past.
			if (box.value.trim() === '') {
				box.value = prompt;
			} else if (box.value.indexOf(prompt) !== 0) {
				box.value = prompt + box.value;
			}

			fit();
			box.focus();
			box.setSelectionRange(box.value.length, box.value.length);
		});
	}

	/* --------------------------------------------------------- submit guard */

	if (form) {
		form.addEventListener('submit', function () {
			var button = form.querySelector('[type="submit"]');

			if (!button || button.disabled) {
				return;
			}

			var label = button.getAttribute('data-byrm-label') || button.value || button.textContent;

			button.setAttribute('data-byrm-label', label);
			button.disabled = true;

			if ('value' in button && button.tagName === 'INPUT') {
				button.value = 'Posting…';
			} else {
				button.textContent = 'Posting…';
			}

			// If the browser restores this page from its back/forward cache the
			// button would still be dead, so put it back.
			window.addEventListener('pageshow', function () {
				button.disabled = false;

				if (button.tagName === 'INPUT') {
					button.value = label;
				} else {
					button.textContent = label;
				}
			});
		});
	}
})();
