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
	var log = el('.mediatk-bulk-log');
	var logList = el('.mediatk-bulk-log-list');
	var logLimit = el('.mediatk-bulk-log-limit');
	var maxRows = 200;
	var lastImage = null;

	function format(text, values) {
		values.forEach(function (value, index) {
			text = text.replace('%' + (index + 1) + '$d', value).replace('%' + (index + 1) + '$s', value);
		});

		return text.replace('%s', values[0]).replace('%d', values[0]);
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

	function getPercent(image) {
		var before = image.before.bytes;

		return before ? Math.round(((before - image.after.bytes) / before) * 100) : 0;
	}

	// "−43%" if the files got smaller, "+5%" if they got bigger, "No change" if they're the same.
	function getBadge(image) {
		var before = image.before.bytes;
		var after = image.after.bytes;
		var percent = Math.max(1, Math.abs(getPercent(image)));

		if (after < before) return { text: '−' + percent + '%', className: '' };
		if (after > before) return { text: '+' + percent + '%', className: ' is-bigger' };

		return { text: i18n.noChange, className: ' is-neutral' };
	}

	// A row like in the current image card: "File size   521 KB → 426 KB", the old value crossed out.
	function compareRow(label, before, after, extra) {
		var wrap = document.createElement('div');
		var term = document.createElement('dt');
		var value = document.createElement('dd');
		var strong = document.createElement('strong');

		term.textContent = label;
		strong.textContent = after;

		if (before !== after) {
			var old = document.createElement('span');
			var arrow = document.createElement('span');

			old.className = 'mediatk-before';
			old.textContent = before;
			arrow.className = 'mediatk-arrow';
			arrow.setAttribute('aria-hidden', 'true');
			arrow.textContent = '→';

			value.appendChild(old);
			value.appendChild(arrow);
		}

		value.appendChild(strong);

		if (extra) value.appendChild(extra);

		wrap.appendChild(term);
		wrap.appendChild(value);

		return wrap;
	}

	// Add an image to the list below the current one, newest on top.
	function addRow(image) {
		var failed = 'failed' === image.status;
		var row = document.createElement('li');
		var thumb;
		var info = document.createElement('div');
		var title = document.createElement('a');
		var meta = document.createElement(failed ? 'span' : 'dl');
		var badge = document.createElement('span');
		var result = failed ? { text: i18n.failedTag, className: ' is-failed' } : getBadge(image);

		row.className = 'mediatk-log-row' + (failed ? ' is-failed' : '');

		if (!failed && image.thumbnail) {
			thumb = document.createElement('img');
			thumb.src = image.thumbnail;
			thumb.alt = '';
			thumb.loading = 'lazy';
		} else {
			thumb = document.createElement('span');
			thumb.className = 'dashicons ' + (failed ? 'dashicons-warning' : 'dashicons-format-image');
			thumb.setAttribute('aria-hidden', 'true');
		}

		thumb.classList.add('mediatk-log-thumb');

		title.className = 'mediatk-log-title';
		title.href = image.edit || window.mediaToolkit.editUrl + image.id;
		title.target = '_blank';
		title.rel = 'noopener';
		title.textContent = image.title || format(i18n.image, [image.id]);

		badge.className = 'mediatk-badge' + result.className;
		badge.textContent = result.text;

		if (failed) {
			meta.className = 'mediatk-log-meta';
			meta.textContent = image.reason;
		} else {
			// Same layout as the current image card, the badge next to the file size.
			meta.className = 'mediatk-bulk-compare';
			meta.appendChild(compareRow(i18n.dimensions, image.before.width + ' × ' + image.before.height, image.after.width + ' × ' + image.after.height));
			meta.appendChild(compareRow(i18n.fileSize, formatBytes(image.before.bytes), formatBytes(image.after.bytes), badge));
		}

		info.className = 'mediatk-log-info';
		info.appendChild(title);
		info.appendChild(meta);

		row.appendChild(thumb);
		row.appendChild(info);

		if (failed) row.appendChild(badge);

		logList.insertBefore(row, logList.firstChild);
		log.hidden = false;

		// Keep the page fast on big libraries.
		if (logList.children.length > maxRows) {
			logList.removeChild(logList.lastChild);
			logLimit.textContent = format(i18n.logLimit, [maxRows]);
			logLimit.hidden = false;
		}
	}

	// A failed image goes straight into the list, after the image that's currently shown.
	function addFailedRow(image) {
		if (lastImage) addRow(lastImage);
		lastImage = null;
		current.hidden = true;
		addRow(image);
	}

	function clearLog() {
		lastImage = null;
		logList.innerHTML = '';
		logLimit.hidden = true;
		log.hidden = true;
	}

	function showImage(image) {
		var before = image.before;
		var after = image.after;
		var result = getBadge(image);
		var badge = el('.mediatk-bulk-percent');

		// The previous image moves into the list.
		if (lastImage) addRow(lastImage);
		lastImage = image;

		current.hidden = false;

		// Restart the "squash" animation for each image.
		currentImage.style.animation = 'none';
		void currentImage.offsetWidth;
		currentImage.style.animation = '';
		currentImage.src = image.thumbnail;
		currentImage.hidden = !image.thumbnail;

		var resized = before.width !== after.width || before.height !== after.height;
		var changed = before.bytes !== after.bytes;

		el('.mediatk-bulk-current-title').textContent = image.title;
		el('.mediatk-bulk-dimensions-before').textContent = before.width + ' × ' + before.height;
		el('.mediatk-bulk-dimensions-after').textContent = after.width + ' × ' + after.height;
		el('.mediatk-bulk-bytes-before').textContent = formatBytes(before.bytes);
		el('.mediatk-bulk-bytes-after').textContent = formatBytes(after.bytes);

		// Only show "before →" if something changed.
		el('.mediatk-bulk-dimensions-before').hidden = !resized;
		el('.mediatk-bulk-dimensions-before + .mediatk-arrow').hidden = !resized;
		el('.mediatk-bulk-bytes-before').hidden = !changed;
		el('.mediatk-bulk-bytes-before + .mediatk-arrow').hidden = !changed;

		badge.textContent = result.text;
		badge.className = 'mediatk-badge mediatk-bulk-percent' + result.className;
	}

	function showDone(run) {
		if (lastImage) addRow(lastImage);
		lastImage = null;

		progressWrap.hidden = true;
		current.hidden = true;
		done.hidden = false;
		bulkStatus.textContent = '';

		var summary = i18n.already;

		if (run.saved > 0) {
			summary = format(i18n.total, [formatBytes(run.saved)]);
		} else if (run.saved < 0) {
			summary = format(i18n.grew, [formatBytes(-run.saved)]);
		}

		el('.mediatk-bulk-done-saved').textContent = summary;
		done.classList.toggle('is-bigger', run.saved < 0);

		var note = el('.mediatk-bulk-done-note');

		note.textContent = 1 === run.grew ? i18n.grewOne : run.grew ? format(i18n.grewNote, [run.grew]) : '';
		note.hidden = !run.grew;
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
						if ('failed' === image.status) {
							addFailedRow(image);
							return;
						}

						run.saved += image.before.bytes - image.after.bytes;

						if (image.after.bytes > image.before.bytes) run.grew = (run.grew || 0) + 1;
						showImage(image);
					});

					failures = 0;
					run.index++;
					saveRun(run);

					// Give each image a moment on screen and leave room for other requests.
					return wait(Math.max(0, minDelay - (Date.now() - startedAt)));
				})
				.catch(function (err) {
					failures++;

					// Several failures in a row: probably the connection, stop so it can be resumed.
					if (failures >= 3) throw err;

					// Retry once, then count the image as failed and move on, so one broken image can't block the run.
					if (failures === 2) {
						addFailedRow({ status: 'failed', id: run.ids[run.index], reason: i18n.crashed });
						run.totals.failed++;
						run.index++;
						saveRun(run);
					}

					return wait(1000);
				})
				.then(next);
		}

		var failures = 0;

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
		clearLog();
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
