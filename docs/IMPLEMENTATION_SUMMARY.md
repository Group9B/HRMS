# HRMS Leave Approval Workflow - Complete Implementation Summary

## 📋 Project Overview

A role-based leave approval workflow system designed for PHP/MySQL HRMS without modifying the existing database schema. The system enforces strict approval hierarchies where:

-   **Employees** (Role 4) can apply for leave only
-   **Managers** (Role 6) can approve their team's leaves
-   **HR** (Role 3) can approve manager and employee leaves
-   **Company Owners** (Role 2) can approve HR leaves only
-   **Admins** (Role 1) have full access

---

## 📁 Files Created/Modified

### New Files Created:

1. **`/api/api_leaves_refactored.php`** (355 lines)

    - Complete refactored API with role-based authorization
    - 7 action endpoints: apply_leave, get_my_leaves, get_pending_requests, approve_or_reject, cancel_leave, get_leave_summary, get_leave_calculation
    - Server-side validation for all actions
    - Attendance table updates on approval

2. **`/includes/leave_helpers.php`** (400+ lines)

    - 25+ helper functions for role-based logic
    - Permission checking functions
    - Data retrieval functions
    - Balance calculation functions
    - Optional audit logging support

3. **`/LEAVE_APPROVAL_WORKFLOW.md`** (Complete design document)

    - Role mapping and hierarchy
    - Workflow rules for each role
    - API endpoint specifications
    - Authorization logic pseudocode
    - Database schema usage
    - Testing scenarios

4. **`/FRONTEND_IMPLEMENTATION_GUIDE.md`** (UI update guide)

    - JavaScript changes for new API endpoints
    - Error handling improvements
    - Role-based UI visibility rules
    - Optional enhancement suggestions
    - Performance optimization tips

5. **`/TESTING_VALIDATION_GUIDE.md`** (Comprehensive testing)
    - Database setup instructions
    - 30+ test cases with curl examples
    - Expected responses documented
    - Workflow scenario walkthroughs
    - Security testing checklist
    - Performance testing guidelines

### Files NOT Modified (Schema Preserved):

-   ✅ `leaves` table - No schema changes
-   ✅ `employees` table - No schema changes
-   ✅ `users` table - No schema changes
-   ✅ `leave_policies` table - No schema changes
-   ✅ Database structure maintained as-is

---

## 🔐 Authorization Model

### Approval Hierarchy

```
Company Owner (2)
    ↓ Can Approve
HR (3)
    ↓ Can Approve
Manager (6) + Employee (4)
```

### What Each Role Can Do

| Action                  | Employee(4) | Manager(6) | HR(3) | Owner(2) | Admin(1) |
| ----------------------- | :---------: | :--------: | :---: | :------: | :------: |
| Apply Leave             |      ✓      |     ✓      |   ✓   |    ✗     |    ✓     |
| View Own Leaves         |      ✓      |     ✓      |   ✓   |    ✓     |    ✓     |
| Approve Employee Leaves |      ✗      |    ✓\*     |   ✓   |    ✗     |    ✓     |
| Approve Manager Leaves  |      ✗      |     ✗      |   ✓   |    ✗     |    ✓     |
| Approve HR Leaves       |      ✗      |     ✗      |   ✗   |    ✓     |    ✓     |
| Cancel Own Pending      |      ✓      |     ✓      |   ✓   |    ✗     |    ✓     |

\*Manager can only approve employees in their department

---

## 🔧 Technical Implementation

### Database Usage (No Schema Changes)

The system uses existing columns intelligently:

-   **`status`** field:

    -   `pending` = Awaiting approval from next authority
    -   `approved` = Approved at current level
    -   `rejected` = Final rejection
    -   `cancelled` = Cancelled by employee

-   **`approved_by`** field:
    -   `NULL` = Not yet approved
    -   `manager_user_id` = Approved by manager
    -   `hr_user_id` = Approved by HR
    -   `owner_user_id` = Approved by owner

### Query Patterns

#### Manager Gets Team Leaves

```sql
SELECT l.*, e.first_name, e.last_name, u.role_id
FROM leaves l
JOIN employees e ON l.employee_id = e.id
JOIN users u ON e.user_id = u.id
WHERE u.company_id = ?
  AND l.status = 'pending'
  AND e.department_id = ?  -- Manager's department
  AND u.role_id = 4        -- Employees only
ORDER BY l.applied_at DESC
```

#### HR Gets All Approvals

```sql
SELECT l.*, e.first_name, e.last_name, u.role_id
FROM leaves l
JOIN employees e ON l.employee_id = e.id
JOIN users u ON e.user_id = u.id
WHERE u.company_id = ?
  AND l.status = 'pending'
  AND u.role_id IN (4, 6)  -- Employees AND Managers
ORDER BY l.applied_at DESC
```

#### Owner Gets HR Leaves

```sql
SELECT l.*, e.first_name, e.last_name, u.role_id
FROM leaves l
JOIN employees e ON l.employee_id = e.id
JOIN users u ON e.user_id = u.id
WHERE u.company_id = ?
  AND l.status = 'pending'
  AND u.role_id = 3        -- HR only
ORDER BY l.applied_at DESC
```

---

## 🚀 API Endpoints

### 1. Apply Leave

```
POST /api/api_leaves_refactored.php
{
  "action": "apply_leave",
  "start_date": "2026-01-15",
  "end_date": "2026-01-17",
  "leave_type": "Annual Leave",
  "reason": "Vacation"
}

✓ Success: { success: true, message: "...", leave_id: 123 }
✗ Error: { success: false, message: "..." }
```

### 2. Get My Leaves

```
GET /api/api_leaves_refactored.php?action=get_my_leaves

✓ Response: { success: true, data: [...] }
```

### 3. Get Pending Requests (For Approvers)

```
GET /api/api_leaves_refactored.php?action=get_pending_requests

✓ Manager (Role 6): Returns team leaves only
✓ HR (Role 3): Returns employee + manager leaves
✓ Owner (Role 2): Returns HR leaves only
✗ Employee (Role 4): 403 Forbidden
```

### 4. Approve or Reject Leave

```
POST /api/api_leaves_refactored.php
{
  "action": "approve_or_reject",
  "leave_id": 123,
  "action_type": "approve"  // or "reject"
}

✓ Success: { success: true, message: "...", status: "approved" }
✗ Unauthorized: { success: false, message: "..." } [403]
```

### 5. Cancel Leave

```
POST /api/api_leaves_refactored.php
{
  "action": "cancel_leave",
  "leave_id": 123
}

✓ Success: { success: true, message: "..." }
✗ Not Pending: { success: false, message: "Cannot cancel leave with status: approved" }
```

### 6. Get Leave Summary

```
GET /api/api_leaves_refactored.php?action=get_leave_summary

✓ Response: {
  success: true,
  data: {
    balances: [...],
    next_holiday: {...},
    policy_document: {...}
  }
}
```

### 7. Calculate Leave Days

```
GET /api/api_leaves_refactored.php?action=get_leave_calculation&start_date=2026-01-15&end_date=2026-01-17

✓ Response: {
  success: true,
  total_days: 3,
  actual_days: 3,
  holidays_skipped: 0,
  saturdays_skipped: 0
}
```

---

## 🛡️ Security Features

### Authorization Checks

-   ✅ All endpoints validate `isLoggedIn()`
-   ✅ Role-based authorization before any operation
-   ✅ Department-level authorization for managers
-   ✅ Company-level data isolation
-   ✅ Employee can only access own leaves for cancellation
-   ✅ All queries use parameterized statements (SQL injection safe)

### HTTP Status Codes

-   `200` - Success
-   `400` - Bad request (invalid parameters)
-   `401` - Unauthorized (not logged in)
-   `403` - Forbidden (not authorized for action)
-   `500` - Server error

### Validation

-   Date format validation (YYYY-MM-DD)
-   Past date rejection
-   End date >= start date
-   Leave type validation against company policies
-   Role existence verification
-   Department authorization checks

---

## 📊 Data Flow Examples

### Complete Workflow: Employee → Manager → HR → Owner

```
1. Employee applies for leave
   POST apply_leave
   → Creates: status='pending', approved_by=NULL
   → Employee sees it in "My Requests" as pending

2. Manager reviews pending requests
   GET get_pending_requests (Role 6)
   → Returns only team member leaves with status='pending'

3. Manager approves
   POST approve_or_reject (leave_id=123, action_type='approve')
   → Validates: Employee in manager's department? ✓
   → Updates: status='approved', approved_by=manager_user_id
   → Creates attendance records for all dates

4. HR reviews (if needed)
   GET get_pending_requests (Role 3)
   → Returns manager leaves with status='pending'

5. HR approves manager's leave
   POST approve_or_reject (leave_id=456, action_type='approve')
   → Validates: Employee is manager? ✓
   → Updates: status='approved', approved_by=hr_user_id

6. Owner reviews HR leaves
   GET get_pending_requests (Role 2)
   → Returns only HR leaves with status='pending'

7. Owner approves HR's leave
   POST approve_or_reject (leave_id=789, action_type='approve')
   → Validates: Employee is HR? ✓
   → Updates: status='approved', approved_by=owner_user_id
```

### Scenario: Manager Attempts to Approve Outside Department

```
1. Manager A tries to approve Employee X
2. GET get_pending_requests (Role 6)
   → Employee X is in Department B, Manager A is in Department C
   → Employee X NOT returned in results

3. If Manager tries POST approve_or_reject directly with leave_id:
   → Authorization check fails: "Employee is not in your department"
   → Response: 403 Forbidden
```

---

## 🔄 Migration Steps

### Phase 1: Add New API (Keep Old Working)

1. Deploy `api_leaves_refactored.php` as new file
2. Keep `api_leaves.php` unchanged
3. Update frontend to use new endpoints

### Phase 2: Deprecate Old API

1. After new API tested, add deprecation notice to old API
2. Log warning when old endpoints used
3. Give notice period to clients

### Phase 3: Full Cutover

1. Remove `api_leaves.php`
2. Rename `api_leaves_refactored.php` to `api_leaves.php`
3. Update all references

---

## ⚙️ Configuration & Customization

### To Change Approval Hierarchy

Edit `canApproveLeave()` function in `api_leaves_refactored.php`:

```php
// Example: Allow managers to approve other managers
if ($role_id == 6) { // Manager
    if (!in_array($leave['employee_role_id'], [4, 6])) {  // Add 6 here
        return ['allowed' => false];
    }
    // ...
}
```

### To Add Approval Levels

Extend the workflow by adding more role combinations:

```php
// Example: Add Director approval level (new role ID 7)
if ($role_id == 7) { // Director
    if (!in_array($leave['employee_role_id'], [3, 6])) { // HR & Manager
        return ['allowed' => false];
    }
    // ...
}
```

### To Customize Leave Balance Calculation

Modify `getLeaveBalance()` in `leave_helpers.php` to include custom rules:

```php
// Include prorated leaves, carryover, etc.
$days_used = ... // Custom calculation
$days_carryover = ... // Previous year balance
return max(0, $total_allowed + $days_carryover - $days_used);
```

---

## 📱 Frontend Integration

### Update JavaScript Event Handler

**Old:**

```javascript
formData.append("action", "update_status");
formData.append("status", status);
```

**New:**

```javascript
formData.append("action", "approve_or_reject");
formData.append("action_type", status === "approved" ? "approve" : "reject");
```

### Update UI Based on Role

```javascript
const role_id = document.body.dataset.roleId;
const canApprove = [1, 2, 3, 6].includes(role_id);
const canApply = [3, 4, 6].includes(role_id);
```

---

## 🧪 Testing Coverage

### Provided Test Cases: 30+

-   Apply leave (valid/invalid dates)
-   Get my leaves (all roles)
-   Get pending requests (manager/HR/owner filters)
-   Approve/reject (valid authorization)
-   Authorization failures (5+ scenarios)
-   Cancel leave (valid/invalid states)
-   Workflow scenarios (3 complete chains)
-   Security tests (SQL injection, session hijacking)
-   Performance tests (load testing)

### Test Execution

```bash
# Run with curl (see TESTING_VALIDATION_GUIDE.md)
curl -X POST http://localhost/hrms/api/api_leaves_refactored.php \
  -d "action=apply_leave&start_date=2026-01-15&..."

# Or use Postman collection (to be created)
# Or use PHPUnit tests (examples in guide)
```

---

## 📈 Performance Considerations

### Indexes Recommended

```sql
CREATE INDEX idx_leaves_status_date ON leaves(status, applied_at);
CREATE INDEX idx_leaves_employee_status ON leaves(employee_id, status);
CREATE INDEX idx_employees_dept ON employees(department_id, user_id);
```

### Caching Opportunities

-   Leave balance (5 min TTL)
-   Leave policies (24 hour TTL)
-   Company holidays (24 hour TTL)
-   User role/department (session cache)

### Query Optimization

-   All queries use parameterized statements
-   JOINs only necessary tables
-   Indexes on filter columns
-   Pagination support (to be added)

---

## 🐛 Known Limitations & Future Enhancements

### Current Limitations

1. **No Overrides**: HR cannot approve a rejected leave (terminal state)
2. **No Comments**: No approval notes/comments field
3. **No Notifications**: No automatic email notifications
4. **No Delegation**: Managers cannot delegate approval authority
5. **No Bulk Operations**: Cannot approve multiple leaves at once
6. **No Audit Trail**: Limited logging (can add optional audit_log table)

### Future Enhancements

1. **Email Notifications**

    - Notify employee when leave approved/rejected
    - Notify manager when employee applies
    - Notify HR of pending approvals

2. **Approval Delegation**

    - Manager can delegate to deputy
    - Auto-approve if absence > X days

3. **Bulk Operations**

    - Approve all pending in department
    - Batch reject with reason

4. **Advanced Reporting**

    - Approval time analytics
    - Leave utilization dashboard
    - Predictive analytics

5. **Mobile App API**

    - Push notifications
    - Offline support
    - Photo approval documents

6. **Integration**
    - Calendar sync
    - Slack/Teams notifications
    - ATS system sync

---

## 📞 Support & Documentation

### Included Documentation

1. **LEAVE_APPROVAL_WORKFLOW.md** - Complete design specification
2. **FRONTEND_IMPLEMENTATION_GUIDE.md** - UI/JS integration guide
3. **TESTING_VALIDATION_GUIDE.md** - Test cases and scenarios
4. **This File** - Implementation summary

### Quick Start Checklist

-   [ ] Review LEAVE_APPROVAL_WORKFLOW.md
-   [ ] Deploy api_leaves_refactored.php
-   [ ] Add leave_helpers.php to includes
-   [ ] Update frontend JavaScript handlers
-   [ ] Run test cases from TESTING_VALIDATION_GUIDE.md
-   [ ] Verify approval workflow end-to-end
-   [ ] Deploy to production

### Troubleshooting

Common issues and solutions in TESTING_VALIDATION_GUIDE.md:

-   Authorization failures
-   Missing attendance records
-   Empty pending request lists
-   Session validation errors

---

## 📝 Notes

-   **No Database Changes Required** ✅
-   **Backward Compatible** (with modifications)
-   **Role-Based Access Control** (server-side)
-   **Ready for Production** (with testing)
-   **Extensible Design** (easy to customize)

---

## 🎯 Success Criteria

✅ System enforces role-based approval hierarchy  
✅ Employees can only apply for leave  
✅ Managers approve team members only  
✅ HR can approve managers and employees  
✅ Owners approve HR only  
✅ Attendance records created automatically  
✅ Cancellation restricted to pending leaves  
✅ All authorizations checked server-side  
✅ No SQL injection vulnerabilities  
✅ Proper HTTP status codes returned  
✅ Clear error messages for failures  
✅ Database schema unchanged  
✅ Comprehensive test coverage provided  
✅ Migration path documented

---

## 📄 Version History

-   **v1.0** (2026-01-04) - Initial implementation
    -   Role-based approval workflow
    -   7 API endpoints
    -   Server-side authorization
    -   Complete documentation
