# HRMS Leave Workflow - Visual Diagrams & Flowcharts

## 1. Approval Hierarchy

```
┌─────────────────────────────────────────────────────────────────┐
│                     LEAVE APPROVAL HIERARCHY                      │
└─────────────────────────────────────────────────────────────────┘

                        COMPANY OWNER (2)
                              ↑
                       Can Approve: HR Leaves

                         HUMAN RESOURCE (3)
                              ↑
                Can Approve: Manager & Employee Leaves

                ┌─────────────────────────────┐
                ↓                             ↑
             MANAGER (6)                EMPLOYEE (4)
        Can Approve:                  Can Apply: Only
        Employee Leaves
        (Same Dept Only)

```

## 2. Leave Application Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                   EMPLOYEE APPLIES FOR LEAVE                     │
└─────────────────────────────────────────────────────────────────┘

Start
  │
  ├─→ Fill Form
  │    • Start Date (≥ today)
  │    • End Date (≥ Start Date)
  │    • Leave Type (from policy)
  │    • Reason (optional)
  │
  ├─→ Validate
  │    • Date format ✓
  │    • Start ≥ today ✓
  │    • End ≥ Start ✓
  │    • Type exists ✓
  │
  ├─→ Create Leave Record
  │    • status = 'pending'
  │    • approved_by = NULL
  │
  └─→ Success
      Message: "Leave request submitted!"
      Status: Pending approval

```

## 3. Manager Approval Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    MANAGER APPROVES LEAVE                        │
└─────────────────────────────────────────────────────────────────┘

Manager Views Pending Requests
  │
  ├─→ Get Pending Leaves
  │    Query: WHERE e.department_id = manager_dept_id
  │            AND l.status = 'pending'
  │            AND u.role_id = 4 (employees only)
  │
  ├─→ Manager Sees List
  │    • Employee Name
  │    • Leave Type
  │    • Date Range
  │    • Status
  │
  ├─→ Manager Clicks Approve/Reject
  │
  ├─→ Authorization Check
  │    ┌─────────────────────────────┐
  │    │ Is role 6 (Manager)?        │ → No: DENY
  │    └─────────────────────────────┘
  │    ↓ Yes
  │    ┌─────────────────────────────┐
  │    │ Employee in my department?  │ → No: DENY
  │    └─────────────────────────────┘
  │    ↓ Yes
  │    ┌─────────────────────────────┐
  │    │ Leave status = pending?     │ → No: DENY
  │    └─────────────────────────────┘
  │    ↓ Yes
  │
  ├─→ ALLOW - Update Leave
  │    • Set status = 'approved' OR 'rejected'
  │    • Set approved_by = manager_user_id
  │
  ├─→ If Approved:
  │    • Create attendance records
  │    • Mark each day as 'leave'
  │
  └─→ Reload Tables
      Employee sees leave status updated

```

## 4. HR Approval Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                      HR APPROVES LEAVES                          │
└─────────────────────────────────────────────────────────────────┘

HR Views Pending Requests
  │
  ├─→ Get Pending Leaves
  │    Query: WHERE u.role_id IN (4, 6)  ← Employees + Managers
  │            AND l.status = 'pending'
  │
  ├─→ HR Sees Two Groups
  │
  │    ┌──────────────────────────────────┐
  │    │  EMPLOYEE LEAVES                 │
  │    │  • John - Annual Leave - Pending │
  │    │  • Jane - Sick Leave - Pending   │
  │    └──────────────────────────────────┘
  │
  │    ┌──────────────────────────────────┐
  │    │  MANAGER LEAVES                  │
  │    │  • Bob - Personal Leave - Pending│
  │    └──────────────────────────────────┘
  │
  ├─→ HR Clicks Approve/Reject
  │
  ├─→ Authorization Check
  │    ┌──────────────────────────────┐
  │    │ Is role 3 (HR)?              │ → No: DENY
  │    └──────────────────────────────┘
  │    ↓ Yes
  │    ┌──────────────────────────────┐
  │    │ Employee role in [4, 6]?     │ → No: DENY
  │    │ (Employee or Manager?)       │
  │    └──────────────────────────────┘
  │    ↓ Yes
  │
  ├─→ ALLOW
  │    • Update leave status
  │    • Set approved_by = hr_user_id
  │    • Create attendance if approved
  │
  └─→ Done

```

## 5. Company Owner Approval Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                   OWNER APPROVES HR LEAVES                       │
└─────────────────────────────────────────────────────────────────┘

Owner Views Pending Requests
  │
  ├─→ Get Pending HR Leaves Only
  │    Query: WHERE u.role_id = 3 (HR only)
  │            AND l.status = 'pending'
  │
  ├─→ Owner Sees
  │    • HR Manager Name
  │    • Leave Type
  │    • Dates
  │    • Approve/Reject Buttons
  │
  ├─→ Owner Clicks Approve/Reject
  │
  ├─→ Authorization Check
  │    ┌──────────────────────────────┐
  │    │ Is role 2 (Owner)?           │ → No: DENY
  │    └──────────────────────────────┘
  │    ↓ Yes
  │    ┌──────────────────────────────┐
  │    │ Employee role = 3 (HR)?      │ → No: DENY
  │    └──────────────────────────────┘
  │    ↓ Yes
  │
  ├─→ ALLOW
  │    • Update status
  │    • Set approved_by = owner_user_id
  │    • Create attendance if approved
  │
  └─→ Done

```

## 6. Employee Cancellation Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                   EMPLOYEE CANCELS LEAVE                         │
└─────────────────────────────────────────────────────────────────┘

Employee Views "My Requests"
  │
  ├─→ Gets all employee's leaves
  │
  ├─→ Sees Leave Record
  │    • Status: Pending
  │    • Cancel Button appears (only for pending)
  │
  ├─→ Clicks Cancel Button
  │
  ├─→ Confirmation Dialog
  │    "Are you sure you want to cancel?"
  │    [Cancel] [Proceed]
  │
  ├─→ Authorization Check
  │    ┌──────────────────────────────┐
  │    │ Leave belongs to current     │ → No: DENY
  │    │ employee?                    │
  │    └──────────────────────────────┘
  │    ↓ Yes
  │    ┌──────────────────────────────┐
  │    │ Status = 'pending'?          │ → No: DENY
  │    │ (Can't cancel approved)      │
  │    └──────────────────────────────┘
  │    ↓ Yes
  │
  ├─→ ALLOW
  │    • Set status = 'cancelled'
  │
  └─→ Toast: "Leave cancelled successfully"
      Record disappears from pending

```

## 7. Database State Transitions

```
┌─────────────────────────────────────────────────────────────────┐
│                    LEAVE STATUS TRANSITIONS                      │
└─────────────────────────────────────────────────────────────────┘

                           PENDING
                          /       \
                   APPROVE         REJECT
                    /                \
              APPROVED              REJECTED
                  ↓                   (Terminal)
           (Attendance
            Created)          PENDING → CANCELLED
                              (Employee Action)
                              (Terminal)

                         APPROVAL CHAIN

  PENDING → APPROVED → APPROVED → APPROVED
            (Manager)   (HR)      (Owner)

  Each level can be rejected at any point
```

## 8. Role-Based View Matrix

```
┌────────────────────────────────────────────────────────────────┐
│              WHAT EACH ROLE SEES IN UI                          │
└────────────────────────────────────────────────────────────────┘

EMPLOYEE (4)
┌─────────────────────────────────────────┐
│ My Leave Requests Tab:                  │
│  • Own leaves (all statuses)            │
│  • Cancel button (pending only)         │
│                                         │
│ Approval Tab: NOT VISIBLE              │
└─────────────────────────────────────────┘

MANAGER (6)
┌─────────────────────────────────────────┐
│ My Leave Requests Tab:                  │
│  • Own leaves (all statuses)            │
│  • Cancel button (pending only)         │
│                                         │
│ Approval Tab:                           │
│  • Team member leaves (pending only)    │
│  • Approve/Reject buttons               │
│  • Dept filtered automatically          │
└─────────────────────────────────────────┘

HR (3)
┌─────────────────────────────────────────┐
│ My Leave Requests Tab:                  │
│  • Own leaves (all statuses)            │
│  • Cancel button (pending only)         │
│                                         │
│ Approval Tab:                           │
│  • Manager leaves (pending only)        │
│  • Employee leaves (pending only)       │
│  • Approve/Reject buttons for each      │
│  • Separate sections if needed          │
└─────────────────────────────────────────┘

COMPANY OWNER (2)
┌─────────────────────────────────────────┐
│ My Leave Requests Tab: HIDDEN          │
│                                         │
│ Approval Tab:                           │
│  • HR leaves only (pending)             │
│  • Approve/Reject buttons               │
│                                         │
│ All Leaves View (Optional):             │
│  • Entire company leaves (read-only)    │
│  • Statistics & dashboard               │
└─────────────────────────────────────────┘

ADMIN (1)
┌─────────────────────────────────────────┐
│ My Leave Requests Tab:                  │
│  • Own leaves                           │
│                                         │
│ Approval Tab:                           │
│  • ALL leaves (can approve anyone)      │
│  • No restrictions applied              │
│                                         │
│ Admin Dashboard (Optional):             │
│  • All leaves data                      │
│  • Approval statistics                  │
└─────────────────────────────────────────┘
```

## 9. Authorization Decision Tree

```
┌─────────────────────────────────────────────────────────────────┐
│              CAN USER APPROVE THIS LEAVE?                        │
└─────────────────────────────────────────────────────────────────┘

START: User wants to approve leave_id=X
  │
  ├─→ [1] Is user logged in?
  │   ├─ No → 401 UNAUTHORIZED
  │   └─ Yes → Continue
  │
  ├─→ [2] Is role_id = 1 (Admin)?
  │   ├─ Yes → ALLOW
  │   └─ No → Continue
  │
  ├─→ [3] Is role_id = 2 (Owner)?
  │   ├─ Yes: Is employee role = 3 (HR)?
  │   │   ├─ Yes → ALLOW
  │   │   └─ No → 403 FORBIDDEN
  │   └─ No → Continue
  │
  ├─→ [4] Is role_id = 3 (HR)?
  │   ├─ Yes: Is employee role in [3, 4, 6]?
  │   │   ├─ Yes → ALLOW
  │   │   └─ No → 403 FORBIDDEN
  │   └─ No → Continue
  │
  ├─→ [5] Is role_id = 6 (Manager)?
  │   ├─ Yes: Is employee role = 4 (Employee)?
  │   │   ├─ Yes: Are we in same department?
  │   │   │   ├─ Yes → ALLOW
  │   │   │   └─ No → 403 FORBIDDEN
  │   │   └─ No → 403 FORBIDDEN
  │   └─ No → Continue
  │
  ├─→ [6] Default (Employee or other)
  │   └─ 403 FORBIDDEN

END: Authorization decision made
```

## 10. Complete Multi-Level Approval Sequence

```
TIME    ACTOR           ACTION              STATUS              approved_by
────────────────────────────────────────────────────────────────────────────

T=0     EMPLOYEE        Apply Leave         pending             NULL
        (John)          Annual Leave
                        Jan 15-17

T+1H    MANAGER         Review Pending      (no change)         NULL
        (Jane)          - Sees John's leave

T+2H    MANAGER         Approve             approved            5
        (Jane)          - Creates attendance records

T+4H    HR              Review Pending      (no change)         5
        (Bob)           - No pending employee leaves

T+5H    MANAGER         Apply Leave         pending             NULL
        (Jane)          Sick Leave
                        Feb 1-2

T+6H    HR              Review Pending      (no change)         NULL
        (Bob)           - Sees Jane's leave

T+7H    HR              Approve             approved            6
        (Bob)           - Creates attendance records

T+8H    HR              Apply Leave         pending             NULL
        (Bob)           Personal Leave
                        Mar 1

T+9H    OWNER           Review Pending      (no change)         NULL
        (Alice)         - Sees Bob's leave

T+10H   OWNER           Approve             approved            7
        (Alice)         - Creates attendance records

FINAL STATE:
- John's leave: approved (by Manager Jane)
- Jane's leave: approved (by HR Bob)
- Bob's leave: approved (by Owner Alice)
- All attendance records created
- All workflow complete
```

## 11. API Response Timeline

```
                    REQUEST FLOW

┌──────────────────────────────────────┐
│ 1. Client Submits Form               │
│    (Approve/Reject button clicked)   │
└────────┬─────────────────────────────┘
         │
         │ POST /api/api_leaves_refactored.php
         │ {action: approve_or_reject, leave_id: X, action_type: approve}
         │
         ↓
┌──────────────────────────────────────┐
│ 2. API Receives Request              │
│    - Check session validity          │
│    - Extract parameters              │
└────────┬─────────────────────────────┘
         │
         ↓
┌──────────────────────────────────────┐
│ 3. Authorization Check               │
│    - Verify role_id can approve      │
│    - Check department/role match     │
│    - Return 403 if denied            │
└────────┬─────────────────────────────┘
         │
         ├─ DENIED → Response 403
         │
         └─ ALLOWED
         │
         ↓
┌──────────────────────────────────────┐
│ 4. Fetch Leave Details               │
│    - Get leave record                │
│    - Check status = pending          │
│    - Get employee info               │
└────────┬─────────────────────────────┘
         │
         ├─ NOT FOUND → Response 400
         │ NOT PENDING → Response 400
         │
         └─ VALID
         │
         ↓
┌──────────────────────────────────────┐
│ 5. Update Database                   │
│    - Set status = approved/rejected  │
│    - Set approved_by = user_id       │
│    - Create attendance (if approved) │
└────────┬─────────────────────────────┘
         │
         ├─ ERROR → Response 500
         │
         └─ SUCCESS
         │
         ↓
┌──────────────────────────────────────┐
│ 6. Return JSON Response              │
│    {                                 │
│      success: true,                  │
│      message: "Approved!",           │
│      status: "approved",             │
│      approved_by_user_id: 5          │
│    }                                 │
└────────┬─────────────────────────────┘
         │
         ↓
┌──────────────────────────────────────┐
│ 7. Client Processes Response         │
│    - Show toast notification         │
│    - Reload tables                   │
│    - Update UI state                 │
└──────────────────────────────────────┘
```

## 12. Error Handling Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    ERROR HANDLING FLOWCHART                      │
└─────────────────────────────────────────────────────────────────┘

User Attempt
  │
  ├─→ Not Logged In?
  │   └─ Return: 401 "Unauthorized access"
  │
  ├─→ Invalid Parameter?
  │   └─ Return: 400 "Missing leave ID"
  │
  ├─→ Leave Not Found?
  │   └─ Return: 400 "Leave not found"
  │
  ├─→ Not Your Leave (cancel)?
  │   └─ Return: 403 "Not authorized"
  │
  ├─→ No Permission (approve)?
  │   ├─ Is Employee? → 403 "Employees cannot approve"
  │   ├─ Wrong Role? → 403 "Managers can only approve employees"
  │   ├─ Wrong Dept? → 403 "Employee not in your department"
  │   └─ Other? → 403 "Not authorized"
  │
  ├─→ Leave Not Pending?
  │   └─ Return: 400 "Cannot modify status: approved"
  │
  ├─→ Database Error?
  │   └─ Return: 500 "Failed to update leave"
  │
  └─→ Success!
      └─ Return: 200 + JSON response
```
