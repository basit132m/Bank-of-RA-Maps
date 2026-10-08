/**
 * The countdown on the download wait page.
 *
 * Deliberately small and defensive. If anything here throws, or the script
 * never loads at all, the page must still hand over the file — so the ready
 * block is revealed on any failure rather than left hidden. A timer is a
 * courtesy, not a lock.
 *
 * Time is read from the clock rather than counted in ticks: a background tab
 * throttles setInterval to once a second at best and sometimes far less, so
 * counting ticks would leave somebody staring at "4" after switching back.
 */
(function () {
	'use strict';

	var panels = document.querySelectorAll('[data-byrm-countdown]');

	if (!panels.length) {
		return;
	}

	Array.prototype.forEach.call(panels, function (panel) {
		var counting = panel.querySelector('[data-byrm-counting]');
		var ready    = panel.querySelector('[data-byrm-ready]');
		var number   = panel.querySelector('[data-byrm-number]');
		var status   = panel.querySelector('[data-byrm-status]');
		var ring     = panel.querySelector('[data-byrm-ring]');
		var button   = panel.querySelector('[data-byrm-go]');

		if (!counting || !ready || !button) {
			return;
		}

		var total = parseInt(panel.getAttribute('data-seconds'), 10);

		if (!isFinite(total) || total < 1) {
			finish();
			return;
		}

		// The ring is drawn by offsetting a dashed stroke the length of the
		// circle, so the dash has to match the circumference exactly.
		var circumference = 0;

		if (ring && typeof ring.getAttribute === 'function') {
			var r = parseFloat(ring.getAttribute('r'));

			if (isFinite(r) && r > 0) {
				circumference = 2 * Math.PI * r;
				ring.style.strokeDasharray = String(circumference);
				ring.style.strokeDashoffset = '0';
			}
		}

		var endsAt = Date.now() + total * 1000;
		var shown  = total;
		var timer  = null;

		// Wired before the first tick, so a stall cannot leave it unreachable.
		panel.setAttribute('data-byrm-state', 'counting');

		function remaining() {
			return Math.max(0, (endsAt - Date.now()) / 1000);
		}

		function finish() {
			if (timer) {
				window.clearInterval(timer);
				timer = null;
			}

			panel.setAttribute('data-byrm-state', 'ready');

			if (counting) {
				counting.hidden = true;
			}

			ready.hidden = false;

			// Announce it, then put the keyboard where the visitor needs it.
			// preventScroll keeps the page from jumping on browsers that honour it.
			try {
				button.focus({ preventScroll: true });
			} catch (e) {
				try {
					button.focus();
				} catch (e2) {
					/* Focus is a nicety; never let it stop the reveal. */
				}
			}
		}

		function tick() {
			var left = remaining();

			if (ring && circumference) {
				ring.style.strokeDashoffset = String(circumference * (1 - left / total));
			}

			var whole = Math.ceil(left);

			if (whole !== shown) {
				shown = whole;

				if (number) {
					number.textContent = String(whole);
				}

				// Only the last few seconds are announced. A screen reader
				// reading out all ten would be unbearable.
				if (status && whole <= 3) {
					status.textContent = whole > 0
						? whole + (whole === 1 ? ' second left.' : ' seconds left.')
						: 'Your download is ready.';
				}
			}

			if (left <= 0) {
				finish();
			}
		}

		try {
			tick();
			timer = window.setInterval(tick, 100);
		} catch (err) {
			finish();
		}

		// A tab brought back to the front after the timer should already be
		// done, not still counting from where it was throttled.
		document.addEventListener('visibilitychange', function () {
			if (!document.hidden && timer) {
				tick();
			}
		});

		// Last resort: if the interval is killed or the clock misbehaves, this
		// still reveals the button.
		window.setTimeout(finish, (total + 2) * 1000);
	});
}());
