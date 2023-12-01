(function () {
	init();

	function init() {
		setupFieldsInteraction();
		setupTemplateTags();
	}

	function setupTemplateTags() {
		const metabox = document.querySelector(".mediatk-tags-metabox");
		if (!metabox) return;

		const tags = metabox.querySelectorAll("code");
		if (!tags.length) return;

		tags.forEach((tag) => {
			tag.addEventListener("click", handleTagClick);
		});
	}

	function handleTagClick(e: Event) {
		const tag = e.target as HTMLElement | null;
		if (!tag) return;

		const value = tag.innerText;
		if (!value) return;

		// Copy value to clipboard.
		copyToClipboard(value);

		const notice = document.querySelector(
			".mediatk-tags-metabox .action-status"
		) as HTMLElement | null;
		if (!notice) return;

		notice.classList.add("is-shown");

		setTimeout(() => {
			notice.classList.remove("is-shown");
		}, 1500);
	}

	async function copyToClipboard(text: string) {
		try {
			await navigator.clipboard.writeText(text);
		} catch (err) {
			// console.error("Unable to copy text to clipboard:", err);
			copyToClipboardViaExecCommand(text);
		}
	}

	async function copyToClipboardViaExecCommand(text: string) {
		const textArea = document.createElement("textarea");

		textArea.value = text;
		textArea.style.position = "fixed";
		textArea.style.top = "-3px";
		textArea.style.left = "-3px";
		textArea.style.width = "1px";
		textArea.style.height = "1px";
		textArea.style.background = "transparent";
		textArea.style.opacity = "0";

		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			document.execCommand("copy");
		} catch (err) {
			console.error("Unable to copy text to clipboard:", err);
		}

		document.body.removeChild(textArea);
	}

	function setupFieldsInteraction() {
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
		const inputs = trTag2.querySelectorAll("input");

		if (isChecked) {
			trTag2.classList.remove("row-disabled");

			inputs.forEach((input) => {
				input.readOnly = false;
			});
		} else {
			trTag2.classList.add("row-disabled");

			inputs.forEach((input) => {
				input.readOnly = true;
			});
		}
	}
})();
