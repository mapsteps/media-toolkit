(function () {
	init();

	function init() {
		const form = document.querySelector("form.mediatoolkit-settings-form");
		if (!form) return;

		const replaceOriginalImageField = document.getElementById(
			"mediatoolkit_settings[replace_original_image]"
		) as HTMLInputElement | null;

		if (!replaceOriginalImageField) return;

		handleReplaceOriginalImageState(replaceOriginalImageField);

		replaceOriginalImageField.addEventListener(
			"change",
			handleReplaceOriginalImageChange
		);
	}

	function handleReplaceOriginalImageChange(e: Event) {
		const checkbox = e.target as HTMLInputElement;
		if (!checkbox) return;

		handleReplaceOriginalImageState(checkbox);
	}

	function handleReplaceOriginalImageState(checkbox: HTMLInputElement) {
		const trTag = checkbox.closest("tr");
		if (!trTag) return;

		const table = trTag?.closest("table");
		if (!table) return;

		const trTag2 = table.querySelector("tr:nth-child(2)");
		if (!trTag2) return;

		const isChecked = checkbox.checked;

		if (isChecked) {
			trTag2.classList.remove("row-disabled");
		} else {
			trTag2.classList.add("row-disabled");
		}
	}
})();
