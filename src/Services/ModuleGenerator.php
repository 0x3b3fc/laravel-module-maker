<?php

namespace PhpSamurai\LaravelModuleMaker\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleGenerator
{
    /**
     * Navigation manager instance.
     */
    protected NavigationManager $navigationManager;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->navigationManager = new NavigationManager();
    }
    /**
     * Generator options.
     *
     * @var array
     */
    protected array $options = [];

    /**
     * Set generator options.
     */
    public function setOptions(array $options): void
    {
        $this->options = array_merge($this->getDefaultOptions(), $options);
    }

    /**
     * Get default options.
     */
    protected function getDefaultOptions(): array
    {
        return [
            'force' => false,
            'type' => config('module-maker.default_type', 'api'),
            'generate_tests' => config('module-maker.generate_tests', true),
            'generate_seeders' => config('module-maker.generate_seeders', true),
            'generate_factories' => config('module-maker.generate_factories', true),
        ];
    }

    /**
     * Check if module exists.
     */
    public function moduleExists(string $moduleName): bool
    {
        $modulePath = $this->getModulePath($moduleName);
        return File::exists($modulePath);
    }

    /**
     * Generate module structure.
     */
    public function generate(string $moduleName): array
    {
        try {
            $modulePath = $this->getModulePath($moduleName);

            // Create main module directory
            File::makeDirectory($modulePath, 0755, true);

            $generatedFiles = [];

            // Generate directory structure
            $directories = $this->getModuleDirectories($moduleName);
            foreach ($directories as $directory) {
                File::makeDirectory($directory, 0755, true);
                $generatedFiles[] = $directory;
            }

            // Generate files from stubs
            $files = $this->generateFilesFromStubs($moduleName, $modulePath);
            $generatedFiles = array_merge($generatedFiles, $files);

            // Create Livewire layout if needed
            if (in_array($this->options['type'], ['livewire', 'full'])) {
                $this->ensureLivewireLayout();
            }

            // Register routes if auto_register_routes is enabled
            if (config('module-maker.auto_register_routes', true)) {
                $this->registerRoutes($moduleName);
            }

            // Add navigation link for Livewire and Full modules
            if (in_array($this->options['type'], ['livewire', 'full'])) {
                $this->navigationManager->addNavigationLink($moduleName);
            }

            return [
                'success' => true,
                'files' => $generatedFiles,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get module path.
     */
    protected function getModulePath(string $moduleName): string
    {
        return base_path(config('module-maker.path', 'modules') . '/' . $moduleName);
    }

    /**
     * Get module directories to create.
     */
    protected function getModuleDirectories(string $moduleName): array
    {
        $basePath = $this->getModulePath($moduleName);
        $type = $this->options['type'];

        if ($type === 'livewire') {
            // Livewire only: just UI components
            return [
                $basePath . '/Livewire',
                $basePath . '/Views/livewire',
                $basePath . '/Routes',
                $basePath . '/Providers',
            ];
        }

        // For API and Full modules
        $directories = [
            $basePath . '/Models',
            $basePath . '/Routes',
            $basePath . '/Database/Migrations',
            $basePath . '/Database/Seeders',
            $basePath . '/Database/Factories',
            $basePath . '/Http/Middleware',
            $basePath . '/Http/Requests',
            $basePath . '/Http/Resources',
            $basePath . '/Providers',
            $basePath . '/Tests/Feature',
            $basePath . '/Tests/Unit',
            $basePath . '/Config',
        ];

        if ($type === 'full') {
            // Full-stack: both API and Livewire
            $directories[] = $basePath . '/Controllers';
            $directories[] = $basePath . '/Livewire';
            $directories[] = $basePath . '/Views';
            $directories[] = $basePath . '/Views/livewire';
        } else {
            // API only
            $directories[] = $basePath . '/Controllers';
            $directories[] = $basePath . '/Views';
        }

        return $directories;
    }

    /**
     * Generate files from stubs.
     */
    protected function generateFilesFromStubs(string $moduleName, string $modulePath): array
    {
        $generatedFiles = [];
        $type = $this->options['type'];

        if ($type === 'full') {
            // Generate both API and Livewire files
            $apiFiles = $this->generateApiFiles($moduleName, $modulePath);
            $livewireFiles = $this->generateLivewireFiles($moduleName, $modulePath);
            $generatedFiles = array_merge($apiFiles, $livewireFiles);

            // Generate common files for full modules
            $commonFiles = $this->generateCommonFiles($moduleName, $modulePath);
            $generatedFiles = array_merge($generatedFiles, $commonFiles);
        } elseif ($type === 'livewire') {
            // Livewire only: just UI components and service provider
            $generatedFiles = $this->generateLivewireFiles($moduleName, $modulePath);

            // Only generate service provider for Livewire-only modules
            $serviceProviderFile = $this->generateServiceProvider($moduleName, $modulePath, 'livewire');
            if ($serviceProviderFile) $generatedFiles[] = $serviceProviderFile;
        } else {
            // API: generate backend with models, migrations, etc.
            $generatedFiles = $this->generateApiFiles($moduleName, $modulePath);

            // Generate common files for API modules
            $commonFiles = $this->generateCommonFiles($moduleName, $modulePath);
            $generatedFiles = array_merge($generatedFiles, $commonFiles);
        }

        return $generatedFiles;
    }

    /**
     * Generate API-specific files.
     */
    protected function generateApiFiles(string $moduleName, string $modulePath): array
    {
        $generatedFiles = [];
        $stubPath = $this->getStubPath();
        $isFull = $this->options['type'] === 'full';

        // Generate Controller (use API controller for full type, regular for API-only)
        $controllerStub = $isFull ? 'controller-api.stub' : 'controller.stub';
        $controllerName = $isFull ? $moduleName . 'ApiController.php' : $moduleName . 'Controller.php';

        $controllerFile = $this->generateFileFromStub(
            $stubPath . '/' . $controllerStub,
            $modulePath . '/Controllers/' . $controllerName,
            $this->getReplacements($moduleName)
        );
        if ($controllerFile) $generatedFiles[] = $controllerFile;

        // Generate API Resources (for full or API type)
        $resourceFile = $this->generateFileFromStub(
            $stubPath . '/resource.stub',
            $modulePath . '/Http/Resources/' . $moduleName . 'Resource.php',
            $this->getReplacements($moduleName)
        );
        if ($resourceFile) $generatedFiles[] = $resourceFile;

        $collectionFile = $this->generateFileFromStub(
            $stubPath . '/resource-collection.stub',
            $modulePath . '/Http/Resources/' . $moduleName . 'Collection.php',
            $this->getReplacements($moduleName)
        );
        if ($collectionFile) $generatedFiles[] = $collectionFile;

        // Generate Routes
        if (!$isFull) {
            // For API-only, generate web routes
            $webRoutesFile = $this->generateFileFromStub(
                $stubPath . '/routes-web.stub',
                $modulePath . '/Routes/web.php',
                $this->getReplacements($moduleName)
            );
            if ($webRoutesFile) $generatedFiles[] = $webRoutesFile;
        }

        // Generate API routes (use full stub for full type)
        $apiRoutesStub = $isFull ? 'routes-api-full.stub' : 'routes-api.stub';
        $apiRoutesFile = $this->generateFileFromStub(
            $stubPath . '/' . $apiRoutesStub,
            $modulePath . '/Routes/api.php',
            $this->getReplacements($moduleName)
        );
        if ($apiRoutesFile) $generatedFiles[] = $apiRoutesFile;

        // Generate Views (only if not full type, as full type will have Livewire views)
        if (!$isFull) {
            $indexViewFile = $this->generateFileFromStub(
                $stubPath . '/view-index.stub',
                $modulePath . '/Views/index.blade.php',
                $this->getReplacements($moduleName)
            );
            if ($indexViewFile) $generatedFiles[] = $indexViewFile;

            $createViewFile = $this->generateFileFromStub(
                $stubPath . '/view-create.stub',
                $modulePath . '/Views/create.blade.php',
                $this->getReplacements($moduleName)
            );
            if ($createViewFile) $generatedFiles[] = $createViewFile;

            $editViewFile = $this->generateFileFromStub(
                $stubPath . '/view-edit.stub',
                $modulePath . '/Views/edit.blade.php',
                $this->getReplacements($moduleName)
            );
            if ($editViewFile) $generatedFiles[] = $editViewFile;
        }

        return $generatedFiles;
    }

    /**
     * Generate Livewire-specific files.
     */
    protected function generateLivewireFiles(string $moduleName, string $modulePath): array
    {
        $generatedFiles = [];
        $stubPath = $this->getStubPath();

        // Generate Livewire Components
        $indexComponent = $this->generateFileFromStub(
            $stubPath . '/livewire-index.stub',
            $modulePath . '/Livewire/Index.php',
            $this->getReplacements($moduleName)
        );
        if ($indexComponent) $generatedFiles[] = $indexComponent;

        $createComponent = $this->generateFileFromStub(
            $stubPath . '/livewire-create.stub',
            $modulePath . '/Livewire/Create.php',
            $this->getReplacements($moduleName)
        );
        if ($createComponent) $generatedFiles[] = $createComponent;

        $editComponent = $this->generateFileFromStub(
            $stubPath . '/livewire-edit.stub',
            $modulePath . '/Livewire/Edit.php',
            $this->getReplacements($moduleName)
        );
        if ($editComponent) $generatedFiles[] = $editComponent;

        // Generate Livewire Views
        $indexView = $this->generateFileFromStub(
            $stubPath . '/livewire-view-index.stub',
            $modulePath . '/Views/livewire/index.blade.php',
            $this->getReplacements($moduleName)
        );
        if ($indexView) $generatedFiles[] = $indexView;

        $createView = $this->generateFileFromStub(
            $stubPath . '/livewire-view-create.stub',
            $modulePath . '/Views/livewire/create.blade.php',
            $this->getReplacements($moduleName)
        );
        if ($createView) $generatedFiles[] = $createView;

        $editView = $this->generateFileFromStub(
            $stubPath . '/livewire-view-edit.stub',
            $modulePath . '/Views/livewire/edit.blade.php',
            $this->getReplacements($moduleName)
        );
        if ($editView) $generatedFiles[] = $editView;

        // Generate Livewire Routes
        $webRoutesFile = $this->generateFileFromStub(
            $stubPath . '/routes-livewire.stub',
            $modulePath . '/Routes/web.php',
            $this->getReplacements($moduleName)
        );
        if ($webRoutesFile) $generatedFiles[] = $webRoutesFile;

        return $generatedFiles;
    }

    /**
     * Generate common files (Model, Migration, Service Provider, etc.).
     */
    protected function generateCommonFiles(string $moduleName, string $modulePath): array
    {
        $generatedFiles = [];
        $stubPath = $this->getStubPath();

        // Generate Model
        $modelFile = $this->generateFileFromStub(
            $stubPath . '/model.stub',
            $modulePath . '/Models/' . $moduleName . '.php',
            $this->getReplacements($moduleName)
        );
        if ($modelFile) $generatedFiles[] = $modelFile;

        // Generate Migration
        $migrationFile = $this->generateMigrationFile($moduleName, $modulePath);
        if ($migrationFile) $generatedFiles[] = $migrationFile;

        // Generate Service Provider
        $type = $this->options['type'];
        if ($type === 'full') {
            $serviceProviderStub = 'service-provider-full.stub';
        } elseif ($type === 'livewire') {
            $serviceProviderStub = 'service-provider-livewire.stub';
        } else {
            $serviceProviderStub = 'service-provider-api.stub';
        }

        $serviceProviderFile = $this->generateFileFromStub(
            $stubPath . '/' . $serviceProviderStub,
            $modulePath . '/Providers/' . $moduleName . 'ServiceProvider.php',
            $this->getReplacements($moduleName)
        );
        if ($serviceProviderFile) $generatedFiles[] = $serviceProviderFile;

        // Generate Tests
        if ($this->options['generate_tests']) {
            $testStub = $this->options['type'] === 'livewire' ? 'test-livewire.stub' : 'test-feature.stub';
            $featureTestFile = $this->generateFileFromStub(
                $stubPath . '/' . $testStub,
                $modulePath . '/Tests/Feature/' . $moduleName . 'Test.php',
                $this->getReplacements($moduleName)
            );
            if ($featureTestFile) $generatedFiles[] = $featureTestFile;

            $unitTestFile = $this->generateFileFromStub(
                $stubPath . '/test-unit.stub',
                $modulePath . '/Tests/Unit/' . $moduleName . 'Test.php',
                $this->getReplacements($moduleName)
            );
            if ($unitTestFile) $generatedFiles[] = $unitTestFile;
        }

        // Generate Seeders
        if ($this->options['generate_seeders']) {
            $seederFile = $this->generateFileFromStub(
                $stubPath . '/seeder.stub',
                $modulePath . '/Database/Seeders/' . $moduleName . 'Seeder.php',
                $this->getReplacements($moduleName)
            );
            if ($seederFile) $generatedFiles[] = $seederFile;
        }

        // Generate Factories
        if ($this->options['generate_factories']) {
            $factoryFile = $this->generateFileFromStub(
                $stubPath . '/factory.stub',
                $modulePath . '/Database/Factories/' . $moduleName . 'Factory.php',
                $this->getReplacements($moduleName)
            );
            if ($factoryFile) $generatedFiles[] = $factoryFile;
        }

        return $generatedFiles;
    }

    /**
     * Generate service provider only.
     */
    protected function generateServiceProvider(string $moduleName, string $modulePath, string $type): ?string
    {
        $stubPath = $this->getStubPath();

        if ($type === 'full') {
            $serviceProviderStub = 'service-provider-full.stub';
        } elseif ($type === 'livewire') {
            $serviceProviderStub = 'service-provider-livewire.stub';
        } else {
            $serviceProviderStub = 'service-provider-api.stub';
        }

        return $this->generateFileFromStub(
            $stubPath . '/' . $serviceProviderStub,
            $modulePath . '/Providers/' . $moduleName . 'ServiceProvider.php',
            $this->getReplacements($moduleName)
        );
    }

    /**
     * Generate file from stub.
     */
    protected function generateFileFromStub(string $stubPath, string $targetPath, array $replacements): ?string
    {
        if (!File::exists($stubPath)) {
            \Log::warning("Stub file not found: {$stubPath}");
            return null;
        }

        if (!$this->options['force'] && File::exists($targetPath)) {
            \Log::info("Target file already exists (skipping): {$targetPath}");
            return null;
        }

        try {
            $content = File::get($stubPath);
            $content = str_replace(array_keys($replacements), array_values($replacements), $content);

            // Ensure directory exists
            $directory = dirname($targetPath);
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            File::put($targetPath, $content);
            \Log::info("Generated file: {$targetPath}");

            return $targetPath;
        } catch (\Exception $e) {
            \Log::error("Failed to generate file {$targetPath}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate migration file.
     */
    protected function generateMigrationFile(string $moduleName, string $modulePath): ?string
    {
        $timestamp = date('Y_m_d_His');
        $migrationName = "create_{$this->getSnakeCase($moduleName)}_table";
        $fileName = "{$timestamp}_{$migrationName}.php";
        $filePath = $modulePath . '/Database/Migrations/' . $fileName;

        if (!$this->options['force'] && File::exists($filePath)) {
            return null;
        }

        $stubPath = $this->getStubPath() . '/migration.stub';
        if (!File::exists($stubPath)) {
            return null;
        }

        $content = File::get($stubPath);
        $replacements = $this->getReplacements($moduleName);
        $replacements['{{migrationClass}}'] = 'Create' . $moduleName . 'Table';
        $replacements['{{tableName}}'] = $this->getSnakeCase($moduleName);

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        File::put($filePath, $content);

        return $filePath;
    }

    /**
     * Get stub path.
     */
    protected function getStubPath(): string
    {
        $publishedStubsPath = resource_path('stubs/module-maker');

        if (File::exists($publishedStubsPath)) {
            return $publishedStubsPath;
        }

        return __DIR__ . '/../../stubs';
    }

    /**
     * Get replacements for stub files.
     */
    protected function getReplacements(string $moduleName): array
    {
        $namespace = config('module-maker.namespace', 'Modules');
        $moduleLower = strtolower($moduleName);
        $moduleSnake = $this->getSnakeCase($moduleName);
        $modulePlural = Str::plural($moduleName);
        $modulePluralLower = strtolower($modulePlural);

        return [
            '{{module}}' => $moduleName,
            '{{moduleLower}}' => $moduleLower,
            '{{moduleSnake}}' => $moduleSnake,
            '{{modulePlural}}' => $modulePlural,
            '{{modulePluralLower}}' => $modulePluralLower,
            '{{namespace}}' => $namespace,
            '{{moduleNamespace}}' => $namespace . '\\' . $moduleName,
        ];
    }

    /**
     * Convert string to snake_case.
     */
    protected function getSnakeCase(string $string): string
    {
        return Str::snake($string);
    }

    /**
     * Register module routes.
     */
    protected function registerRoutes(string $moduleName): void
    {
        $routeRegistration = config('module-maker.route_registration', 'both');
        $modulePath = $this->getModulePath($moduleName);

        // Register web routes
        if (in_array($routeRegistration, ['web', 'both'])) {
            $webRoutesPath = $modulePath . '/Routes/web.php';
            if (File::exists($webRoutesPath)) {
                $this->addRouteInclude('web', $webRoutesPath, $moduleName);
            }
        }

        // Register API routes
        if (in_array($routeRegistration, ['api', 'both'])) {
            $apiRoutesPath = $modulePath . '/Routes/api.php';
            if (File::exists($apiRoutesPath)) {
                $this->addRouteInclude('api', $apiRoutesPath, $moduleName);
            }
        }
    }

    /**
     * Add route include to main route files.
     */
    protected function addRouteInclude(string $type, string $routePath, string $moduleName): void
    {
        $mainRouteFile = base_path("routes/{$type}.php");
        $relativePath = $this->getRelativePath($mainRouteFile, $routePath);

        $includeStatement = "\n// Module: {$moduleName}\nrequire_once __DIR__ . '/{$relativePath}';\n";

        if (File::exists($mainRouteFile)) {
            $content = File::get($mainRouteFile);

            // Check if include already exists
            if (strpos($content, "Module: {$moduleName}") === false) {
                File::append($mainRouteFile, $includeStatement);
            }
        }
    }

    /**
     * Get relative path between two files.
     */
    protected function getRelativePath(string $from, string $to): string
    {
        $from = dirname($from);
        $to = dirname($to);

        // Get the relative path from routes directory to module directory
        $relativePath = str_replace(base_path() . '/', '', $to);

        return '../' . $relativePath . '/web.php';
    }

    /**
     * Ensure Livewire layout exists.
     */
    protected function ensureLivewireLayout(): void
    {
        $layoutPath = resource_path('views/components/layouts/app.blade.php');

        // If layout already exists, don't overwrite it
        if (File::exists($layoutPath)) {
            return;
        }

        // Create the directory if it doesn't exist
        $layoutDir = dirname($layoutPath);
        if (!File::exists($layoutDir)) {
            File::makeDirectory($layoutDir, 0755, true);
        }

        // Copy layout from stub
        $stubPath = $this->getStubPath() . '/layout-app.stub';
        if (File::exists($stubPath)) {
            File::copy($stubPath, $layoutPath);
            \Log::info("Created Livewire layout: {$layoutPath}");
        }
    }
}
