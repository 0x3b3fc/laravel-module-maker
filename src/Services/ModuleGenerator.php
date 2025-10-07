<?php

namespace PhpSamurai\LaravelModuleMaker\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleGenerator
{
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

            // Register routes if auto_register_routes is enabled
            if (config('module-maker.auto_register_routes', true)) {
                $this->registerRoutes($moduleName);
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

        return [
            $basePath . '/Controllers',
            $basePath . '/Models',
            $basePath . '/Views',
            $basePath . '/Routes',
            $basePath . '/Database/Migrations',
            $basePath . '/Database/Seeders',
            $basePath . '/Database/Factories',
            $basePath . '/Http/Middleware',
            $basePath . '/Http/Requests',
            $basePath . '/Providers',
            $basePath . '/Tests/Feature',
            $basePath . '/Tests/Unit',
            $basePath . '/Config',
        ];
    }

    /**
     * Generate files from stubs.
     */
    protected function generateFilesFromStubs(string $moduleName, string $modulePath): array
    {
        $generatedFiles = [];
        $stubPath = $this->getStubPath();

        // Generate Controller
        $controllerFile = $this->generateFileFromStub(
            $stubPath . '/controller.stub',
            $modulePath . '/Controllers/' . $moduleName . 'Controller.php',
            $this->getReplacements($moduleName)
        );
        if ($controllerFile) $generatedFiles[] = $controllerFile;

        // Generate Model
        $modelFile = $this->generateFileFromStub(
            $stubPath . '/model.stub',
            $modulePath . '/Models/' . $moduleName . '.php',
            $this->getReplacements($moduleName)
        );
        if ($modelFile) $generatedFiles[] = $modelFile;

        // Generate Routes
        $webRoutesFile = $this->generateFileFromStub(
            $stubPath . '/routes-web.stub',
            $modulePath . '/Routes/web.php',
            $this->getReplacements($moduleName)
        );
        if ($webRoutesFile) $generatedFiles[] = $webRoutesFile;

        $apiRoutesFile = $this->generateFileFromStub(
            $stubPath . '/routes-api.stub',
            $modulePath . '/Routes/api.php',
            $this->getReplacements($moduleName)
        );
        if ($apiRoutesFile) $generatedFiles[] = $apiRoutesFile;

        // Generate Migration
        $migrationFile = $this->generateMigrationFile($moduleName, $modulePath);
        if ($migrationFile) $generatedFiles[] = $migrationFile;

        // Generate Views
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

        // Generate Service Provider
        $serviceProviderFile = $this->generateFileFromStub(
            $stubPath . '/service-provider.stub',
            $modulePath . '/Providers/' . $moduleName . 'ServiceProvider.php',
            $this->getReplacements($moduleName)
        );
        if ($serviceProviderFile) $generatedFiles[] = $serviceProviderFile;

        // Generate Tests
        if ($this->options['generate_tests']) {
            $featureTestFile = $this->generateFileFromStub(
                $stubPath . '/test-feature.stub',
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
     * Generate file from stub.
     */
    protected function generateFileFromStub(string $stubPath, string $targetPath, array $replacements): ?string
    {
        if (!File::exists($stubPath)) {
            return null;
        }

        if (!$this->options['force'] && File::exists($targetPath)) {
            return null;
        }

        $content = File::get($stubPath);
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        File::put($targetPath, $content);

        return $targetPath;
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
}
