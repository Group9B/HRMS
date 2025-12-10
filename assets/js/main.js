const themeToggleBtn = document.getElementById("toggleThemeBtn");
if (themeToggleBtn) {
	themeToggleBtn.addEventListener("click", () => {
		const html = document.documentElement;
		const currentTheme = html.getAttribute("data-bs-theme");
		const newTheme = currentTheme === "dark" ? "light" : "dark";
		html.style.transition = "background-color 0.3s ease";
		html.setAttribute("data-bs-theme", newTheme);
		localStorage.setItem("theme", newTheme);
	});
}

document.addEventListener("DOMContentLoaded", () => {
	const savedTheme = localStorage.getItem("theme");
	if (savedTheme) {
		document.documentElement.setAttribute("data-bs-theme", savedTheme);
	}
	initializePasswordToggle("passwordInput", "togglePassword");
	// Sidebar toggle is now handled largely by Tabler's CSS/Bootstrap,
	// but we keep this if you have custom backdrop logic.
	initializeSidebarToggle("navbar-menu", "sidebarToggle", "backdrop");
});

window.addEventListener("load", () => {
	const pageLoader = document.getElementById("pageLoader");
	if (pageLoader) {
		pageLoader.style.transition = "opacity 0.5s ease";
		pageLoader.style.opacity = "0";
		setTimeout(() => {
			pageLoader.remove();
		}, 500);
	}
});

/**
 * Generates a deterministic background color from a number.
 */
function generateColorFromId(id) {
	let hash = 0;
	const idStr = String(id);
	for (let i = 0; i < idStr.length; i++) {
		hash = idStr.charCodeAt(i) + ((hash << 5) - hash);
		hash = hash & hash;
	}
	const hue = Math.abs(hash) % 360;
	const saturation = 70;
	const lightness = 26;
	return `hsl(${hue}, ${saturation}%, ${lightness}%)`;
}

function getInitialsFromUsername(username) {
	if (!username) return "?";
	const namePart = username.split("@")[0];
	const cleanedName = namePart.replace(/[._-]/g, " ");
	const parts = cleanedName.split(" ").filter((part) => part.length > 0);

	if (parts.length > 1) {
		const firstInitial = parts[0][0];
		const lastInitial = parts[parts.length - 1][0];
		return `${firstInitial}${lastInitial}`.toUpperCase();
	} else if (parts.length === 1 && parts[0].length > 0) {
		return parts[0].substring(0, 2).toUpperCase();
	} else {
		return username.substring(0, 1).toUpperCase();
	}
}

function generateAvatarData(user) {
	return {
		initials: getInitialsFromUsername(user.username),
		color: generateColorFromId(user.id),
	};
}

function createAvatar(user) {
	const avatar = document.querySelector(".avatar");
	if (avatar) {
		let userData = generateAvatarData(user);
		avatar.style.backgroundColor = userData.color;
		avatar.textContent = userData.initials;
		return true;
	}
	return false;
}

function initializeSidebarToggle(sidebarId, toggleBtnId, backdropId) {
	const sidebar = document.getElementById(sidebarId);
	const toggleBtn = document.getElementById(toggleBtnId);

	// Tabler handles mobile menus via standard Bootstrap collapse/offcanvas mostly,
	// but this ensures your specific toggle button works.
	if (toggleBtn && sidebar) {
		toggleBtn.addEventListener("click", function () {
			sidebar.classList.toggle("show");
			// If you use a backdrop
			const backdrop = document.getElementById(backdropId);
			if (backdrop) {
				backdrop.style.display = sidebar.classList.contains("show")
					? "block"
					: "none";
				backdrop.addEventListener("click", function () {
					sidebar.classList.remove("show");
					backdrop.style.display = "none";
				});
			}
		});
	}
}

// --- UTILITY FUNCTIONS ---

function showToast(message, type = "success") {
	if (!$("#toast-container").length) {
		$("body").append(
			'<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>'
		);
	}
	const toastId = "toast-" + Date.now();
	// Tabler uses text-bg-{color} for solid colored elements or specific alert classes
	const bgClass = type === "success" ? "text-bg-success" : "text-bg-danger";

	const toastHTML = `
		<div id="${toastId}" class="toast align-items-center ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
			<div class="d-flex">
				<div class="toast-body">${message}</div>
				<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
			</div>
		</div>`;

	$("#toast-container").append(toastHTML);
	const toastElement = new bootstrap.Toast(document.getElementById(toastId));
	document
		.getElementById(toastId)
		.addEventListener("hidden.bs.toast", function () {
			this.remove();
		});
	toastElement.show();
}

function initializePasswordToggle(inputId, toggleBtnId) {
	const passwordinp = document.getElementById(inputId);
	const togglePasswordBtn = document.getElementById(toggleBtnId);
	if (passwordinp && togglePasswordBtn) {
		togglePasswordBtn.addEventListener("click", function (e) {
			e.preventDefault();
			const type =
				passwordinp.getAttribute("type") === "password"
					? "text"
					: "password";
			passwordinp.setAttribute("type", type);
			// Assuming font awesome or tabler icons
			const icon = this.querySelector("i") || this.querySelector("svg");
			if (icon && icon.classList.contains("fa-eye")) {
				icon.classList.remove("fa-eye");
				icon.classList.add("fa-eye-slash");
			} else if (icon && icon.classList.contains("fa-eye-slash")) {
				icon.classList.remove("fa-eye-slash");
				icon.classList.add("fa-eye");
			}
		});
	}
}

function initializeTodoList(formSelector, listSelector) {
	const todoForm = $(formSelector);
	const todoList = $(listSelector);

	if (!todoForm.length || !todoList.length) return;

	function loadTodos() {
		fetch("/hrms/api/todo.php?action=get_todos")
			.then((res) => res.json())
			.then((result) => {
				todoList.empty();
				if (result.success && result.data.length > 0) {
					result.data.forEach((item) => {
						const isCompleted = parseInt(item.is_completed) === 1;
						// Tabler list group item styling
						const li = `
                            <li class="list-group-item d-flex justify-content-between align-items-center" data-id="${
								item.id
							}">
                                <span class="task-text ${
									isCompleted
										? "text-decoration-line-through text-secondary"
										: ""
								}">${escapeHTML(item.task)}</span>
                                <div class="btn-list">
                                    ${
										!isCompleted
											? '<button class="btn btn-sm btn-icon btn-success complete-btn"><i class="fas fa-check"></i></button>'
											: ""
									}
                                    <button class="btn btn-sm btn-icon btn-danger delete-btn"><i class="fas fa-trash"></i></button>
                                </div>
                            </li>`;
						todoList.append(li);
					});
				} else {
					todoList.append(
						'<li class="list-group-item text-secondary text-center">No tasks yet. Add one above!</li>'
					);
				}
			});
	}

	todoForm.on("submit", function (e) {
		e.preventDefault();
		const formData = new FormData(this);
		formData.append("action", "add_todo");
		fetch("/hrms/api/todo.php", { method: "POST", body: formData }).then(
			() => {
				this.reset();
				loadTodos();
			}
		);
	});

	todoList.on("click", ".complete-btn", function () {
		const li = $(this).closest("li");
		const formData = new FormData();
		formData.append("action", "update_todo_status");
		formData.append("task_id", li.data("id"));
		formData.append("is_completed", 1);
		fetch("/hrms/api/todo.php", { method: "POST", body: formData }).then(
			() => loadTodos()
		);
	});

	todoList.on("click", ".delete-btn", function () {
		if (confirm("Delete this task?")) {
			const li = $(this).closest("li");
			const formData = new FormData();
			formData.append("action", "delete_todo");
			formData.append("task_id", li.data("id"));
			fetch("/hrms/api/todo.php", {
				method: "POST",
				body: formData,
			}).then(() => loadTodos());
		}
	});

	loadTodos();
}

function escapeHTML(str) {
	if (str === null || str === undefined) return "";
	return String(str).replace(
		/[&<>"']/g,
		(m) =>
			({
				"&": "&amp;",
				"<": "&lt;",
				">": "&gt;",
				'"': "&quot;",
				"'": "&#039;",
			}[m])
	);
}

function capitalize(str) {
	if (!str) return "";
	return str.charAt(0).toUpperCase() + str.slice(1);
}

// Helper to return tabler-compliant status colors
function getStatusClass(status) {
	switch (status) {
		case "approved":
		case "active":
			return "success";
		case "rejected":
		case "cancelled":
		case "inactive":
			return "danger";
		case "pending":
			return "warning";
		default:
			return "secondary";
	}
}

/**
 * Formats a date string into a more readable format.
 * @param {string} dateStr The date string (e.g., '2025-09-30').
 * @param {boolean} longFormat Whether to use a long format (e.g., 'September 30, 2025').
 * @returns {string} The formatted date.
 */
function formatDate(dateStr, longFormat = false) {
	if (!dateStr) return "N/A";
	const date = new Date(dateStr + "T00:00:00");
	if (longFormat) {
		return date.toLocaleDateString("en-US", {
			month: "long",
			day: "numeric",
			year: "numeric",
		});
	}
	return date.toLocaleDateString("en-CA"); // YYYY-MM-DD
}

/**
 * Calculates the number of days between two dates, inclusive.
 * @param {string} start The start date string.
 * @param {string} end The end date string.
 * @returns {number} The number of days.
 */
function countDays(start, end) {
	if (!start || !end) return 0;
	const diffTime = Math.abs(new Date(end) - new Date(start));
	return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
}

/**
 * Returns a Bootstrap color class based on a performance score.
 * @param {number} score The score (0-100).
 * @returns {string} A Bootstrap class name.
 */
function getScoreColor(score) {
	if (score >= 80) return "success";
	if (score >= 60) return "warning";
	if (score >= 40) return "info";
	return "danger";
}
