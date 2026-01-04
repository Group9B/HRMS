# HRMS Leave Workflow - Quick Reference Card

## Role IDs

```
1 = Admin
2 = Company Owner
3 = HR
4 = Employee
6 = Manager
```

## Who Can Do What?

### Apply for Leave

✓ Employee (4)
✓ Manager (6)
✓ HR (3)

### Approve Leaves

✓ Manager (6) → Only employees in their department
✓ HR (3) → Employees and managers (company-wide)
✓ Owner (2) → HR only
✓ Admin (1) → Anyone

### Cancel Leave

✓ Employee/Manager/HR → Their own pending leaves only

### View Leaves

✓ Everyone → Their own leaves
✓ Manager (6) → Own + team members
✓ HR (3) → Everyone in company
✓ Owner (2) → Everyone (read-only)

---

## API Endpoints

| Action                | Method | Parameters                               | Auth    |
| --------------------- | ------ | ---------------------------------------- | ------- |
| apply_leave           | POST   | start_date, end_date, leave_type, reason | 4,6,3   |
| get_my_leaves         | GET    | -                                        | All     |
| get_pending_requests  | GET    | -                                        | 1,2,3,6 |
| approve_or_reject     | POST   | leave_id, action_type                    | 1,2,3,6 |
| cancel_leave          | POST   | leave_id                                 | 4,6,3   |
| get_leave_summary     | GET    | -                                        | All     |
| get_leave_calculation | GET    | start_date, end_date                     | All     |

---

## Common Tasks

### Employee Applies

```javascript
fetch("/hrms/api/api_leaves_refactored.php", {
	method: "POST",
	body: new FormData({
		action: "apply_leave",
		start_date: "2026-01-15",
		end_date: "2026-01-17",
		leave_type: "Annual Leave",
		reason: "Vacation",
	}),
});
```

### Manager Approves

```javascript
fetch("/hrms/api/api_leaves_refactored.php", {
	method: "POST",
	body: new FormData({
		action: "approve_or_reject",
		leave_id: 123,
		action_type: "approve",
	}),
});
```

### Get Pending (Role-Based)

```javascript
// Manager gets team leaves
// HR gets employee + manager leaves
// Owner gets HR leaves
fetch("/hrms/api/api_leaves_refactored.php?action=get_pending_requests");
```

---

## Database Fields

| Field         | Type                                | Usage         |
| ------------- | ----------------------------------- | ------------- |
| `status`      | pending/approved/rejected/cancelled | Current state |
| `approved_by` | NULL or user_id                     | Who approved  |
| `start_date`  | DATE                                | Leave start   |
| `end_date`    | DATE                                | Leave end     |
| `applied_at`  | TIMESTAMP                           | When applied  |
| `employee_id` | FK                                  | Whose leave   |

---

## Status Flow

```
PENDING
  ↓
APPROVED (Attendance marked)
  OR
REJECTED (No attendance)

PENDING
  ↓
CANCELLED (By employee only)
```

---

## Authorization Checks

```php
// Manager can approve?
- Is role 6? ✓
- Is employee in my department? ✓
- Is employee role 4? ✓
→ ALLOW

// HR can approve?
- Is role 3? ✓
- Is employee role 4 or 6? ✓
→ ALLOW

// Owner can approve?
- Is role 2? ✓
- Is employee role 3? ✓
→ ALLOW
```

---

## Response Codes

```
200 OK          - Success
400 Bad Request - Invalid input
401 Unauthorized - Not logged in
403 Forbidden   - Permission denied
500 Server Error - Server error
```

---

## Error Messages

| Scenario            | Message                                     |
| ------------------- | ------------------------------------------- |
| Not logged in       | Unauthorized access                         |
| Invalid role        | Not authorized to approve                   |
| Wrong department    | Employee is not in your department          |
| Wrong employee role | Managers can only approve employee leaves   |
| Non-pending leave   | Cannot modify a leave with status: approved |
| Past date           | Leave start date cannot be in the past      |
| End < Start         | End date cannot be before start date        |

---

## Helper Functions

```php
// Check if user can apply
canApplyForLeave($role_id)

// Check if user can approve
canApproveLeaves($role_id)

// Get department
getEmployeeDepartment($mysqli, $employee_id)

// Same department?
isInSameDepartment($mysqli, $emp1, $emp2)

// Get balance
getLeaveBalance($mysqli, $employee_id, $type, $company_id)

// Validate dates
validateLeaveDates($start, $end)
```

---

## SQL Queries

### Manager's Team Leaves

```sql
SELECT l.*, e.first_name, e.last_name
FROM leaves l
JOIN employees e ON l.employee_id = e.id
JOIN users u ON e.user_id = u.id
WHERE u.company_id = ?
  AND l.status = 'pending'
  AND e.department_id = ?
  AND u.role_id = 4
```

### HR's Pending

```sql
SELECT l.*, e.first_name, e.last_name
FROM leaves l
JOIN employees e ON l.employee_id = e.id
JOIN users u ON e.user_id = u.id
WHERE u.company_id = ?
  AND l.status = 'pending'
  AND u.role_id IN (4, 6)
```

### Owner's Pending

```sql
SELECT l.*, e.first_name, e.last_name
FROM leaves l
JOIN employees e ON l.employee_id = e.id
JOIN users u ON e.user_id = u.id
WHERE u.company_id = ?
  AND l.status = 'pending'
  AND u.role_id = 3
```

---

## Frontend Changes

### Old Event Handler

```javascript
formData.append("action", "update_status");
formData.append("status", "approved");
```

### New Event Handler

```javascript
formData.append("action", "approve_or_reject");
formData.append("action_type", "approve");
```

---

## Testing Checklist

-   [ ] Employee applies ✓
-   [ ] Manager approves team member ✓
-   [ ] Manager cannot approve outside team ✓
-   [ ] HR approves manager ✓
-   [ ] HR approves employee ✓
-   [ ] Owner approves HR ✓
-   [ ] Owner cannot approve employee ✓
-   [ ] Attendance created on approve ✓
-   [ ] Cannot approve twice ✓
-   [ ] Cannot cancel approved ✓

---

## Files Reference

| File                               | Purpose                           |
| ---------------------------------- | --------------------------------- |
| `api_leaves_refactored.php`        | Main API - deploy this            |
| `leave_helpers.php`                | Helper functions - include in API |
| `LEAVE_APPROVAL_WORKFLOW.md`       | Design docs                       |
| `FRONTEND_IMPLEMENTATION_GUIDE.md` | UI changes                        |
| `TESTING_VALIDATION_GUIDE.md`      | Test cases                        |
| `IMPLEMENTATION_SUMMARY.md`        | Overview                          |

---

## Deployment Steps

1. Deploy `api_leaves_refactored.php` to `/api/`
2. Add `leave_helpers.php` to `/includes/`
3. Update frontend JS handlers
4. Test all role combinations
5. Switch to new API endpoint
6. Monitor for errors

---

## Performance Tips

-   Add indexes on `leaves(status, applied_at)`
-   Cache leave policies (24h TTL)
-   Cache company holidays (24h TTL)
-   Use parameterized queries (already done)
-   Paginate pending requests if > 100

---

## Common Mistakes

❌ Checking role only on UI
✓ Check role in API with authorization

❌ Trusting user-submitted role_id
✓ Get role from session/database

❌ Approving rejected leaves
✓ Only approve pending leaves

❌ Skipping department check
✓ Verify employee in manager's dept

❌ No attendance record update
✓ Create attendance when approving

---

## Debugging

### Enable Logging

```php
error_log("User $user_id attempting to approve leave $leave_id");
error_log("Authorization result: " . json_encode($auth_check));
```

### Check Role

```sql
SELECT u.role_id, e.department_id FROM users u
JOIN employees e ON e.user_id = u.id
WHERE u.id = ?;
```

### Check Leave

```sql
SELECT l.status, l.approved_by, e.user_id, u.role_id
FROM leaves l
JOIN employees e ON l.employee_id = e.id
JOIN users u ON e.user_id = u.id
WHERE l.id = ?;
```

---

**Last Updated: 2026-01-04**
**Version: 1.0**
