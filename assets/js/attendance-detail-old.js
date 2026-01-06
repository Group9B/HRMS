class AttendanceDetailPage {
	constructor(config) {
		// Use role name instead of role ID for security
		this.roleName = config.roleName || "unknown";
		this.userId = config.userId;
		this.currentEmployeeId = config.currentEmployeeId;
		this.canViewOthers = config.canViewOthers;
		this.canViewSelf = config.canViewSelf;
		this.showDropdown = config.showDropdown;
		this.selectedEmployeeId = config.selectedEmployeeId;
		this.month = new Date();
		this.calendar = null;
		this.latestData = null;
		this.tableBody = document.querySelector("#attendanceTable tbody");
		this.summaryChips = document.getElementById("summaryChips");
		this.employeeSelect = document.getElementById("employeeSelect");
		this.monthLabel = document.getElementById("monthLabel");
		this.prevBtn = document.getElementById("prevMonthBtn");
		this.nextBtn = document.getElementById("nextMonthBtn");

		// Validate role access
		const allowedRoles = ["company_owner", "hr", "manager", "employee"];
		if (!allowedRoles.includes(this.roleName)) {
			console.error("Unauthorized role for attendance detail page");
			return;
		}

		this.bindEvents();
		this.initCalendar();
	}

	bindEvents() {
		if (this.prevBtn) {
			this.prevBtn.addEventListener("click", () => {
				this.shiftMonth(-1);
			});
		}
		if (this.nextBtn) {
			this.nextBtn.addEventListener("click", () => {
				this.shiftMonth(1);
			});
		}
		if (this.employeeSelect) {
			this.employeeSelect.addEventListener("change", () => {
				const val = parseInt(this.employeeSelect.value, 10);
				if (!Number.isNaN(val)) {
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
		// For attendance detail page, hide the calendar's own navigation since we have page-level controls
		this.calendar = new AttendanceCalendar({
			containerId: "attendance-calendar",
			employeeId: this.selectedEmployeeId,
			initialDate: this.month,
			showMonthNavigation: false, // Use page-level navigation instead
			onDataLoaded: (data) => this.handleDataLoaded(data),
			onDayClick: (payload) => this.highlightRow(payload.date),
		});
		this.updateMonthLabel();
	}

	shiftMonth(delta) {
		this.month.setMonth(this.month.getMonth() + delta);
		if (this.calendar) {
			this.calendar.currentMonth = new Date(this.month);
			this.calendar.loadAttendanceData();
		}
		this.updateMonthLabel();
	}

	handleDataLoaded(data) {
		this.latestData = data;
		this.populateEmployeeSelect(data.employees || []);
		this.renderSummary(data.summary || {});
		this.renderTable(data);
		this.updateMonthLabel(data.month_details);
	}

	populateEmployeeSelect(employees) {
		// Only populate if dropdown should be shown
		if (!this.showDropdown) return;
		if (!this.employeeSelect) return;

		// Validate that we have employees to show
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
			if (emp.id === this.selectedEmployeeId) opt.selected = true;
			frag.appendChild(opt);
		});
		this.employeeSelect.appendChild(frag);

		// If selectedEmployeeId not in list, select first available
		const selectedExists = employees.some(
			(e) => e.id === this.selectedEmployeeId
		);
		if (!selectedExists && employees.length > 0) {
			this.selectedEmployeeId = employees[0].id;
			this.employeeSelect.value = this.selectedEmployeeId;
			// Reload data for the first employee
			if (this.calendar) {
				this.calendar.employeeId = this.selectedEmployeeId;
			}
		}
	}

	renderSummary(summary) {
		if (!this.summaryChips) return;
		const chips = [
			{
				label: "Present",
				value: summary.total_present || 0,
				color: "success",
			},
			{
				label: "Absent",
				value: summary.total_absent || 0,
				color: "danger",
			},
			{
				label: "Leave",
				value: summary.total_leave || 0,
				color: "warning",
			},
			{
				label: "Half Day",
				value: summary.total_half_day || 0,
				color: "primary",
			},
			{
				label: "Holiday",
				value: summary.total_holiday || 0,
				color: "info",
			},
		];
		this.summaryChips.innerHTML = chips
			.map(
				(c) =>
					`<span class="badge bg-${c.color}-subtle text-${c.color} attendance-summary-chip px-3 py-2 d-inline-flex align-items-center justify-content-center">${c.label}: ${c.value}</span>`
			)
			.join("");
	}

	renderTable(data) {
		if (!this.tableBody) return;
		const employees = data.employees || [];
		const emp = employees.find((e) => e.id === this.selectedEmployeeId);
		if (!emp) {
			this.tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No data for this employee.</td></tr>`;
			return;
		}

		const month = data.month_details;
		if (!month) {
			this.tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No data.</td></tr>`;
			return;
		}

		const rows = [];
		const today = new Date();
		today.setHours(0, 0, 0, 0);

		for (let day = 1; day <= month.days_in_month; day++) {
			const dateObj = new Date(month.year, month.month - 1, day);
			// Skip future dates - only show up to today
			if (dateObj > today) continue;

			const dateStr = dateObj.toISOString().slice(0, 10);
			const att = emp.attendance?.[dateStr] || {};
			const status = att.status || "-";
			const checkIn = att.check_in || "";
			const checkOut = att.check_out || "";
			const worked = this.calculateWorkedHours(checkIn, checkOut);
			rows.push(`
				<tr data-date="${dateStr}">
					<td>${this.formatDateWithDay(dateObj)}</td>
					<td>${this.renderStatusBadge(status)}</td>
					<td>${checkIn ? this.formatTime(checkIn) : "-"}</td>
					<td>${checkOut ? this.formatTime(checkOut) : "-"}</td>
					<td>${worked}</td>
				</tr>
			`);
		}

		this.tableBody.innerHTML = rows.join("");

		// Attach row click to open modal via calendar helper
		this.tableBody.querySelectorAll("tr").forEach((row) => {
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
		const map = {
			present: "success",
			absent: "danger",
			"half-day": "primary",
			leave: "warning",
			holiday: "info",
		};
		const color = map[s] || "secondary";
		const label =
			s === "-"
				? "Not Marked"
				: s.replace(/\b\w/g, (c) => c.toUpperCase());
		return `<span class="badge bg-${color}-subtle text-${color}">${label}</span>`;
	}

	formatTime(timeStr) {
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

	formatDateWithDay(dateObj) {
		return dateObj.toLocaleDateString("en-US", {
			weekday: "short",
			month: "short",
			day: "numeric",
			year: "numeric",
		});
	}

	calculateWorkedHours(checkIn, checkOut) {
		if (!checkIn || !checkOut) return "-";
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
	}

	updateMonthLabel(monthDetails) {
		if (!this.monthLabel) return;
		if (monthDetails) {
			this.monthLabel.textContent = `${new Date(
				monthDetails.year,
				monthDetails.month - 1
			).toLocaleString("default", { month: "long", year: "numeric" })}`;
			return;
		}
		this.monthLabel.textContent = this.month.toLocaleString("default", {
			month: "long",
			year: "numeric",
		});
	}
}

window.addEventListener("DOMContentLoaded", () => {
	const cfg = window.__ATTENDANCE_DETAIL__ || {};
	new AttendanceDetailPage(cfg);
});
