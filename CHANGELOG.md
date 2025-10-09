# Changelog

All notable changes to the Laravel Module Maker package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.2] - 2025-10-09

### Added
- Initial release of Laravel Module Maker package
- `make:module` artisan command for generating HMVC modules with API or Livewire support
- **Zero Configuration Setup** - Fully automatic registration and configuration:
  - ✅ Auto-registers service providers in `bootstrap/providers.php`
  - ✅ Auto-updates `composer.json` with Modules namespace
  - ✅ Auto-runs `composer dump-autoload`
  - ✅ Auto-registers routes, views, and migrations
- **Three Module Types** - Choose between API, Livewire, or Full-Stack:
  - **Full-Stack** (Recommended): Complete CRUD with both API endpoints AND Livewire components
  - **API**: Traditional controllers with JSON API resources
  - **Livewire**: Reactive components with Tailwind CSS
- **Interactive module type selection** - Prompted to choose module type when not specified
- Complete module structure generation including:
  - **API Modules**: Controllers with CRUD operations, Bootstrap-styled views
  - **Livewire Modules**: Livewire components with Tailwind CSS views
  - Models with Eloquent configuration
  - Routes (web and/or API) with RESTful patterns
  - Migrations with common fields
  - Feature and unit tests (including Livewire-specific tests)
  - Factories for testing data generation
  - Seeders with sample data
  - Service providers for module bootstrapping
- Dynamic name replacement system with placeholders:
  - `{{module}}` - Module name (PascalCase)
  - `{{moduleLower}}` - Module name (lowercase)
  - `{{moduleSnake}}` - Module name (snake_case)
  - `{{modulePlural}}` - Module name (pluralized)
  - `{{modulePluralLower}}` - Module name (pluralized, lowercase)
  - `{{namespace}}` - Namespace prefix
  - `{{moduleNamespace}}` - Full module namespace
- Configuration file for customizing behavior
- Stub template publishing for customization
- Automatic route registration
- Conflict handling for existing modules
- Command options for selective generation:
  - `--type=api|livewire` - Specify module type (or interactive prompt)
  - `--force` - Overwrite existing modules
  - `--no-tests` - Skip test generation
  - `--no-seeders` - Skip seeder generation
  - `--no-factories` - Skip factory generation
- Comprehensive documentation including:
  - README.md with installation and usage instructions
  - QUICKSTART.md for rapid setup
  - SOP.md with standard operating procedures
  - CHANGELOG.md for version tracking
- Laravel 12+ compatibility
- PHP 8.2+ requirement

### Features
- HMVC (Hierarchical Model-View-Controller) pattern support
- **Dual module types**: API (traditional controllers) and Livewire (reactive components)
- Modular architecture for scalable Laravel applications
- Self-contained modules with isolated functionality
- Consistent code structure and naming conventions
- **API Modules**: Bootstrap-compatible view templates with traditional forms
- **Livewire Modules**: Tailwind CSS templates with reactive components
- RESTful API endpoint generation
- **Livewire features**: Real-time validation, search, pagination, delete modals
- Database migration with timestamp-based naming
- PSR-4 autoloading compliance
- Service provider auto-registration
- Route group organization with middleware support

### Technical Details
- Package namespace: `PhpSamurai\LaravelModuleMaker`
- Command signature: `make:module {name} [--type=api|livewire]`
- Default module path: `modules/`
- Default namespace: `Modules`
- Default module type: `api` (configurable)
- Route registration: Both web and API routes (API modules) or web only (Livewire modules)
- Test framework: PHPUnit with Laravel testing features (including Livewire test helpers)
- View engine: Blade templates
- CSS frameworks: Bootstrap (API) or Tailwind CSS (Livewire)
- Database: Laravel Eloquent ORM
- Livewire version: ^3.0 (suggested dependency)

### Documentation
- Complete installation guide
- Usage examples and best practices
- Troubleshooting procedures
- Configuration options
- Customization instructions
- Quality assurance guidelines
- Emergency procedures

### Testing
- Verified functionality with Laravel 12
- Tested module generation with various names
- Confirmed route registration and accessibility
- Validated migration creation and execution
- Tested conflict handling and overwrite protection
- Verified stub template publishing
- Confirmed autoloading and namespace resolution

---

## [Unreleased]

### Planned Features
- ~~Livewire component generation for TALL stack~~ ✅ **Implemented in v1.0.0**
- Module-specific middleware generation
- Advanced relationship scaffolding
- API resource generation (JSON responses)
- Module dependency management
- Multi-language support for views
- Custom stub template categories
- Module removal commands
- Module listing and management commands
- Integration with popular Laravel packages (Inertia.js, Filament)
- Enhanced testing with Pest framework
- Docker support for development
- Alpine.js integration for Livewire modules

### Potential Improvements
- Performance optimization for large applications
- Enhanced error handling and validation
- Better integration with Laravel's service container
- Advanced configuration options
- Module versioning support
- Automated documentation generation
- Code quality checks and linting
- Integration with CI/CD pipelines
