document.getElementById("toggleThemeBtn").addEventListener("click", () => {
	const html = document.documentElement;
	const currentTheme = html.getAttribute("data-bs-theme");
	const newTheme = currentTheme === "dark" ? "light" : "dark";

	// Animate the transition
	html.style.transition = "background-color 0.3s ease";
	html.setAttribute("data-bs-theme", newTheme);

	// Store theme preference
	localStorage.setItem("theme", newTheme);
});

// Load saved theme preference
document.addEventListener("DOMContentLoaded", () => {
	const savedTheme = localStorage.getItem("theme");
	if (savedTheme) {
		document.documentElement.setAttribute("data-bs-theme", savedTheme);
	}
});
