# Integration Plan: Enhancing HRMS to BambooHR Envy

This plan details the technical steps to integrate the identified missing features into your current PHP/MySQL HRMS.

## Phase 1: The "Paperless" Foundation (High Impact)

### 1. Digital Onboarding & Offboarding
**Goal**: Automate the "New Hire Packet" and "Exit Process".

#### Database Changes
```sql
CREATE TABLE onboarding_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT,
    name VARCHAR(100), -- e.g., "Full-time Engineer", "Contractor"
    tasks JSON -- List of tasks: ["Sign Contract", "Upload ID", "Watch Intro Video"]
);

CREATE TABLE employee_tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT,
    assigned_by INT,
    task_name VARCHAR(255),
    status ENUM('pending', 'completed') DEFAULT 'pending',
    due_date DATE,
    related_document_id INT NULL -- If task requires signing a doc
);
```

#### Implementation Steps
1.  **Backend (`api/api_onboarding.php`)**: Create endpoints to manage templates and assign tasks to new hires automatically upon "Hire" action in `api_recruitment.php`.
2.  **Frontend (`onboarding/index.php`)**: A dedicated view for new hires (Role 4) to see their checklist.
3.  **Automation**: In `api_recruitment.php`, when status becomes 'hired', trigger `assignOnboardingTask($employee_id, $template_id)`.

### 2. E-Signatures (Native)
**Goal**: Allow employees to sign documents digitally without printing.

#### Technology Stack
-   **Frontend**: `signature_pad` (JS library) for capturing mouse/touch signatures.
-   **Backend**: `TCPDF` or `FPDF` to overlay the captured PNG signature onto the PDF document.

#### Implementation Steps
1.  **Schema**: Add `signature_data` (LONGTEXT) status to `documents` or creating a `signed_documents` table.
2.  **UI**: Create a modal where users draw their signature.
3.  **Process**:
    -   User clicks "Sign".
    -   Modal opens -> User draws -> "Save" converts to Base64 image.
    -   AJAX posts Base64 to `api/api_documents.php?action=sign`.
    -   PHP decodes image, loads the PDF template, places image at coordinates (X,Y), saves new PDF.

---

## Phase 2: Culture & Engagement

### 1. Employee Satisfaction (eNPS)
**Goal**: Anonymous quarterly surveys.

#### Database Changes
```sql
CREATE TABLE surveys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT,
    question TEXT, -- "How likely are you to recommend us?"
    is_active BOOLEAN
);

CREATE TABLE survey_responses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    survey_id INT,
    score INT, -- 0-10
    feedback TEXT,
    created_at TIMESTAMP
    -- NO employee_id to ensure anonymity
);
```

#### Implementation Steps
1.  **Scheduled Job (`cron/send_surveys.php`)**: Monthly cron job to email a unique, one-time-use link to all active employees.
2.  **Public Page (`survey.php?token=...`)**: Simple page to capture score and comment.

### 2. Peer & 360 Feedback
**Goal**: Allow peers to review each other.

#### Implementation Steps
1.  **Modify `performance` table**: Add `review_type` ENUM('manager', 'peer', 'self').
2.  **UI**: "Request Feedback" button on dashboard. User selects peers.
3.  **Notifications**: Peers get a "Pending Review" task.

---

## Phase 3: Advanced HRIS Features

### 1. Benefits Administration
**Goal**: track insurance and perks.

#### Database Changes
```sql
CREATE TABLE benefit_plans (
    id INT PRIMARY KEY,
    name VARCHAR(100), -- "Gold Health Plan"
    provider VARCHAR(100), -- "BlueCross"
    employee_cost DECIMAL(10,2),
    employer_cost DECIMAL(10,2)
);

CREATE TABLE employee_benefits (
    id INT PRIMARY KEY,
    employee_id INT,
    plan_id INT,
    enrollment_date DATE,
    status ENUM('active', 'waived')
);
```

#### Implementation Steps
1.  **Link to Payroll**: Update `api_payroll.php` to automatically pull `employee_cost` from `employee_benefits` into the `deductions` JSON during generation.

### 2. Organizational Chart
**Goal**: Visual hierarchy.

#### Implementation Steps
1.  **Frontend Library**: Use `OrgChart.js` or `d3.js`.
2.  **Data Source**: `api/api_employees.php?action=get_org_data`.
3.  **Logic**: Build a recursive tree using `manager_id` (you may need to add `manager_id` column to `employees` table if it's currently only linked via departments/teams).

---

## Technical Recommendations
1.  **Move to MVC**: Your current `api_*.php` structure is functional but will get messy. Consider a lightweight router foundation.
2.  **Security**: Ensure your file uploads for "Signed Documents" are stored outside the public web root (`public_html` or `htdocs`) and served via a proxy script (`download.php`) to prevent direct access.
