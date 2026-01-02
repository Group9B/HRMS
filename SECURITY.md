# Security Policy

## Supported Versions

We release patches for security vulnerabilities in the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

The StaffSync HRMS team takes security bugs seriously. We appreciate your efforts to responsibly disclose your findings.

### How to Report

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please report them via email to:
- **Email**: security@staffsync.com

Please include the following information:

1. **Type of issue** (e.g., SQL injection, XSS, CSRF, etc.)
2. **Full paths of source file(s)** related to the issue
3. **Location of the affected source code** (tag/branch/commit or direct URL)
4. **Step-by-step instructions** to reproduce the issue
5. **Proof-of-concept or exploit code** (if possible)
6. **Impact** of the issue, including how an attacker might exploit it

### What to Expect

- You will receive an acknowledgment within **48 hours**
- We will investigate and keep you updated on our progress
- We aim to release a security patch within **7-14 days** for critical issues
- We will credit you in the security advisory (if desired)

## Security Best Practices

When using StaffSync HRMS:

### For Administrators

1. **Keep PHP and MySQL up to date**
2. **Use strong passwords** for all accounts
3. **Enable HTTPS** in production
4. **Regularly backup your database**
5. **Monitor error logs** for suspicious activity
6. **Limit file upload sizes** and types
7. **Keep the `.env` file secure** and never commit it to version control
8. **Regularly update** to the latest version

### For Developers

1. **Always use prepared statements** for database queries
2. **Validate and sanitize all user inputs**
3. **Escape output** using `htmlspecialchars()`
4. **Use CSRF tokens** for all forms
5. **Never expose sensitive data** in error messages
6. **Use `password_hash()`** for password storage
7. **Review code** for security issues before committing
8. **Run security scans** regularly

## Known Security Features

StaffSync HRMS includes:

- ✅ SQL injection protection via prepared statements
- ✅ XSS protection with output escaping
- ✅ CSRF protection for forms
- ✅ Password hashing with bcrypt
- ✅ Session management with secure cookies
- ✅ File upload validation
- ✅ Role-based access control (RBAC)
- ✅ Security headers (X-Frame-Options, X-XSS-Protection, etc.)
- ✅ Directory listing protection
- ✅ PHP execution prevention in uploads directory

## Security Updates

Security updates will be announced through:
- GitHub Security Advisories
- Release notes
- Email notifications to registered administrators

## Bug Bounty Program

We currently do not have a bug bounty program, but we deeply appreciate responsible security research and will acknowledge contributors in our security advisories.

## Contact

For security-related questions or concerns, please contact:
- **Email**: security@staffsync.com
- **GitHub Issues**: For non-security bugs only

Thank you for helping keep StaffSync HRMS and our users safe!
