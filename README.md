# SlimKit

Slim 4 starter kit for building REST APIs and server rendered pages with Twig.

## Features

- Slim 4 with PHP-DI container
- REST API and Twig SSR in one app
- PDO database access (no ORM)
- Native PHP sessions with secure cookies
- CSRF protection for forms
- CORS middleware for APIs
- Secure headers and CSP with per-request nonces
- Validation using Respect/Validation
- Error handling hides details in production
- Environment variables via vlucas/phpdotenv
- Example auth and file upload workflows
- PHPUnit, PHPStan and PHP-CS-Fixer configs

## Installation

```bash
composer install
cp .env.example .env
```

Edit `.env` with database credentials and other settings.

## Running

```bash
php -S localhost:8080 -t public
```

## Testing & QA

```bash
composer test
composer analyse
composer fix -- --dry-run
```

## Security Notes

- HTTPS is enforced via `SecureHeaders` and HSTS (configure your web server for TLS).
- CSP uses per-request nonces exposed to Twig templates through `csp_nonce`.
- Sessions use secure, HTTP only cookies.
