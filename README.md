# Laravel Module Maker

A powerful Laravel package for generating modular HMVC (Hierarchical Model-View-Controller) structures with artisan commands. This package helps you organize your Laravel applications into self-contained modules, improving maintainability and scalability.

## Features

- 🚀 **Easy Module Generation**: Generate complete HMVC modules with a single artisan command
- 🏗️ **Complete Structure**: Creates controllers, models, views, routes, migrations, tests, seeders, and factories
- 🔄 **Dynamic Templates**: Uses customizable stub templates with dynamic name replacements
- 📝 **Auto Route Registration**: Automatically registers module routes in your main route files
- 🧪 **Test Generation**: Generates feature and unit tests for your modules
- 🎨 **Customizable Stubs**: Publish and customize stub templates to match your project needs
- ⚡ **Conflict Handling**: Prevents overwriting existing modules unless forced
- 🔧 **Laravel 12+ Compatible**: Built for the latest Laravel framework

## Requirements

- PHP 8.2 or higher
- Laravel 12.0 or higher
- Composer
- Git (for version control)

## Installation

### Via Composer

```bash
composer require phpsamurai/laravel-module-maker
```

## Configuration

After installation, publish the configuration file:

```bash
php artisan vendor:publish --tag=module-maker-config
```

This will create a `config/module-maker.php` file where you can customize:

- **Module Path**: Where modules are generated (default: `modules`)
- **Namespace**: Module namespace prefix (default: `Modules`)
- **Auto Register Routes**: Whether to automatically register routes (default: `true`)
- **Route Registration**: Which routes to register (`web`, `api`, or `both`)
- **Generate Tests**: Whether to generate test files (default: `true`)
- **Generate Seeders**: Whether to generate seeder files (default: `true`)
- **Generate Factories**: Whether to generate factory files (default: `true`)

## Usage

### Basic Usage

Generate a new module:

```bash
php artisan make:module Post
```

This will create a complete module structure:

```bash
modules/
└── Post/
    ├── Controllers/
    │   └── PostController.php
    ├── Models/
    │   └── Post.php
    ├── Views/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   └── edit.blade.php
    ├── Routes/
    │   ├── web.php
    │   └── api.php
    ├── Database/
    │   ├── Migrations/
    │   │   └── 2025_10_07_165451_create_post_table.php
    │   ├── Seeders/
    │   │   └── PostSeeder.php
    │   └── Factories/
    │       └── PostFactory.php
    ├── Tests/
    │   ├── Feature/
    │   │   └── PostTest.php
    │   └── Unit/
    │       └── PostTest.php
    ├── Providers/
    │   └── PostServiceProvider.php
    └── Http/
        ├── Middleware/
        └── Requests/
```

### Command Options

```bash
# Force overwrite existing module
php artisan make:module Post --force

# Skip generating test files
php artisan make:module Post --no-tests

# Skip generating seeder files
php artisan make:module Post --no-seeders

# Skip generating factory files
php artisan make:module Post --no-factories

# Combine options
php artisan make:module Post --no-tests --no-seeders
```

### Setup After Generation

1. **Register Service Provider**: Add the generated service provider to `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    
    // Module Service Providers
    Modules\Post\Providers\PostServiceProvider::class,
];
```

2. **Update Autoload**: Add modules namespace to your `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "Modules\\": "modules/"
        }
    }
}
```

3. **Regenerate Autoload**:

```bash
composer dump-autoload
```

4. **Run Migrations**:

```bash
php artisan migrate
```

## Customizing Stubs

Publish the stub templates to customize them:

```bash
php artisan vendor:publish --tag=module-maker-stubs
```

This will copy all stub templates to `resources/stubs/module-maker/`. You can then modify these templates to match your project's coding standards and preferences.

### Available Stub Variables

The following variables are available in all stub templates:

- `{{module}}` - Module name (e.g., "Post")
- `{{moduleLower}}` - Module name in lowercase (e.g., "post")
- `{{moduleSnake}}` - Module name in snake_case (e.g., "post")
- `{{modulePlural}}` - Module name pluralized (e.g., "Posts")
- `{{modulePluralLower}}` - Pluralized module name in lowercase (e.g., "posts")
- `{{namespace}}` - Namespace prefix (e.g., "Modules")
- `{{moduleNamespace}}` - Full module namespace (e.g., "Modules\Post")

## Module Structure

Each generated module follows the HMVC pattern with:

### Controllers

- Full CRUD operations
- Proper validation
- Resource routing
- Type hints and return types

### Models

- Mass assignable attributes
- Proper table configuration
- Useful scopes (e.g., `active()`)
- Eloquent relationships ready

### Views

- Bootstrap-compatible templates
- Form validation display
- Success/error messages
- Responsive design

### Routes

- RESTful resource routes
- Proper middleware groups
- Named routes for easy reference

### Database

- Migration with common fields
- Factory for testing
- Seeder with sample data

### Tests

- Feature tests for HTTP endpoints
- Unit tests for models
- Database testing with RefreshDatabase

## Advanced Usage

### Custom Module Configuration

You can customize module generation by modifying the configuration file:

```php
// config/module-maker.php
return [
    'path' => 'app/Modules',
    'namespace' => 'App\\Modules',
    'auto_register_routes' => false,
    'route_registration' => 'web',
    'generate_tests' => false,
];
```

### Manual Route Registration

If you disable auto route registration, you can manually register routes in your service provider:

```php
// In your module's ServiceProvider
public function boot(): void
{
    $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
    $this->loadViewsFrom(__DIR__ . '/../Views', 'posts');
    $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
}
```

### Custom Directory Structure

You can extend the `ModuleGenerator` service to create custom directory structures or add additional files.

## Best Practices

1. **Naming Convention**: Use PascalCase for module names (e.g., `Post`, `UserManagement`)
2. **Service Providers**: Always register module service providers in `bootstrap/providers.php`
3. **Namespace**: Keep the default `Modules` namespace for consistency
4. **Routes**: Use the generated routes as a starting point and customize as needed
5. **Views**: Customize the generated views to match your application's design
6. **Tests**: Write additional tests beyond the generated ones for complex functionality

## Troubleshooting

### Module Classes Not Found

If you get "Class not found" errors:

1. Ensure the modules namespace is added to `composer.json`
2. Run `composer dump-autoload`
3. Check that the service provider is registered

### Routes Not Working

If routes aren't accessible:

1. Verify the service provider is registered
2. Check that routes are properly included in main route files
3. Ensure the controller namespace is correct

### Migration Not Found

If migrations aren't detected:

1. Verify the service provider is registered
2. Check that `loadMigrationsFrom()` is called in the service provider
3. Ensure migration files are in the correct directory

## Examples

### Creating a Blog Module

```bash
# Generate the module
php artisan make:module Blog

# Update autoload
composer dump-autoload

# Add service provider to bootstrap/providers.php
Modules\Blog\Providers\BlogServiceProvider::class

# Run migrations
php artisan migrate

# Check routes
php artisan route:list --name=blogs
```

### Creating an E-commerce Module

```bash
# Generate with custom options
php artisan make:module Product --no-tests

# The module will be created without test files
# You can add tests later if needed
```

## Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `path` | string | `modules` | Directory where modules are generated |
| `namespace` | string | `Modules` | Namespace prefix for modules |
| `auto_register_routes` | boolean | `true` | Automatically register module routes |
| `route_registration` | string | `both` | Which routes to register (`web`, `api`, `both`) |
| `generate_tests` | boolean | `true` | Generate test files |
| `generate_seeders` | boolean | `true` | Generate seeder files |
| `generate_factories` | boolean | `true` | Generate factory files |

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

For support, please open an issue on the GitHub repository or contact the maintainer.

## Changelog

### Version 1.0.0

- Initial release
- Basic module generation
- HMVC structure support
- Route auto-registration
- Test, seeder, and factory generation
- Customizable stub templates
