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

	// Bulk optimize existing images, one at a time so the site stays responsive.
	var box = document.querySelector('.mediatk-bulk-metabox');

	if (!box || !window.mediaToolkit) return;

	var i18n = window.mediaToolkit.i18n;
	var storageKey = 'mediatoolkitBulkRun';
	var minDelay = 400;

	function el(selector) {
		return box.querySelector(selector);
	}

	var bulkButton = el('.mediatk-bulk-button');
	var resumeButton = el('.mediatk-bulk-resume');
	var bulkStatus = el('.mediatk-bulk-status');
	var confirmBox = el('.mediatk-bulk-confirm');
	var bulkRunning = el('.mediatk-bulk-running');
	var progressWrap = el('.mediatk-bulk-progress');
	var progress = el('.mediatk-progress');
	var progressBar = el('.mediatk-progress-bar');
	var count = el('.mediatk-bulk-count');
	var saved = el('.mediatk-bulk-saved');
	var current = el('.mediatk-bulk-current');
	var currentImage = el('.mediatk-bulk-current-image');
	var done = el('.mediatk-bulk-done');

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

	function wait(ms) {
		return new Promise(function (resolve) {
			setTimeout(resolve, ms);
		});
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
		bulkRunning.hidden = !isRunning;

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
		count.textContent = format(i18n.count, [run.index, run.ids.length]);
		saved.textContent = formatBytes(run.saved);
	}

	function showImage(image) {
		var before = image.before;
		var after = image.after;
		var percent = before.bytes ? Math.round(((before.bytes - after.bytes) / before.bytes) * 100) : 0;
		var badge = el('.mediatk-bulk-percent');

		current.hidden = false;

		// Restart the "squash" animation for each image.
		currentImage.style.animation = 'none';
		void currentImage.offsetWidth;
		currentImage.style.animation = '';
		currentImage.src = image.thumbnail;

		var resized = before.width !== after.width || before.height !== after.height;
		var smaller = before.bytes > after.bytes;

		el('.mediatk-bulk-current-title').textContent = image.title;
		el('.mediatk-bulk-dimensions-before').textContent = before.width + ' × ' + before.height;
		el('.mediatk-bulk-dimensions-after').textContent = after.width + ' × ' + after.height;
		el('.mediatk-bulk-bytes-before').textContent = formatBytes(before.bytes);
		el('.mediatk-bulk-bytes-after').textContent = formatBytes(after.bytes);

		// Only show "before →" if something changed.
		el('.mediatk-bulk-dimensions-before').hidden = !resized;
		el('.mediatk-bulk-dimensions-before + .mediatk-arrow').hidden = !resized;
		el('.mediatk-bulk-bytes-before').hidden = !smaller;
		el('.mediatk-bulk-bytes-before + .mediatk-arrow').hidden = !smaller;

		badge.textContent = '−' + percent + '%';
		badge.hidden = percent <= 0;
	}

	function showDone(run) {
		progressWrap.hidden = true;
		current.hidden = true;
		done.hidden = false;
		bulkStatus.textContent = '';

		el('.mediatk-bulk-done-saved').textContent = run.saved > 0 ? format(i18n.total, [formatBytes(run.saved)]) : i18n.already;
		el('.mediatk-bulk-done-counts').textContent = format(i18n.done, [run.totals.optimized, run.totals.skipped, run.totals.failed]);
	}

	function start(run) {
		resumeButton.hidden = true;
		done.hidden = true;
		bulkStatus.textContent = i18n.running;
		setRunning(true);

		function next() {
			showProgress(run);

			if (run.index >= run.ids.length) {
				showDone(run);
				clearRun();
				return;
			}

			var startedAt = Date.now();

			return request('mediatoolkit_bulk_optimize', [run.ids[run.index]])
				.then(function (result) {
					run.totals.optimized += result.optimized;
					run.totals.skipped += result.skipped;
					run.totals.failed += result.failed;

					result.images.forEach(function (image) {
						run.saved += image.before.bytes - image.after.bytes;
						showImage(image);
					});

					run.index++;
					saveRun(run);

					// Give each image a moment on screen and leave room for other requests.
					return wait(Math.max(0, minDelay - (Date.now() - startedAt)));
				})
				.then(next);
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
		confirmBox.hidden = false;
		bulkButton.hidden = true;
		resumeButton.hidden = true;
		el('.mediatk-bulk-confirm-yes').focus();
	});

	el('.mediatk-bulk-confirm-no').addEventListener('click', function () {
		confirmBox.hidden = true;
		bulkButton.hidden = false;
		resumeButton.hidden = !loadRun();
		bulkButton.focus();
	});

	el('.mediatk-bulk-confirm-yes').addEventListener('click', function () {
		confirmBox.hidden = true;
		bulkButton.hidden = false;
		current.hidden = true;
		done.hidden = true;
		clearRun();
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
