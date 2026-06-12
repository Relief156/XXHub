# Security Policy

## Supported Versions

| Version | Supported          |
|---------|--------------------|
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

If you discover a security vulnerability within this project, please follow these steps:

1. **Do not create a public issue** - Security vulnerabilities should be reported privately.

2. **Send an email** to security@example.com with the following information:
   - Description of the vulnerability
   - Steps to reproduce
   - Impact assessment
   - Any potential fixes (if known)

3. **Wait for response** - We will acknowledge receipt within 48 hours and provide a timeline for fixing the issue.

4. **Disclosure** - After the vulnerability is fixed, we will coordinate a public disclosure with you.

## Security Best Practices

### For Deployers

1. **Keep software updated** - Regularly update the application to the latest version.

2. **Secure the installation** - After installation, delete or restrict access to `install.php`.

3. **Set proper permissions**:
   ```bash
   chmod 600 config.json
   chmod 700 data/
   ```

4. **Use HTTPS** - Always serve the application over HTTPS to protect data in transit.

5. **Limit uploads** - Configure appropriate file size limits and allowed file types.

6. **Backup regularly** - Schedule regular backups of the database and uploaded files.

### For Developers

1. **Input validation** - Always validate and sanitize user input.

2. **Parameterized queries** - Use prepared statements for all database queries.

3. **File upload security** - Validate file types, sizes, and contents before saving.

4. **Authentication** - Use secure password hashing and session management.

5. **Error handling** - Avoid exposing sensitive information in error messages.

## Known Security Features

- Passwords are hashed using bcrypt
- CSRF protection for forms
- Input validation and sanitization
- SQL injection prevention (prepared statements)
- File type validation
- Rate limiting for API endpoints
