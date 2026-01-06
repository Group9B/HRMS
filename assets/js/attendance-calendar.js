/**
 * Attendance Calendar Module
 * Displays a read-only employee attendance calendar for dashboards
 * Supports viewing past months back to employee's joining date
 * Compatible with HR, Manager, and Employee dashboards
 */

class AttendanceCalendar {
	constructor(config) {
		this.containerId = config.containerId;
		this.employeeId = config.employeeId || null;
		this.initialDate = config.initialDate
			? new Date(config.initialDate)
			: new Date();
		this.detailUrl =
			config.detailUrl || "/hrms/employee/attendance_detail.php";
		this.onDataLoaded = config.onDataLoaded || null;
		this.onDayClick = config.onDayClick || null;
		this.showModalOnClick = config.showModalOnClick !== false;
		this.readOnly = config.readOnly !== false; // Default to true
		this.showMonthNavigation = config.showMonthNavigation !== false;
		this.maxMonthsBack = config.maxMonthsBack || 12; // How many months back to allow
		this.joiningDate = config.joiningDate || null;
		this.companyCreatedAt = config.companyCreatedAt || null;
		this.onlyCurrentEmployee = config.onlyCurrentEmployee || false; // If true, only show current user's data
		this.skipEmployeeIdInApi = config.skipEmployeeIdInApi || false; // If true, don't send employee_id to API (for detail page dropdown)
		this.chartHeight = config.chartHeight || 300;
		this.chartWidth = config.chartWidth || null;

		this.currentMonth = new Date(this.initialDate);
		this.allAttendanceData = null;
		this.chartInstance = null;
		this.container = null;
		this.cursor = config.cursor || "pointer";

		this.init();
	}

	init() {
		this.container = document.getElementById(this.containerId);
		if (!this.container) {
			console.error(`Container with ID "${this.containerId}" not found.`);
			return;
		}

		this.render();
	}

	render() {
		this.container.innerHTML = "";
		this.ensureDayModal();

		// Create wrapper
		const wrapper = document.createElement("div");
		wrapper.className = "attendance-calendar-wrapper";

		// Add header with navigation
		if (this.showMonthNavigation) {
			wrapper.appendChild(this.createHeader());
		}

		// Add loading spinner
		const spinner = document.createElement("div");
		spinner.id = `${this.containerId}-spinner`;
		spinner.className = "text-center p-4";
		spinner.innerHTML = `
            <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;
		wrapper.appendChild(spinner);

		// Add employee cards container
		const cardsContainer = document.createElement("div");
		cardsContainer.id = `${this.containerId}-cards`;
		cardsContainer.className = "attendance-cards-container";
		cardsContainer.style.display = "none";
		wrapper.appendChild(cardsContainer);

		// Add no data message
		const noDataMsg = document.createElement("div");
		noDataMsg.id = `${this.containerId}-no-data`;
		noDataMsg.className = "alert alert-info text-center";
		noDataMsg.style.display = "none";
		wrapper.appendChild(noDataMsg);

		this.container.appendChild(wrapper);

		// Load attendance data
		this.loadAttendanceData();
	}

	createHeader() {
		const header = document.createElement("div");
		header.className = "attendance-calendar-header card shadow-sm mb-3 p-3";
		header.innerHTML = `
	            <div class="d-flex align-items-center flex-wrap gap-2">
	                <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-2">
	                    <div class="d-flex align-items-center justify-content-between flex-grow-1" style="min-width: 240px;">
	                        <button class="btn btn-sm border-0 prev-month-btn fs-4" title="Previous month">
	                            <i class="ti ti-chevron-left"></i>
	                        </button>
	                        <h6 class="m-0 mx-3 current-month-display" style="min-width: 150px; text-align: center;"></h6>
	                        <button class="btn btn-sm border-0 next-month-btn fs-4" title="Next month">
	                            <i class="ti ti-chevron-right"></i>
	                        </button>
	                    </div>
	                </div>
	            </div>
        `;

		// Add event listeners
		header
			.querySelector(".prev-month-btn")
			.addEventListener("click", () => this.previousMonth());
		header
			.querySelector(".next-month-btn")
			.addEventListener("click", () => this.nextMonth());

		this.headerElement = header;
		return header;
	}

	previousMonth() {
		const newMonth = new Date(this.currentMonth);
		newMonth.setMonth(newMonth.getMonth() - 1);

		// Check if we've gone back too far
		if (this.joiningDate) {
			const joiningDate = new Date(this.joiningDate);
			if (newMonth < joiningDate) {
				showToast(
					"Cannot go back further than your joining date.",
					"warning"
				);
				return;
			}
		}

		if (this.companyCreatedAt) {
			const companyDate = new Date(this.companyCreatedAt);
			if (newMonth < companyDate) {
				showToast(
					"Cannot go back further than company creation date.",
					"warning"
				);
				return;
			}
		}

		this.currentMonth = newMonth;
		this.loadAttendanceData();
	}

	nextMonth() {
		const newMonth = new Date(this.currentMonth);
		newMonth.setMonth(newMonth.getMonth() + 1);

		// Don't allow future months
		const today = new Date();
		today.setHours(0, 0, 0, 0);
		const firstDayOfNextMonth = new Date(
			today.getFullYear(),
			today.getMonth() + 1,
			1
		);

		if (newMonth > firstDayOfNextMonth) {
			showToast("Cannot view future months.", "warning");
			return;
		}

		this.currentMonth = newMonth;
		this.loadAttendanceData();
	}

	loadAttendanceData() {
		const monthString = this.currentMonth.toISOString().slice(0, 7);
		const spinner = document.getElementById(`${this.containerId}-spinner`);
		const cardsContainer = document.getElementById(
			`${this.containerId}-cards`
		);
		const noDataMsg = document.getElementById(
			`${this.containerId}-no-data`
		);

		if (spinner) spinner.style.display = "block";
		if (cardsContainer) cardsContainer.style.display = "none";
		if (noDataMsg) noDataMsg.style.display = "none";

		// Build API URL with parameters
		let apiUrl = `/hrms/api/api_attendance.php?action=get_attendance_data&month=${monthString}`;
		if (this.employeeId && !this.skipEmployeeIdInApi) {
			apiUrl += `&employee_id=${this.employeeId}`;
		}
		if (this.onlyCurrentEmployee) {
			apiUrl += `&current_user_only=1`;
		}

		fetch(apiUrl)
			.then((res) => res.json())
			.then((result) => {
				if (result.error || !result.employees) {
					const errorMsg =
						result.error || "Failed to load attendance data.";
					noDataMsg.innerHTML = `<i class="ti ti-alert-circle me-2"></i>${errorMsg}`;
					noDataMsg.style.display = "block";
					return;
				}

				this.allAttendanceData = result;
				this.updateMonthDisplay();
				this.renderEmployeeCards();
				this.renderStatistics();

				if (typeof this.onDataLoaded === "function") {
					this.onDataLoaded(result);
				}
			})
			.catch((err) => {
				console.error("Error loading attendance data:", err);
				noDataMsg.innerHTML =
					'<i class="ti ti-alert-circle me-2"></i>A network error occurred while loading attendance data.';
				noDataMsg.style.display = "block";
			})
			.finally(() => {
				if (spinner) spinner.style.display = "none";
			});
	}

	updateMonthDisplay() {
		const display = document.querySelector(".current-month-display");
		if (display) {
			display.textContent = this.currentMonth.toLocaleString("default", {
				month: "long",
				year: "numeric",
			});
		}
	}

	renderStatistics() {
		// This can be extended to show summary statistics
		// Currently handled in the main dashboard
	}

	renderEmployeeCards() {
		const {
			employees,
			month_details,
			company_holidays,
			saturday_policy,
			employee_leaves,
			company_created_at,
		} = this.allAttendanceData;
		const cardsContainer = document.getElementById(
			`${this.containerId}-cards`
		);
		const noDataMsg = document.getElementById(
			`${this.containerId}-no-data`
		);

		if (!employees || employees.length === 0) {
			noDataMsg.innerHTML =
				'<i class="ti ti-info-circle me-2"></i>No employee attendance data available for this period.';
			noDataMsg.style.display = "block";
			cardsContainer.style.display = "none";
			return;
		}

		cardsContainer.innerHTML = "";
		const today = new Date();
		today.setHours(0, 0, 0, 0);

		// Filter employees if employeeId is specified
		const employeesToRender = this.employeeId
			? employees.filter((emp) => emp.id === this.employeeId)
			: employees;

		employeesToRender.forEach((emp) => {
			const card = this.createEmployeeCard(
				emp,
				month_details,
				company_holidays,
				saturday_policy,
				employee_leaves,
				company_created_at,
				today
			);
			cardsContainer.appendChild(card);
		});

		cardsContainer.style.display = "block";
		noDataMsg.style.display = "none";
	}

	createEmployeeCard(
		emp,
		monthDetails,
		companyHolidays,
		saturdayPolicy,
		employeeLeaves,
		companyCreatedAt,
		today
	) {
		const card = document.createElement("div");
		card.className = "card shadow-sm hover-shadow-lg mb-3";

		let p = 0,
			a = 0,
			l = 0,
			h = 0,
			hd = 0;
		let calendarHtml = "";
		let linkEle = "";
		const weekdayLabels = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
		weekdayLabels.forEach((label) => {
			calendarHtml += `<div class="day-square day-header fw-semibold text-muted">${label}</div>`;
		});

		const firstDayOfMonth = new Date(
			monthDetails.year,
			monthDetails.month - 1,
			1
		);
		const startDayOffset = firstDayOfMonth.getDay();

		// Add empty cells for days before month starts
		for (let i = 0; i < startDayOffset; i++) {
			calendarHtml += `<div class="day-square empty"></div>`;
		}

		// Add days of month
		for (let day = 1; day <= monthDetails.days_in_month; day++) {
			const dateObj = new Date(
				monthDetails.year,
				monthDetails.month - 1,
				day
			);
			const year = dateObj.getFullYear();
			const month = String(dateObj.getMonth() + 1).padStart(2, "0");
			const date = String(dateObj.getDate()).padStart(2, "0");
			const dateStr = `${year}-${month}-${date}`;

			const attendance = emp.attendance[dateStr];
			let status = attendance?.status || "empty";
			let classes = "day-square";
			let title = "";
			const hasTimes =
				attendance && attendance.check_in && attendance.check_out;
			if (this.cursor && this.cursor != "pointer") {
				classes += ` cursor-${this.cursor}`;
			}
			if (companyHolidays[dateStr]) {
				status = "holiday";
				title = companyHolidays[dateStr];
			} else if (employeeLeaves[emp.id]?.[dateStr]) {
				status = "leave";
			}

			const dayOfWeek = dateObj.getDay();
			const isWeekendOff =
				dayOfWeek === 0 ||
				(dayOfWeek === 6 &&
					this.isSaturdayHoliday(day, saturdayPolicy));

			const companyCreatedAtDate = companyCreatedAt.split("T")[0];

			// Determine if day is disabled (read-only mode)
			const isDisabled =
				dateObj > today ||
				dateStr < emp.date_of_joining ||
				dateStr < companyCreatedAtDate ||
				(isWeekendOff && status === "empty") ||
				(status === "holiday" && !emp.attendance[dateStr]) ||
				(status === "leave" && !emp.attendance[dateStr]);

			classes += ` status-${status}`;
			if (isWeekendOff && status === "empty") {
				classes += " status-disabled";
				if (dayOfWeek === 0) classes += " status-sunday";
			}
			if (isDisabled) {
				classes += " status-disabled";
			}

			// Count statuses
			if (status !== "empty") {
				if (status === "present") p++;
				else if (status === "absent") a++;
				else if (status === "leave") l++;
				else if (status === "holiday") h++;
				else if (status === "half-day") hd++;
			}

			const dataAttrs = hasTimes
				? `data-date="${dateStr}" data-status="${status}" data-checkin="${attendance.check_in}" data-checkout="${attendance.check_out}" data-emp="${emp.name}"`
				: `data-date="${dateStr}"`;
			if (hasTimes) classes += " clickable";
			calendarHtml += `<div class="${classes.trim()}" title="${title}" ${dataAttrs}>${day}</div>`;
		}

		// Only show detail link if detailUrl is provided (not already on detail page)
		if (this.detailUrl) {
			linkEle = `<a class="btn btn-primary btn-sm d-flex align-items-center mt-4 gap-1" href="${this.detailUrl}">
	                    <i class="ti ti-link"></i><span>Attendance Detail</span>
	                </a>`;
		}

		card.innerHTML = `
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col">
                        <h6 class="mb-0 fw-bold text-primary">${emp.name}</h6>
                        <small class="text-body-secondary">${
							emp.designation || "N/A"
						}</small>
                    </div>
                    <div class="col-auto">
                        <small class="text-muted">Joined: ${new Date(
							emp.date_of_joining
						).toLocaleDateString("en-US", {
							year: "numeric",
							month: "short",
							day: "numeric",
						})}</small>
                    </div>
                </div>
                <div class="d-flex justify-content-around text-center small my-3">
                    <div><strong class="text-success">${p}</strong><br><span class="text-muted" style="font-size: 0.8em;">Present</span></div>
                    <div><strong class="text-danger">${a}</strong><br><span class="text-muted" style="font-size: 0.8em;">Absent</span></div>
                    <div><strong class="text-primary">${hd}</strong><br><span class="text-muted" style="font-size: 0.8em;">Half-day</span></div>
                    <div><strong class="text-warning">${l}</strong><br><span class="text-muted" style="font-size: 0.8em;">Leave</span></div>
                    <div><strong class="text-info">${h}</strong><br><span class="text-muted" style="font-size: 0.8em;">Holiday</span></div>
                </div>
                <div class="mini-cal" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px;">
                    ${calendarHtml}
                </div>
				${linkEle}
            </div>
        `;

		card.querySelectorAll(".day-square.clickable").forEach((el) => {
			el.addEventListener("click", () => {
				const payload = {
					empName: el.getAttribute("data-emp") || emp.name,
					date: el.getAttribute("data-date"),
					status: el.getAttribute("data-status"),
					checkIn: el.getAttribute("data-checkin"),
					checkOut: el.getAttribute("data-checkout"),
				};

				if (typeof this.onDayClick === "function") {
					this.onDayClick(payload);
				}

				if (this.showModalOnClick) {
					this.showDayModal(payload);
				}
			});
		});

		return card;
	}

	ensureDayModal() {
		if (document.getElementById("attendanceDayModal")) return;
		const modal = document.createElement("div");
		modal.id = "attendanceDayModal";
		modal.className = "modal fade";
		modal.tabIndex = -1;
		modal.innerHTML = `
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="attendanceDayModalLabel">Attendance Details</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="mb-2"><strong>Date:</strong> <span id="attendanceDayDate">-</span></div>
						<div class="mb-2"><strong>Status:</strong> <span id="attendanceDayStatus">-</span></div>
						<div class="mb-2"><strong>Check In:</strong> <span id="attendanceDayIn">-</span></div>
						<div class="mb-2"><strong>Check Out:</strong> <span id="attendanceDayOut">-</span></div>
						<div class="mb-2"><strong>Worked:</strong> <span id="attendanceDayHours">-</span></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		`;
		document.body.appendChild(modal);
	}

	showDayModal({ empName, date, status, checkIn, checkOut }) {
		const modalEl = document.getElementById("attendanceDayModal");
		if (!modalEl) return;

		const dayLabel = this.formatDateWithDay(date);
		const workedHours = this.calculateWorkedHours(checkIn, checkOut);

		modalEl.querySelector("#attendanceDayModalLabel").textContent =
			empName || "Attendance Details";
		modalEl.querySelector("#attendanceDayDate").textContent = dayLabel;
		modalEl.querySelector("#attendanceDayStatus").textContent = (
			status || ""
		).replace(/\b\w/g, (c) => c.toUpperCase());
		modalEl.querySelector("#attendanceDayIn").textContent =
			this.formatTimeString(checkIn);
		modalEl.querySelector("#attendanceDayOut").textContent =
			this.formatTimeString(checkOut);
		modalEl.querySelector("#attendanceDayHours").textContent = workedHours;

		const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
		modal.show();
	}

	formatTimeString(timeStr) {
		if (!timeStr) return "-";
		const [h, m] = timeStr.split(":");
		const d = new Date();
		d.setHours(parseInt(h, 10), parseInt(m, 10), 0, 0);
		return d.toLocaleTimeString([], {
			hour: "2-digit",
			minute: "2-digit",
			hour12: true,
		});
	}

	formatDateWithDay(dateStr) {
		if (!dateStr) return "-";
		const d = new Date(dateStr + "T00:00:00");
		return d.toLocaleDateString("en-US", {
			weekday: "long",
			year: "numeric",
			month: "long",
			day: "numeric",
		});
	}

	calculateWorkedHours(checkIn, checkOut) {
		if (!checkIn || !checkOut) return "-";
		const [inH, inM] = checkIn.split(":").map(Number);
		const [outH, outM] = checkOut.split(":").map(Number);
		const inDate = new Date();
		inDate.setHours(inH, inM, 0, 0);
		const outDate = new Date();
		outDate.setHours(outH, outM, 0, 0);
		let diffMinutes = Math.max(0, (outDate - inDate) / (1000 * 60));
		const hours = Math.floor(diffMinutes / 60);
		const mins = Math.round(diffMinutes % 60);
		return `${hours}:${mins.toString().padStart(2, "0")}`;
	}

	isSaturdayHoliday(day, policy) {
		const weekNum = Math.ceil(day / 7);
		return (
			policy === "all" ||
			(policy === "1st_3rd" && (weekNum === 1 || weekNum === 3)) ||
			(policy === "2nd_4th" && (weekNum === 2 || weekNum === 4))
		);
	}

	destroy() {
		if (this.chartInstance) {
			this.chartInstance.destroy();
		}
		if (this.container) {
			this.container.innerHTML = "";
		}
	}

	// Public methods for external control
	goToMonth(date) {
		this.currentMonth = new Date(date);
		this.loadAttendanceData();
	}

	goToToday() {
		this.currentMonth = new Date();
		this.loadAttendanceData();
	}

	refresh() {
		this.loadAttendanceData();
	}
}

/**
 * Utility function to initialize attendance calendar from HTML data attribute
 * Usage: <div data-attendance-calendar='{"employeeId": 123, "containerId": "calendar"}' id="calendar"></div>
 */
function initializeAttendanceCalendars() {
	document
		.querySelectorAll("[data-attendance-calendar]")
		.forEach((element) => {
			try {
				const config = JSON.parse(
					element.getAttribute("data-attendance-calendar")
				);
				config.containerId = element.id || `calendar-${Date.now()}`;
				if (!element.id) element.id = config.containerId;
				new AttendanceCalendar(config);
			} catch (e) {
				console.error("Error initializing attendance calendar:", e);
			}
		});
}

// Auto-initialize on DOM ready
if (document.readyState === "loading") {
	document.addEventListener(
		"DOMContentLoaded",
		initializeAttendanceCalendars
	);
} else {
	initializeAttendanceCalendars();
}
