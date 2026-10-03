/**
 * Map tweaker — form niceties.
 *
 * All of this is an enhancement. With JavaScript off the file input, the
 * checkboxes and the submit button are ordinary form controls and the tool
 * works exactly the same; you just lose the drag target and the running count.
 */
(function () {
	'use strict';

	var form = document.querySelector('[data-byrm-tweak-form]');

	if (!form) {
		return;
	}

	var drop = form.querySelector('[data-byrm-drop]');
	var input = form.querySelector('input[type="file"]');
	var label = form.querySelector('[data-byrm-drop-label]');
	var count = form.querySelector('[data-byrm-count]');
	var submit = form.querySelector('[type="submit"]');
	var opts = form.querySelectorAll('[data-byrm-opt]');

	var idle = label ? label.textContent.trim() : '';

	/* ------------------------------------------------------- chosen file --- */

	function showFile() {
		if (!input || !label) {
			return;
		}

		var file = input.files && input.files[0];

		if (!file) {
			label.textContent = idle;
			drop.classList.remove('has-file');

			return;
		}

		var kb = Math.max(1, Math.round(file.size / 1024));

		label.textContent = file.name + ' — ' + kb.toLocaleString() + ' KB';
		drop.classList.add('has-file');
	}

	if (input) {
		input.addEventListener('change', showFile);
		showFile();
	}

	/* ------------------------------------------------------------- drag --- */

	if (drop && input) {
		['dragenter', 'dragover'].forEach(function (name) {
			drop.addEventListener(name, function (event) {
				event.preventDefault();
				drop.classList.add('is-dragging');
			});
		});

		['dragleave', 'drop'].forEach(function (name) {
			drop.addEventListener(name, function (event) {
				event.preventDefault();
				drop.classList.remove('is-dragging');
			});
		});

		drop.addEventListener('drop', function (event) {
			var files = event.dataTransfer && event.dataTransfer.files;

			if (!files || !files.length) {
				return;
			}

			// DataTransfer assignment is what makes a dropped file reach the
			// form on submit. Where it is unsupported, the click-to-choose path
			// is untouched.
			try {
				input.files = files;
			} catch (e) {
				return;
			}

			showFile();
		});
	}

	/* ----------------------------------------------------------- counter --- */

	function tally() {
		var chosen = 0;

		Array.prototype.forEach.call(opts, function (box) {
			if (box.checked) {
				chosen++;
			}
		});

		if (count) {
			count.textContent = chosen === 0
				? ''
				: chosen + (chosen === 1 ? ' change selected' : ' changes selected');
		}
	}

	Array.prototype.forEach.call(opts, function (box) {
		box.addEventListener('change', tally);
	});

	tally();

	/* ------------------------------------------------------ submit guard --- */

	form.addEventListener('submit', function (event) {
		var chosen = 0;

		Array.prototype.forEach.call(opts, function (box) {
			if (box.checked) {
				chosen++;
			}
		});

		// Caught here as well as on the server, so nobody waits for an upload
		// only to be told they forgot to tick anything.
		if (chosen === 0) {
			event.preventDefault();

			if (count) {
				count.textContent = 'Pick at least one change first';
			}

			if (opts.length) {
				opts[0].focus();
			}

			return;
		}

		if (!submit) {
			return;
		}

		submit.disabled = true;
		submit.textContent = 'Working…';

		// The response is a file download, so this page never navigates. Put the
		// button back so a second map can be done without reloading.
		window.setTimeout(function () {
			submit.disabled = false;
			submit.textContent = 'Edit my map';
		}, 4000);
	});
})();
