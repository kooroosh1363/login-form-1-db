# VaultDB — Secure PHP Database Authentication

[![Quality](https://github.com/kooroosh1363/login-form-1-db/actions/workflows/quality.yml/badge.svg)](https://github.com/kooroosh1363/login-form-1-db/actions/workflows/quality.yml)

VaultDB modernizes the original 2023 PHP + MySQL login exercise into a secure, database-focused authentication demo using PDO.

## Why this upgrade matters

The original implementation contained:

- SQL built by concatenating user input
- plaintext passwords in the database
- sample plaintext credentials committed in an SQL dump
- no CSRF protection
- GET-based logout
- no session ID rotation
- no login throttling
- hard-coded database credentials
- raw error values passed through the URL
- a ~5.3 MB background image and external web fonts

The current version replaces those patterns with explicit security controls.

## Security controls

- PDO prepared statements
- `password_hash()` / `password_verify()`
- automatic password rehashing
- CSRF tokens for sign-in and sign-out
- strict session mode
- session ID rotation after login and during active sessions
- 30-minute inactivity timeout
- generic credential errors
- dummy password hash work for unknown accounts
- temporary login throttling
- POST-only logout
- security headers
- environment-based database configuration

## Database support

VaultDB supports both:

- SQLite for zero-setup local development
- MySQL for the original database-oriented use case

The same repository and authentication service are tested against both engines in GitHub Actions.

## Local SQLite setup

No configuration is required:

```bash
php bin/migrate.php
php bin/create-user.php user@example.com "Demo User" "correct-horse-battery-staple"
php -S localhost:8000
```

Then open `http://localhost:8000`.

## MySQL setup

Create a database/user, then configure environment variables:

```bash
export DB_DSN='mysql:host=127.0.0.1;port=3306;dbname=auth_demo;charset=utf8mb4'
export DB_USER='auth_app'
export DB_PASSWORD='change-me'

php bin/migrate.php
php bin/create-user.php user@example.com "Demo User" "correct-horse-battery-staple"
php -S localhost:8000
```

See `.env.example` for configuration examples.

## Architecture

```text
HTTP request
   │
   ▼
index.php / home.php / logout.php
   │
   ├── validation
   ├── CSRF/session helpers
   └── AuthService
          │
          ├── password verification
          ├── throttle policy
          └── UserRepository
                  │
                  ▼
               PDO
             /     \
         SQLite   MySQL
```

## Tests

```bash
php tests/run.php
```

CI additionally starts a real MySQL 8 service and runs the same authentication flow against it.

## Important history note

The original repository history contains plaintext sample passwords from the old SQL dump. The current application does not use them, and the dump has been removed from the maintained branch.

Do not reuse those historical sample credentials for any real system.

## Scope

This is an authentication engineering demo, not a full identity platform. Registration, password reset, MFA, email verification, audit retention, and distributed rate limiting remain out of scope.

## Deployment

GitHub Pages cannot execute PHP. Deploy to a PHP-capable host and keep environment credentials out of the repository.

## License

No license is currently included.
