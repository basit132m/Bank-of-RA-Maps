/**
 * Infinite scroll for the image wall.
 *
 * The "Load more" control in the markup is a real link to the next page. This
 * script upgrades it: clicking fetches the next tiles and appends them, and an
 * IntersectionObserver clicks it for you as it comes into view.
 *
 * Everything here degrades to that link. If fetch fails, if the REST route is
 * blocked, if the observer never fires — the link is put back the way it was
 * and ordinary navigation still gets the visitor to the next page. The wall
 * must never become a dead end.
 */
(function () {
	'use strict';

	var grid = document.querySelector('[data-byrm-gallery]');

	if (!grid) {
		return;
	}

	var more = document.querySelector('[data-byrm-gal-more]');
	var foot = more ? more.parentNode : null;
	var status = document.querySelector('[data-byrm-gal-status]');

	if (!more || !foot || typeof window.fetch !== 'function') {
		return;
	}

	var rest = grid.getAttribute('data-rest');
	var page = parseInt(grid.getAttribute('data-page'), 10);

	if (!rest || !isFinite(page) || page < 1) {
		return;
	}

	var busy = false;
	var done = false;
	var observer = null;

	function say(text) {
		if (status) {
			status.textContent = text;
		}
	}

	/** Put the link back to being an ordinary link and stop interfering. */
	function standDown(message) {
		busy = false;

		if (observer) {
			observer.disconnect();
			observer = null;
		}

		more.removeEventListener('click', onClick);
		more.removeAttribute('aria-busy');
		more.textContent = more.getAttribute('data-label') || 'Load more maps';

		if (message) {
			say(message);
		}
	}

	function finish() {
		done = true;

		if (observer) {
			observer.disconnect();
			observer = null;
		}

		var end = document.createElement('p');
		end.className = 'byrm-gal__end';
		end.textContent = 'That is every map.';

		if (more.parentNode) {
			more.parentNode.replaceChild(end, more);
		}
	}

	function load(fromClick) {
		if (busy || done) {
			return;
		}

		busy = true;
		more.setAttribute('aria-busy', 'true');
		more.textContent = 'Loading…';
		say('Loading more maps.');

		var next = page + 1;
		var url = rest + (rest.indexOf('?') === -1 ? '?' : '&') + 'page=' + next;

		window.fetch(url, { credentials: 'same-origin' })
			.then(function (response) {
				if (!response.ok) {
					throw new Error('HTTP ' + response.status);
				}

				return response.json();
			})
			.then(function (data) {
				if (!data || typeof data.html !== 'string') {
					throw new Error('Unexpected response');
				}

				var first = null;

				if (data.html) {
					// A template element parses the markup without running
					// anything or touching the live document until it is ready.
					var holder = document.createElement('template');
					holder.innerHTML = data.html;

					first = holder.content.firstElementChild;
					grid.appendChild(holder.content);
				}

				page = next;
				grid.setAttribute('data-page', String(page));

				var added = grid.querySelectorAll('.byrm-gal__tile').length;
				say(added + ' maps shown.');

				busy = false;
				more.removeAttribute('aria-busy');
				more.textContent = more.getAttribute('data-label') || 'Load more maps';
				more.setAttribute('href', more.getAttribute('href').replace(/gal=\d+/, 'gal=' + (page + 1)));

				if (!data.has_more) {
					finish();
					say('That is every map.');
					return;
				}

				// IntersectionObserver reports transitions, not states. Appending
				// tiles below the sentinel often leaves it exactly where it was —
				// still inside the root margin — so no new entry ever arrives and
				// the wall stalls after one load. Re-observing forces a fresh
				// evaluation, which fires again if it is still in view.
				if (observer) {
					observer.unobserve(more);
					observer.observe(more);
				}

				// Only steal focus when the visitor asked for more. Doing it on
				// a scroll-triggered load would yank them out of the wall.
				if (fromClick && first) {
					var link = first.querySelector('a');

					if (link) {
						link.setAttribute('tabindex', '-1');
						try {
							link.focus({ preventScroll: true });
						} catch (e) {
							link.focus();
						}
					}
				}
			})
			.catch(function () {
				standDown('Could not load more maps. Use the link to carry on.');
			});
	}

	function onClick(event) {
		// Let modified clicks (new tab, new window) behave normally.
		if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
			return;
		}

		event.preventDefault();
		load(true);
	}

	more.setAttribute('data-label', more.textContent.trim());
	more.addEventListener('click', onClick);

	// Load ahead of the viewport so the wall feels continuous rather than
	// stopping at the bottom and then filling in.
	if ('IntersectionObserver' in window) {
		observer = new IntersectionObserver(
			function (entries) {
				for (var i = 0; i < entries.length; i++) {
					if (entries[i].isIntersecting) {
						load(false);
					}
				}
			},
			{ rootMargin: '600px 0px' }
		);

		observer.observe(more);
	}
}());
