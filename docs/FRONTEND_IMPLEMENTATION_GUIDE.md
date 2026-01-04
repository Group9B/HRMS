# HRMS Leave Management - Frontend UI Implementation Guide

## Overview

This document provides guidance for updating the `company/leaves.php` UI to work with the new role-based approval workflow API.

## Key Changes Summary

### Old Endpoints (to be deprecated)

```javascript
GET  /hrms/api/api_leaves.php?action=get_pending_requests
POST /hrms/api/api_leaves.php?action=update_status
```

### New Endpoints (refactored)

```javascript
// Same as before, but with enhanced role-based logic
GET  /hrms/api/api_leaves.php?action=get_pending_requests
POST /hrms/api/api_leaves.php?action=approve_or_reject  // Replaces update_status
```

---

## JavaScript Event Handlers to Update

### 1. Approve/Reject Button Handler

**OLD CODE:**

```javascript
$(document).on("click", ".approve-leave-btn, .reject-leave-btn", function () {
	const leaveId = $(this).data("leave-id");
	const status = $(this).data("action"); // 'approved' or 'rejected'

	const formData = new FormData();
	formData.append("action", "update_status");
	formData.append("leave_id", leaveId);
	formData.append("status", status);

	fetch("/hrms/api/api_leaves.php", { method: "POST", body: formData })
		.then((r) => r.json())
		.then((d) => {
			if (d.success) {
				showToast(d.message, "success");
				if (approveRequestsTable) approveRequestsTable.ajax.reload();
			} else {
				showToast(d.message, "error");
			}
		});
});
```

**NEW CODE:**

```javascript
$(document).on("click", ".approve-leave-btn, .reject-leave-btn", function () {
	const leaveId = $(this).data("leave-id");
	const isApprove = $(this).hasClass("approve-leave-btn");
	const actionType = isApprove ? "approve" : "reject";

	const formData = new FormData();
	formData.append("action", "approve_or_reject");
	formData.append("leave_id", leaveId);
	formData.append("action_type", actionType); // Changed from 'status'

	fetch("/hrms/api/api_leaves.php", { method: "POST", body: formData })
		.then((r) => r.json())
		.then((d) => {
			if (d.success) {
				showToast(`Leave request ${d.status} successfully`, "success");
				if (approveRequestsTable) approveRequestsTable.ajax.reload();
				if (myRequestsTable) myRequestsTable.ajax.reload();
				if (typeof loadLeaveSummary === "function") loadLeaveSummary();
			} else {
				showToast(d.message, "error");
			}
		})
		.catch((err) => {
			console.error("Error:", err);
			showToast(
				"An error occurred while processing your request",
				"error"
			);
		});
});
```

---

## HTML Button Update

### Current Button HTML:

```html
<button
	class="btn btn-outline-success approve-leave-btn"
	data-leave-id="${escapeHTML(r.id)}"
	data-action="approved"
	title="Approve"
>
	Approve
</button>
<button
	class="btn btn-outline-danger reject-leave-btn"
	data-leave-id="${escapeHTML(r.id)}"
	data-action="rejected"
	title="Reject"
>
	Reject
</button>
```

**Note:** No HTML changes needed. The JavaScript handler will work with the existing HTML structure.

---

## DataTable Column Configuration

The approval requests table column configuration remains mostly the same:

```javascript
approveRequestsTable = $("#approveRequestsTable").DataTable({
	responsive: true,
	ajax: {
		url: "/hrms/api/api_leaves.php?action=get_pending_requests",
		dataSrc: "data",
		error: function (xhr, error, thrown) {
			console.error("Error loading pending requests:", error);
			showToast("Failed to load pending requests", "error");
		},
	},
	columns: [
		{
			data: null,
			render: (d, t, r) =>
				`${escapeHTML(r.first_name)} ${escapeHTML(r.last_name)}`,
		},
		{ data: "leave_type" },
		{
			data: null,
			render: (d, t, r) =>
				`${formatDate(r.start_date)} to ${formatDate(r.end_date)}`,
		},
		{
			data: "reason",
			render: (d) => `<small>${escapeHTML(d) || "N/A"}</small>`,
		},
		{
			data: "status",
			render: (d) =>
				`<span class="badge bg-${getStatusClass(
					d
				)}-subtle bg-opacity-10 text-${getStatusClass(
					d
				)}-emphasis">${capitalize(d)}</span>`,
		},
		{
			data: null,
			orderable: false,
			render: (d, t, r) =>
				r.status === "pending"
					? `<div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-success approve-leave-btn" data-leave-id="${escapeHTML(
						r.id
					)}" title="Approve">Approve</button>
                    <button class="btn btn-outline-danger reject-leave-btn" data-leave-id="${escapeHTML(
						r.id
					)}" title="Reject">Reject</button>
                  </div>`
					: "Actioned",
		},
	],
	order: [[2, "asc"]],
	// Add error handling
	initComplete: function () {
		console.log("Approval requests table loaded");
	},
});
```

---

## Role-Based UI Visibility

### For Employee (Role 4):

```javascript
// Show "My Requests" tab only
// Hide "Approve Requests" tab
const canApprove =
	document.getElementById("leaveTabsContent").dataset.canApprove === "true";
// canApprove will be 'false' for employees
```

### For Manager (Role 6):

```javascript
// Show both tabs
// "My Requests" = Own leaves
// "Approve Requests" = Team member leaves (pending only)
```

### For HR (Role 3):

```javascript
// Show both tabs
// "My Requests" = Own leaves
// "Approve Requests" = Manager + Employee leaves (pending only)
```

### For Company Owner (Role 2):

```javascript
// Hide "My Requests" (read-only role)
// Show "Approve Requests" = HR leaves only
// Optional: Add "All Leaves View" (read-only dashboard)
```

---

## Error Handling Improvements

### Response Codes to Handle:

```javascript
// 401 Unauthorized - User not logged in
// 403 Forbidden - User lacks permission to perform action
// 400 Bad Request - Invalid parameters
// 500 Internal Server Error

fetch("/hrms/api/api_leaves.php", { method: "POST", body: formData })
	.then((res) => {
		if (res.status === 401) {
			showToast(
				"Your session has expired. Please log in again.",
				"error"
			);
			window.location.href = "/hrms/auth/login.php";
			return null;
		}
		if (res.status === 403) {
			showToast(
				"You do not have permission to perform this action.",
				"error"
			);
			return null;
		}
		return res.json();
	})
	.then((result) => {
		if (!result) return;
		if (result.success) {
			showToast(result.message, "success");
			// Reload tables
		} else {
			showToast(result.message, "error");
		}
	})
	.catch((err) => {
		console.error("Error:", err);
		showToast("A network error occurred. Please try again.", "error");
	});
```

---

## Approval Workflow UI Messages

### When Manager Approves:

```
✓ "Leave request approved successfully!"
```

### When Manager Rejects:

```
✗ "Leave request has been rejected."
```

### When HR Approves:

```
✓ "Leave request approved successfully!"
[Note: HR can also override previously rejected leaves]
```

### When Employee Cancels:

```
✓ "Your leave request has been cancelled."
```

### When Unauthorized:

```
✗ "You are not authorized to approve this leave request"
✗ "Managers can only approve employee leave requests"
✗ "HR can only approve Employee and Manager leave requests"
✗ "Company Owner can only approve HR leave requests"
```

---

## Optional Enhancements

### 1. Approver Name Display

Show who approved the leave (if approved):

```javascript
// In My Requests table
{
    data: 'approver_name',
    render: d => d ? `<small>By: ${escapeHTML(d)}</small>` : 'Pending'
}
```

### 2. Approval Reason/Notes

Allow approver to add comments (requires schema extension):

```javascript
// In approval modal
<div class="mb-3">
	<label class="form-label">Approval Notes (Optional)</label>
	<textarea class="form-control" name="approval_notes" rows="2"></textarea>
</div>
```

### 3. Audit Trail

Show approval history (requires separate query):

```javascript
function showApprovalHistory(leaveId) {
	fetch(
		`/hrms/api/api_leaves.php?action=get_leave_history&leave_id=${leaveId}`
	)
		.then((r) => r.json())
		.then((d) => {
			// Display timeline of changes
		});
}
```

### 4. Dashboard Statistics

Show pending/approved/rejected counts:

```javascript
function loadLeaveStatistics() {
	fetch("/hrms/api/api_leaves.php?action=get_statistics")
		.then((r) => r.json())
		.then((d) => {
			// Display stats
		});
}
```

---

## Testing Checklist

### Unit Tests:

-   [ ] Employee can apply for leave
-   [ ] Employee cannot approve leave
-   [ ] Manager can approve employee leave in their department
-   [ ] Manager cannot approve employee outside their department
-   [ ] HR can approve both manager and employee leaves
-   [ ] Company Owner can approve HR leaves only
-   [ ] Leave status updates correctly
-   [ ] Attendance records created when approved
-   [ ] Cancellation only works for pending leaves

### Integration Tests:

-   [ ] Apply → Manager Approves → Attendance marked
-   [ ] Apply → HR Overrides Manager → Status changes
-   [ ] Manager applies → HR approves
-   [ ] HR applies → Owner approves
-   [ ] Cancellation removes pending request

### UI Tests:

-   [ ] Correct tabs visible for each role
-   [ ] Buttons enabled/disabled appropriately
-   [ ] Error messages display correctly
-   [ ] Tables reload after action
-   [ ] Toast notifications appear

---

## Migration Path from Old API

If you want to maintain backward compatibility during transition:

```php
// In api_leaves.php, keep both action handlers
case 'update_status':
    // OLD handler - deprecated but still works
    // Log warning in error_log
    error_log("Deprecated: update_status used. Use approve_or_reject instead.");
    // Forward to new handler
    $_POST['action'] = 'approve_or_reject';
    $_POST['action_type'] = $_POST['status'] === 'approved' ? 'approve' : 'reject';
    // Fall through to approve_or_reject handler

case 'approve_or_reject':
    // NEW handler - current implementation
    // ...
    break;
```

---

## Performance Considerations

### API Optimization:

1. **Pagination**: For large companies, add pagination to approval tables:

    ```javascript
    ajax: {
        url: '/hrms/api/api_leaves.php?action=get_pending_requests&page=1&limit=10',
        dataSrc: function(json) {
            // Handle pagination metadata
            return json.data;
        }
    }
    ```

2. **Caching**: Cache leave balance for current user (5 min TTL)

3. **Lazy Loading**: Load approval tables only when tab is clicked

### Database Optimization:

```sql
-- Indexes for common queries
CREATE INDEX idx_leaves_status_date
ON leaves(status, applied_at);

CREATE INDEX idx_leaves_employee_status
ON leaves(employee_id, status);

CREATE INDEX idx_leaves_company
ON leaves(company_id, employee_id);
```

---

## Conclusion

The new approval workflow maintains backward compatibility with the existing UI while adding robust role-based authorization at the API level. Update the JavaScript event handlers and you'll be ready to go!
