# HRMS Leave Approval Workflow Design

## Role Mapping

```
1 = Admin (Super Admin)
2 = Company Owner
3 = Human Resources (HR)
4 = Employee
5 = Candidate
6 = Manager
```

---

## Approval Workflow Hierarchy

### Employee (Role ID: 4)

-   **Can Apply**: Yes
-   **Can Approve**: No
-   **Can View**: Own leave history + balance only
-   **Constraints**:
    -   Can only create `status = 'pending'`
    -   Cannot update status or `approved_by`
    -   Can cancel only own pending leaves

### Manager (Role ID: 6)

-   **Can Apply**: Yes (as employee)
-   **Can Approve**:
    -   Employee leaves in their department
    -   Own leaves as employee
-   **Cannot Approve**:
    -   Other manager leaves
    -   HR leaves
    -   Company owner leaves
-   **View Permission**:
    -   Own leaves
    -   Team member leaves (same department)
-   **Constraints**:
    -   Set `approved_by = manager_user_id`
    -   Change status from pending → approved/rejected
    -   Only if employee is in same department

### HR (Role ID: 3)

-   **Can Apply**: Yes (as employee)
-   **Can Approve**:
    -   Manager leaves (ALL managers)
    -   Employee leaves (override - ANY employee)
    -   Own leaves as employee
-   **Cannot Approve**: Company owner leaves
-   **View Permission**:
    -   All manager leaves
    -   All employee leaves
    -   Own leaves
-   **Constraints**:
    -   Set `approved_by = hr_user_id`
    -   Can override manager-approved leaves if needed

### Company Owner (Role ID: 2)

-   **Can Apply**: No (read-only for this role in many HRMS systems)
-   **Can Approve**: HR leaves only
-   **Can View**: All leave data (read-only)
-   **Constraints**:
    -   Set `approved_by = owner_user_id`
    -   Can only approve HR leaves
    -   Cannot modify employee/manager leaves

### Admin (Role ID: 1)

-   **Full System Access**: Can approve anything
-   **Not typically used in workflows**

---

## Database Schema (Existing - No Changes)

```sql
CREATE TABLE leaves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason VARCHAR(255),
    status ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_by INT NULL
);
```

### Field Usage:

-   **status**: Current state in approval workflow

    -   `pending` = Awaiting approval
    -   `approved` = Approved at current/final level
    -   `rejected` = Rejected at any level (terminal state)
    -   `cancelled` = Cancelled by employee (terminal state)

-   **approved_by**: Stores USER_ID of approver
    -   `NULL` = Not yet approved
    -   `manager_user_id` = Approved by manager
    -   `hr_user_id` = Approved by HR
    -   `owner_user_id` = Approved by owner

---

## API Endpoints & Logic

### 1. POST `/api/api_leaves.php?action=apply_leave`

**Purpose**: Employee applies for leave

**Validation**:

-   User must be logged in
-   Start date >= today
-   End date >= start date
-   Leave type exists in policies

**Business Logic**:

-   Create record with `status = 'pending'` and `approved_by = NULL`
-   Calculate leave days (using existing calculateActualLeaveDays)
-   Validate employee has sufficient balance

**Response**:

```json
{
	"success": true,
	"message": "Leave request submitted successfully",
	"leave_id": 123
}
```

---

### 2. GET `/api/api_leaves.php?action=get_my_leaves`

**Purpose**: Employee views own leaves

**Query Logic**:

-   Filter: `WHERE employee_id = current_employee_id`
-   Role Agnostic: All roles can see their own leaves

**Response**:

```json
{
	"success": true,
	"data": [
		{
			"id": 1,
			"leave_type": "Sick Leave",
			"start_date": "2026-01-10",
			"end_date": "2026-01-12",
			"status": "approved",
			"applied_at": "2026-01-04 10:30:00",
			"approved_by": 5,
			"approver_name": "John Manager"
		}
	]
}
```

---

### 3. GET `/api/api_leaves.php?action=get_pending_requests`

**Purpose**: Approvers view leaves awaiting their approval

**Role-Based Query**:

#### Manager (Role 6):

```sql
SELECT l.*, e.first_name, e.last_name, u.role_id, e.department_id
FROM leaves l
JOIN employees e ON l.employee_id = e.id
JOIN users u ON e.user_id = u.id
WHERE u.company_id = {company_id}
  AND l.status = 'pending'
  AND e.department_id = {manager_department_id}
  AND e.user_id != {current_user_id}
  AND u.role_id = 4  -- Only employee leaves
ORDER BY l.applied_at DESC
```

#### HR (Role 3):

```sql
SELECT l.*, e.first_name, e.last_name, u.role_id
FROM leaves l
JOIN employees e ON l.employee_id = e.id
JOIN users u ON e.user_id = u.id
WHERE u.company_id = {company_id}
  AND l.status = 'pending'
  AND u.role_id IN (4, 6)  -- Employee AND Manager leaves
  AND e.user_id != {current_user_id}
ORDER BY l.applied_at DESC
```

#### Company Owner (Role 2):

```sql
SELECT l.*, e.first_name, e.last_name, u.role_id
FROM leaves l
JOIN employees e ON l.employee_id = e.id
JOIN users u ON e.user_id = u.id
WHERE u.company_id = {company_id}
  AND l.status = 'pending'
  AND u.role_id = 3  -- HR leaves only
ORDER BY l.applied_at DESC
```

#### Response:

```json
{
	"success": true,
	"data": [
		{
			"id": 5,
			"employee_id": 10,
			"first_name": "Jane",
			"last_name": "Employee",
			"leave_type": "Annual Leave",
			"start_date": "2026-01-15",
			"end_date": "2026-01-17",
			"reason": "Family trip",
			"status": "pending",
			"applied_at": "2026-01-05",
			"user_role": 4
		}
	]
}
```

---

### 4. POST `/api/api_leaves.php?action=approve_or_reject`

**Purpose**: Approver approves/rejects a leave request

**Required Parameters**:

-   `leave_id` (int)
-   `action` (string: 'approve' or 'reject')
-   `comments` (string, optional)

**Authorization Checks**:

```
BEFORE updating:
  IF (role_id == 6) [Manager]:
    - Verify leave.employee_id is in manager's department
    - Verify leave status is 'pending'
    - Verify employee role is 4 (Employee)

  IF (role_id == 3) [HR]:
    - Verify leave.employee_role in [4, 6] (Employee or Manager)
    - Verify leave status is 'pending'
    - Allow override of manager-approved leaves

  IF (role_id == 2) [Company Owner]:
    - Verify leave.employee_role is 3 (HR only)
    - Verify leave status is 'pending'

  IF (role_id == 1) [Admin]:
    - Allow all

  ELSE:
    - DENY: "Not authorized to approve leaves"
```

**Business Logic**:

```
1. Fetch leave record with employee & user details
2. Check authorization (above)
3. Update:
   - status = 'approved' OR 'rejected'
   - approved_by = current_user_id
   - If approved: Insert attendance records (status='leave')
4. Audit log (optional but recommended)
```

**Success Response**:

```json
{
	"success": true,
	"message": "Leave request approved successfully",
	"status": "approved",
	"approved_by_user_id": 5
}
```

**Error Responses**:

```json
{
	"success": false,
	"message": "You are not authorized to approve this leave request"
}
```

---

### 5. POST `/api/api_leaves.php?action=cancel_leave`

**Purpose**: Employee cancels own pending leave

**Validation**:

-   Only own leaves can be cancelled
-   Only `pending` status leaves
-   Terminal states (approved, rejected, cancelled) cannot be changed

**Response**:

```json
{
	"success": true,
	"message": "Your leave request has been cancelled"
}
```

---

## UI Behavior (Role-Aware)

### All Roles:

1. **Leave Balance Card** (Read-only)
    - Show balance per leave type
    - Calculated from approved leaves

### Employee (Role 4):

1. **Apply Leave Modal**
    - Visible
2. **My Requests Table**
    - Only own leaves
    - Can cancel pending leaves
3. **Approve Requests Tab**
    - Hidden

### Manager (Role 6):

1. **Apply Leave Modal**
    - Visible (apply as employee)
2. **My Requests Table**
    - Own leaves
3. **Approve Requests Tab**
    - Visible
    - Shows team member leaves (pending only)
    - Approve/Reject buttons
    - Cannot approve own leaves in this context (conflicts of interest)

### HR (Role 3):

1. **Apply Leave Modal**
    - Visible (apply as employee)
2. **My Requests Table**
    - Own leaves
3. **Approve Requests Tab** (Dual View)
    - Section 1: Manager Leaves
        - All pending manager leaves
        - Approve/Reject buttons
    - Section 2: Employee Leaves
        - All pending employee leaves
        - Approve/Reject buttons (can override)

### Company Owner (Role 2):

1. **Apply Leave Modal**
    - Hidden (read-only role)
2. **My Requests Table**
    - Hidden
3. **Approve Requests Tab**
    - Only HR leaves
    - Approve/Reject buttons
4. **All Leave Data View**
    - All leaves across company
    - Read-only, no actions

---

## Helper Functions (PHP)

### 1. `canApproveLeave($mysqli, $user_id, $leave_id, $role_id, $company_id)`

```php
function canApproveLeave($mysqli, $user_id, $leave_id, $role_id, $company_id) {
    // Fetch leave details
    $leave = query($mysqli, "
        SELECT l.*, e.user_id as employee_user_id, u.role_id as employee_role
        FROM leaves l
        JOIN employees e ON l.employee_id = e.id
        JOIN users u ON e.user_id = u.id
        WHERE l.id = ? AND u.company_id = ?
    ", [$leave_id, $company_id])['data'][0] ?? null;

    if (!$leave) return false;

    if ($role_id == 1) return true; // Admin

    if ($role_id == 6) { // Manager
        // Check department match
        $mgr = query($mysqli,
            "SELECT department_id FROM employees WHERE user_id = ?",
            [$user_id])['data'][0] ?? null;
        $emp = query($mysqli,
            "SELECT department_id FROM employees WHERE id = ?",
            [$leave['employee_id']])['data'][0] ?? null;
        return $mgr && $emp && $mgr['department_id'] === $emp['department_id']
               && in_array($leave['employee_role'], [4]); // Employee only
    }

    if ($role_id == 3) { // HR
        return in_array($leave['employee_role'], [4, 6]); // Employee OR Manager
    }

    if ($role_id == 2) { // Company Owner
        return $leave['employee_role'] == 3; // HR only
    }

    return false;
}
```

### 2. `getPendingLeavesForApprover($mysqli, $user_id, $role_id, $company_id)`

```php
function getPendingLeavesForApprover($mysqli, $user_id, $role_id, $company_id) {
    $employee_info = query($mysqli,
        "SELECT id FROM employees WHERE user_id = ?",
        [$user_id])['data'][0] ?? null;
    $employee_id = $employee_info['id'] ?? 0;

    if ($role_id == 6) { // Manager
        $dept = query($mysqli,
            "SELECT department_id FROM employees WHERE id = ?",
            [$employee_id])['data'][0] ?? null;
        $dept_id = $dept['department_id'] ?? 0;

        return query($mysqli, "
            SELECT l.*, e.first_name, e.last_name, u.role_id
            FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE u.company_id = ?
              AND l.status = 'pending'
              AND e.department_id = ?
              AND e.user_id != ?
              AND u.role_id = 4
            ORDER BY l.applied_at DESC
        ", [$company_id, $dept_id, $user_id])['data'] ?? [];
    }

    if ($role_id == 3) { // HR
        return query($mysqli, "
            SELECT l.*, e.first_name, e.last_name, u.role_id
            FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE u.company_id = ?
              AND l.status = 'pending'
              AND u.role_id IN (4, 6)
              AND e.user_id != ?
            ORDER BY l.applied_at DESC
        ", [$company_id, $user_id])['data'] ?? [];
    }

    if ($role_id == 2) { // Company Owner
        return query($mysqli, "
            SELECT l.*, e.first_name, e.last_name, u.role_id
            FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            WHERE u.company_id = ?
              AND l.status = 'pending'
              AND u.role_id = 3
            ORDER BY l.applied_at DESC
        ", [$company_id])['data'] ?? [];
    }

    return [];
}
```

### 3. `getApproverName($mysqli, $user_id)`

```php
function getApproverName($mysqli, $user_id) {
    if (!$user_id) return null;

    $result = query($mysqli,
        "SELECT e.first_name, e.last_name FROM employees e
         JOIN users u ON u.id = ?
         WHERE e.user_id = u.id",
        [$user_id])['data'][0] ?? null;

    return $result ? "{$result['first_name']} {$result['last_name']}" : null;
}
```

---

## Security Checkpoints

### ✅ All Endpoints Must:

1. Check `isLoggedIn()`
2. Validate company_id matches session
3. Validate user role with strict server-side checks
4. Never trust user input for role/permissions
5. Log all approval actions (recommended)
6. Return only authorized data in responses

### ❌ NEVER:

-   Allow UI-only role checks to determine backend logic
-   Return sensitive leave data to unauthorized roles
-   Allow cascading approvals (manager approves for HR, etc.)
-   Modify approval hierarchy without explicit code changes

---

## Implementation Checklist

-   [ ] Add helper functions to `includes/functions.php`
-   [ ] Refactor `api_leaves.php` with role-based logic
-   [ ] Update `get_pending_requests` with role-specific queries
-   [ ] Add `approve_or_reject` endpoint with authorization checks
-   [ ] Update frontend UI in `company/leaves.php` to use new endpoints
-   [ ] Add optional audit logging
-   [ ] Test all role combinations
-   [ ] Document role-based URLs for frontend

---

## Testing Scenarios

### Scenario 1: Employee Applies

```
Employee (ID 4) applies for leave
→ API creates: status='pending', approved_by=NULL
✓ System waits for manager approval
```

### Scenario 2: Manager Approves Employee Leave

```
Manager (ID 6) approves Employee (ID 4) leave
→ Check: Employee in manager's department? YES
→ Update: status='approved', approved_by=manager_user_id
✓ Leave approved, attendance marked
```

### Scenario 3: HR Overrides Employee Leave

```
HR (ID 3) rejects Employee (ID 4) approved leave
→ Check: Can HR approve employees? YES
→ Update: status='rejected', approved_by=hr_user_id
✓ Leave rejected (override)
```

### Scenario 4: Owner Approves HR Leave

```
Manager applies leave → HR approves → Owner approves
Owner (ID 2) approves HR (ID 3) leave
→ Check: Is approver HR? YES
→ Update: status='approved', approved_by=owner_user_id
✓ Multi-level approval workflow complete
```

### Scenario 5: Unauthorized Access

```
Employee (ID 4) attempts to approve Manager leave
→ Check: Employee role can approve? NO
→ Return: "Not authorized"
✗ Request denied
```
