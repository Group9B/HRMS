// --- Theme Toggle (from your original file) ---
document.getElementById("toggleThemeBtn").addEventListener("click", () => {
	const html = document.documentElement;
	const currentTheme = html.getAttribute("data-bs-theme");
	const newTheme = currentTheme === "dark" ? "light" : "dark";
	html.style.transition = "background-color 0.3s ease";
	html.setAttribute("data-bs-theme", newTheme);
	localStorage.setItem("theme", newTheme);
});

document.addEventListener("DOMContentLoaded", () => {
	const savedTheme = localStorage.getItem("theme");
	if (savedTheme) {
		document.documentElement.setAttribute("data-bs-theme", savedTheme);
	}
});

// --- NEW UTILITY FUNCTIONS ---

/**
 * Displays a Bootstrap 5 toast notification.
 * @param {string} message The message to display.
 * @param {string} type The type of toast ('success' or 'error').
 */
function showToast(message, type = "success") {
	// Ensure a toast container exists on the page
	if (!$("#toast-container").length) {
		$("body").append(
			'<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>'
		);
	}

	const toastId = "toast-" + Date.now();
	const bgClass = type === "success" ? "bg-success" : "bg-danger";

	const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

	$("#toast-container").append(toastHTML);

	const toastElement = new bootstrap.Toast(document.getElementById(toastId));

	// Remove the toast from the DOM after it's hidden
	document
		.getElementById(toastId)
		.addEventListener("hidden.bs.toast", function () {
			this.remove();
		});

	toastElement.show();
}

/**
 * Escapes HTML special characters in a string to prevent XSS.
 * @param {string} str The string to escape.
 * @returns {string} The escaped string.
 */
function escapeHTML(str) {
	if (str === null || str === undefined) return "";
	const div = document.createElement("div");
	div.appendChild(document.createTextNode(str));
	return div.innerHTML;
}
