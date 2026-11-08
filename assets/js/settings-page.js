/**
 * Media Toolkit settings page.
 */
(function () {
	var statusTimer;

	/**
	 * Copy a template tag and show the "Copied!" status.
	 *
	 * @param {HTMLElement} tag The clicked tag.
	 */
	function copyTag(tag) {
		var text = tag.textContent.trim();
		var status = document.querySelector('.tags-heatbox .action-status');

		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(text).catch(function () {
				copyViaTextarea(text);
			});
		} else {
			copyViaTextarea(text);
		}

		if (!status) return;

		status.classList.add('is-shown');
		clearTimeout(statusTimer);
		statusTimer = setTimeout(function () {
			status.classList.remove('is-shown');
		}, 1500);
	}

	/**
	 * Fallback for sites without HTTPS.
	 *
	 * @param {string} text The text to copy.
	 */
	function copyViaTextarea(text) {
		var textarea = document.createElement('textarea');

		textarea.value = text;
		textarea.setAttribute('readonly', '');
		textarea.style.position = 'fixed';
		textarea.style.opacity = '0';
		document.body.appendChild(textarea);
		textarea.select();

		try {
			document.execCommand('copy');
		} catch (err) {}

		document.body.removeChild(textarea);
	}

	document.querySelectorAll('.tags-heatbox .tags-wrapper code').forEach(function (tag) {
		tag.addEventListener('click', function () {
			copyTag(tag);
		});

		tag.addEventListener('keydown', function (e) {
			if ('Enter' === e.key || ' ' === e.key) {
				e.preventDefault();
				copyTag(tag);
			}
		});
	});

	// Disable the max image size while "Limit image size" is off.
	var toggle = document.getElementById('mediatoolkit_settings--replace-original-image');
	var maxSize = document.getElementById('mediatoolkit_settings--image-max-dimension');

	if (toggle && maxSize) {
		var row = maxSize.closest('tr');

		var updateMaxSize = function () {
			maxSize.readOnly = !toggle.checked;

			if (row) row.classList.toggle('row-disabled', !toggle.checked);
		};

		updateMaxSize();
		toggle.addEventListener('change', updateMaxSize);
	}

	// Bulk optimize existing images, a few at a time.
	var bulkButton = document.querySelector('.mediatk-bulk-button');
	var resumeButton = document.querySelector('.mediatk-bulk-resume');
	var bulkStatus = document.querySelector('.mediatk-bulk-status');

	if (!bulkButton || !resumeButton || !bulkStatus || !window.mediaToolkit) return;

	var i18n = window.mediaToolkit.i18n;
	var storageKey = 'mediatoolkitBulkRun';
	var batchSize = 3;

	var bulkRunning = document.querySelector('.mediatk-bulk-running');
	var progressWrap = document.querySelector('.mediatk-bulk-progress');
	var progress = document.querySelector('.mediatk-progress');
	var progressBar = document.querySelector('.mediatk-progress-bar');
	var current = document.querySelector('.mediatk-bulk-current');
	var currentImage = document.querySelector('.mediatk-bulk-current-image');
	var currentTitle = document.querySelector('.mediatk-bulk-current-title');
	var currentDimensions = document.querySelector('.mediatk-bulk-current-dimensions');
	var currentBytes = document.querySelector('.mediatk-bulk-current-bytes');
	var saved = document.querySelector('.mediatk-bulk-saved');

	function format(text, values) {
		values.forEach(function (value, index) {
			text = text.replace('%' + (index + 1) + '$d', value).replace('%' + (index + 1) + '$s', value);
		});

		return text.replace('%s', values[0]);
	}

	function formatBytes(bytes) {
		var units = ['B', 'KB', 'MB', 'GB'];
		var unit = 0;

		bytes = Math.max(0, bytes);

		while (bytes >= 1024 && unit < units.length - 1) {
			bytes /= 1024;
			unit++;
		}

		return (unit ? bytes.toFixed(1) : bytes) + ' ' + units[unit];
	}

	function request(action, data) {
		var body = new FormData();

		body.append('action', action);
		body.append('nonce', window.mediaToolkit.nonce);

		(data || []).forEach(function (id) {
			body.append('ids[]', id);
		});

		return fetch(window.mediaToolkit.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (response) {
				return response.json();
			})
			.then(function (json) {
				if (!json || !json.success) throw new Error('Request failed');

				return json.data;
			});
	}

	// The progress is stored in the browser, so an interrupted run can be resumed.
	function loadRun() {
		try {
			var run = JSON.parse(window.localStorage.getItem(storageKey));

			return run && Array.isArray(run.ids) && run.index < run.ids.length ? run : null;
		} catch (err) {
			return null;
		}
	}

	function saveRun(run) {
		try {
			window.localStorage.setItem(storageKey, JSON.stringify(run));
		} catch (err) {}
	}

	function clearRun() {
		try {
			window.localStorage.removeItem(storageKey);
		} catch (err) {}
	}

	// Ask the browser to confirm leaving the page while optimizing.
	function preventLeaving(e) {
		e.preventDefault();
		e.returnValue = '';
	}

	function setRunning(isRunning) {
		bulkButton.disabled = isRunning;
		resumeButton.disabled = isRunning;

		if (bulkRunning) bulkRunning.hidden = !isRunning;

		if (isRunning) {
			window.addEventListener('beforeunload', preventLeaving);
		} else {
			window.removeEventListener('beforeunload', preventLeaving);
		}
	}

	function showProgress(run) {
		var percent = Math.round((run.index / run.ids.length) * 100);

		progressWrap.hidden = false;
		progressBar.style.width = percent + '%';
		progress.setAttribute('aria-valuenow', percent);
		saved.textContent = format(i18n.saved, [formatBytes(run.saved)]);
	}

	function showImage(image) {
		var dimensions = image.after.width + ' × ' + image.after.height;

		if (image.before.width !== image.after.width || image.before.height !== image.after.height) {
			dimensions = image.before.width + ' × ' + image.before.height + ' → ' + dimensions;
		}

		current.hidden = false;

		// Restart the "squash" animation for each image.
		currentImage.style.animation = 'none';
		void currentImage.offsetWidth;
		currentImage.style.animation = '';
		currentImage.src = image.thumbnail;
		currentTitle.textContent = image.title;
		currentDimensions.textContent = dimensions;
		currentBytes.textContent = formatBytes(image.before.bytes) + ' → ' + formatBytes(image.after.bytes);
	}

	function start(run) {
		resumeButton.hidden = true;
		setRunning(true);

		function next() {
			showProgress(run);

			if (run.index >= run.ids.length) {
				var totals = run.totals;

				bulkStatus.textContent = format(i18n.done, [totals.optimized, totals.skipped, totals.failed]);
				saved.textContent = format(i18n.total, [formatBytes(run.saved)]);
				clearRun();
				return;
			}

			bulkStatus.textContent = format(i18n.progress, [run.index, run.ids.length]);

			var batch = run.ids.slice(run.index, run.index + batchSize);

			return request('mediatoolkit_bulk_optimize', batch).then(function (result) {
				run.totals.optimized += result.optimized;
				run.totals.skipped += result.skipped;
				run.totals.failed += result.failed;

				result.images.forEach(function (image) {
					run.saved += image.before.bytes - image.after.bytes;
				});

				if (result.images.length) showImage(result.images[result.images.length - 1]);

				run.index += batch.length;
				saveRun(run);

				return next();
			});
		}

		return Promise.resolve(next())
			.catch(function () {
				bulkStatus.textContent = i18n.error;
				resumeButton.hidden = false;
			})
			.finally(function () {
				setRunning(false);
			});
	}

	bulkButton.addEventListener('click', function () {
		if (!window.confirm(i18n.confirm)) return;

		clearRun();
		resumeButton.hidden = true;
		current.hidden = true;
		setRunning(true);
		bulkStatus.textContent = i18n.starting;

		request('mediatoolkit_bulk_ids')
			.then(function (ids) {
				if (!ids.length) {
					bulkStatus.textContent = i18n.none;
					setRunning(false);
					return;
				}

				var run = { ids: ids, index: 0, saved: 0, totals: { optimized: 0, skipped: 0, failed: 0 } };

				saveRun(run);
				return start(run);
			})
			.catch(function () {
				bulkStatus.textContent = i18n.failed;
				setRunning(false);
			});
	});

	resumeButton.addEventListener('click', function () {
		var run = loadRun();

		if (run) start(run);
	});

	// Offer to resume an interrupted run.
	var interrupted = loadRun();

	if (interrupted) {
		resumeButton.hidden = false;
		bulkStatus.textContent = format(i18n.stopped, [interrupted.index, interrupted.ids.length]);
		showProgress(interrupted);
	}
})();
