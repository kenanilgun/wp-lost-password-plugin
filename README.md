# WordPress Lost Password Plugin with REST API

A WordPress plugin that provides REST API endpoints for handling lost password requests securely. This plugin allows users to reset their passwords through a simple three-step process using email verification.

## Features

- Secure password reset flow
- Email-based verification
- 6-digit numeric verification code
- 10-minute expiration time for reset codes
- RESTful API endpoints

## API Endpoints

### 1. Request Password Reset

Initiates the password reset process by sending a verification code to the user's email.

```
POST /wp-json/lostpassword/v1/request

Request body:
{
    "email": "user@example.com"
}

Success Response (200):
{
    "reset_code": "123456"
}

Error Response (404):
{
    "error": "Email not found."
}
```

### 2. Validate Reset Code

Validates the reset code sent to the user's email.

```
POST /wp-json/lostpassword/v1/validate

Request body:
{
    "email": "user@example.com",
    "code": "123456"
}

Success Response (200):
{
    "status": "approved"
}

Error Response (403):
{
    "status": "not approved",
    "error": "Code expired."
}
```

### 3. Reset Password

Sets a new password after successful code validation.

```
POST /wp-json/lostpassword/v1/reset

Request body:
{
    "email": "user@example.com",
    "code": "123456",
    "new_password": "your-new-password"
}

Success Response (200):
{
    "status": "password reset successfully"
}

Error Response (403):
{
    "status": "not approved"
}
```

## Testing URLs

You can test the endpoints using these curl commands:

1. Request Password Reset:
```bash
curl -X POST \
  http://your-wordpress-site.com/wp-json/lostpassword/v1/request \
  -H 'Content-Type: application/json' \
  -d '{"email": "user@example.com"}'
```

2. Validate Reset Code:
```bash
curl -X POST \
  http://your-wordpress-site.com/wp-json/lostpassword/v1/validate \
  -H 'Content-Type: application/json' \
  -d '{"email": "user@example.com", "code": "123456"}'
```

3. Reset Password:
```bash
curl -X POST \
  http://your-wordpress-site.com/wp-json/lostpassword/v1/reset \
  -H 'Content-Type: application/json' \
  -d '{"email": "user@example.com", "code": "123456", "new_password": "your-new-password"}'
```

## Security Features

- Verification codes expire after 10 minutes
- Unique 6-digit numeric codes
- Email verification required
- Invalid email addresses are rejected
- Built-in WordPress security measures

## License

Licensed under the Apache License, Version 2.0. See the LICENSE file for details.
