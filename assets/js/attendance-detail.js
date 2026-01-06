/**
 * Attendance Detail Page Controller
 * Manages the attendance detail view with calendar and table
 * Role-based access control and data fetching
 */

class AttendanceDetailPage {
	constructor(config) {
		// Configuration
		this.roleName = config.roleName || "unknown";
		this.userId = config.userId;
		this.currentEmployeeId = config.currentEmployeeId;
		this.selectedEmployeeId = config.selectedEmployeeId;
		this.canViewOthers = config.canViewOthers || false;
		this.canViewSelf = config.canViewSelf || false;
		this.showDropdown = config.showDropdown || false;

		// State
		this.month = new Date();
		this.calendar = null;
		this.latestData = null;

		// DOM Elements
		this.tableBody = document.querySelector("#attendanceTable tbody");
		this.summaryChips = document.getElementById("summaryChips");
		this.employeeSelect = document.getElementById("employeeSelect");
		this.monthLabel = document.getElementById("monthLabel");
		this.prevBtn = document.getElementById("prevMonthBtn");
		this.nextBtn = document.getElementById("nextMonthBtn");

		// Validate role access
		const allowedRoles = ["company_owner", "hr", "manager", "employee"];
		if (!allowedRoles.includes(this.roleName)) {
			console.error("Unauthorized role:", this.roleName);
			showToast("Unauthorized access", "error");
			return;
		}

		console.log("Initializing Attendance Detail Page", {
			roleName: this.roleName,
			showDropdown: this.showDropdown,
			selectedEmployeeId: this.selectedEmployeeId,
		});

		this.init();
	}

	init() {
		this.bindEvents();
		this.initCalendar();
		this.updateMonthLabel();
	}

	bindEvents() {
		if (this.prevBtn) {
			this.prevBtn.addEventListener("click", () => this.shiftMonth(-1));
		}
		if (this.nextBtn) {
			this.nextBtn.addEventListener("click", () => this.shiftMonth(1));
		}
		if (this.employeeSelect && this.showDropdown) {
			this.employeeSelect.addEventListener("change", (e) => {
				const val = parseInt(e.target.value, 10);
				if (!isNaN(val) && val > 0) {
					this.selectedEmployeeId = val;
					if (this.calendar) {
						this.calendar.employeeId = val;
						this.calendar.loadAttendanceData();
					}
				}
			});
		}
	}

	initCalendar() {
		try {
			this.calendar = new AttendanceCalendar({
				containerId: "attendance-calendar",
				employeeId: this.selectedEmployeeId,
				skipEmployeeIdInApi: true, // Don't send to API, only filter display
				initialDate: this.month,
				showMonthNavigation: false,
				onDataLoaded: (data) => this.handleDataLoaded(data),
				onDayClick: (payload) => this.highlightRow(payload.date),
				detailUrl: null,
			});
			// Set employeeId after initialization for frontend filtering only
			if (this.calendar) {
				this.calendar.employeeId = this.selectedEmployeeId;
			}
		} catch (error) {
			console.error("Error initializing calendar:", error);
			showToast("Failed to initialize calendar", "error");
		}
	}

	shiftMonth(delta) {
		const newMonth = new Date(this.month);
		newMonth.setMonth(newMonth.getMonth() + delta);

		// Prevent navigating to future months (beyond current month)
		const today = new Date();
		const currentMonthStart = new Date(
			today.getFullYear(),
			today.getMonth(),
			1
		);
		currentMonthStart.setHours(0, 0, 0, 0);

		const newMonthStart = new Date(
			newMonth.getFullYear(),
			newMonth.getMonth(),
			1
		);
		newMonthStart.setHours(0, 0, 0, 0);

		if (newMonthStart > currentMonthStart) {
			showToast("Cannot navigate to future months", "warning");
			return;
		}

		// Prevent navigating before company creation date
		if (this.latestData?.company_created_at) {
			const companyCreatedDate = new Date(
				this.latestData.company_created_at
			);
			companyCreatedDate.setDate(1); // Set to first day for month comparison
			companyCreatedDate.setHours(0, 0, 0, 0);
			if (newMonth < companyCreatedDate) {
				showToast(
					"Cannot navigate before company creation date",
					"warning"
				);
				return;
			}
		}

		this.month = newMonth;
		this.updateMonthLabel();
		if (this.calendar) {
			this.calendar.currentMonth = new Date(this.month);
			this.calendar.loadAttendanceData();
		}
	}

	handleDataLoaded(data) {
		console.log("Data loaded:", data);
		this.latestData = data;

		if (this.showDropdown) {
			this.populateEmployeeSelect(data.employees || []);
		}

		// Calculate summary for the selected employee only, not all employees
		const selectedEmpData = (data.employees || []).find(
			(e) => e.id === this.selectedEmployeeId
		);
		const perEmployeeSummary = this.calculateEmployeeSummary(
			selectedEmpData,
			data.month_details
		);

		this.renderSummary(perEmployeeSummary);
		this.renderTable(data);
		this.updateMonthLabel(data.month_details);
	}

	populateEmployeeSelect(employees) {
		if (!this.employeeSelect || !this.showDropdown) return;

		if (!employees || employees.length === 0) {
			this.employeeSelect.innerHTML =
				'<option value="">No employees found</option>';
			return;
		}

		this.employeeSelect.innerHTML = "";
		const frag = document.createDocumentFragment();

		employees.forEach((emp) => {
			const opt = document.createElement("option");
			opt.value = emp.id;
			opt.textContent = emp.name;
			if (emp.id === this.selectedEmployeeId) {
				opt.selected = true;
			}
			frag.appendChild(opt);
		});

		this.employeeSelect.appendChild(frag);

		// If selected employee not in list, select first
		const selectedExists = employees.some(
			(e) => e.id === this.selectedEmployeeId
		);
		if (!selectedExists && employees.length > 0) {
			this.selectedEmployeeId = employees[0].id;
			this.employeeSelect.value = this.selectedEmployeeId;
			if (this.calendar) {
				this.calendar.employeeId = this.selectedEmployeeId;
			}
		}
	}

	calculateEmployeeSummary(employee, monthDetails) {
		const summary = {
			total_present: 0,
			total_absent: 0,
			total_leave: 0,
			total_half_day: 0,
			total_holiday: 0,
		};

		if (!employee || !monthDetails) {
			console.log(
				"calculateEmployeeSummary: Missing employee or monthDetails"
			);
			return summary;
		}

		const { company_holidays, employee_leaves } = this.latestData || {};
		console.log(
			"calculateEmployeeSummary for:",
			employee.name,
			"(ID:",
			employee.id,
			")"
		);
		console.log(
			"Month:",
			monthDetails.year,
			"-",
			monthDetails.month,
			"Days:",
			monthDetails.days_in_month
		);
		console.log("Employee leaves data:", employee_leaves?.[employee.id]);

		// Iterate through all days of the month (same logic as calendar)
		for (let day = 1; day <= monthDetails.days_in_month; day++) {
			const dateObj = new Date(
				monthDetails.year,
				monthDetails.month - 1,
				day
			);
			// Use local date components to avoid timezone issues (same as calendar)
			const year = dateObj.getFullYear();
			const month = String(dateObj.getMonth() + 1).padStart(2, "0");
			const date = String(dateObj.getDate()).padStart(2, "0");
			const dateStr = `${year}-${month}-${date}`;

			// Check attendance record first
			const attendance = employee.attendance?.[dateStr];
			let status = attendance?.status || "empty";

			// Override with holiday if exists
			if (company_holidays && company_holidays[dateStr]) {
				status = "holiday";
			}
			// Override with leave if exists (even if no attendance record)
			else if (
				employee_leaves &&
				employee_leaves[employee.id]?.[dateStr]
			) {
				status = "leave";
				console.log(
					"  Day",
					day,
					"(" + dateStr + "): Found in employee_leaves, status=leave"
				);
			}

			// Count only non-empty statuses
			if (status !== "empty") {
				const key = "total_" + status.replace("-", "_");
				if (summary.hasOwnProperty(key)) {
					summary[key]++;
				}
			}
		}
		console.log("Final summary:", summary);

		return summary;
	}

	renderSummary(summary) {
		if (!this.summaryChips) return;

		const chips = [
			{
				label: "Present",
				value: summary.total_present || 0,
				color: "success",
				icon: "ti-check",
			},
			{
				label: "Absent",
				value: summary.total_absent || 0,
				color: "danger",
				icon: "ti-x",
			},
			{
				label: "Leave",
				value: summary.total_leave || 0,
				color: "warning",
				icon: "ti-plane",
			},
			{
				label: "Half Day",
				value: summary.total_half_day || 0,
				color: "primary",
				icon: "ti-clock",
			},
			{
				label: "Holiday",
				value: summary.total_holiday || 0,
				color: "info",
				icon: "ti-calendar-off",
			},
		];

		this.summaryChips.innerHTML = chips
			.map(
				(c) =>
					`<span class="badge bg-${c.color}-subtle text-${c.color} px-3 py-2 d-inline-flex align-items-center gap-2">
						<i class="ti ${c.icon}"></i>
						<span>${c.label}: <strong>${c.value}</strong></span>
					</span>`
			)
			.join("");
	}

	renderTable(data) {
		if (!this.tableBody) return;

		const employees = data.employees || [];
		const emp = employees.find((e) => e.id === this.selectedEmployeeId);

		if (!emp) {
			this.tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">
				<i class="ti ti-user-off fs-2 d-block mb-2"></i>
				No data for this employee.
			</td></tr>`;
			return;
		}

		const month = data.month_details;
		if (!month) {
			this.tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">
				<i class="ti ti-calendar-off fs-2 d-block mb-2"></i>
				No month data available.
			</td></tr>`;
			return;
		}

		const rows = [];
		const today = new Date();
		today.setHours(0, 0, 0, 0);

		for (let day = 1; day <= month.days_in_month; day++) {
			const dateObj = new Date(month.year, month.month - 1, day);

			// Skip future dates
			if (dateObj > today) continue;

			const dateStr = dateObj.toISOString().slice(0, 10);
			const att = emp.attendance?.[dateStr] || {};
			const status = att.status || "-";
			const checkIn = att.check_in || "";
			const checkOut = att.check_out || "";
			const worked = this.calculateWorkedHours(checkIn, checkOut);

			rows.push(`
				<tr data-date="${dateStr}" class="cursor-pointer">
					<td>${this.formatDateWithDay(dateObj)}</td>
					<td>${this.renderStatusBadge(status)}</td>
					<td>${checkIn ? this.formatTime(checkIn) : "-"}</td>
					<td>${checkOut ? this.formatTime(checkOut) : "-"}</td>
					<td>${worked}</td>
				</tr>
			`);
		}

		if (rows.length === 0) {
			this.tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">
				<i class="ti ti-calendar-search fs-2 d-block mb-2"></i>
				No attendance records found for this period.
			</td></tr>`;
			return;
		}

		this.tableBody.innerHTML = rows.join("");

		// Attach click events
		this.tableBody.querySelectorAll("tr[data-date]").forEach((row) => {
			row.addEventListener("click", () => {
				const date = row.getAttribute("data-date");
				const att = emp.attendance?.[date] || {};
				if (att.check_in && att.check_out && this.calendar) {
					this.calendar.showDayModal({
						empName: emp.name,
						date,
						status: att.status,
						checkIn: att.check_in,
						checkOut: att.check_out,
					});
				}
				this.highlightRow(date);
			});
		});
	}

	highlightRow(dateStr) {
		if (!this.tableBody) return;
		this.tableBody.querySelectorAll("tr").forEach((row) => {
			if (row.getAttribute("data-date") === dateStr) {
				row.classList.add("table-active");
				row.scrollIntoView({ behavior: "smooth", block: "center" });
			} else {
				row.classList.remove("table-active");
			}
		});
	}

	renderStatusBadge(status) {
		const s = (status || "-").toLowerCase();
		const statusMap = {
			present: { color: "success", icon: "ti-check", label: "Present" },
			absent: { color: "danger", icon: "ti-x", label: "Absent" },
			"half-day": {
				color: "primary",
				icon: "ti-clock",
				label: "Half Day",
			},
			leave: { color: "warning", icon: "ti-plane", label: "Leave" },
			holiday: {
				color: "info",
				icon: "ti-calendar-off",
				label: "Holiday",
			},
		};

		const config = statusMap[s] || {
			color: "secondary",
			icon: "ti-minus",
			label: "Not Marked",
		};

		return `<span class="badge bg-${config.color}-subtle text-${config.color}">
			<i class="ti ${config.icon} me-1"></i>${config.label}
		</span>`;
	}

	formatTime(timeStr) {
		if (!timeStr) return "-";
		try {
			const [h, m] = timeStr.split(":");
			const d = new Date();
			d.setHours(parseInt(h, 10), parseInt(m, 10), 0, 0);
			return d.toLocaleTimeString([], {
				hour: "2-digit",
				minute: "2-digit",
				hour12: true,
			});
		} catch (error) {
			return timeStr;
		}
	}

	formatDateWithDay(dateObj) {
		try {
			return dateObj.toLocaleDateString("en-US", {
				weekday: "short",
				month: "short",
				day: "numeric",
				year: "numeric",
			});
		} catch (error) {
			return dateObj.toISOString().slice(0, 10);
		}
	}

	calculateWorkedHours(checkIn, checkOut) {
		if (!checkIn || !checkOut) return "-";
		try {
			const [inH, inM] = checkIn.split(":").map(Number);
			const [outH, outM] = checkOut.split(":").map(Number);
			const start = new Date();
			start.setHours(inH, inM, 0, 0);
			const end = new Date();
			end.setHours(outH, outM, 0, 0);
			const minutes = Math.max(0, (end - start) / 60000);
			const h = Math.floor(minutes / 60);
			const m = Math.round(minutes % 60);
			return `${h}:${m.toString().padStart(2, "0")}`;
		} catch (error) {
			return "-";
		}
	}

	updateMonthLabel(monthDetails) {
		if (!this.monthLabel) return;
		try {
			if (monthDetails) {
				this.monthLabel.textContent = new Date(
					monthDetails.year,
					monthDetails.month - 1
				).toLocaleString("default", {
					month: "long",
					year: "numeric",
				});
			} else {
				this.monthLabel.textContent = this.month.toLocaleString(
					"default",
					{
						month: "long",
						year: "numeric",
					}
				);
			}
		} catch (error) {
			console.error("Error updating month label:", error);
		}
	}
}

// Initialize on DOMContentLoaded
window.addEventListener("DOMContentLoaded", () => {
	const cfg = window.__ATTENDANCE_DETAIL__;
	if (!cfg) {
		console.error("Attendance detail configuration not found");
		return;
	}
	new AttendanceDetailPage(cfg);
});
