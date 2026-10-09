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

/* ====================================================================
 * The viewer
 *
 * The tiles link to the full-size image, so with no script a click still
 * opens the picture. Here that is intercepted and the dialog opens instead.
 *
 * The list of tiles is read from the DOM every time the viewer opens, not
 * cached at load: infinite scroll keeps adding tiles, and a list captured
 * once would stop at whatever was on the page when the script ran.
 * ================================================================= */
(function viewer() {
	var lb = document.getElementById('byrm-glb');

	if (!lb) {
		return;
	}

	var img = lb.querySelector('[data-glb-img]');
	var cap = lb.querySelector('[data-glb-cap]');
	var count = lb.querySelector('[data-glb-count]');
	var openLink = lb.querySelector('[data-glb-open]');
	var prev = lb.querySelector('[data-glb-prev]');
	var next = lb.querySelector('[data-glb-next]');
	var closers = lb.querySelectorAll('[data-glb-close]');

	if (!img || !prev || !next) {
		return;
	}

	var shots = [];
	var at = -1;
	var opener = null;

	function collect() {
		shots = Array.prototype.slice.call(document.querySelectorAll('[data-byrm-gal-shot]'));
	}

	function show(index) {
		if (index < 0) { index = shots.length - 1; }
		if (index >= shots.length) { index = 0; }

		var shot = shots[index];

		if (!shot) {
			return;
		}

		at = index;

		var title = shot.getAttribute('data-title') || '';
		var w = parseInt(shot.getAttribute('data-width'), 10);
		var h = parseInt(shot.getAttribute('data-height'), 10);

		// Set the dimensions before the source so the browser reserves the
		// right box and the dialog does not jump as each image arrives.
		if (isFinite(w) && w > 0) { img.setAttribute('width', String(w)); }
		if (isFinite(h) && h > 0) { img.setAttribute('height', String(h)); }

		img.setAttribute('src', shot.getAttribute('data-full') || '');
		img.setAttribute('alt', title);

		if (cap) { cap.textContent = title; }

		if (openLink) {
			openLink.setAttribute('href', shot.getAttribute('data-map') || '#');
		}

		if (count) {
			count.textContent = (index + 1) + ' / ' + shots.length;
		}

		var only = shots.length < 2;
		prev.hidden = only;
		next.hidden = only;
	}

	function open(index, trigger) {
		collect();

		if (!shots.length) {
			return;
		}

		opener = trigger || null;
		lb.hidden = false;
		document.documentElement.classList.add('byrm-glb-open');
		show(index);

		// Focus lands on Close: the first thing a keyboard user needs, and
		// it puts focus inside the dialog so the trap below has something
		// to hold on to.
		var first = lb.querySelector('[data-glb-close]:not([hidden])');

		if (first && typeof first.focus === 'function') {
			try { first.focus({ preventScroll: true }); } catch (e) { first.focus(); }
		}
	}

	function close() {
		lb.hidden = true;
		document.documentElement.classList.remove('byrm-glb-open');
		img.removeAttribute('src');
		at = -1;

		// Put focus back where it came from, not at the top of the page.
		if (opener && typeof opener.focus === 'function') {
			try { opener.focus({ preventScroll: true }); } catch (e) { opener.focus(); }
		}

		opener = null;
	}

	function step(delta) {
		if (at > -1) {
			show(at + delta);
		}
	}

	// Delegated, so tiles added by the scroll loader work without rebinding.
	document.addEventListener('click', function (event) {
		var shot = event.target.closest ? event.target.closest('[data-byrm-gal-shot]') : null;

		if (!shot) {
			return;
		}

		// Leave modified clicks alone — somebody asking for a new tab should
		// get the image in a new tab.
		if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
			return;
		}

		event.preventDefault();
		collect();
		open(shots.indexOf(shot), shot);
	});

	prev.addEventListener('click', function () { step(-1); });
	next.addEventListener('click', function () { step(1); });

	Array.prototype.forEach.call(closers, function (el) {
		el.addEventListener('click', close);
	});

	// Clicking away from the picture closes it. The stage fills the middle
	// column, so without this only the thin margins either side of it would
	// work — which reads as a viewer that ignores you.
	lb.addEventListener('click', function (event) {
		var t = event.target;

		if (t === lb || t.classList.contains('byrm-glb__backdrop') || t.classList.contains('byrm-glb__stage')) {
			close();
		}
	});

	document.addEventListener('keydown', function (event) {
		if (lb.hidden) {
			return;
		}

		if (event.key === 'Escape') { close(); return; }
		if (event.key === 'ArrowLeft') { event.preventDefault(); step(-1); return; }
		if (event.key === 'ArrowRight') { event.preventDefault(); step(1); return; }

		// Keep Tab inside the dialog while it is open.
		if (event.key === 'Tab') {
			var able = Array.prototype.slice.call(
				lb.querySelectorAll('button:not([hidden]), a[href]')
			).filter(function (el) { return el.offsetParent !== null; });

			if (!able.length) {
				return;
			}

			var firstEl = able[0];
			var lastEl = able[able.length - 1];

			if (event.shiftKey && document.activeElement === firstEl) {
				event.preventDefault();
				lastEl.focus();
			} else if (!event.shiftKey && document.activeElement === lastEl) {
				event.preventDefault();
				firstEl.focus();
			}
		}
	});
}());
