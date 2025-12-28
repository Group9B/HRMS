/**
 * Attendance Check-In/Check-Out Module
 * Modular component for HR, Manager, and Employee dashboards
 * Intelligently tracks work hours and marks half-day attendance
 */

class AttendanceCheckIn {
	constructor(config) {
		this.containerId = config.containerId;
		this.buttonContainerId = config.buttonContainerId || null;
		this.userId = config.userId || null;
		this.employeeId = config.employeeId || null;
		this.companyId = config.companyId || null;
		this.showDetailedTime = config.showDetailedTime !== false; // Show check-in/out times
		this.allowCheckIn = config.allowCheckIn !== false;
		this.allowCheckOut = config.allowCheckOut !== false;
		this.refreshInterval = config.refreshInterval || 60000; // Refresh every minute
		this.shiftStartTime = config.shiftStartTime || "09:00"; // Default 9 AM
		this.shiftEndTime = config.shiftEndTime || "17:00"; // Default 5 PM
		this.halfDayThreshold = config.halfDayThreshold || 0.5; // Mark half-day if < 50% shift completed

		this.container = null;
		this.buttonContainer = null;
		this.refreshTimer = null;

		this.init();
	}

	init() {
		this.container = document.getElementById(this.containerId);
		if (!this.container) {
			console.error(`Container with ID "${this.containerId}" not found.`);
			return;
		}

		if (this.buttonContainerId) {
			this.buttonContainer = document.getElementById(
				this.buttonContainerId
			);
		}

		// Validate device time on initialization
		this.validateDeviceTime();

		this.render();
		this.loadAttendanceStatus();

		// Auto-refresh status
		this.refreshTimer = setInterval(
			() => this.loadAttendanceStatus(),
			this.refreshInterval
		);
	}

	validateDeviceTime() {
		// Get current client time info using UTC-based approach
		const now = new Date();
		const timezoneOffset = now.getTimezoneOffset(); // In minutes, negative for ahead of UTC

		// Store for use in check-in/out requests
		this.clientTimestamp = Math.floor(now.getTime() / 1000); // Unix timestamp in seconds
		this.tzOffset = timezoneOffset; // In minutes

		// Warn if time seems very off (e.g., way too early or late)
		const hour = now.getHours();
		if (hour < 5 || hour > 23) {
			console.warn(
				"Device time appears unusual. Please verify your system clock is accurate."
			);
		}
	}

	render() {
		this.container.innerHTML = `
			<!-- Work Hours Summary -->
			<div id="${this.containerId}-summary" class="p-3 mb-4 bg-info-subtle rounded-end remove-b" style="display: none;">
				<div class="d-flex justify-content-between align-items-start mb-3">
					<div>
						<p class="text-muted mb-1 small">Total Work Hours</p>
						<h4 id="${this.containerId}-hours" class="mb-0 fw-bold">0.00 hrs</h4>
					</div>
					<small id="${this.containerId}-attendance-status" class="bg-info px-2 py-1 rounded text-black fw-semibold" style="display: none;">Present</small>
				</div>
				<small class="text-muted">
					<i class="ti ti-circle-check me-1"></i>Attendance Recorded
				</small>
			</div>

			<!-- Loading State -->
			<div id="${this.containerId}-loading" class="text-center py-5">
				<div class="spinner-border spinner-border-sm text-primary" role="status">
					<span class="visually-hidden">Loading...</span>
				</div>
				<p class="text-muted mt-3 mb-0">Loading attendance status...</p>
			</div>

			<!-- Timeline -->
			<div id="${this.containerId}-timeline" class="row g-4" style="display: none;">
				<div class="col-6">
					<div class="d-flex align-items-start gap-3">
						<div id="${this.containerId}-checkin-icon" class="icon-box bg-success-subtle text-success-emphasis">
							<i class="ti ti-login"></i>
						</div>
						<div>
							<p class="text-muted mb-1 small">Clock In</p>
							<h6 id="${this.containerId}-checkin-time" class="mb-0 fw-semibold">--:-- --</h6>
							<small id="${this.containerId}-checkin-status" class="d-none"></small>
						</div>
					</div>
				</div>

				<div class="col-6">
					<div class="d-flex align-items-start gap-3">
						<div id="${this.containerId}-checkout-icon" class="icon-box bg-danger-subtle text-danger-emphasis">
							<i class="ti ti-logout"></i>
						</div>
						<div>
							<p class="text-muted mb-1 small">Clock Out</p>
							<h6 id="${this.containerId}-checkout-time" class="mb-0 fw-semibold">--:-- --</h6>
							<small id="${this.containerId}-checkout-status" class="d-none"></small>
						</div>
					</div>
				</div>
			</div>

			<!-- Error State -->
			<div id="${this.containerId}-error" class="alert alert-danger" role="alert" style="display: none;">
				<i class="ti ti-alert-circle me-2"></i>
				<span id="${this.containerId}-error-message">Failed to load attendance status</span>
			</div>

			<!-- Not Clocked In State -->
			<div id="${this.containerId}-notchecked" class="text-center py-4" style="display: none;">
				<div class="icon-box bg-warning-subtle text-warning-emphasis mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.8rem;">
					<i class="ti ti-clock-off"></i>
				</div>
				<p class="text-muted mb-0">You have not clocked in today yet.</p>
			</div>

			<!-- Actions -->
			<div id="${this.containerId}-actions" class="d-flex gap-2 mt-4" style="display: none;">
				<button class="checkin-btn btn btn-success flex-grow-1">
					<i class="ti ti-login me-1"></i> Clock In
				</button>
				<button class="checkout-btn btn btn-danger flex-grow-1">
					<i class="ti ti-logout me-1"></i> Clock Out
				</button>
			</div>
		`;

		// Attach event listeners
		const checkInBtn = this.container.querySelector(".checkin-btn");
		const checkOutBtn = this.container.querySelector(".checkout-btn");

		if (checkInBtn) {
			checkInBtn.addEventListener("click", () => this.handleCheckIn());
		}
		if (checkOutBtn) {
			checkOutBtn.addEventListener("click", () => this.handleCheckOut());
		}
	}

	loadAttendanceStatus() {
		let apiUrl =
			"/hrms/api/api_employee_attendance.php?action=get_today_status";
		if (this.employeeId) {
			apiUrl += `&employee_id=${this.employeeId}`;
		}

		fetch(apiUrl)
			.then((res) => res.json())
			.then((result) => this.renderStatus(result))
			.catch((err) => {
				console.error("Error loading attendance status:", err);
				this.renderErrorStatus("Failed to load attendance status");
			});
	}

	renderStatus(result) {
		// Hide loading state
		const loadingDiv = document.getElementById(
			`${this.containerId}-loading`
		);
		if (loadingDiv) loadingDiv.style.display = "none";

		// Hide error state
		const errorDiv = document.getElementById(`${this.containerId}-error`);
		if (errorDiv) errorDiv.style.display = "none";

		const timelineDiv = document.getElementById(
			`${this.containerId}-timeline`
		);
		const notCheckedDiv = document.getElementById(
			`${this.containerId}-notchecked`
		);
		const summaryDiv = document.getElementById(
			`${this.containerId}-summary`
		);
		const badgeDiv = document.getElementById(`${this.containerId}-badge`);
		const actionsDiv = document.getElementById(
			`${this.containerId}-actions`
		);

		if (!result.success) {
			this.renderErrorStatus("Could not load attendance status");
			return;
		}

		const data = result.data;
		let canCheckIn = this.allowCheckIn;
		let canCheckOut = this.allowCheckOut;

		if (data && data.check_in) {
			if (timelineDiv) timelineDiv.style.display = "flex";
			if (notCheckedDiv) notCheckedDiv.style.display = "none";

			const checkInTime = this.formatTime(data.check_in);
			const checkInStatusEl = document.getElementById(
				`${this.containerId}-checkin-time`
			);
			const checkInStatusBadge = document.getElementById(
				`${this.containerId}-checkin-status`
			);

			if (checkInStatusEl) {
				checkInStatusEl.textContent = checkInTime;
				if (data.check_in_status && checkInStatusBadge) {
					checkInStatusBadge.textContent = `(${data.check_in_status})`;
					checkInStatusBadge.className =
						data.check_in_status === "Late"
							? "d-block text-warning"
							: "d-block text-info";
				} else if (checkInStatusBadge) {
					checkInStatusBadge.className = "d-none";
				}
			}

			if (data.check_out) {
				// Fully clocked out
				const checkOutTime = this.formatTime(data.check_out);
				const checkOutStatusEl = document.getElementById(
					`${this.containerId}-checkout-time`
				);
				const checkOutStatusBadge = document.getElementById(
					`${this.containerId}-checkout-status`
				);
				const workHours = data.work_hours_decimal || 0;

				if (checkOutStatusEl) {
					checkOutStatusEl.textContent = checkOutTime;
					if (data.check_out_status && checkOutStatusBadge) {
						checkOutStatusBadge.textContent = `(${data.check_out_status})`;
						checkOutStatusBadge.className =
							data.check_out_status === "Early Out"
								? "d-block text-danger"
								: "d-block text-info";
					} else if (checkOutStatusBadge) {
						checkOutStatusBadge.className = "d-none";
					}
				}

				// Show summary
				if (summaryDiv) summaryDiv.style.display = "block";
				const hoursDisplay = document.getElementById(
					`${this.containerId}-hours`
				);
				if (hoursDisplay) {
					hoursDisplay.textContent = `${workHours.toFixed(2)} hrs`;
				}

				// Show attendance status
				const attendanceStatusEl = document.getElementById(
					`${this.containerId}-attendance-status`
				);
				if (attendanceStatusEl && data.status) {
					attendanceStatusEl.textContent =
						data.status.charAt(0).toUpperCase() +
						data.status.slice(1);
					attendanceStatusEl.style.display = "inline-block";
				}

				if (badgeDiv) {
					badgeDiv.style.display = "inline-block";
					badgeDiv.textContent = "Recorded";
					badgeDiv.className =
						"badge bg-info-subtle text-info-emphasis";
				}

				canCheckIn = false;
				canCheckOut = false;
			} else {
				// Only checked in
				const checkOutStatusEl = document.getElementById(
					`${this.containerId}-checkout-time`
				);
				const checkOutStatusBadge = document.getElementById(
					`${this.containerId}-checkout-status`
				);

				if (checkOutStatusEl) {
					checkOutStatusEl.textContent = "--:-- --";
					if (checkOutStatusBadge) {
						checkOutStatusBadge.className = "d-none";
					}
				}

				// Calculate hours worked
				const today = new Date();
				const [hours, minutes, seconds] = data.check_in.split(":");
				const checkInDate = new Date(
					today.getFullYear(),
					today.getMonth(),
					today.getDate(),
					parseInt(hours),
					parseInt(minutes),
					parseInt(seconds)
				);
				const currentTime = new Date();
				const hoursWorked =
					(currentTime - checkInDate) / (1000 * 60 * 60);

				// Show summary with partial hours
				if (summaryDiv) summaryDiv.style.display = "block";
				const hoursDisplay = document.getElementById(
					`${this.containerId}-hours`
				);
				if (hoursDisplay) {
					hoursDisplay.textContent = `${hoursWorked.toFixed(2)} hrs`;
				}
				if (badgeDiv) {
					badgeDiv.textContent = "In Progress";
					badgeDiv.className =
						"badge bg-info-subtle text-info-emphasis";
					badgeDiv.style.display = "inline-block";
				}

				canCheckIn = false;
				canCheckOut = this.allowCheckOut;
			}
		} else {
			// Not clocked in yet
			if (timelineDiv) timelineDiv.style.display = "none";
			if (notCheckedDiv) notCheckedDiv.style.display = "block";
			if (summaryDiv) summaryDiv.style.display = "none";
			if (badgeDiv) badgeDiv.style.display = "none";

			canCheckIn = this.allowCheckIn;
			canCheckOut = false;
		}

		// Update button states
		if (actionsDiv) {
			const checkInBtn = actionsDiv.querySelector(".checkin-btn");
			const checkOutBtn = actionsDiv.querySelector(".checkout-btn");

			if (checkInBtn) {
				if (canCheckIn) {
					checkInBtn.disabled = false;
					checkInBtn.classList.remove("disabled");
				} else {
					checkInBtn.disabled = true;
					checkInBtn.classList.add("disabled");
				}
			}

			if (checkOutBtn) {
				if (canCheckOut) {
					checkOutBtn.disabled = false;
					checkOutBtn.classList.remove("disabled");
				} else {
					checkOutBtn.disabled = true;
					checkOutBtn.classList.add("disabled");
				}
			}

			actionsDiv.style.display = "flex";
		}
	}

	renderErrorStatus(message) {
		// Hide other sections
		const loadingDiv = document.getElementById(
			`${this.containerId}-loading`
		);
		const timelineDiv = document.getElementById(
			`${this.containerId}-timeline`
		);
		const notCheckedDiv = document.getElementById(
			`${this.containerId}-notchecked`
		);
		const summaryDiv = document.getElementById(
			`${this.containerId}-summary`
		);
		const actionsDiv = document.getElementById(
			`${this.containerId}-actions`
		);
		const errorDiv = document.getElementById(`${this.containerId}-error`);
		const errorMsgSpan = document.getElementById(
			`${this.containerId}-error-message`
		);

		if (loadingDiv) loadingDiv.style.display = "none";
		if (timelineDiv) timelineDiv.style.display = "none";
		if (notCheckedDiv) notCheckedDiv.style.display = "none";
		if (summaryDiv) summaryDiv.style.display = "none";
		if (actionsDiv) actionsDiv.style.display = "none";

		if (errorDiv) {
			errorDiv.style.display = "block";
			if (errorMsgSpan) {
				errorMsgSpan.textContent = message;
			}
		}
	}

	handleCheckIn() {
		const btn = this.container.querySelector(".checkin-btn");
		const originalHTML = btn.innerHTML;
		btn.disabled = true;
		btn.innerHTML =
			'<span class="spinner-border spinner-border-sm me-2"></span>Clocking in...';

		let apiUrl = "/hrms/api/api_employee_attendance.php?action=check_in";
		if (this.employeeId) {
			apiUrl += `&employee_id=${this.employeeId}`;
		}

		// Send Unix timestamp and timezone offset
		const now = new Date();
		const timestamp = Math.floor(now.getTime() / 1000); // Unix timestamp in seconds
		const tzOffset = now.getTimezoneOffset(); // In minutes
		apiUrl += `&client_timestamp=${timestamp}&tz_offset=${tzOffset}`;

		fetch(apiUrl, { method: "POST" })
			.then((res) => res.json())
			.then((result) => {
				if (result.success) {
					showToast(
						result.message || "Successfully clocked in!",
						"success"
					);
					this.loadAttendanceStatus();
				} else {
					showToast(result.message || "Failed to clock in", "error");
					btn.disabled = false;
					btn.innerHTML = originalHTML;
				}
			})
			.catch((err) => {
				console.error("Check-in error:", err);
				showToast(
					"A network error occurred. Please try again.",
					"error"
				);
				btn.disabled = false;
				btn.innerHTML = originalHTML;
			});
	}

	handleCheckOut() {
		const btn = this.container.querySelector(".checkout-btn");
		const originalHTML = btn.innerHTML;
		const confirmMessage =
			"You are about to clock out. This will finalize your attendance for today.";

		if (!confirm(confirmMessage)) {
			return;
		}

		btn.disabled = true;
		btn.innerHTML =
			'<span class="spinner-border spinner-border-sm me-2"></span>Clocking out...';

		let apiUrl = "/hrms/api/api_employee_attendance.php?action=check_out";
		if (this.employeeId) {
			apiUrl += `&employee_id=${this.employeeId}`;
		}

		// Send Unix timestamp and timezone offset (timezone-safe approach)
		const now = new Date();
		const timestamp = Math.floor(now.getTime() / 1000); // Unix timestamp in seconds
		const tzOffset = now.getTimezoneOffset(); // In minutes
		apiUrl += `&client_timestamp=${timestamp}&tz_offset=${tzOffset}`;

		fetch(apiUrl, { method: "POST" })
			.then((res) => res.json())
			.then((result) => {
				if (result.success) {
					showToast(
						result.message || "Successfully clocked out!",
						"success"
					);
					this.loadAttendanceStatus();
				} else {
					showToast(result.message || "Failed to clock out", "error");
					btn.disabled = false;
					btn.innerHTML = originalHTML;
				}
			})
			.catch((err) => {
				console.error("Check-out error:", err);
				showToast(
					"A network error occurred. Please try again.",
					"error"
				);
				btn.disabled = false;
				btn.innerHTML = originalHTML;
			});
	}

	formatTime(timeString) {
		if (!timeString) return "";
		const [hour, minute] = timeString.split(":");
		const date = new Date();
		date.setHours(hour, minute);
		return date.toLocaleTimeString([], {
			hour: "2-digit",
			minute: "2-digit",
			hour12: true,
		});
	}

	/**
	 * Calculate work hours and determine if it's a half-day
	 * @param {string} checkInTime - Check-in time (HH:MM format)
	 * @param {string} checkOutTime - Check-out time (HH:MM format)
	 * @returns {object} - {workHours: number, isHalfDay: boolean, workPercentage: number}
	 */
	calculateWorkHours(checkInTime, checkOutTime) {
		const [inHour, inMin] = checkInTime.split(":").map(Number);
		const [outHour, outMin] = checkOutTime.split(":").map(Number);
		const [shiftStartHour, shiftStartMin] = this.shiftStartTime
			.split(":")
			.map(Number);
		const [shiftEndHour, shiftEndMin] = this.shiftEndTime
			.split(":")
			.map(Number);

		const checkInMinutes = inHour * 60 + inMin;
		const checkOutMinutes = outHour * 60 + outMin;
		const shiftStartMinutes = shiftStartHour * 60 + shiftStartMin;
		const shiftEndMinutes = shiftEndHour * 60 + shiftEndMin;

		const workMinutes = checkOutMinutes - checkInMinutes;
		const shiftMinutes = shiftEndMinutes - shiftStartMinutes;

		const workHours = workMinutes / 60;
		const workPercentage = workMinutes / shiftMinutes;
		const isHalfDay = workPercentage < this.halfDayThreshold;

		return {
			workHours: workHours,
			isHalfDay: isHalfDay,
			workPercentage: workPercentage,
			attendanceStatus: isHalfDay ? "half-day" : "present",
		};
	}

	refresh() {
		this.loadAttendanceStatus();
	}

	destroy() {
		if (this.refreshTimer) {
			clearInterval(this.refreshTimer);
		}
		if (this.container) {
			this.container.innerHTML = "";
		}
	}
}

/**
 * Utility function to initialize attendance check-in from HTML data attribute
 * Usage: <div data-attendance-checkin='{"containerId": "checkin"}' id="checkin"></div>
 */
function initializeAttendanceCheckins() {
	document
		.querySelectorAll("[data-attendance-checkin]")
		.forEach((element) => {
			try {
				const config = JSON.parse(
					element.getAttribute("data-attendance-checkin")
				);
				config.containerId = element.id || `checkin-${Date.now()}`;
				if (!element.id) element.id = config.containerId;
				new AttendanceCheckIn(config);
			} catch (e) {
				console.error("Error initializing attendance check-in:", e);
			}
		});
}

// Auto-initialize on DOM ready
if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", initializeAttendanceCheckins);
} else {
	initializeAttendanceCheckins();
}
