# SlimKit

SlimKit is a starter kit for building full featured applications with [Slim 4](https://www.slimframework.com/) and Twig. It combines REST APIs and server rendered pages in a single project and includes batteries‑included tooling for modern PHP development.

## Features

- PHP 8.2 with Slim 4 and PHP-DI
- REST API and Twig-based SSR in one app
- Native sessions secured with HTTP-only cookies
- CSRF protection, secure headers and CORS middleware
- Validation via `respect/validation`
- PDO database access (no ORM)
- Example authentication and file upload flows
- Environment configuration using `vlucas/phpdotenv`
- PHPUnit, PHPStan and PHP-CS-Fixer configurations

## Requirements

- PHP 8.2+
- Composer

## Installation

```bash
composer install
cp .env.example .env
```

Update `.env` with database credentials and other environment settings.

## Running the Application

```bash
php -S localhost:8080 -t public
```

Navigate to <http://localhost:8080> in your browser. Requests are served through `public/index.php`.

## Project Structure

```
app/
├── Controllers/      # Route action classes
├── Middleware/       # Custom middleware
├── Routes/           # Route definitions grouped by area
├── Services/         # Reusable services (e.g. Auth, Upload)
├── Support/          # Helpers and utility classes
├── View/             # View helpers
└── bootstrap.php     # Creates container and application

config/
├── dependencies.php  # Container definitions
├── settings.php      # Application settings
└── twig.php          # Twig environment setup

public/
├── index.php         # Front controller / entry point
└── assets/           # Public assets served directly

storage/
├── cache/            # Cache files
└── uploads/          # Uploaded files

templates/
├── layouts/          # Layout templates
├── auth/             # Authentication views
└── files/            # File upload views

tests/
├── Feature/          # Integration tests
└── Unit/             # Unit tests
```

### Adding Routes and Controllers

1. Create a controller in `app/Controllers`:

```php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HelloController
{
    public function __invoke(Request $request, Response $response): Response
    {
        $response->getBody()->write('Hello');
        return $response;
    }
}
```

2. Register a route in `app/Routes` (e.g. `app/Routes/web.php`):

```php
use Slim\App;
use App\Controllers\HelloController;

return function (App $app) {
    $app->get('/hello', HelloController::class);
};
```

Routes are loaded from `app/bootstrap.php`.

### Working with Views

Twig templates live in the `templates/` directory. Layouts reside under `templates/layouts` and are extended by page templates. Render a view from a controller using the `Twig` view helper:

```php
use Slim\Views\Twig;

class HomeController
{
    public function __construct(private Twig $view) {}

    public function __invoke(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'home.twig');
    }
}
```

Static assets such as CSS or JS go in `public/assets` and are referenced directly in templates.

### Middleware & Services

Place custom middleware in `app/Middleware` and register them in `app/bootstrap.php` or within route groups. Reusable business logic belongs in `app/Services` and can be autowired via PHP-DI.

### Testing & Quality Assurance

```bash
composer test     # PHPUnit tests
composer analyse  # PHPStan static analysis
composer fix -- --dry-run  # PHP-CS-Fixer in dry run mode
```

### Deployment Notes

- Configure your web server to point the document root to the `public/` directory.
- Ensure HTTPS is enabled. Secure headers and HSTS are enforced by middleware.
- Sessions use secure, HTTP-only cookies; adjust cookie settings in `config/settings.php` if needed.

## Contributing

Feel free to open issues or submit pull requests to improve SlimKit.

## License

Released under the MIT License.
