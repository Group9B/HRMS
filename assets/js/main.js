// --- Theme Toggle (from your original file) ---
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
	initializeSidebarToggle("sidebar", "sidebarToggle", "backdrop");
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

function initializeSidebarToggle(sidebarId, toggleBtnId, backdropId) {
	const sidebar = document.getElementById(sidebarId);
	const toggleBtn = document.getElementById(toggleBtnId);
	const backdrop = document.getElementById(backdropId);
	if (toggleBtn && sidebar && backdrop) {
		toggleBtn.addEventListener("click", function () {
			sidebar.classList.toggle("show");
			backdrop.style.display = sidebar.classList.contains("show")
				? "block"
				: "none";
		});
		backdrop.addEventListener("click", function () {
			sidebar.classList.remove("show");
			backdrop.style.display = "none";
		});
	}
}

// --- UTILITY FUNCTIONS ---

function showToast(message, type = "success") {
	if (!$("#toast-container").length) {
		$("body").append(
			'<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>'
		);
	}
	const toastId = "toast-" + Date.now();
	const bgClass = type === "success" ? "bg-success" : "bg-danger";
	const toastHTML = `<div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true"><div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div></div>`;
	$("#toast-container").append(toastHTML);
	const toastElement = new bootstrap.Toast(document.getElementById(toastId));
	document
		.getElementById(toastId)
		.addEventListener("hidden.bs.toast", function () {
			this.remove();
		});
	toastElement.show();
}

function escapeHTML(str) {
	if (str === null || str === undefined) return "";
	const div = document.createElement("div");
	div.appendChild(document.createTextNode(str));
	return div.innerHTML;
}

function initializePasswordToggle(inputId, toggleBtnId) {
	const passwordinp = document.getElementById(inputId);
	const togglePasswordBtn = document.getElementById(toggleBtnId);
	if (passwordinp && togglePasswordBtn) {
		togglePasswordBtn.addEventListener("click", function () {
			const type =
				passwordinp.getAttribute("type") === "password"
					? "text"
					: "password";
			passwordinp.setAttribute("type", type);
			this.querySelector("i").classList.toggle("fa-eye");
			this.querySelector("i").classList.toggle("fa-eye-slash");
		});
	}
}

/**
 * Initializes a modular To-Do list widget.
 * @param {string} formSelector The CSS selector for the form element.
 * @param {string} listSelector The CSS selector for the UL element.
 */
function initializeTodoList(formSelector, listSelector) {
	const todoForm = $(formSelector);
	const todoList = $(listSelector);

	if (!todoForm.length || !todoList.length) {
		return; // Exit if the required elements aren't on the page
	}

	function loadTodos() {
		fetch("/hrms/api/todo.php?action=get_todos")
			.then((res) => res.json())
			.then((result) => {
				todoList.empty();
				if (result.success && result.data.length > 0) {
					result.data.forEach((item) => {
						const isCompleted = parseInt(item.is_completed) === 1;
						const li = `
                            <li class="list-group-item d-flex justify-content-between align-items-center" data-id="${
								item.id
							}">
                                <span class="task-text ${
									isCompleted
										? "text-decoration-line-through text-muted"
										: ""
								}">${escapeHTML(item.task)}</span>
                                <div class="btn-group">
                                    ${
										!isCompleted
											? '<button class="btn btn-sm btn-success complete-btn"><i class="fas fa-check"></i></button>'
											: ""
									}
                                    <button class="btn btn-sm btn-danger delete-btn"><i class="fas fa-trash"></i></button>
                                </div>
                            </li>`;
						todoList.append(li);
					});
				} else {
					todoList.append(
						'<li class="list-group-item text-muted text-center">No tasks yet. Add one above!</li>'
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

	// New handler for the complete button
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

	// Existing handler for the delete button
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

	loadTodos(); // Initial load
}

function capitalize(str) {
	if (!str) return "";
	return str.charAt(0).toUpperCase() + str.slice(1);
}
