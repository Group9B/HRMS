# Changelog

All notable changes to StaffSync HRMS will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Comprehensive README.md with installation instructions and feature documentation
- .env.example file for easy environment configuration
- composer.json for dependency management
- CONTRIBUTING.md with contribution guidelines
- robots.txt for SEO optimization
- PHPDoc comments to all functions in includes/functions.php
- Security headers (Referrer-Policy, Permissions-Policy) in .htaccess
- Upload directory protection with .htaccess and index.php files
- Error logging for database connection failures
- Detailed .gitignore for common PHP artifacts

### Changed
- Enhanced .htaccess with improved security headers
- Fixed error document paths in .htaccess (removed old 'eduvault' references)
- Improved database connection error handling with logging support
- Updated .gitignore to include vendor/, error.log, and IDE files

### Security
- Added .htaccess in uploads directory to prevent PHP execution
- Added index.php in uploads directories to prevent directory listing
- Enhanced security headers in main .htaccess
- Improved error handling to avoid exposing sensitive information

## [1.0.0] - 2025-12-29

### Added
- Initial release of StaffSync HRMS
- Smart attendance tracking
- Payroll automation
- Performance analytics
- Employee onboarding
- Task management
- Leave management
- Recruitment module
- NexusBot AI assistant
- Role-based access control (6 user roles)
- Multi-company support
