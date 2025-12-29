# StaffSync HRMS - Human Resource Management System

A comprehensive, modern HR management solution designed to streamline business operations and empower your workforce.

## Features

- **Smart Attendance** - Real-time attendance tracking with geofencing support
- **Payroll Automation** - Automated salary calculations, deductions, and tax compliance
- **Performance Analytics** - Detailed insights into employee performance and productivity
- **Easy Onboarding** - Seamless digital onboarding with automated workflows
- **Task Management** - Assign tasks and track progress efficiently
- **Secure Data** - Enterprise-grade security for employee data
- **Leave Management** - Comprehensive leave policy and approval workflow
- **Recruitment** - Complete recruitment and candidate management system
- **NexusBot AI Assistant** - Intelligent HR chatbot for daily HR tasks

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Libraries**: jQuery, Chart.js, DataTables

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server with mod_rewrite enabled
- Composer (optional, for dependency management)

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Group9B/HRMS.git
   cd HRMS
   ```

2. **Configure environment variables**
   - Copy `.env.example` to `.env`
   - Update database credentials and other settings
   ```bash
   cp .env.example .env
   ```

3. **Import the database**
   - Create a new MySQL database
   - Import the SQL file from `config/hrms_db.sql`
   ```bash
   mysql -u your_username -p your_database_name < config/hrms_db.sql
   ```

4. **Configure Apache**
   - Ensure mod_rewrite is enabled
   - Point your virtual host document root to the HRMS directory
   - Ensure .htaccess is being processed

5. **Set permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/policies/
   ```

6. **Access the application**
   - Navigate to `http://yourdomain.com/hrms/`
   - Default login credentials are in the database

## Project Structure

```
HRMS/
├── admin/          # Super admin dashboard and management
├── api/            # REST API endpoints
├── assets/         # CSS, JS, images, and SVG files
├── auth/           # Authentication (login/logout)
├── candidate/      # Candidate portal
├── company/        # Company admin dashboard
├── components/     # Reusable UI components
├── config/         # Database configuration
├── database/       # SQL backup files
├── employee/       # Employee dashboard
├── hr/             # HR manager dashboard
├── includes/       # Shared functions and utilities
├── manager/        # Manager dashboard
├── nexusbot/       # AI chatbot system
├── pages/          # Error pages (404, 500, etc.)
└── uploads/        # User uploaded files
```

## Security Features

- Password hashing using PHP's `password_hash()`
- Prepared statements to prevent SQL injection
- XSS protection with output escaping
- CSRF protection for forms
- Session management with secure cookies
- File upload validation and restrictions
- Directory listing protection
- Security headers (X-Frame-Options, X-XSS-Protection, etc.)

## Usage

### User Roles

1. **Super Admin** - Full system access and management
2. **Company Admin** - Company-wide management and settings
3. **HR Manager** - Employee and HR operations management
4. **Manager** - Team management and approvals
5. **Employee** - Personal dashboard and self-service
6. **Auditor** - Read-only access for compliance

### Key Workflows

- **Employee Onboarding**: HR creates user account → Assigns department/designation → Employee completes profile
- **Attendance**: Employees check in/out → Managers review → Reports generated
- **Leave Management**: Employee requests leave → Manager approves → HR tracks
- **Payroll**: HR enters payroll data → System generates payslips → Employees view/download

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, email support@staffsync.com or create an issue in the repository.

## Acknowledgments

- Bootstrap for the UI framework
- Chart.js for data visualization
- DataTables for advanced table features
- Tabler Icons for the icon set
