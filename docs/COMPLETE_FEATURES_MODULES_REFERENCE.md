# StaffSync HRMS - Complete Features & Modules Reference

> **Document Purpose**: Comprehensive documentation of all features, modules, capabilities, and functionalities in StaffSync HRMS.  
> **Last Updated**: February 11, 2026  
> **Version**: 2.0

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Core Modules](#core-modules)
3. [Role-Based Access & Dashboards](#role-based-access--dashboards)
4. [Authentication & Security](#authentication--security)
5. [Employee Management](#employee-management)
6. [Attendance & Time Tracking](#attendance--time-tracking)
7. [Leave Management](#leave-management)
8. [Payroll Management](#payroll-management)
9. [Recruitment & Hiring](#recruitment--hiring)
10. [Performance Management](#performance-management)
11. [Asset Management](#asset-management)
12. [Organization Management](#organization-management)
13. [Communication & Notifications](#communication--notifications)
14. [Reports & Analytics](#reports--analytics)
15. [AI Assistant (NexusBot)](#ai-assistant-nexusbot)
16. [Support & Help Desk](#support--help-desk)
17. [User Settings & Preferences](#user-settings--preferences)
18. [Admin & System Management](#admin--system-management)
19. [Public Pages & Company Portal](#public-pages--company-portal)
20. [API & Integrations](#api--integrations)

---

## System Overview

### Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5
- **Libraries**: jQuery, Chart.js, DataTables, FullCalendar
- **Architecture**: MVC-inspired modular architecture
- **Security**: Session-based authentication, CSRF protection, prepared statements, password hashing

### Deployment Model

- Cloud-ready web application
- Multi-tenant architecture with company isolation
- Role-based access control (RBAC)
- Trial and subscription-based licensing

### Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile responsive design

---

## Core Modules

### 1. **Authentication & Session Management**

- **Location**: `/auth/`
- **Features**:
    - Email/password login
    - Password reset workflow with email tokens
    - Forgot password with rate limiting
    - CSRF token protection
    - Session timeout and security
    - Trial expiry enforcement
    - Remember me functionality
    - Logout with session cleanup

### 2. **User Roles & Permissions**

- **Roles Supported**:
    - **Super Admin (role_id: 1)**: System-wide administration
    - **Company Owner (role_id: 2)**: Full company control
    - **HR Manager (role_id: 3)**: HR operations across company
    - **Employee (role_id: 4)**: Standard employee access
    - **Auditor (role_id: 5)**: Read-only access (future)
    - **Manager (role_id: 6)**: Team/department management
    - **Candidate (role_id: 7)**: Job application portal (future)

- **Permission System**:
    - Role-based sidebar navigation
    - API endpoint authorization checks
    - Data isolation per company
    - Hierarchical approval workflows

---

## Role-Based Access & Dashboards

### Super Admin Dashboard

**Location**: `/admin/`

**Features**:

- System-wide statistics (companies, users, employees)
- Company management (create, activate, deactivate)
- User management across all companies
- Support ticket management
- Storage usage monitoring (disk space charts)
- Role distribution analytics
- System settings and maintenance mode
- Activity log viewer
- Database backup controls (future)

**Key Components**:

- Active companies count
- Total users count
- Total employees across system
- Open support tickets tracker
- Storage usage doughnut chart
- Role distribution pie chart
- Recent activity feed
- Company list with status

---

### Company Owner Dashboard

**Location**: `/company/`

**Features**:

- Company-wide overview and KPIs
- Quick actions panel (hire, manage departments, approve leaves)
- Recent hires trend (last 3 months bar chart)
- Department-wise headcount
- Pending leave requests summary
- Quick links to all modules
- Employee growth analytics

**Statistics Cards**:

- Total Employees
- Total Departments
- Pending Leaves
- On Leave Today

**Quick Actions**:

- Hire New Employee → recruitment
- Manage Departments → organization
- Approve Leaves → leave approval
- View Reports → analytics

**Navigation Access**:

- All HR modules
- Attendance management
- Payroll processing
- Recruitment & hiring
- Asset management
- Company settings

---

### HR Manager Dashboard

**Location**: `/hr/`

**Features**:

- HR-focused statistics and insights
- Attendance check-in widget
- Personal attendance calendar (monthly)
- Personal to-do list
- Pending leave requests (company-wide)
- Recent hires list (last 30 days)
- Quick access to HR tasks

**Statistics Cards**:

- Total Employees (company)
- Pending Leaves (company)
- New Applications (recruitment)
- New Hires This Month

**Key Widgets**:

- **Attendance Check-In**: Clock in/out with time tracking
- **Attendance Calendar**: Monthly view with status colors
- **To-Do List**: Personal task management
- **Pending Leaves**: Quick approval access
- **Recent Hires**: New joiners snapshot

**Module Access**:

- Full employee management
- Leave approval workflows
- Recruitment management
- Attendance oversight
- Payroll preparation
- Performance reviews
- Organization structure

---

### Manager Dashboard

**Location**: `/manager/`

**Features**:

- Team management overview
- Team member attendance calendar
- Pending leave requests (team only)
- Team member list with status
- Recent team activities feed
- Personal attendance tracking
- Task assignment and monitoring

**Statistics Cards**:

- Team Members count
- Pending Tasks (team)
- Completed Tasks (team)
- Team On Leave Today

**Key Sections**:

- **Personal**: Attendance check-in, calendar, to-do list
- **Team**: Pending leaves, team members, activities
- **Leave Approval**: Department-specific requests
- **Attendance**: Team attendance tracking
- **Tasks**: Assign and track team tasks

**Capabilities**:

- Approve/reject team leave requests
- View team attendance records
- Assign tasks to team members
- Monitor team performance
- Access interview scheduling

---

### Employee Dashboard

**Location**: `/employee/`

**Features**:

- Personal statistics and insights
- Attendance check-in/check-out
- Monthly attendance calendar
- To-do list management
- Leave balance display
- Recent payslips summary
- Quick access to personal modules

**Statistics Cards**:

- Leave Balance (days)
- Days Present (current month)
- Total Leaves Taken (year)
- Pending Tasks

**Personal Tools**:

- **Attendance Check-In**: Daily clock in/out
- **Attendance Calendar**: Personal monthly view
- **To-Do List**: Task management
- **Profile**: View and update personal info
- **My Leaves**: Apply and track leave requests
- **My Assets**: View assigned assets
- **My Tasks**: View assigned tasks and goals
- **Payslips**: Download payslips
- **Notifications**: View system alerts

---

## Authentication & Security

### Login System

**File**: `/auth/login.php`

**Features**:

- Email and password authentication
- Password verification with bcrypt hashing
- Trial expiry check before login
- User status validation (active/inactive)
- Employee record validation for non-admin roles
- Role-based redirection after login
- Error messages for inactive accounts
- Trial expiry notification with subscription CTA

**Security Measures**:

- Prepared SQL statements
- Session hijacking prevention
- CSRF token validation
- Rate limiting (via functions)
- Secure password storage (bcrypt)

---

### Password Reset Workflow

**Files**: `/auth/forgot_password.php`, `/auth/reset_password.php`

**Features**:

- Email-based password reset
- Token generation and validation (SHA-256 hashed)
- Time-limited reset links (1 hour expiry)
- CSRF protection
- Honeypot anti-bot field
- Rate limiting (5 attempts per 15 minutes)
- Email confirmation
- Secure token transmission

**Process Flow**:

1. User enters email on forgot password page
2. System validates email and user status
3. Token generated and hashed, stored in database
4. Reset link sent via email (MailService queue)
5. User clicks link with token in URL
6. Token validated against database
7. User sets new password
8. Token cleared from database
9. Activity logged

---

### Registration & Trial

**File**: `/pages/register.php`

**Features**:

- Company registration (demo signup)
- 14-day free trial activation
- Company and user creation
- Email validation
- Phone number validation
- Terms and conditions acceptance
- Password strength enforcement (min 8 chars)
- AJAX form submission

**Data Created**:

- Company record with trial status
- Owner user account (role_id: 2)
- Default departments (future)
- Welcome email sent

---

## Employee Management

### Employee Module

**Location**: `/company/employees.php`, `/hr/employees.php`

**Core Features**:

- **CRUD Operations**: Add, edit, view, deactivate employees
- **Bulk Import**: CSV/Excel employee data import
- **Profile Management**: Complete employee profiles
- **Document Storage**: Upload and manage employee documents
- **Department Assignment**: Link employees to departments
- **User Account Linking**: Create login credentials
- **Role Assignment**: Assign system roles
- **Status Management**: Active, inactive, terminated

**Employee Profile Fields**:

- Personal Information:
    - First Name, Last Name
    - Date of Birth
    - Gender
    - Contact Number, Email
    - Address (current and permanent)
    - Emergency Contact
- Employment Details:
    - Employee ID (auto-generated)
    - Department
    - Designation/Position
    - Date of Joining
    - Employment Type (full-time, part-time, contract, intern)
    - Work Location
    - Reporting Manager
- Compensation:
    - Basic Salary
    - Allowances
    - Deductions
    - Bank Account Details
- Documents:
    - Resume/CV
    - ID Proof
    - Address Proof
    - Education Certificates
    - Offer Letter
    - Signed Contract

**Employee List View**:

- Searchable DataTable
- Sortable columns
- Department filter
- Status filter
- Pagination
- Bulk actions (future)
- Export to Excel/PDF

**Actions**:

- View Profile
- Edit Details
- Deactivate/Activate
- View Attendance
- View Leave History
- Assign Tasks
- Generate Payslip

---

### Organization Structure

**Location**: `/company/organization.php`

**Features**:

- **Department Management**:
    - Create, edit, delete departments
    - Assign department heads
    - Set department budgets
    - Track department headcount
- **Hierarchy View**:
    - Visual org chart
    - Reporting relationships
    - Tree structure navigation
- **Team Management**:
    - Create teams within departments
    - Assign team leads
    - Track team members

**Department Fields**:

- Name
- Description
- Head of Department (employee)
- Parent Department (for sub-departments)
- Budget Allocation
- Cost Center Code

---

### Employee Self-Service

**Location**: `/employee/profile.php`

**Features**:

- View personal profile
- Update contact information
- Upload profile photo
- Update emergency contact
- View employment details (read-only)
- Download documents
- View reporting manager
- Update bank details (with approval workflow)

---

## Attendance & Time Tracking

### Attendance System

**Location**: `/company/attendance.php`, `/hr/attendance.php`, `/manager/attendance.php`, `/employee/attendance.php`

**Core Features**:

- **Check-In/Check-Out**: Real-time attendance marking
- **Geofencing**: Location-based attendance validation
- **Calendar View**: Monthly attendance visualization
- **Bulk Attendance**: Mark attendance for multiple employees
- **Manual Adjustments**: HR/Manager can edit attendance
- **Late/Early Tracking**: Automatic calculation
- **Overtime Tracking**: Auto-calculated based on check-out time
- **Shift Management**: Support for multiple shifts
- **Holiday Management**: Public holidays excluded
- **Weekend Configuration**: Configurable week-off days

**Attendance Calendar Widget**
**File**: `/assets/js/attendance-calendar.js`

**Features**:

- Monthly calendar view with color-coded days
- Status indicators:
    - 🟢 **Green**: Present
    - 🔴 **Red**: Absent
    - 🟡 **Yellow**: On Leave
    - 🔵 **Blue**: Holiday
    - ⚪ **Gray**: Weekend
- Month navigation (back to joining date)
- Hover tooltips with time details
- Responsive grid layout
- Supports viewing other employees (HR/Manager)
- Fetch data via AJAX (API endpoint)

**Attendance Check-In Widget**
**File**: `/assets/js/attendance-checkin.js`

**Features**:

- Real-time status display (Not Checked In / Checked In / Checked Out)
- Check-in button with geolocation capture
- Check-out button with work hours calculation
- Timer display (hours worked)
- Break tracking (future)
- Today's summary (check-in time, check-out time, total hours)
- Auto-refresh every minute
- Visual status indicators

**Attendance Reports**:

- Daily Attendance Report
- Monthly Attendance Summary
- Late Coming Report
- Absent Employee Report
- Overtime Report
- Department-wise Attendance
- Employee-wise Attendance History

**Attendance Statistics Dashboard**:

- Total Present Today
- Total Absent Today
- On Leave Today
- Late Arrivals Today
- Holidays This Month
- Average Work Hours

**Bulk Attendance Modal**:

- Mark multiple employees present/absent
- Select by department
- Select by employee list
- Set custom check-in/out times
- Reason for manual entry

**Attendance Details View**:

- Check-in time with location
- Check-out time with location
- Total work hours
- Late duration (if applicable)
- Early leaving (if applicable)
- Overtime hours
- Remarks/Notes
- Edit/Delete options (HR only)

---

### Geofencing & Location Tracking

**Features**:

- GPS-based check-in validation
- Define office geofence radius
- Capture latitude/longitude on check-in
- Validate employee is within office premises
- Block remote check-ins (optional)
- View check-in location on map (future)

---

## Leave Management

### Leave System

**Location**: `/company/leaves.php`, `/hr/leaves.php`, `/manager/leave_approval.php`, `/employee/my_leaves.php`

**Leave Types**:

- Casual Leave (CL)
- Sick Leave (SL)
- Earned Leave (EL)
- Maternity Leave (ML)
- Paternity Leave (PL)
- Compensatory Off (CO)
- Loss of Pay (LOP)
- Custom Leave Types (configurable)

**Core Features**:

- **Apply Leave**: Employee submit leave requests
- **Approval Workflow**: Multi-level approval (Manager → HR → Owner)
- **Leave Balance**: Track available and consumed leaves
- **Leave Calendar**: Visual leave overview
- **Leave Encashment**: Convert unused leaves to cash
- **Half-Day Leave**: Support for half-day requests
- **Emergency Leave**: Fast-track approval (future)
- **Leave Cancellation**: Cancel pending or approved leaves
- **Sandwich Leave**: Auto-include weekends between leave days

**Leave Approval Workflow**:
**Reference**: `docs/LEAVE_APPROVAL_WORKFLOW.md`

**Approval Hierarchy**:

1. **Employee (role_id: 4)**: Applies leave
2. **Manager (role_id: 6)**: First-level approval (if department has manager)
3. **HR Manager (role_id: 3)**: Second-level approval (or first if no manager)
4. **Company Owner (role_id: 2)**: Final approval (or can approve directly)
5. **Super Admin (role_id: 1)**: Can approve any leave (override)

**Leave Statuses**:

- `pending`: Awaiting approval
- `approved`: Approved by authorized person
- `rejected`: Rejected by approver
- `cancelled`: Cancelled by employee or admin

**Authorization Rules**:

- Employees can only apply and view their own leaves
- Managers can approve leaves for their department members only
- HR can approve leaves for all employees in the company
- Owners can approve any leave in their company
- Super Admin has no restrictions

**Leave Balance System**:

- Annual leave quota per employee
- Carry forward rules (configurable)
- Accrual rules (monthly/quarterly)
- Leave balance display on dashboard
- Negative balance alerts
- LOP (Loss of Pay) auto-calculation for negative balance

**Leave Application Form**:

- Leave Type (dropdown)
- Start Date (date picker)
- End Date (date picker)
- Half Day option (checkbox)
- Reason (textarea, required)
- File Attachment (optional, for medical certificates)
- Number of Days (auto-calculated)
- Available Balance display
- Insufficient balance warning

**Leave List View**:

- Searchable DataTable
- Filter by status, type, date range
- Sort by applied date
- Color-coded status badges
- Quick approve/reject buttons (for approvers)
- View details modal
- Pagination

**Leave Dashboard Widgets**:

- Pending Approvals (for managers/HR)
- Leave Balance (for employees)
- Team on Leave Today (for managers)
- Leave History (recent)
- Leave Statistics (monthly breakdown)

**Leave Calendar View**:

- FullCalendar integration
- Color-coded leave types
- Employee name on events
- Hover tooltips
- Month/week/day views
- Export to iCal (future)

**Leave Reports**:

- Leave Summary Report (employee-wise)
- Leave Balance Report
- Department-wise Leave Report
- Leave Type Analysis
- Monthly Leave Trends
- Absenteeism Report

---

### Leave Policy Management

**Location**: `/company/leave_policy.php`

**Features**:

- Define leave types and quotas
- Set carry forward rules
- Configure accrual rates
- Set maximum carry forward limit
- Define encashment rules
- Set notice period for leave application
- Configure sandwich leave policy
- Define negative balance rules

---

## Payroll Management

### Payroll System

**Location**: `/company/payslips.php`, `/hr/payroll.php`, `/employee/my_payslips.php`

**Core Features**:

- **Salary Structure**: Define CTC breakup
- **Payslip Generation**: Automated monthly payslip creation
- **Salary Components**:
    - Earnings: Basic, HRA, DA, Special Allowance, Bonus
    - Deductions: PF, ESI, Professional Tax, TDS, Loans
    - Net Salary Calculation
- **Attendance Integration**: Absent/LOP deductions
- **Tax Calculation**: Automated TDS computation
- **Bank Transfer File**: Generate salary transfer file
- **Payslip PDF**: Download individual payslips
- **Bulk Payslip**: Generate for all employees
- **Payroll History**: Month-wise records
- **Salary Revision**: Track increment history

**Payslip Components**:

- Employee Details (name, ID, department, designation)
- Salary Month and Year
- Working Days and Present Days
- Earnings Breakup (component-wise)
- Deductions Breakup (component-wise)
- Gross Salary
- Total Deductions
- Net Salary
- Bank Details
- Company Seal/Signature

**Payroll Processing Flow**:

1. HR selects month and year
2. System fetches attendance data
3. Calculates LOP days (absent + unpaid leaves)
4. Computes pro-rata salary
5. Applies statutory deductions (PF, ESI, PT)
6. Calculates TDS based on salary and regime
7. Generates payslip records
8. Sends payslip email to employees
9. Marks payroll as processed for the month

**Payroll Dashboard**:

- Total Payroll Cost (current month)
- Number of Employees
- Processed Payslips
- Pending Approvals
- Payroll Compliance Status

**Payroll Reports**:

- Monthly Payroll Summary
- Department-wise Payroll
- Salary Register
- PF/ESI Report
- TDS Report
- Bank Transfer Statement
- Cost Center Analysis

**Salary Revision**:

- Record increment/promotion
- Effective date
- Old vs New CTC
- Reason for revision
- Approval workflow

---

## Recruitment & Hiring

### Recruitment Module (ATS - Applicant Tracking System)

**Location**: `/company/recruitment.php`, `/hr/recruitment.php`

**Core Features**:

- **Job Posting Management**: Create and publish job openings
- **Candidate Database**: Store and search candidates
- **Application Tracking**: Track applications through hiring stages
- **Interview Scheduling**: Schedule and manage interviews with Google Meet integration
- **Interview Calendar**: FullCalendar view of scheduled interviews
- **Offer Management**: Generate and send offer letters (planned)
- **Onboarding Tasks**: Pre-joining task checklists (planned)
- **Recruitment Analytics**: Funnel reports and hiring metrics

**Job Posting Features**:

- Job Title (validated, 3-100 chars, alphanumeric)
- Department Assignment
- Job Description (rich text, max 2000 chars)
- Employment Type (full-time, part-time, internship, contract)
- Location (max 100 chars)
- Number of Openings (1-999)
- Status (open/closed)
- Posted Date (auto)
- Job Link Sharing (copy public application link)

**Job Posting Validation**:

- Cannot edit/delete job with applicants
- Can only close job opening if applicants exist
- Title must be 3-100 characters
- Invalid characters blocked
- Department must exist in company
- Openings must be 1-999

**Public Job Listings**:
**Location**: `/pages/careers.php`

**Features**:

- Public job board with company branding
- Search and filter jobs
- Apply directly via modal
- Mobile-responsive application form
- Resume upload (PDF/DOC/DOCX, max 5 MB)
- Application status check by email
- Email confirmation on application

**Application Form** (Public):

- First Name, Last Name (letters only, no numbers, 2-100 chars)
- Email (validated format)
- Phone (optional, digits/+/-/space, 7-20 chars)
- Date of Birth (optional)
- Gender (optional dropdown)
- Resume Upload (required, PDF/DOC/DOCX, max 5MB)
- Job ID (hidden)

**Application Validation**:

- Name validation: letters, spaces, hyphen, apostrophe only
- Phone validation: 7-20 characters, digits/+/-/spaces
- Resume required: PDF/DOC/DOCX only
- File size limit: 5 MB
- Duplicate check: Same email + job_id blocked
- Error messages for validation failures

**Application Status Check**:
**Location**: `/candidate/check_status.php`

**Features**:

- Enter email to view applications
- Shows all applications by email
- Application status badges (color-coded)
- Interview details if scheduled
- Company and job title display
- Applied date

**Application Stages**:

- `pending`: Initial application received
- `screening`: Under review by recruiter
- `shortlisted`: Selected for interview
- `interviewed`: Interview conducted
- `offered`: Offer extended
- `hired`: Candidate accepted and joined
- `rejected`: Application rejected

**Candidate Management**:

- Candidate profile with resume link
- Application history
- Status change tracking
- Add notes/comments
- Schedule interview
- Send rejection/offer email
- Mark as hired
- Delete candidate (only if no applications)

**Interview Scheduling**:
**Location**: Interview modal in recruitment page

**Features**:

- Select Interviewer (dropdown of employees)
- Interview Date & Time (datetime picker, future dates only)
- Interview Mode:
    - **Online**: Requires Google Meet link
    - **In-Person**: Office/onsite
- **Google Meet Integration**:
    - Meeting Link field (shown for online mode only)
    - Validation: Must be `https://meet.google.com/...`
    - Link stored in database
    - Link sent in email notification
    - Link shown in calendar event details
- Interview Status: `scheduled`, `completed`, `cancelled`
- Interview Result: `pending`, `selected`, `rejected`
- Interview Feedback (text)

**Interview Validation**:

- Interviewer required
- Date required and must be in future
- Mode must be online/offline
- Google Meet link required for online interviews
- Meet link must match pattern: `https://meet.google.com/`

**Interview Calendar**:
**Location**: `/company/interview_calendar.php`

**Features**:

- FullCalendar integration
- Month/Week/Day views
- Color-coded by mode:
    - 🔵 Blue: Online (virtual)
    - 🟣 Purple: In-Person (offline)
- Click event to view details:
    - Candidate name and email
    - Job title
    - Interview date & time
    - Mode badge
    - Interviewer name
    - **Google Meet join link** (for online)
- Fetch interviews via API (`get_scheduled_interviews`)
- Auto-refresh on new interview scheduled

**Interview Email Notification**:
**Template**: `/includes/mail/templates/interview_scheduled.php`

**Content**:

- Candidate name
- Job title and company name
- Interview date and time (formatted)
- Interview mode (Online/In-Person)
- **Google Meet link** (if online)
- Interview preparation tips
- Company branding

**Interview Actions**:

- Mark as Completed
- Cancel Interview (marks status as cancelled, not deleted)
- Reschedule (future)
- Add Feedback
- Select/Reject candidate

**Recruitment Dashboard**:

- Total Jobs Posted
- Total Applications Received
- Hired This Month
- Open Positions (sum of openings)
- Application Status Breakdown (pie chart):
    - Pending
    - Shortlisted
    - Interviewed
    - Offered
    - Hired
    - Rejected
- Job Status Breakdown (open vs closed)

**Recruitment Reports**:

- Time-to-Fill Report
- Source of Hire Analysis
- Offer Acceptance Rate
- Interview-to-Hire Ratio
- Candidate Funnel Report
- Recruiter Performance

**Shortlisted Candidates View**:

- List of shortlisted candidates
- Quick schedule interview button
- Application date
- Job title

**Recent Applications**:

- Latest applications (last 10)
- Status badges
- Quick actions (view, schedule, reject)

**Document Management**:

- Resume storage in `/uploads/resumes/`
- Unique filename generation (uniqid + original name)
- Mime type validation
- File size check
- Download resume link
- Delete document (when candidate deleted)

**Anti-Spam & Security**:

- Duplicate application check (email + job_id)
- File type validation (PDF/DOC/DOCX only)
- File size limit enforcement (5 MB)
- SQL injection prevention (prepared statements)
- Candidate data encryption (future)

**Planned Features**:

- Offer letter generation
- E-signature integration
- Pre-onboarding tasks
- Background verification tracking
- Assessment test links
- Video interview scheduling
- Candidate portal login

---

### Candidate Portal (Future)

**Location**: `/candidate/` (partial implementation)

**Planned Features**:

- Magic link login (no password)
- View application status
- Upload additional documents
- Accept/Decline offer
- Complete pre-joining forms
- Track onboarding tasks
- View interview feedback

---

## Performance Management

### Performance Module

**Location**: `/hr/performance.php`, `/employee/my_performance.php`

**Features** (Partial Implementation):

- Performance appraisal cycles
- Goal setting (OKRs, KPIs)
- 360-degree feedback
- Manager reviews
- Self-assessment
- Peer reviews
- Performance ratings
- Performance improvement plans (PIP)
- Promotion recommendations

**Planned Enhancements**:

- Automated review reminders
- Performance analytics dashboard
- Competency matrix
- Skill gap analysis
- Training recommendations based on performance

---

### Tasks & Goals

**Location**: `/employee/goals.php`, `/manager/tasks.php`

**Features**:

- Create and assign tasks to employees
- Set due dates and priorities
- Task status tracking (pending, in-progress, completed)
- Task categories/tags
- Subtasks and checklists
- Task comments and attachments
- Task reminders and notifications
- Task completion reports

**Task Dashboard**:

- My Tasks (assigned to me)
- Tasks I Assigned (created by me)
- Task statistics (pending, in-progress, completed, overdue)
- Task calendar view
- Gantt chart (future)

**Task Filters**:

- By status
- By priority
- By assignee
- By due date
- By department

---

## Asset Management

### Asset Tracking System

**Location**: `/company/assets.php`, `/employee/my_assets.php`

**Core Features**:

- **Asset Categories**: Laptop, Desktop, Mobile, Monitor, Keyboard, Mouse, Headset, Software License, etc.
- **Asset CRUD**: Add, edit, view, delete assets
- **Asset Assignment**: Assign assets to employees
- **Asset Return**: Track returned assets
- **Asset Status**: Available, Assigned, Under Maintenance, Retired
- **Asset Condition**: New, Good, Fair, Damaged
- **Asset History**: Track assignment/return history per asset
- **Asset Reports**: Assets by category, employee, status

**Asset Fields**:

- Asset Name
- Asset Tag/Serial Number (unique identifier)
- Category (dropdown)
- Brand/Manufacturer
- Model
- Purchase Date
- Purchase Cost
- Warranty Expiry
- Supplier/Vendor
- Location (office/warehouse)
- Status (Available, Assigned, Maintenance, Retired)
- Condition (New, Good, Fair, Damaged)
- Notes/Remarks

**Asset Assignment**:

- Select Employee (dropdown)
- Assignment Date (auto or custom)
- Expected Return Date (optional)
- Condition at Assignment
- Remarks
- Assignment Email Notification

**Asset Return**:

- Return Date (auto or custom)
- Condition at Return
- Return Remarks
- Damage Assessment
- Refurbishment Required (checkbox)
- Asset status changes to Available after return

**Asset Categories Management**:

- Create custom asset categories
- Edit category names
- Delete unused categories
- Category-wise asset count

**Asset Dashboard**:

- Total Assets
- Available Assets
- Assigned Assets
- Under Maintenance
- Assets by Category (pie chart)
- Recent Assignments
- Assets Near Warranty Expiry

**My Assets (Employee View)**:
**Location**: `/employee/my_assets.php`

**Features**:

- View assigned assets
- Asset details (name, tag, category, assignment date)
- Report damage/issue
- Request replacement (future)
- Asset return request (future)

**Asset Reports**:

- Asset Inventory Report
- Asset Assignment Report
- Asset Maintenance Report
- Asset Depreciation Report (future)
- Asset Utilization Report

**Bug Fixes Applied**:

- Fixed bug where editing an assigned asset reverted status to "Available"
- Status preservation logic: Backend enforces status integrity; if asset has active assignment, status forced to "Assigned"
- Frontend hides status dropdown when editing assigned asset but still posts status value
- Assigned option added to status dropdown for clarity

---

## Organization Management

### Organization Structure

**Location**: `/company/organization.php`

**Features**:

- **Department Management**:
    - Create departments
    - Edit department details
    - Assign department head
    - Set parent department (for hierarchies)
    - Delete empty departments
- **Visual Org Chart**:
    - Tree view of departments
    - Employee count per department
    - Click to view department details
- **Team Management** (Future):
    - Create teams within departments
    - Assign team leads
    - Team member management

**Department Fields**:

- Name
- Description
- Head of Department (employee dropdown)
- Parent Department (for sub-departments)
- Budget (optional)
- Cost Center Code (optional)

---

### Company Settings

**Location**: `/company/company_settings.php`

**Features**:

- Company profile (name, logo, address, contact)
- Working hours configuration
- Week-off days (Saturday/Sunday)
- Geofence radius for attendance
- Leave policies
- Payroll settings (PF%, ESI%, PT)
- Tax regime (old/new)
- Notification preferences
- Email templates customization
- System preferences

---

## Communication & Notifications

### Notification System

**Features**:

- Real-time in-app notifications
- Email notifications
- SMS notifications (future)
- Push notifications (future)
- Notification preferences per user
- Mark as read/unread
- Notification history

**Notification Types**:

- Leave approval/rejection
- Task assignment
- Interview scheduled
- Payslip generated
- Asset assigned
- Document uploaded
- System announcements
- Birthday/anniversary reminders

**Notification Bell**:

- Badge count (unread)
- Dropdown list (recent 10)
- Mark all as read
- View all notifications link
- Real-time updates via AJAX polling

**Email Templates**:

- Welcome email
- Password reset email
- Leave approval email
- Leave rejection email
- Interview scheduled email (with Google Meet link)
- Payslip email
- Task assignment email
- System maintenance email

**Email Queue System**:
**Location**: `/includes/mail/MailService.php`, `/cron/process_queue.php`

**Features**:

- Asynchronous email delivery
- Queue-based processing
- Retry mechanism for failures
- Email logging
- Delivery status tracking
- Cron job for queue processing

---

## Reports & Analytics

### Dashboard Analytics

**Features**:

- **Company Dashboard**:
    - Employee growth trend (bar chart)
    - Department headcount (pie chart)
    - Recent hires timeline
    - Pending leaves summary
- **HR Dashboard**:
    - Employee statistics
    - Leave balance overview
    - Attendance trends
    - Recruitment funnel
- **Manager Dashboard**:
    - Team statistics (members, tasks, leaves)
    - Team attendance calendar
    - Pending leave approvals
    - Task completion rate
- **Employee Dashboard**:
    - Personal stats (leave balance, attendance, tasks)
    - Attendance calendar
    - Recent payslips
- **Admin Dashboard**:
    - System-wide metrics
    - Company distribution (active/trial/expired)
    - User role distribution (pie chart)
    - Storage usage (doughnut chart)
    - Support ticket stats

### Report Modules

**Location**: `/company/reports.php`, `/admin/reports.php`

**Available Reports** (Planned):

- **Attendance Reports**:
    - Daily Attendance Report
    - Monthly Attendance Summary
    - Late Coming Report
    - Overtime Report
    - Absenteeism Analysis
- **Leave Reports**:
    - Leave Balance Report
    - Leave Summary (employee-wise)
    - Department Leave Report
    - Leave Type Analysis
- **Payroll Reports**:
    - Monthly Payroll Summary
    - Department-wise Payroll
    - PF/ESI Report
    - TDS Report
    - Salary Register
- **Recruitment Reports**:
    - Time-to-Fill
    - Source of Hire
    - Interview-to-Hire Ratio
    - Candidate Funnel
- **Performance Reports**:
    - Performance Summary
    - Goal Achievement
    - Training Completion
- **Asset Reports**:
    - Asset Inventory
    - Asset Assignment
    - Maintenance Log

**Report Features**:

- Date range filters
- Department/employee filters
- Export to Excel/CSV
- Export to PDF
- Schedule automated reports (email delivery)
- Visual charts and graphs
- Drill-down capabilities

---

## AI Assistant (NexusBot)

### NexusBot - AI HR Assistant

**Location**: `/nexusbot/`, `/assets/js/nexus_bot.js`

**Features**:

- Chat widget (bottom-right corner)
- Natural language query processing
- Context-aware responses
- Pre-defined HR knowledge base
- Quick reply suggestions
- Chat history
- Guest and logged-in user support

**Capabilities**:

- Answer HR policy questions (leave, attendance, payroll)
- Provide feature explanations
- Guide users through processes
- Search knowledge base
- Escalate to human support
- Collect feedback

**NexusBot Knowledge Base**:

- Leave policy queries
- Attendance marking help
- Payroll questions
- Recruitment status
- Asset management help
- System navigation help
- Password reset assistance

**Chat Interface**:

- Minimized/maximized widget
- Typing indicators
- Message timestamps
- User/bot message distinction
- Emoji support
- File attachment (future)
- Voice input (future)

**Integration Points**:

- Search knowledge base
- Fetch leave balance
- Check attendance status
- View recent payslips
- Raise support ticket
- Get HR contact

**Future Enhancements**:

- Natural Language Processing (NLP) integration
- Machine Learning for better responses
- Multi-language support
- Voice assistant
- Integration with HR workflows (apply leave via bot)

---

## Support & Help Desk

### Support System

**Location**: `/pages/support.php`, `/admin/support.php`

**Features** (Basic Implementation):

- Raise support ticket
- Ticket categories (Technical, HR, Payroll, Other)
- Ticket priority (Low, Medium, High, Critical)
- Ticket status (Open, In Progress, Resolved, Closed)
- Ticket assignment to support staff
- Ticket history and comments
- File attachments
- Email notifications on ticket updates

**Ticket Fields**:

- Subject
- Category
- Priority
- Description
- Attachments
- Status
- Assigned To
- Created By
- Created At
- Updated At

**Admin Support Dashboard**:

- Open Tickets Count
- Tickets by Priority
- Tickets by Category
- Recent Tickets List
- Average Resolution Time

**User Support View**:

- My Tickets
- Create New Ticket
- Ticket Status Tracking
- Add Comments
- Close Ticket

---

## User Settings & Preferences

### User Account Settings

**Location**: `/user/account.php`

**Features**:

- Change Password
- Update Email
- Update Phone Number
- Profile Photo Upload
- Notification Preferences
- Email Preferences
- Theme Preferences (Light/Dark)
- Language Preferences (future)
- Time Zone Settings (future)

**Security Settings**:

- Two-Factor Authentication (2FA) (future)
- Session Management (view active sessions)
- Login History
- Trusted Devices (future)

---

### System Preferences

**Location**: `/company/company_settings.php`

**Features**:

- Date Format (DD/MM/YYYY, MM/DD/YYYY, YYYY-MM-DD)
- Time Format (12-hour, 24-hour)
- Currency Symbol
- Fiscal Year Start Month
- Week Start Day (Sunday/Monday)
- Default Language
- Time Zone

---

## Admin & System Management

### Super Admin Panel

**Location**: `/admin/`

**Features**:

- **Company Management**:
    - View all companies
    - Create new companies
    - Activate/Deactivate companies
    - Reset company trials
    - View company details
    - Delete companies (with confirmation)
- **User Management**:
    - View all users across companies
    - Create users
    - Edit user roles
    - Reset passwords
    - Deactivate users
    - View user activity logs
- **System Settings**:
    - Maintenance Mode toggle
    - System announcements
    - Email settings (SMTP configuration)
    - Backup settings
    - Security settings
    - License management
- **Support Management**:
    - View all support tickets
    - Assign tickets
    - Resolve tickets
    - Ticket analytics
- **Storage Management**:
    - View disk usage
    - Storage by company
    - Clean up old files
    - Backup management
- **Activity Logs**:
    - System-wide activity log
    - User login history
    - API request logs
    - Error logs

**System Settings**:

- Maintenance mode (blocks non-admin access)
- System name and branding
- Email configuration (SMTP host, port, username, password)
- Default trial period (14 days)
- Maximum file upload size
- Session timeout duration
- Password policy (min length, complexity)

---

## Public Pages & Company Portal

### Public Website

**Location**: `/pages/`, `/index.php`

**Pages**:

- **Homepage** (`/index.php`):
    - Hero section with CTA
    - Features showcase
    - Testimonials
    - Pricing plans
    - FAQ section
    - Newsletter signup
- **Features Page** (`/pages/features.php`):
    - Core HR Management
    - Time & Attendance
    - Automated Payroll
    - Recruitment ATS
    - Performance Management
    - Analytics & Reports
- **Benefits Page** (`/pages/benefits.php`):
    - Lightning Fast Setup
    - Cost Efficient
    - Bank-Grade Security
    - Mobile First
    - Employee Wellbeing
    - 24/7 Support
- **Careers Page** (`/pages/careers.php`):
    - Job listings (public)
    - Apply for jobs via modal
    - Application status check
    - Company information
- **Pricing Page** (`/subscription/purchase.php`):
    - Pricing tiers (Starter, Professional, Enterprise)
    - Feature comparison
    - Free trial CTA
    - Payment gateway integration (future)
- **Register Page** (`/pages/register.php`):
    - Company signup form
    - Free 14-day trial
    - AJAX form submission
    - Email verification (future)

**Public Features**:

- Mobile responsive design
- AOS animations (Animate On Scroll)
- Dark mode support
- SEO-friendly URLs (future)
- Contact form
- Live chat (NexusBot)

---

### Authentication Pages

**Location**: `/auth/`

**Pages**:

- **Login** (`/auth/login.php`):
    - Email/password login
    - Remember me option
    - Forgot password link
    - Register link
    - Trial expiry handling
- **Forgot Password** (`/auth/forgot_password.php`):
    - Email input
    - Rate limiting
    - CSRF protection
    - Honeypot field
- **Reset Password** (`/auth/reset_password.php`):
    - Token validation
    - New password form
    - Password strength indicator
    - Success message with login link
- **Logout** (`/auth/logout.php`):
    - Session cleanup
    - Redirect to login

---

## API & Integrations

### API Endpoints

**Location**: `/api/`

**API Files**:

- `api_attendance.php`: Attendance operations
- `api_companies.php`: Company management (admin)
- `api_company_settings.php`: Company settings
- `api_company_users.php`: Company user management
- `api_dashboard.php`: Dashboard statistics
- `api_emp.php`: Employee operations
- `api_employee_attendance.php`: Employee attendance
- `api_employees.php`: Employee CRUD
- `api_leaves.php`: Leave management
- `api_leaves_refactored.php`: Improved leave API
- `api_manager.php`: Manager operations
- `api_notifications.php`: Notification system
- `api_payroll.php`: Payroll operations
- `api_performance.php`: Performance management
- `api_policies.php`: Policy management
- `api_preferences.php`: User preferences
- `api_profile.php`: User profile
- `api_public_recruitment.php`: Public job applications
- `api_recruitment.php`: Recruitment management
- `api_reports_superadmin.php`: Admin reports
- `api_settings.php`: System settings
- `api_support.php`: Support tickets
- `api_tasks.php`: Task management
- `api_users.php`: User management
- `api_assets.php`: Asset management

**API Features**:

- JSON response format
- HTTP status codes
- Error handling
- Authentication checks
- CSRF protection
- Rate limiting (selective)
- Input validation
- SQL injection prevention
- Logging

**API Response Format**:

```json
{
  "success": true/false,
  "message": "Success or error message",
  "data": {...} // Response data
}
```

---

### Third-Party Integrations

**Current Integrations**:

- **Google Meet**: Interview scheduling with automatic link generation
- **Email (SMTP)**: Transactional emails via MailService
- **Chart.js**: Analytics and charts
- **DataTables**: Table rendering and pagination
- **FullCalendar**: Event and calendar views

**Planned Integrations**:

- **Google Calendar**: Sync leave and interviews
- **Slack**: Notifications and bot
- **Zoom**: Alternative for video interviews
- **WhatsApp Business API**: SMS notifications
- **Payment Gateway** (Razorpay/Stripe): Subscription payments
- **Aadhaar/PAN Verification**: Employee KYC
- **Background Verification APIs**: Candidate screening
- **E-Signature** (DocuSign): Offer letter signing
- **Cloud Storage** (AWS S3/Google Drive): Document backup
- **SSO** (Google/Microsoft): Single Sign-On

---

## Additional Features & Utilities

### To-Do List

**Location**: Dashboard widgets

**Features**:

- Add tasks
- Mark as complete
- Delete tasks
- Persist in localStorage or database
- Personal task management
- Quick access from dashboards

---

### Document Management

**Features**:

- Upload documents (PDF, DOC, DOCX, images)
- Organize by type (resume, ID proof, certificates)
- Link documents to employees, candidates, assets
- Secure file storage
- Download documents
- Delete documents (admin only)
- Document expiry tracking (for licenses, visas)

**Document Types**:

- Resume/CV
- ID Proof (Aadhaar, PAN, Passport)
- Address Proof
- Education Certificates
- Experience Letters
- Offer Letter
- Signed Contract
- Visa/Work Permit
- Driving License
- Medical Certificates
- Asset Invoices

---

### Theme Toggle

**Location**: Header widget

**Features**:

- Light mode (default)
- Dark mode
- System preference detection
- Persistent theme storage (localStorage)
- Toggle button in header
- CSS variable-based theming

---

### Breadcrumbs & Navigation

**Features**:

- Breadcrumb trail on all pages
- Sidebar navigation with role-based menus
- Collapsible sidebar (mobile)
- Active page highlighting
- Submenu support
- Icon-based navigation

---

### Skeleton Loading

**File**: `/assets/css/skeleton.css`

**Features**:

- Skeleton screens for loading states
- Pulse animation
- Multiple skeleton types (card, table, stat)
- Reduces perceived load time
- Improves UX

---

### Error Handling & Logging

**Features**:

- Custom error pages (404, 500, 401)
- Error logging to file (`logs/error.log`)
- Activity logging (`logs/activity.log`)
- Debug mode (`.env` configuration)
- User-friendly error messages
- Stack trace (dev mode only)

---

## Security Features

### Authentication & Authorization

- Session-based authentication
- Password hashing (bcrypt)
- CSRF token validation
- Role-based access control (RBAC)
- Permission checks on every API call
- Session timeout (configurable)
- Logout on trial expiry
- Secure cookie flags (HttpOnly, Secure)

### Input Validation

- Server-side validation for all inputs
- Client-side validation for UX
- SQL injection prevention (prepared statements)
- XSS prevention (htmlspecialchars)
- File upload validation (type, size)
- Email format validation
- Phone number validation
- Date range validation

### Data Protection

- Database credentials in `.env` file (not in repo)
- Encrypted passwords (bcrypt)
- Secure file upload directory (outside public root)
- Unique file naming to prevent overwrites
- User data isolation per company
- SQL prepared statements for all queries
- Parameterized queries

### Rate Limiting

- Password reset attempts (5 per 15 minutes)
- Login attempts (future)
- API request throttling (future)
- CAPTCHA on public forms (future)

### Audit & Compliance

- Activity logging (user actions)
- Login history
- Data access logs
- GDPR-ready (data export, deletion)
- Data retention policies
- Backup and disaster recovery

---

## Mobile Responsiveness

### Responsive Design

- Bootstrap 5 responsive grid
- Mobile-first approach
- Touch-friendly UI elements
- Collapsible sidebar on mobile
- Responsive tables (DataTables)
- Mobile-optimized forms
- Responsive charts (Chart.js)
- Mobile navigation menu (offcanvas)

### Mobile Features

- Attendance check-in via mobile browser
- Geolocation for attendance
- Apply leave on mobile
- View payslips on mobile
- Mobile-friendly dashboards
- Push notifications (future)
- Progressive Web App (PWA) (future)

---

## Future Enhancements & Roadmap

### Short-Term (Next 3 Months)

- Offer letter generation and e-signature
- Pre-onboarding task checklists
- Background verification tracking
- Assessment test integration
- Video interview (Zoom/Google Meet scheduling)
- Candidate portal with login
- Advanced recruitment analytics
- Offboarding workflow with exit checklists

### Mid-Term (3-6 Months)

- Performance review cycles automation
- 360-degree feedback system
- Training and development module
- Skill matrix and competency management
- Employee engagement surveys
- Multi-language support
- Mobile app (iOS/Android)
- API documentation and webhook support

### Long-Term (6-12 Months)

- AI-powered resume screening
- Predictive analytics (attrition risk)
- Chatbot for HR queries (advanced NLP)
- Integration marketplace
- White-label/reseller support
- Advanced reporting with BI tools
- Blockchain for credential verification
- Compliance automation (tax filing, audit reports)

---

## Technical Documentation

### File Structure

```
HRMS/
├── admin/               # Super admin panel
├── api/                 # Backend API endpoints
├── assets/              # CSS, JS, images, SVG
├── auth/                # Login, logout, password reset
├── candidate/           # Candidate portal (partial)
├── company/             # Company owner dashboard & modules
├── components/          # Reusable components (header, footer, sidebar)
├── config/              # Database and mail configuration
├── cron/                # Cron jobs (email queue processing)
├── database/            # SQL migration files
├── docs/                # Documentation (this file)
├── employee/            # Employee self-service portal
├── hr/                  # HR manager dashboard & modules
├── includes/            # Helper functions, mail templates
├── manager/             # Manager dashboard & modules
├── nexusbot/            # AI chatbot widget
├── pages/               # Public pages (features, careers, etc.)
├── subscription/        # Subscription and pricing
├── uploads/             # File uploads (resumes, documents)
├── user/                # User account settings
├── vendor/              # Composer dependencies
├── index.php            # Public homepage
├── .env                 # Environment variables (not in repo)
├── composer.json        # PHP dependencies
└── README.md            # Project readme
```

### Database Schema

**Tables**:

- `users`: System users with roles
- `companies`: Company/organization records
- `employees`: Employee profiles
- `departments`: Organization departments
- `attendance`: Daily attendance records
- `leaves`: Leave applications
- `leave_balances`: Employee leave quotas
- `jobs`: Job postings
- `candidates`: Candidate profiles
- `job_applications`: Application records
- `interviews`: Interview schedules (with meeting_link, status)
- `payslips`: Monthly payslips
- `tasks`: Task/goal assignments
- `documents`: File storage metadata
- `notifications`: System notifications
- `support_tickets`: Help desk tickets
- `activity_logs`: User activity tracking
- `assets`: Asset inventory
- `asset_categories`: Asset category definitions
- `asset_assignments`: Asset assignment history
- `system_settings`: Global configuration

---

## Conclusion

This document comprehensively covers **all features, modules, and capabilities** of StaffSync HRMS as of February 11, 2026. It serves as the single source of truth for developers, testers, administrators, and end-users to understand the system's full scope.

For specific technical details, refer to:

- **`LEAVE_APPROVAL_WORKFLOW.md`**: Detailed leave approval logic
- **`HIRING_ONBOARDING_SIMPLE_PLAN.md`**: Recruitment and onboarding plan
- **`IMPLEMENTATION_CHECKLIST.md`**: Deployment checklist
- **`TESTING_VALIDATION_GUIDE.md`**: QA testing scenarios
- **`README.md`**: Installation and setup guide

---

**Document Maintained By**: Development Team  
**For Updates**: Submit pull request or contact admin  
**License**: Proprietary - StaffSync HRMS © 2026
