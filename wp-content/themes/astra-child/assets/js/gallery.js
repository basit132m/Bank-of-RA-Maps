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

/* ============================================================================
 * The viewer
 *
 * The tiles link to the full-size image, so with no script a click still
 * opens the picture. Here that is intercepted and the dialog opens instead.
 *
 * Two things this is careful about, both of which turn the viewer back into
 * plain navigation — a click on a tile loading the bare image file:
 *
 *   The dialog may not be in the page at all. It is printed by the gallery
 *   template, and a theme whose template has not been updated has tiles that
 *   point at images and nothing to show them in. So the markup is built here
 *   when it is missing, and the template's copy is used when it is there.
 *
 *   position: fixed resolves against the nearest ancestor with a transform,
 *   a filter or contain — not the viewport. Inside the page content, one such
 *   ancestor anywhere above it (Astra, a page builder, an optimisation plugin)
 *   drops the overlay into the flow at the foot of the page instead of over
 *   the screen. Moving it to <body> removes every candidate but body itself.
 *
 * The list of tiles is read from the DOM every time the viewer opens, not
 * cached at load: infinite scroll keeps adding tiles, and a list captured
 * once would stop at whatever was on the page when the script ran.
 * ================================================================= */
(function viewer() {
	'use strict';

	// Only the labels: everything structural is in the classes and the
	// data- hooks. The template's copy is translated; this fallback is not,
	// which is the price of it existing at all.
	var MARKUP = [
		'<div class="byrm-glb__backdrop"></div>',
		'<div class="byrm-glb__bar">',
		'<p class="byrm-glb__count" data-glb-count></p>',
		'<a class="byrm-glb__open" data-glb-open href="#" target="_blank" rel="noopener">Open the map page</a>',
		'<button type="button" class="byrm-glb__btn" data-glb-close>',
		'<span class="byrm-gal__sr">Close</span>',
		'<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18"/></svg>',
		'</button>',
		'</div>',
		'<button type="button" class="byrm-glb__nav byrm-glb__nav--prev" data-glb-prev>',
		'<span class="byrm-gal__sr">Previous map</span>',
		'<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7"/></svg>',
		'</button>',
		'<figure class="byrm-glb__stage">',
		'<img class="byrm-glb__img" data-glb-img alt="">',
		'<figcaption class="byrm-glb__cap" data-glb-cap></figcaption>',
		'</figure>',
		'<button type="button" class="byrm-glb__nav byrm-glb__nav--next" data-glb-next>',
		'<span class="byrm-gal__sr">Next map</span>',
		'<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 5l7 7-7 7"/></svg>',
		'</button>'
	].join('');

	// The site icon, spun while a full-size picture is on its way. Overridable
	// without touching this file: put a URL in data-spinner on the grid.
	var SPINNER = 'https://www.bankofyrmaps.com/wp-content/uploads/2026/09/cropped-Ban-of-YR-Maps-Logo.webp';

	/**
	 * The viewer's own stylesheet, used only when gallery.css did not bring it.
	 *
	 * A minifier that choked on the file, a combine-and-cache plugin holding a
	 * copy from before the viewer existed, a stylesheet that never got saved —
	 * each leaves the dialog in the page with nothing positioning it, so it
	 * lands in the flow under the footer at the full size of the image. That is
	 * not a viewer, so the script carries a fallback rather than trusting a
	 * second file to arrive intact.
	 *
	 * Deliberately the same declarations as the .byrm-glb block in gallery.css.
	 * When that block is present this never runs, so the two cannot disagree on
	 * screen; if the design changes, change both.
	 */
	var CSS = [
		'.byrm-glb-open,.byrm-glb-open body{overflow:hidden}',
		'.byrm-glb{position:fixed;inset:0;z-index:9999;display:grid;',
		'grid-template-columns:auto minmax(0,1fr) auto;grid-template-rows:auto minmax(0,1fr);',
		'align-items:center;font-family:var(--byrm-font,system-ui,sans-serif)}',
		'.byrm-glb[hidden]{display:none}',
		'.byrm-glb *,.byrm-glb *::before,.byrm-glb *::after{box-sizing:border-box}',
		'.byrm-glb__backdrop{position:absolute;inset:0;background:rgba(4,6,9,.93)}',
		'.byrm-glb__bar{position:relative;z-index:1;grid-column:1/-1;display:flex;align-items:center;',
		'justify-content:flex-end;gap:16px;padding:14px 18px}',
		'.byrm-glb__count{margin:0 auto 0 0;font-size:12.5px;letter-spacing:.08em;',
		'color:var(--byrm-chrome-lo,#8b949e);font-variant-numeric:tabular-nums}',
		'.byrm-glb__open{font-size:13px;font-weight:700;letter-spacing:.04em;',
		'color:var(--byrm-chrome-hi,#e6edf3);text-decoration:underline;text-underline-offset:3px}',
		'.byrm-glb__open:hover,.byrm-glb__open:focus-visible{color:var(--byrm-red-bright,#ff4747)}',
		'.byrm-glb__btn{display:grid;place-items:center;width:40px;height:40px;padding:0;',
		'color:var(--byrm-chrome-hi,#e6edf3);background:transparent;',
		'border:1px solid var(--byrm-line,#30363d);cursor:pointer}',
		'.byrm-glb__btn svg{width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:2}',
		'.byrm-glb__btn:hover,.byrm-glb__btn:focus-visible{color:#fff;background:var(--byrm-red,#e01f1f);',
		'border-color:var(--byrm-red-bright,#ff4747)}',
		'.byrm-glb__stage{position:relative;z-index:1;grid-column:2;grid-row:2;display:flex;',
		'flex-direction:column;align-items:center;justify-content:center;gap:12px;margin:0;',
		'padding:0 8px 24px;min-height:0}',
		'.byrm-glb__img{max-width:100%;max-height:calc(100vh - 160px);width:auto;height:auto;',
		'object-fit:contain;border:1px solid var(--byrm-line,#30363d);',
		'background:var(--byrm-steel-800,#0d1117)}',
		'.byrm-glb__cap{margin:0;font-size:14px;font-weight:700;letter-spacing:.02em;',
		'text-align:center;color:var(--byrm-chrome-hi,#e6edf3)}',
		'.byrm-glb__nav{position:relative;z-index:1;grid-row:2;display:grid;place-items:center;',
		'width:54px;height:54px;margin-inline:8px;padding:0;color:var(--byrm-chrome-hi,#e6edf3);',
		'background:rgba(13,17,23,.8);border:1px solid var(--byrm-line,#30363d);cursor:pointer}',
		'.byrm-glb__nav svg{width:24px;height:24px;fill:none;stroke:currentColor;stroke-width:2}',
		'.byrm-glb__nav--prev{grid-column:1}.byrm-glb__nav--next{grid-column:3}',
		'.byrm-glb__nav[hidden]{display:none}',
		'.byrm-glb__nav:hover,.byrm-glb__nav:focus-visible{color:#fff;background:var(--byrm-red,#e01f1f);',
		'border-color:var(--byrm-red-bright,#ff4747)}',
		'.byrm-glb__btn:focus-visible,.byrm-glb__nav:focus-visible,.byrm-glb__open:focus-visible',
		'{outline:2px solid var(--byrm-ember,#f0a020);outline-offset:2px}',
		// The visually-hidden label, in case gallery.css is missing entirely.
		'.byrm-glb .byrm-gal__sr{position:absolute;width:1px;height:1px;margin:-1px;padding:0;',
		'overflow:hidden;clip:rect(0 0 0 0);clip-path:inset(50%);white-space:nowrap;border:0}',
		'.byrm-glb__wait{position:absolute;inset:0;z-index:2;display:grid;place-items:center;',
		'opacity:0;pointer-events:none;transition:opacity .2s linear}',
		'.byrm-glb[data-wait] .byrm-glb__wait{opacity:1}',
		'.byrm-glb[data-loading] .byrm-glb__img{visibility:hidden}',
		'.byrm-glb__spin{width:64px;height:64px;object-fit:contain;',
		'animation:byrm-glb-spin 1.1s linear infinite}',
		'.byrm-glb__wait--ring .byrm-glb__spin{display:none}',
		'.byrm-glb__wait--ring::after{content:"";width:48px;height:48px;border-radius:50%;',
		'border:3px solid var(--byrm-line,#30363d);border-top-color:var(--byrm-red-bright,#ff4747);',
		'animation:byrm-glb-spin 1s linear infinite}',
		'@keyframes byrm-glb-spin{to{transform:rotate(360deg)}}',
		'@keyframes byrm-glb-pulse{0%,100%{opacity:.35}50%{opacity:1}}',
		'@media (prefers-reduced-motion:reduce){',
		'.byrm-glb__spin,.byrm-glb__wait--ring::after',
		'{animation:byrm-glb-pulse 1.4s ease-in-out infinite}}',
		'@media (max-width:600px){',
		'.byrm-glb{grid-template-columns:minmax(0,1fr)}',
		'.byrm-glb__spin{width:52px;height:52px}',
		'.byrm-glb__stage{grid-column:1;padding-inline:12px}',
		'.byrm-glb__nav{position:absolute;top:50%;width:44px;height:44px;margin:0;',
		'transform:translateY(-50%)}',
		'.byrm-glb__nav--prev{left:8px}.byrm-glb__nav--next{right:8px}',
		'.byrm-glb__img{max-height:calc(100vh - 150px)}',
		'.byrm-glb__open{display:none}}'
	].join('');

	var lb = null;
	var img, cap, count, openLink, prev, next;
	var shots = [];
	var at = -1;
	var opener = null;
	var styled = false;
	var waitTimer = null;

	function collect() {
		shots = Array.prototype.slice.call(document.querySelectorAll('[data-byrm-gal-shot]'));
	}

	/**
	 * The dialog, built if the template did not print one, reparented to
	 * <body> either way. Runs once, on the first click.
	 *
	 * @return {boolean} Whether there is a usable dialog.
	 */
	function build() {
		if (lb) {
			return true;
		}

		lb = document.getElementById('byrm-glb');

		if (!lb) {
			lb = document.createElement('div');
			lb.id = 'byrm-glb';
			lb.className = 'byrm-glb';
			lb.setAttribute('role', 'dialog');
			lb.setAttribute('aria-modal', 'true');
			lb.setAttribute('aria-label', 'Map image viewer');
			lb.hidden = true;
			lb.innerHTML = MARKUP;
		}

		if (lb.parentNode !== document.body) {
			document.body.appendChild(lb);
		}

		img = lb.querySelector('[data-glb-img]');
		cap = lb.querySelector('[data-glb-cap]');
		count = lb.querySelector('[data-glb-count]');
		openLink = lb.querySelector('[data-glb-open]');
		prev = lb.querySelector('[data-glb-prev]');
		next = lb.querySelector('[data-glb-next]');

		if (!img || !prev || !next) {
			lb = null;
			return false;
		}

		// The spinner is the script's own: the template never prints one, and
		// a viewer that only exists with JavaScript has no use for a no-JS
		// fallback here.
		if (!lb.querySelector('.byrm-glb__wait')) {
			var wait = document.createElement('div');
			wait.className = 'byrm-glb__wait';
			wait.setAttribute('aria-hidden', 'true');

			var spin = document.createElement('img');
			spin.className = 'byrm-glb__spin';
			spin.alt = '';

			// A renamed or moved logo would otherwise leave a blank middle
			// with nothing to say the picture is coming.
			spin.addEventListener('error', function () {
				wait.className = 'byrm-glb__wait byrm-glb__wait--ring';
			});

			var grid = document.querySelector('[data-byrm-gallery]');
			spin.src = (grid && grid.getAttribute('data-spinner')) || SPINNER;

			wait.appendChild(spin);
			lb.appendChild(wait);
		}

		// One pair for the life of the page. Changing src aborts the request
		// in flight, so only the current picture ever reports back.
		img.addEventListener('load', function () { waiting(false); });
		img.addEventListener('error', function () { waiting(false); });

		prev.addEventListener('click', function () { step(-1); });
		next.addEventListener('click', function () { step(1); });

		Array.prototype.forEach.call(lb.querySelectorAll('[data-glb-close]'), function (el) {
			el.addEventListener('click', close);
		});

		// Clicking away from the picture closes it. The stage fills the middle
		// column, so without this only the thin margins either side of it
		// would work — which reads as a viewer that ignores you.
		lb.addEventListener('click', function (event) {
			var t = event.target;

			if (t === lb || t.classList.contains('byrm-glb__backdrop') || t.classList.contains('byrm-glb__stage')) {
				close();
			}
		});

		return true;
	}

	/**
	 * Whether the stylesheet got here. Checked once, with the dialog visible
	 * so the values are the real used ones, and the fallback injected if not.
	 *
	 * position is the right thing to test for both: it is what the overlay and
	 * the spinner layer each rest on, and nothing else on the page sets it for
	 * these elements. The spinner is checked separately from the dialog
	 * because a stylesheet can be one update behind rather than absent — the
	 * viewer block present, the loading block not — and that renders the logo
	 * full size and motionless in the middle of the picture.
	 */
	function dressed() {
		if (styled) {
			return;
		}

		styled = true;

		var wait = lb.querySelector('.byrm-glb__wait');

		if (window.getComputedStyle(lb).position === 'fixed'
			&& wait && window.getComputedStyle(wait).position === 'absolute') {
			return;
		}

		var tag = document.createElement('style');
		tag.id = 'byrm-glb-css';
		tag.appendChild(document.createTextNode(CSS));
		document.head.appendChild(tag);
	}

	/**
	 * Spinner on or off, and the same thing said to assistive technology.
	 *
	 * Two states, not one. The picture is hidden the moment a new one is asked
	 * for, because leaving the last map up while the next downloads reads as
	 * the arrow key having done nothing. The spinner is held back, because one
	 * already in the cache arrives in a few milliseconds and a spinner that
	 * flashes for a frame on every step is worse than no spinner at all.
	 *
	 * The hold is a timer rather than a CSS transition-delay: the dialog goes
	 * from display:none to visible in the same style recalculation that sets
	 * this, and a transition does not run on an element's first frame after a
	 * display change — the delay would be skipped and the spinner would appear
	 * at once every time.
	 */
	function waiting(on) {
		if (waitTimer) {
			clearTimeout(waitTimer);
			waitTimer = null;
		}

		if (!on) {
			lb.removeAttribute('data-loading');
			lb.removeAttribute('data-wait');
			lb.removeAttribute('aria-busy');

			// Off the placeholder sizing. Left on, an explicit width plus
			// max-height would letterbox a tall picture instead of shrinking
			// it.
			img.style.aspectRatio = '';
			img.style.width = '';
			return;
		}

		lb.setAttribute('data-loading', '');
		lb.setAttribute('aria-busy', 'true');

		waitTimer = setTimeout(function () {
			waitTimer = null;
			lb.setAttribute('data-wait', '');
		}, 200);
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

		if (isFinite(w) && w > 0) { img.setAttribute('width', String(w)); }
		if (isFinite(h) && h > 0) { img.setAttribute('height', String(h)); }

		// Hold the box the picture is going to occupy. The width and height
		// attributes do not do this on their own: the stylesheet's width:auto
		// overrides them as a sizing hint, so an image still downloading
		// measures nothing and the caption sits in the middle of the screen
		// until it lands, then drops. These two reproduce exactly what the
		// loaded image resolves to, and waiting() takes them off again so the
		// real intrinsic size governs once it is here.
		if (isFinite(w) && w > 0 && isFinite(h) && h > 0) {
			img.style.aspectRatio = w + ' / ' + h;
			img.style.width = 'min(100%, ' + w + 'px)';
		}

		waiting(true);
		img.setAttribute('src', shot.getAttribute('data-full') || '');
		img.setAttribute('alt', title);

		// Already decoded: depending on the browser, no load event is coming
		// for it at all. The held-back spinner covers this too, but clearing
		// here means the picture is never hidden for even a frame.
		if (img.complete && img.naturalWidth > 0) {
			waiting(false);
		}

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
		dressed();
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
		waiting(false);
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

		// Only now, so a page with no gallery on it gains nothing.
		if (!build()) {
			return;
		}

		event.preventDefault();
		collect();
		open(shots.indexOf(shot), shot);
	});

	document.addEventListener('keydown', function (event) {
		if (!lb || lb.hidden) {
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
