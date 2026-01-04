# HRMS Leave Approval Workflow - Testing & Validation Guide

## Database Setup for Testing

### 1. Verify Leave Table Structure

```sql
DESC leaves;
-- Should show:
-- id, employee_id, leave_type, start_date, end_date, reason, status, applied_at, approved_by
```

### 2. Create Test Data

```sql
-- Test employees (if needed)
INSERT INTO employees (user_id, first_name, last_name, department_id, company_id, position)
VALUES
  (4, 'John', 'Employee', 1, 1, 'Developer'),
  (5, 'Jane', 'Manager', 1, 1, 'Manager'),
  (6, 'Bob', 'HR', 1, 1, 'HR Manager'),
  (7, 'Alice', 'Owner', 1, 1, 'Company Owner');

-- Test leave policies
INSERT INTO leave_policies (company_id, leave_type, days_per_year)
VALUES
  (1, 'Annual Leave', 20),
  (1, 'Sick Leave', 10),
  (1, 'Personal Leave', 5);

-- Test leave requests
INSERT INTO leaves (employee_id, start_date, end_date, leave_type, reason, status, applied_at, approved_by)
VALUES
  (4, '2026-01-10', '2026-01-12', 'Annual Leave', 'Vacation', 'pending', NOW(), NULL),
  (5, '2026-01-15', '2026-01-17', 'Sick Leave', 'Unwell', 'pending', NOW(), NULL);
```

---

## API Endpoint Testing

### 1. Apply Leave (POST)

**Test Case 1.1: Valid Leave Request**

```bash
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=apply_leave&start_date=2026-01-10&end_date=2026-01-12&leave_type=Annual%20Leave&reason=Vacation"
```

**Expected Response:**

```json
{
	"success": true,
	"message": "Leave request submitted successfully!",
	"leave_id": 123
}
```

**Test Case 1.2: Invalid Date (Past)**

```bash
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=apply_leave&start_date=2025-12-25&end_date=2025-12-27&leave_type=Annual%20Leave"
```

**Expected Response:**

```json
{
	"success": false,
	"message": "Leave start date cannot be in the past."
}
```

**Test Case 1.3: End Date Before Start Date**

```bash
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=apply_leave&start_date=2026-01-15&end_date=2026-01-10&leave_type=Annual%20Leave"
```

**Expected Response:**

```json
{
	"success": false,
	"message": "End date cannot be before start date."
}
```

---

### 2. Get My Leaves (GET)

**Test Case 2.1: Employee Views Own Leaves**

```bash
curl "http://localhost/hrms/api/api_leaves.php?action=get_my_leaves"
```

**Expected Response:**

```json
{
	"success": true,
	"data": [
		{
			"id": 1,
			"employee_id": 4,
			"leave_type": "Annual Leave",
			"start_date": "2026-01-10",
			"end_date": "2026-01-12",
			"reason": "Vacation",
			"status": "pending",
			"applied_at": "2026-01-04 10:30:00",
			"approved_by": null,
			"approver_name": null
		}
	]
}
```

---

### 3. Get Pending Requests (GET)

**Test Case 3.1: Manager Views Team Leaves**

```bash
curl "http://localhost/hrms/api/api_leaves.php?action=get_pending_requests"
# (Logged in as Manager, Role ID 6)
```

**Expected Response (Manager):**

```json
{
	"success": true,
	"data": [
		{
			"id": 1,
			"employee_id": 4,
			"first_name": "John",
			"last_name": "Employee",
			"leave_type": "Annual Leave",
			"start_date": "2026-01-10",
			"end_date": "2026-01-12",
			"reason": "Vacation",
			"status": "pending",
			"employee_role_id": 4
		}
	]
}
```

**Test Case 3.2: HR Views Manager + Employee Leaves**

```bash
curl "http://localhost/hrms/api/api_leaves.php?action=get_pending_requests"
# (Logged in as HR, Role ID 3)
```

**Expected Response (HR):**

```json
{
	"success": true,
	"data": [
		{
			"id": 1,
			"employee_id": 4,
			"first_name": "John",
			"last_name": "Employee",
			"employee_role_id": 4
		},
		{
			"id": 2,
			"employee_id": 5,
			"first_name": "Jane",
			"last_name": "Manager",
			"employee_role_id": 6
		}
	]
}
```

**Test Case 3.3: Company Owner Views HR Leaves Only**

```bash
curl "http://localhost/hrms/api/api_leaves.php?action=get_pending_requests"
# (Logged in as Company Owner, Role ID 2)
```

**Expected Response (Owner):**

```json
{
	"success": true,
	"data": [
		{
			"id": 3,
			"employee_id": 6,
			"first_name": "Bob",
			"last_name": "HR",
			"employee_role_id": 3
		}
	]
}
```

---

### 4. Approve or Reject (POST)

**Test Case 4.1: Manager Approves Employee Leave**

```bash
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=approve_or_reject&leave_id=1&action_type=approve"
# (Logged in as Manager, Role ID 6)
```

**Expected Response:**

```json
{
	"success": true,
	"message": "Leave request has been approved!",
	"status": "approved",
	"approved_by_user_id": 5
}
```

**Verification:**

```sql
SELECT status, approved_by FROM leaves WHERE id = 1;
-- Should show: status='approved', approved_by=5
```

**Test Case 4.2: Manager Rejects Employee Leave**

```bash
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=approve_or_reject&leave_id=2&action_type=reject"
# (Logged in as Manager, Role ID 6)
```

**Expected Response:**

```json
{
	"success": true,
	"message": "Leave request has been rejected!",
	"status": "rejected",
	"approved_by_user_id": 5
}
```

**Test Case 4.3: Manager Attempts to Approve Manager Leave (Fails)**

```bash
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=approve_or_reject&leave_id=2&action_type=approve"
# leave_id=2 is a manager's leave
# (Logged in as Manager, Role ID 6)
```

**Expected Response:**

```json
{
	"success": false,
	"message": "Managers can only approve employee leave requests"
}
```

**HTTP Status:** 403 Forbidden

**Test Case 4.4: Manager Attempts to Approve Leave Outside Department (Fails)**

```bash
-- Assume employee_id=4 is in department_id=1
-- And manager is in department_id=2
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=approve_or_reject&leave_id=1&action_type=approve"
```

**Expected Response:**

```json
{
	"success": false,
	"message": "Employee is not in your department"
}
```

**HTTP Status:** 403 Forbidden

**Test Case 4.5: HR Approves Employee Leave**

```bash
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=approve_or_reject&leave_id=1&action_type=approve"
# (Logged in as HR, Role ID 3)
```

**Expected Response:**

```json
{
	"success": true,
	"message": "Leave request has been approved!",
	"status": "approved",
	"approved_by_user_id": 6
}
```

**Test Case 4.6: HR Approves Manager Leave**

```bash
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=approve_or_reject&leave_id=2&action_type=approve"
# (Logged in as HR, Role ID 3)
```

**Expected Response:**

```json
{
	"success": true,
	"message": "Leave request has been approved!",
	"status": "approved",
	"approved_by_user_id": 6
}
```

**Test Case 4.7: Company Owner Approves HR Leave**

```bash
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=approve_or_reject&leave_id=3&action_type=approve"
# (Logged in as Company Owner, Role ID 2)
```

**Expected Response:**

```json
{
	"success": true,
	"message": "Leave request has been approved!",
	"status": "approved",
	"approved_by_user_id": 7
}
```

**Test Case 4.8: Company Owner Attempts to Approve Employee Leave (Fails)**

```bash
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=approve_or_reject&leave_id=1&action_type=approve"
# (Logged in as Company Owner, Role ID 2)
```

**Expected Response:**

```json
{
	"success": false,
	"message": "Company Owner can only approve HR leave requests"
}
```

**HTTP Status:** 403 Forbidden

**Test Case 4.9: Employee Attempts to Approve Leave (Fails)**

```bash
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=approve_or_reject&leave_id=1&action_type=approve"
# (Logged in as Employee, Role ID 4)
```

**Expected Response:**

```json
{
	"success": false,
	"message": "Employees cannot approve leave requests"
}
```

**HTTP Status:** 403 Forbidden

---

### 5. Cancel Leave (POST)

**Test Case 5.1: Employee Cancels Own Pending Leave**

```bash
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=cancel_leave&leave_id=1"
# (Logged in as Employee, Role ID 4)
```

**Expected Response:**

```json
{
	"success": true,
	"message": "Your leave request has been cancelled."
}
```

**Verification:**

```sql
SELECT status FROM leaves WHERE id = 1;
-- Should show: status='cancelled'
```

**Test Case 5.2: Employee Attempts to Cancel Approved Leave (Fails)**

```bash
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=cancel_leave&leave_id=2"
# (Logged in as Employee, Role ID 4)
# leave_id=2 is already approved
```

**Expected Response:**

```json
{
	"success": false,
	"message": "Cannot cancel leave with status: approved"
}
```

**Test Case 5.3: Employee Attempts to Cancel Another's Leave (Fails)**

```bash
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=cancel_leave&leave_id=3"
# (Logged in as Employee A, but leave belongs to Employee B)
```

**Expected Response:**

```json
{
	"success": false,
	"message": "Leave not found or unauthorized."
}
```

---

## Workflow Scenarios

### Scenario 1: Complete Approval Chain

```
1. Employee applies for leave
   curl -X POST ... -d "action=apply_leave&start_date=2026-01-15&end_date=2026-01-17&leave_type=Annual%20Leave"
   → Response: success=true, leave_id=100

2. Manager approves
   curl -X POST ... -d "action=approve_or_reject&leave_id=100&action_type=approve"
   (Logged in as Manager)
   → Response: success=true, status=approved

3. Verify in database
   SELECT status, approved_by FROM leaves WHERE id = 100;
   → status='approved', approved_by=5 (manager's user_id)

4. Check attendance records created
   SELECT status FROM attendance WHERE employee_id=4 AND date BETWEEN '2026-01-15' AND '2026-01-17';
   → Should show 3 records with status='leave'
```

### Scenario 2: Manager Applies, HR Approves

```
1. Manager applies
   curl -X POST ... -d "action=apply_leave&start_date=2026-02-01&end_date=2026-02-05&leave_type=Annual%20Leave"
   (Logged in as Manager, Role ID 6)
   → success=true, leave_id=101

2. HR views pending
   curl "... ?action=get_pending_requests"
   (Logged in as HR, Role ID 3)
   → Should include leave_id=101 (manager's leave)

3. HR approves
   curl -X POST ... -d "action=approve_or_reject&leave_id=101&action_type=approve"
   (Logged in as HR, Role ID 3)
   → success=true

4. HR applies own leave
   curl -X POST ... -d "action=apply_leave&start_date=2026-03-01&end_date=2026-03-03&leave_type=Annual%20Leave"
   (Logged in as HR, Role ID 3)
   → success=true, leave_id=102

5. Owner approves HR's leave
   curl "... ?action=get_pending_requests"
   (Logged in as Company Owner, Role ID 2)
   → Should include leave_id=102 (HR's leave)

   curl -X POST ... -d "action=approve_or_reject&leave_id=102&action_type=approve"
   (Logged in as Company Owner, Role ID 2)
   → success=true
```

### Scenario 3: HR Override

```
1. Employee applies
   → leave_id=103, status=pending

2. Manager rejects
   curl -X POST ... -d "action=approve_or_reject&leave_id=103&action_type=reject"
   → status=rejected, approved_by=5

3. HR overrides (approves)
   curl -X POST ... -d "action=approve_or_reject&leave_id=103&action_type=approve"
   (Logged in as HR, Role ID 3)
   → success=false (leave is rejected, not pending)

   **Note:** Current design doesn't allow overriding rejected leaves.
   If this is desired, modify API to allow HR to re-approve rejected leaves.
```

---

## Attendance Table Verification

### When Leave is Approved

```sql
-- After approving a leave for dates 2026-01-15 to 2026-01-17
SELECT * FROM attendance
WHERE employee_id = 4
  AND date BETWEEN '2026-01-15' AND '2026-01-17'
  AND status = 'leave';

-- Should return:
-- | employee_id | date       | status |
-- | 4           | 2026-01-15 | leave  |
-- | 4           | 2026-01-16 | leave  |
-- | 4           | 2026-01-17 | leave  |
```

---

## Security Testing

### 1. SQL Injection

```bash
# Test malicious input
curl -X POST http://localhost/hrms/api/api_leaves.php \
  -d "action=apply_leave&leave_id=1' OR '1'='1&action_type=approve"

# Expected: Safe (parameterized queries used)
# Response should be: Invalid leave ID or not found
```

### 2. Authorization Bypass

```bash
# Test elevated privileges
# Logged in as Employee, attempt to get approval requests
curl "http://localhost/hrms/api/api_leaves.php?action=get_pending_requests"
# (Logged in as Employee, Role ID 4)

# Expected: Either empty data or error message
```

### 3. Session Hijacking

```bash
# Test without valid session
curl "http://localhost/hrms/api/api_leaves.php?action=apply_leave"
# (No session cookie)

# Expected Response:
# {
#   "success": false,
#   "message": "Unauthorized access."
# }
# HTTP Status: 401
```

---

## Performance Testing

### Load Test - 1000 Pending Requests

```bash
# Create 1000 pending leaves
for i in {1..1000}; do
  curl -X POST http://localhost/hrms/api/api_leaves.php \
    -d "action=apply_leave&start_date=2026-01-10&end_date=2026-01-12&leave_type=Annual%20Leave"
done

# Test approval query speed
time curl "http://localhost/hrms/api/api_leaves.php?action=get_pending_requests"

# Expected: < 500ms response time
```

---

## Test Checklist

### Authentication & Authorization

-   [ ] Unauthenticated users get 401 error
-   [ ] Employee cannot approve leaves
-   [ ] Manager can only approve team leaves
-   [ ] HR can approve manager & employee leaves
-   [ ] Owner can only approve HR leaves
-   [ ] Admin can do everything

### Leave Application

-   [ ] Valid dates create leave (pending)
-   [ ] Past dates rejected
-   [ ] End before start rejected
-   [ ] Invalid leave type rejected
-   [ ] Only employees/managers/HR can apply

### Approval Workflow

-   [ ] Manager can approve employee leave
-   [ ] Manager cannot approve outside department
-   [ ] HR can approve manager leave
-   [ ] HR can approve employee leave
-   [ ] Owner can approve HR leave
-   [ ] Status changes to approved
-   [ ] approved_by field populated

### Cancellation

-   [ ] Employee can cancel own pending leave
-   [ ] Cannot cancel approved/rejected/cancelled leaves
-   [ ] Cannot cancel others' leaves
-   [ ] Status changes to cancelled

### Data Integrity

-   [ ] Approved leaves create attendance records
-   [ ] Leaves marked with correct dates
-   [ ] Attendance marked as 'leave' status

---

## Troubleshooting

### Issue: "You are not authorized to approve this leave"

**Possible Causes:**

1. Wrong role (employee trying to approve)
2. Employee not in manager's department
3. Trying to approve wrong role type
   **Solution:** Verify role_id and department_id match

### Issue: Attendance Records Not Created

**Possible Causes:**

1. Leave status not 'approved'
2. attendance table doesn't exist
3. Database constraint error
   **Solution:** Check database schema, review SQL error logs

### Issue: Get Pending Requests Returns Empty

**Possible Causes:**

1. No pending leaves in system
2. Role-based filtering too restrictive
3. Wrong company_id
   **Solution:** Create test data, verify role_id matches
