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
	var bulkStatus = document.querySelector('.mediatk-bulk-status');

	if (!bulkButton || !bulkStatus || !window.mediaToolkit) return;

	var i18n = window.mediaToolkit.i18n;

	function format(text, values) {
		values.forEach(function (value, index) {
			text = text.replace('%' + (index + 1) + '$d', value);
		});

		return text;
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

	var bulkRunning = document.querySelector('.mediatk-bulk-running');

	// Ask the browser to confirm leaving the page while optimizing.
	function preventLeaving(e) {
		e.preventDefault();
		e.returnValue = '';
	}

	function setRunning(isRunning) {
		bulkButton.disabled = isRunning;

		if (bulkRunning) bulkRunning.hidden = !isRunning;

		if (isRunning) {
			window.addEventListener('beforeunload', preventLeaving);
		} else {
			window.removeEventListener('beforeunload', preventLeaving);
		}
	}

	bulkButton.addEventListener('click', function () {
		if (!window.confirm(i18n.confirm)) return;

		var totals = { optimized: 0, skipped: 0, failed: 0 };
		var batchSize = 3;

		setRunning(true);
		bulkStatus.textContent = i18n.starting;

		request('mediatoolkit_bulk_ids')
			.then(function (ids) {
				if (!ids.length) {
					bulkStatus.textContent = i18n.none;
					return;
				}

				var index = 0;

				function next() {
					if (index >= ids.length) {
						bulkStatus.textContent = format(i18n.done, [totals.optimized, totals.skipped, totals.failed]);
						return;
					}

					bulkStatus.textContent = format(i18n.progress, [index, ids.length]);

					var batch = ids.slice(index, index + batchSize);

					return request('mediatoolkit_bulk_optimize', batch).then(function (result) {
						totals.optimized += result.optimized;
						totals.skipped += result.skipped;
						totals.failed += result.failed;
						index += batch.length;

						return next();
					});
				}

				return next();
			})
			.catch(function () {
				bulkStatus.textContent = i18n.error;
			})
			.finally(function () {
				setRunning(false);
			});
	});
})();
