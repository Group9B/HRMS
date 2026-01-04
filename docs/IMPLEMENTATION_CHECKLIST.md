# HRMS Leave Approval System - Implementation Checklist

## Pre-Implementation Phase

### Planning & Requirements

-   [ ] Review LEAVE_APPROVAL_WORKFLOW.md document
-   [ ] Review IMPLEMENTATION_SUMMARY.md for overview
-   [ ] Review WORKFLOW_DIAGRAMS.md for visual understanding
-   [ ] Understand current database schema (no changes needed)
-   [ ] Identify all roles in system (confirm IDs: 1,2,3,4,6)
-   [ ] Document current leave approval process (if any)
-   [ ] Stakeholder sign-off on role definitions

### Team Preparation

-   [ ] Assign backend developer for API work
-   [ ] Assign frontend developer for UI integration
-   [ ] Assign QA lead for testing
-   [ ] Create project timeline
-   [ ] Schedule kickoff meeting

### Environment Setup

-   [ ] Development environment ready
-   [ ] Testing database available
-   [ ] Version control (git) configured
-   [ ] API testing tools ready (Postman/curl)
-   [ ] Database backup before changes

---

## Backend Implementation Phase

### 1. Deploy API Files

-   [ ] Copy `api_leaves_refactored.php` to `/api/`
-   [ ] Copy `leave_helpers.php` to `/includes/`
-   [ ] Verify file permissions are correct
-   [ ] Test API endpoints directly (curl)
-   [ ] Check error logs for any issues

### 2. API Functionality Testing

-   [ ] Test `apply_leave` endpoint
    -   [ ] Valid application succeeds
    -   [ ] Past date rejected
    -   [ ] Invalid leave type rejected
    -   [ ] Missing fields rejected
-   [ ] Test `get_my_leaves` endpoint
    -   [ ] Returns only user's leaves
    -   [ ] All roles can access
-   [ ] Test `get_pending_requests` endpoint
    -   [ ] Manager sees only team leaves
    -   [ ] HR sees employee + manager leaves
    -   [ ] Owner sees HR leaves only
    -   [ ] Employee sees nothing
-   [ ] Test `approve_or_reject` endpoint
    -   [ ] Manager can approve employee
    -   [ ] Manager cannot approve outside dept
    -   [ ] HR can approve manager + employee
    -   [ ] Owner can approve HR only
    -   [ ] Employee cannot approve
    -   [ ] Status changes correctly
    -   [ ] approved_by field populated
-   [ ] Test `cancel_leave` endpoint
    -   [ ] User can cancel own pending
    -   [ ] Cannot cancel approved
    -   [ ] Cannot cancel others' leaves
-   [ ] Test `get_leave_summary` endpoint
    -   [ ] Balance calculated correctly
    -   [ ] Next holiday shown
    -   [ ] Policy document linked
-   [ ] Test `get_leave_calculation` endpoint
    -   [ ] Days calculated correctly
    -   [ ] Holidays excluded
    -   [ ] Saturdays excluded per policy

### 3. Error Handling Verification

-   [ ] 401 for unauthenticated
-   [ ] 403 for unauthorized
-   [ ] 400 for bad requests
-   [ ] 500 for server errors
-   [ ] Proper error messages in response

### 4. Security Testing

-   [ ] SQL injection prevention (parameterized queries)
-   [ ] Session validation on all endpoints
-   [ ] Role verification server-side
-   [ ] Department authorization checks
-   [ ] Company data isolation
-   [ ] No direct access to other companies' data

### 5. Database Integration

-   [ ] Attendance table created (if not exists)
-   [ ] Indexes added for performance
    -   [ ] `leaves(status, applied_at)`
    -   [ ] `leaves(employee_id, status)`
    -   [ ] `employees(department_id, user_id)`
-   [ ] Test attendance record creation on approval
-   [ ] Verify no data corruption

### 6. Backward Compatibility

-   [ ] Old API still works (if keeping)
-   [ ] New API doesn't break existing UI
-   [ ] Can run both simultaneously for testing
-   [ ] Deprecation path documented

---

## Frontend Implementation Phase

### 1. JavaScript Updates

-   [ ] Update approve/reject event handler
    -   [ ] Change `action` to `approve_or_reject`
    -   [ ] Change `status` to `action_type`
    -   [ ] Test button functionality
-   [ ] Update form submission
    -   [ ] Apply leave form still works
    -   [ ] Validation messages display
    -   [ ] Toast notifications appear
-   [ ] Update table refresh
    -   [ ] Tables reload after action
    -   [ ] Data displays correctly
    -   [ ] Sorting/filtering works

### 2. UI Role-Based Visibility

-   [ ] Employee (4)
    -   [ ] Sees "My Requests" tab
    -   [ ] Hidden "Approve" tab
    -   [ ] Can click "Apply Leave"
    -   [ ] Can cancel pending leaves
-   [ ] Manager (6)
    -   [ ] Sees both tabs
    -   [ ] "My Requests" shows own leaves
    -   [ ] "Approve" shows team leaves only
    -   [ ] Can approve/reject buttons work
-   [ ] HR (3)
    -   [ ] Sees both tabs
    -   [ ] "My Requests" shows own leaves
    -   [ ] "Approve" shows manager + employee leaves
    -   [ ] Separate sections if needed
-   [ ] Company Owner (2)
    -   [ ] "My Requests" hidden
    -   [ ] "Approve" shows HR leaves only
    -   [ ] Optional: All leaves view
-   [ ] Admin (1)
    -   [ ] Full access to all tabs
    -   [ ] Can approve any leave

### 3. Error Message Display

-   [ ] "Not authorized" message shows
-   [ ] "Department mismatch" message shows
-   [ ] "Invalid status" message shows
-   [ ] Messages are user-friendly
-   [ ] No technical error details shown to user

### 4. UI Performance

-   [ ] Table loads within 2 seconds
-   [ ] No UI lag on approval
-   [ ] Smooth animations
-   [ ] No console errors

### 5. Responsive Design

-   [ ] Works on desktop
-   [ ] Works on tablet
-   [ ] Works on mobile
-   [ ] Tables responsive
-   [ ] Buttons accessible

---

## Integration Testing Phase

### 1. End-to-End Workflows

#### Workflow 1: Employee → Manager Approves

-   [ ] Employee logs in
-   [ ] Employee applies for leave
-   [ ] Leave shows in "pending" status
-   [ ] Manager logs in
-   [ ] Manager sees pending leave
-   [ ] Manager approves leave
-   [ ] Employee's leave status changes to "approved"
-   [ ] Attendance records created
-   [ ] Manager can see updated status

#### Workflow 2: Manager → HR Approves

-   [ ] Manager applies for leave
-   [ ] HR logs in
-   [ ] HR sees manager's pending leave
-   [ ] HR approves manager's leave
-   [ ] Manager's leave status changes to "approved"
-   [ ] Attendance records created

#### Workflow 3: HR → Owner Approves

-   [ ] HR applies for leave
-   [ ] Owner logs in
-   [ ] Owner sees HR's pending leave
-   [ ] Owner approves HR's leave
-   [ ] HR's leave status changes to "approved"
-   [ ] Attendance records created

#### Workflow 4: Employee Cancels

-   [ ] Employee logs in
-   [ ] Employee views pending leave
-   [ ] Employee clicks "Cancel"
-   [ ] Confirmation dialog appears
-   [ ] Employee confirms cancellation
-   [ ] Leave status changes to "cancelled"
-   [ ] Leave removed from pending view

### 2. Authorization Testing

-   [ ] Manager cannot approve employee from other dept
-   [ ] Manager cannot approve other managers
-   [ ] HR cannot approve owners
-   [ ] Owner cannot approve employees
-   [ ] Employee cannot approve anyone
-   [ ] Admin can approve anyone

### 3. Data Integrity Testing

-   [ ] Leave balance calculated correctly
-   [ ] Attendance records accurate
-   [ ] No duplicate records created
-   [ ] Status transitions valid
-   [ ] approved_by field correct
-   [ ] applied_at timestamp correct

### 4. Concurrent Operations

-   [ ] Two approvers don't conflict
-   [ ] Multiple employees can apply simultaneously
-   [ ] No race conditions
-   [ ] Database locks handled properly

---

## Performance Testing Phase

### 1. Load Testing

-   [ ] System handles 100 concurrent users
-   [ ] Load time < 500ms for approval list
-   [ ] No timeout on approval action
-   [ ] Database queries optimized

### 2. Database Performance

-   [ ] Indexes created and verified
-   [ ] Query plans reviewed
-   [ ] No full table scans
-   [ ] Response time < 200ms per query

### 3. Scalability

-   [ ] 1000+ leaves handled
-   [ ] 500+ approval records processed
-   [ ] No degradation with large datasets

---

## Security Testing Phase

### 1. Authorization Testing

-   [ ] Session validation on all endpoints
-   [ ] Invalid session returns 401
-   [ ] Missing authorization returns 403
-   [ ] Role hijacking prevented

### 2. Input Validation

-   [ ] SQL injection attempts blocked
-   [ ] XSS attempts blocked
-   [ ] CSRF protection in place
-   [ ] File upload safe (if applicable)

### 3. Data Security

-   [ ] No sensitive data in logs
-   [ ] No hardcoded credentials
-   [ ] Database connection secure
-   [ ] Error messages don't leak info

### 4. Penetration Testing

-   [ ] Test with role manipulation
-   [ ] Test with parameter tampering
-   [ ] Test with invalid IDs
-   [ ] Test with negative values
-   [ ] Test with special characters

---

## User Acceptance Testing Phase

### 1. Employee Testing

-   [ ] Employee can apply for leave
-   [ ] Employee receives confirmation
-   [ ] Employee sees leave history
-   [ ] Employee can cancel pending
-   [ ] Easy to use interface

### 2. Manager Testing

-   [ ] Manager can view team leaves
-   [ ] Manager can approve leaves
-   [ ] Manager can reject leaves
-   [ ] Manager cannot approve outside team
-   [ ] Approval workflow intuitive

### 3. HR Testing

-   [ ] HR can view all pending leaves
-   [ ] HR can distinguish employee vs manager
-   [ ] HR can approve both types
-   [ ] HR can review approval history
-   [ ] Dashboard informative

### 4. Owner Testing

-   [ ] Owner can view HR leaves
-   [ ] Owner can approve/reject
-   [ ] Owner cannot modify employee leaves
-   [ ] Read-only access appropriate
-   [ ] Dashboard useful

### 5. Admin Testing

-   [ ] Admin has full access
-   [ ] Admin can audit all actions
-   [ ] Admin can override if needed

---

## Documentation Phase

### 1. Code Documentation

-   [ ] API endpoints documented
-   [ ] Helper functions documented
-   [ ] Authorization logic commented
-   [ ] Database queries explained

### 2. User Documentation

-   [ ] Employee guide created
-   [ ] Manager guide created
-   [ ] HR guide created
-   [ ] Owner guide created
-   [ ] Screenshots included

### 3. Admin Documentation

-   [ ] System architecture documented
-   [ ] Database schema explained
-   [ ] Troubleshooting guide
-   [ ] Maintenance procedures

### 4. Developer Documentation

-   [ ] API documentation complete
-   [ ] Integration examples provided
-   [ ] Code comments clear
-   [ ] Design decisions documented

---

## Deployment Phase

### Pre-Deployment

-   [ ] Full backup taken
-   [ ] Rollback plan documented
-   [ ] Deployment window scheduled
-   [ ] Stakeholders notified

### Deployment Steps

-   [ ] Deploy to staging environment
-   [ ] Run full test suite
-   [ ] Get sign-off from QA
-   [ ] Deploy to production
-   [ ] Monitor for errors
-   [ ] Update documentation

### Post-Deployment

-   [ ] Monitor error logs (24 hours)
-   [ ] Check performance metrics
-   [ ] Verify all workflows working
-   [ ] Get user feedback
-   [ ] Document any issues

### Rollback (If Needed)

-   [ ] Identify issue immediately
-   [ ] Notify stakeholders
-   [ ] Execute rollback plan
-   [ ] Verify previous version working
-   [ ] Root cause analysis
-   [ ] Fix and redeploy

---

## Post-Deployment Phase

### 1. Monitoring

-   [ ] Monitor API response times daily
-   [ ] Check error logs weekly
-   [ ] Database backup verification
-   [ ] Performance metrics tracking

### 2. User Support

-   [ ] Support team trained
-   [ ] FAQ documented
-   [ ] Help desk tickets monitored
-   [ ] User issues logged

### 3. Maintenance

-   [ ] Database optimization monthly
-   [ ] Security patches applied
-   [ ] Performance tuning as needed
-   [ ] Backup verification

### 4. Feedback & Improvements

-   [ ] Collect user feedback
-   [ ] Document feature requests
-   [ ] Plan improvements
-   [ ] Implement enhancements

---

## Testing Checklist

### API Test Cases: 30+

-   [ ] Apply leave (5 cases)
-   [ ] Get leaves (3 cases)
-   [ ] Get pending (4 cases)
-   [ ] Approve/reject (10 cases)
-   [ ] Cancel (3 cases)
-   [ ] Authorization (8 cases)
-   [ ] Error handling (5 cases)

### Manual Test Cases: 20+

-   [ ] Employee workflow (5 cases)
-   [ ] Manager workflow (5 cases)
-   [ ] HR workflow (5 cases)
-   [ ] Owner workflow (3 cases)
-   [ ] Error scenarios (2 cases)

### Browser Testing

-   [ ] Chrome
-   [ ] Firefox
-   [ ] Safari
-   [ ] Edge
-   [ ] Mobile browsers

---

## Sign-Off Checklist

### Development Team

-   [ ] Backend development complete
-   [ ] Frontend development complete
-   [ ] Code review passed
-   [ ] Testing complete
-   [ ] Documentation complete

### QA Team

-   [ ] Test cases executed
-   [ ] All tests passed
-   [ ] No critical issues
-   [ ] Performance acceptable
-   [ ] Security verified

### Business Owner

-   [ ] Workflow requirements met
-   [ ] User experience acceptable
-   [ ] Performance acceptable
-   [ ] Cost within budget
-   [ ] Timeline acceptable

### IT/Infrastructure

-   [ ] Deployment successful
-   [ ] System stable
-   [ ] Monitoring in place
-   [ ] Backup verified
-   [ ] Disaster recovery ready

---

## Go-Live Checklist

-   [ ] All sign-offs obtained
-   [ ] Users trained
-   [ ] Support team ready
-   [ ] Monitoring active
-   [ ] Escalation contacts documented
-   [ ] Rollback plan ready
-   [ ] Go-live approval granted
-   [ ] Deployment executed
-   [ ] System verified
-   [ ] Users notified

---

## Post-Go-Live Monitoring (Week 1)

-   [ ] Daily error log review
-   [ ] Daily performance check
-   [ ] User issue tracking
-   [ ] Database integrity check
-   [ ] Backup verification
-   [ ] Help desk ticket review
-   [ ] System stability confirmed

---

## Success Metrics

-   [ ] System uptime: 99.9%
-   [ ] API response time: < 500ms
-   [ ] Approval workflow completion: 100%
-   [ ] User satisfaction: > 90%
-   [ ] Zero critical issues
-   [ ] Zero data loss incidents
-   [ ] All roles working correctly
-   [ ] No security breaches

---

## Notes Section

```
Implementation Date: _______________

Team Members:
- Backend Lead: _______________
- Frontend Lead: _______________
- QA Lead: _______________
- Project Manager: _______________

Key Contacts:
- Database Admin: _______________
- System Admin: _______________
- Business Owner: _______________

Notes/Issues:
_________________________________________________________
_________________________________________________________
_________________________________________________________
```

---

**Checklist Version: 1.0**
**Last Updated: 2026-01-04**
**Status: Ready for Implementation**
