# Contributing to StaffSync HRMS

Thank you for considering contributing to StaffSync HRMS! We welcome contributions from the community.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue with the following information:
- A clear, descriptive title
- Steps to reproduce the issue
- Expected behavior
- Actual behavior
- Screenshots (if applicable)
- Your environment (PHP version, MySQL version, browser, OS)

### Suggesting Enhancements

We welcome suggestions for new features or improvements. Please:
- Check if the suggestion already exists in issues
- Provide a clear description of the enhancement
- Explain why this enhancement would be useful
- Provide examples if possible

### Pull Requests

1. **Fork the repository** and create your branch from `main`
2. **Make your changes** following our coding standards
3. **Test your changes** thoroughly
4. **Update documentation** if needed
5. **Write clear commit messages**
6. **Submit a pull request**

## Coding Standards

### PHP

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add PHPDoc comments to all functions
- Use prepared statements for all database queries
- Never expose sensitive data in error messages
- Validate and sanitize all user inputs

### JavaScript

- Use ES6+ syntax where possible
- Add JSDoc comments for complex functions
- Use consistent indentation (2 spaces)
- Avoid global variables

### CSS

- Use meaningful class names
- Follow BEM naming convention where applicable
- Keep specificity low
- Comment complex styles

### Database

- Use meaningful table and column names
- Always use prepared statements
- Add indexes for frequently queried columns
- Document schema changes

## Security Guidelines

- **Never commit secrets** (.env files, credentials, API keys)
- **Always sanitize user input** using prepared statements
- **Escape output** using htmlspecialchars() for HTML context
- **Use HTTPS** in production
- **Validate file uploads** (type, size, extension)
- **Implement CSRF protection** for forms
- **Use password_hash()** for password storage

## Testing

- Test your changes on PHP 7.4 and 8.0+
- Test in different browsers (Chrome, Firefox, Safari, Edge)
- Test responsive design on mobile devices
- Verify database migrations work correctly

## Git Commit Messages

- Use present tense ("Add feature" not "Added feature")
- Use imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit first line to 72 characters
- Reference issues and pull requests

Examples:
```
Add employee performance tracking module
Fix SQL injection vulnerability in attendance API
Update README with installation instructions
```

## Code Review Process

1. All submissions require review
2. Reviewers will check for:
   - Code quality and standards
   - Security vulnerabilities
   - Performance issues
   - Test coverage
   - Documentation

3. Address review comments
4. Once approved, maintainers will merge

## Development Setup

1. Clone the repository
2. Copy `.env.example` to `.env` and configure
3. Import the database from `config/hrms_db.sql`
4. Configure your local web server
5. Start developing!

## Questions?

Feel free to:
- Create an issue for questions
- Reach out to maintainers
- Join our community discussions

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
