/**
 * Pro Skeleton Loading System - Component Factory
 *
 * A powerful factory class for creating skeleton loading placeholders
 * using a template registry pattern for maximum reusability.
 *
 * @version 2.0.0
 * @author HRMS Team
 */

class SkeletonFactory {
	/**
	 * Configuration options
	 */
	static config = {
		minDuration: 500, // Minimum time to show skeleton (ms)
		fadeOutDuration: 200, // Fade out animation duration (ms)
		fadeInDuration: 300, // Fade in animation duration (ms)
		defaultAnimation: "wave", // 'wave', 'glow'
	};

	/**
	 * Helper to get Bootstrap animation class
	 * @private
	 */
	static _getAnimClass(animation) {
		if (animation === "pulse") return "placeholder-glow";
		if (animation === "wave" || animation === "shimmer")
			return "placeholder-wave";
		return "placeholder-glow"; // Default
	}

	/**
	 * Store for tracking skeleton state
	 */
	static _state = new Map();

	/**
	 * Template registry - stores blueprint functions
	 */
	static blueprints = {
		/**
		 * Dashboard stat card skeleton
		 */
		"stat-card": (options = {}) => {
			const { animation = SkeletonFactory.config.defaultAnimation } =
				options;
			const animClass = SkeletonFactory._getAnimClass(animation);
			return `
                <div class="col-xl-3 col-md-6 mb-3" aria-hidden="true" role="presentation">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="w-100 me-3">
                                    <span class="placeholder ${animClass} col-6 mb-2 sk-placeholder-bg"></span>
                                    <span class="placeholder ${animClass} col-4 d-block sk-placeholder-bg" style="height: 1.5rem;"></span>
                                </div>
                                <div class="placeholder ${animClass} rounded-circle sk-placeholder-bg" style="width: 3rem; height: 3rem; flex-shrink: 0;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
		},

		/**
		 * Table row skeleton
		 * @param {Object} options - { columns: number }
		 */
		"table-row": (options = {}) => {
			const {
				columns = 5,
				animation = SkeletonFactory.config.defaultAnimation,
			} = options;
			const animClass = SkeletonFactory._getAnimClass(animation);

			const cols = ["col-8", "col-6", "col-10", "col-4", "col-7"];
			let cells = "";

			for (let i = 0; i < columns; i++) {
				const col = cols[i % cols.length];
				cells += `
                    <td>
                        <span class="placeholder ${animClass} ${col} sk-placeholder-bg"></span>
                    </td>
                `;
			}

			return `<tr aria-hidden="true" role="presentation">${cells}</tr>`;
		},

		/**
		 * List item skeleton (with avatar)
		 */
		"list-item": (options = {}) => {
			const { animation = SkeletonFactory.config.defaultAnimation } =
				options;
			const animClass = SkeletonFactory._getAnimClass(animation);
			return `
                <div class="d-flex align-items-center py-3 border-bottom" aria-hidden="true" role="presentation">
                    <div class="placeholder ${animClass} rounded-circle sk-placeholder-bg me-3" style="width: 2.5rem; height: 2.5rem;"></div>
                    <div class="flex-grow-1">
                        <span class="placeholder ${animClass} col-7 mb-1 sk-placeholder-bg d-block"></span>
                        <span class="placeholder ${animClass} col-5 sk-placeholder-bg d-block"></span>
                    </div>
                </div>
            `;
		},

		/**
		 * Generic card skeleton
		 */
		"card": (options = {}) => {
			const { animation = SkeletonFactory.config.defaultAnimation } =
				options;
			const animClass = SkeletonFactory._getAnimClass(animation);
			return `
                <div class="card shadow-sm" aria-hidden="true" role="presentation">
                    <div class="card-body">
                        <span class="placeholder ${animClass} col-6 mb-3 sk-placeholder-bg d-block" style="height: 1.25em;"></span>
                        <span class="placeholder ${animClass} col-12 mb-1 sk-placeholder-bg d-block"></span>
                        <span class="placeholder ${animClass} col-10 mb-1 sk-placeholder-bg d-block"></span>
                        <span class="placeholder ${animClass} col-8 sk-placeholder-bg d-block"></span>
                    </div>
                </div>
            `;
		},

		/**
		 * Paragraph skeleton
		 * @param {Object} options - { lines: number }
		 */
		"paragraph": (options = {}) => {
			const {
				lines = 3,
				animation = SkeletonFactory.config.defaultAnimation,
			} = options;
			const animClass = SkeletonFactory._getAnimClass(animation);

			const cols = ["col-12", "col-10", "col-11", "col-9", "col-8"];
			let html = '<div aria-hidden="true" role="presentation">';

			for (let i = 0; i < lines; i++) {
				const col =
					i === lines - 1 ? "col-7 mb-3" : cols[i % cols.length];
				const margin = i < lines - 1 ? "mb-2" : "";
				html += `<span class="placeholder ${animClass} ${col} ${margin} sk-placeholder-bg d-block"></span>`;
			}

			html += "</div>";
			return html;
		},

		/**
		 * Text line skeleton
		 * @param {Object} options - { width: string, size: string }
		 */
		"text": (options = {}) => {
			const {
				width = "col-12",
				size = "",
				animation = SkeletonFactory.config.defaultAnimation,
			} = options;
			const animClass = SkeletonFactory._getAnimClass(animation);

			return `<span class="placeholder ${animClass} ${size} ${width} sk-placeholder-bg d-block" aria-hidden="true" role="presentation"></span>`;
		},

		/**
		 * Circle skeleton (avatar)
		 * @param {Object} options - { size: string }
		 */
		"circle": (options = {}) => {
			const {
				size = "",
				dim = "2.5rem",
				animation = SkeletonFactory.config.defaultAnimation,
			} = options;
			const animClass = SkeletonFactory._getAnimClass(animation);

			let dimension = dim;
			if (size === "sm") dimension = "2rem";
			if (size === "lg") dimension = "3rem";
			if (size === "xl") dimension = "4rem";

			return `<div class="placeholder ${animClass} rounded-circle sk-placeholder-bg" style="width: ${dimension}; height: ${dimension};" aria-hidden="true" role="presentation"></div>`;
		},

		/**
		 * Rectangle skeleton (image placeholder)
		 * @param {Object} options - { size: string }
		 */
		"rect": (options = {}) => {
			const {
				size = "",
				animation = SkeletonFactory.config.defaultAnimation,
			} = options;
			const animClass = SkeletonFactory._getAnimClass(animation);

			let height = "8rem";
			if (size === "sm") height = "4rem";
			if (size === "lg") height = "12rem";
			if (size === "xl") height = "16rem";

			return `<div class="placeholder ${animClass} w-100 sk-placeholder-bg rounded" style="height: ${height};" aria-hidden="true" role="presentation"></div>`;
		},

		/**
		 * Button skeleton
		 * @param {Object} options - { size: string }
		 */
		"button": (options = {}) => {
			const {
				size = "",
				animation = SkeletonFactory.config.defaultAnimation,
			} = options;
			const animClass = SkeletonFactory._getAnimClass(animation);

			let btnClass =
				"btn btn-secondary disabled placeholder sk-placeholder-bg " +
				animClass;
			if (size) btnClass += " btn-" + size;

			return `<a href="#" tabindex="-1" class="${btnClass} col-6" aria-hidden="true"></a>`;
		},

		/**
		 * DataTable skeleton
		 * @param {Object} options - { rows: number, columns: number }
		 */
		"datatable": (options = {}) => {
			const {
				rows = 5,
				columns = 5,
				animation = SkeletonFactory.config.defaultAnimation,
			} = options;

			let html = '<table class="table"><tbody>';
			for (let i = 0; i < rows; i++) {
				html += SkeletonFactory.blueprints["table-row"]({
					columns,
					animation,
				});
			}
			html += "</tbody></table>";
			return html;
		},

		/**
		 * Profile header skeleton
		 */
		"profile-header": (options = {}) => {
			const { animation = SkeletonFactory.config.defaultAnimation } =
				options;
			const animClass = SkeletonFactory._getAnimClass(animation);
			return `
                <div class="d-flex align-items-center gap-3" aria-hidden="true" role="presentation">
                    <div class="placeholder ${animClass} rounded-circle sk-placeholder-bg" style="width: 4rem; height: 4rem;"></div>
                    <div class="d-flex flex-column gap-2 flex-grow-1">
                        <span class="placeholder ${animClass} col-4 sk-placeholder-bg" style="height: 1.25rem;"></span>
                        <span class="placeholder ${animClass} col-3 sk-placeholder-bg"></span>
                    </div>
                </div>
            `;
		},

		/**
		 * Notification item skeleton
		 */
		"notification": (options = {}) => {
			const { animation = SkeletonFactory.config.defaultAnimation } =
				options;
			const animClass = SkeletonFactory._getAnimClass(animation);
			return `
                <div class="d-flex align-items-start gap-2 py-2 border-bottom" aria-hidden="true" role="presentation">
                    <div class="placeholder ${animClass} rounded-circle sk-placeholder-bg flex-shrink-0" style="width: 2rem; height: 2rem;"></div>
                    <div class="flex-grow-1">
                        <span class="placeholder ${animClass} col-9 mb-1 sk-placeholder-bg d-block"></span>
                        <span class="placeholder ${animClass} col-4 sk-placeholder-bg d-block"></span>
                    </div>
                </div>
            `;
		},
	};

	/**
	 * Register a custom blueprint
	 * @param {string} name - Blueprint name
	 * @param {Function} templateFn - Template function that returns HTML
	 */
	static register(name, templateFn) {
		if (typeof templateFn !== "function") {
			console.error(
				`SkeletonFactory: Blueprint "${name}" must be a function.`,
			);
			return;
		}
		this.blueprints[name] = templateFn;
	}

	/**
	 * Create skeleton HTML
	 * @param {string} blueprint - Blueprint name
	 * @param {number} count - Number of items to create
	 * @param {Object} options - Options passed to blueprint
	 * @returns {string} HTML string
	 */
	static create(blueprint, count = 1, options = {}) {
		const templateFn = this.blueprints[blueprint];

		if (!templateFn) {
			console.warn(
				`SkeletonFactory: Blueprint "${blueprint}" not found. Using text.`,
			);
			return this.blueprints["text"](options).repeat(count);
		}

		let html = "";
		for (let i = 0; i < count; i++) {
			html += templateFn(options);
		}

		return html;
	}

	/**
	 * Show skeleton in a container
	 * @param {string|Element} selector - Container selector or element
	 * @param {string} blueprint - Blueprint name
	 * @param {number} count - Number of items
	 * @param {Object} options - Blueprint options
	 */
	static show(selector, blueprint, count = 1, options = {}) {
		const container =
			typeof selector === "string"
				? document.querySelector(selector)
				: selector;

		if (!container) {
			console.warn(`SkeletonFactory: Container "${selector}" not found.`);
			return;
		}

		const key = typeof selector === "string" ? selector : container;

		// Store current state
		this._state.set(key, {
			startTime: Date.now(),
			originalContent: container.innerHTML,
			originalHeight: container.offsetHeight,
		});

		// Generate and inject skeleton HTML
		const html = this.create(blueprint, count, options);
		container.innerHTML = html;
		container.classList.add("sk-loading");

		// Add screen reader announcement
		const srAnnounce = document.createElement("span");
		srAnnounce.className = "visually-hidden";
		srAnnounce.setAttribute("role", "status");
		srAnnounce.setAttribute("aria-live", "polite");
		srAnnounce.textContent = "Loading content...";
		srAnnounce.id = `sk-announce-${Date.now()}`;
		container.prepend(srAnnounce);
	}

	/**
	 * Hide skeleton and optionally show new content
	 * @param {string|Element} selector - Container selector or element
	 * @param {string|null} newContent - Optional new content HTML
	 * @returns {Promise} Resolves when skeleton is hidden
	 */
	static async hide(selector, newContent = null) {
		const container =
			typeof selector === "string"
				? document.querySelector(selector)
				: selector;

		if (!container) {
			console.warn(`SkeletonFactory: Container "${selector}" not found.`);
			return;
		}

		const key = typeof selector === "string" ? selector : container;
		const state = this._state.get(key);

		if (!state) {
			// No skeleton shown, just update content if provided
			if (newContent !== null) {
				container.innerHTML = newContent;
			}
			return;
		}

		// Calculate remaining time for minimum duration
		const elapsed = Date.now() - state.startTime;
		const remaining = Math.max(0, this.config.minDuration - elapsed);

		// Wait for minimum duration
		if (remaining > 0) {
			await this._sleep(remaining);
		}

		// Perform hide with optional fade transition
		const contentToShow =
			newContent !== null ? newContent : state.originalContent;

		// Remove screen reader announcement
		const srAnnounce = container.querySelector('[role="status"]');
		if (srAnnounce) {
			srAnnounce.remove();
		}

		// Update content
		container.innerHTML = contentToShow;
		container.classList.remove("sk-loading");

		// Clean up state
		this._state.delete(key);

		// Announce to screen readers
		this._announceLoaded();
	}

	/**
	 * Helper: Sleep for specified milliseconds
	 * @private
	 */
	static _sleep(ms) {
		return new Promise((resolve) => setTimeout(resolve, ms));
	}

	/**
	 * Helper: Announce content loaded to screen readers
	 * @private
	 */
	static _announceLoaded() {
		const announcement = document.createElement("div");
		announcement.setAttribute("role", "status");
		announcement.setAttribute("aria-live", "polite");
		announcement.className = "visually-hidden";
		announcement.textContent = "Content loaded";
		document.body.appendChild(announcement);

		// Remove after announcement
		setTimeout(() => announcement.remove(), 1000);
	}

	/**
	 * Show skeleton in a DataTable container
	 * @param {string} tableId - Table ID (without #)
	 * @param {number} rows - Number of rows
	 * @param {number} columns - Number of columns
	 */
	static showTable(tableId, rows = 5, columns = 5) {
		const table = document.getElementById(tableId);
		if (!table) {
			console.warn(`SkeletonFactory: Table "#${tableId}" not found.`);
			return;
		}

		// Store state
		this._state.set(tableId, {
			startTime: Date.now(),
			tableDisplay: table.style.display,
		});

		// Hide actual table
		table.style.display = "none";

		// Create skeleton container
		const skeletonDiv = document.createElement("div");
		skeletonDiv.id = `${tableId}-skeleton`;
		skeletonDiv.innerHTML = this.create("datatable", 1, { rows, columns });

		// Insert after table
		table.parentNode.insertBefore(skeletonDiv, table.nextSibling);
	}

	/**
	 * Hide DataTable skeleton
	 * @param {string} tableId - Table ID (without #)
	 * @returns {Promise} Resolves when skeleton is hidden
	 */
	static async hideTable(tableId) {
		const table = document.getElementById(tableId);
		const skeleton = document.getElementById(`${tableId}-skeleton`);
		const state = this._state.get(tableId);

		if (!skeleton || !table) return;

		// Calculate remaining time for minimum duration
		if (state) {
			const elapsed = Date.now() - state.startTime;
			const remaining = Math.max(0, this.config.minDuration - elapsed);

			if (remaining > 0) {
				await this._sleep(remaining);
			}
		}

		// Remove skeleton and show table
		skeleton.remove();
		table.style.display = state?.tableDisplay || "";

		// Clean up
		this._state.delete(tableId);
	}

	/**
	 * Replace element content with skeleton temporarily
	 * @param {string|Element} selector - Element selector
	 * @param {string} blueprint - Blueprint name
	 * @param {Object} options - Blueprint options
	 */
	static replace(selector, blueprint = "text", options = {}) {
		this.show(selector, blueprint, 1, options);
	}

	/**
	 * Restore original content
	 * @param {string|Element} selector - Element selector
	 * @returns {Promise}
	 */
	static async restore(selector) {
		return this.hide(selector);
	}

	/**
	 * Check if a container is currently showing a skeleton
	 * @param {string|Element} selector - Container selector
	 * @returns {boolean}
	 */
	static isLoading(selector) {
		const key = typeof selector === "string" ? selector : selector;
		return this._state.has(key);
	}

	/**
	 * Update configuration
	 * @param {Object} newConfig - Configuration options to update
	 */
	static configure(newConfig) {
		Object.assign(this.config, newConfig);
	}
}

// Export for module systems
if (typeof module !== "undefined" && module.exports) {
	module.exports = SkeletonFactory;
}

/**
 * Configure DataTables globally to use skeleton loading
 * This runs immediately when the script loads (after jQuery and DataTables)
 */
(function () {
	// Wait for jQuery to be ready
	if (typeof $ === "undefined" || typeof $.fn.dataTable === "undefined") {
		return;
	}

	// Create skeleton loading HTML for DataTables
	const createTableSkeletonRows = (columns = 6, rows = 5) => {
		let html = "";
		const cols = ["col-8", "col-6", "col-10", "col-4", "col-7", "col-5"];

		for (let r = 0; r < rows; r++) {
			let cells = "";
			for (let c = 0; c < columns; c++) {
				const col = cols[c % cols.length];
				cells += `<td><span class="placeholder placeholder-wave ${col} sk-placeholder-bg d-block"></span></td>`;
			}
			html += `<tr>${cells}</tr>`;
		}
		return html;
	};

	// Set global DataTables defaults for loading states
	$.extend(true, $.fn.dataTable.defaults, {
		language: {
			loadingRecords: createTableSkeletonRows(6, 5),
			processing: `
                <div class="d-flex justify-content-center align-items-center p-4">
                    <div class="placeholder placeholder-wave sk-placeholder-bg rounded w-100" style="height: 150px;"></div>
                </div>
            `,
			emptyTable: `
                <div class="text-center text-muted py-4">
                    <i class="ti ti-database-off fs-1 mb-2 d-block"></i>
                    No data available
                </div>
            `,
			zeroRecords: `
                <div class="text-center text-muted py-4">
                    <i class="ti ti-search-off fs-1 mb-2 d-block"></i>
                    No matching records found
                </div>
            `,
		},
	});

	console.log("SkeletonFactory: DataTables skeleton loading configured.");
})();
