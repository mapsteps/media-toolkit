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

	if (!toggle || !maxSize) return;

	var row = maxSize.closest('tr');

	function updateMaxSize() {
		maxSize.readOnly = !toggle.checked;

		if (row) row.classList.toggle('row-disabled', !toggle.checked);
	}

	updateMaxSize();
	toggle.addEventListener('change', updateMaxSize);
})();
