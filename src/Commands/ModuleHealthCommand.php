<?php

namespace PhpSamurai\LaravelModuleMaker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:health {name? : The name of the module to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the health status of modules';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $moduleName = $this->argument('name');

        if ($moduleName) {
            return $this->checkSingleModule($moduleName);
        }

        return $this->checkAllModules();
    }

    /**
     * Check single module health.
     */
    private function checkSingleModule(string $moduleName): int
    {
        $modulePath = $this->getModulePath($moduleName);

        if (!File::exists($modulePath)) {
            $this->error("Module '{$moduleName}' does not exist.");
            return Command::FAILURE;
        }

        $this->info("🏥 Health Check: {$moduleName}");
        $this->line('');

        $health = $this->analyzeModule($modulePath, $moduleName);
        $this->displayHealthReport($moduleName, $health);

        return $health['score'] === 100 ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Check all modules health.
     */
    private function checkAllModules(): int
    {
        $modulesPath = base_path(config('module-maker.path', 'modules'));

        if (!File::exists($modulesPath)) {
            $this->info('No modules found.');
            return Command::SUCCESS;
        }

        $directories = File::directories($modulesPath);

        if (empty($directories)) {
            $this->info('No modules found.');
            return Command::SUCCESS;
        }

        $this->info("🏥 Health Check: All Modules");
        $this->line('');

        $tableData = [];
        $totalScore = 0;

        foreach ($directories as $directory) {
            $moduleName = basename($directory);
            $health = $this->analyzeModule($directory, $moduleName);

            $status = $health['score'] >= 90 ? '✅' : ($health['score'] >= 70 ? '⚠️' : '❌');

            $tableData[] = [
                $moduleName,
                $status,
                $health['score'] . '%',
                $health['issues'],
                $health['type'],
            ];

            $totalScore += $health['score'];
        }

        $this->table(
            ['Module', 'Status', 'Score', 'Issues', 'Type'],
            $tableData
        );

        $avgScore = round($totalScore / count($directories));
        $this->line('');
        $this->info("Average Health Score: {$avgScore}%");

        if ($avgScore >= 90) {
            $this->info('🎉 All modules are healthy!');
        } elseif ($avgScore >= 70) {
            $this->warn('⚠️  Some modules need attention.');
        } else {
            $this->error('❌ Critical issues found in modules.');
        }

        return Command::SUCCESS;
    }

    /**
     * Analyze module health.
     */
    private function analyzeModule(string $path, string $moduleName): array
    {
        $checks = [
            'has_controller' => $this->hasControllers($path),
            'has_model' => $this->hasModels($path),
            'has_routes' => $this->hasRoutes($path),
            'has_views' => $this->hasViews($path),
            'has_migration' => $this->hasMigrations($path),
            'has_tests' => $this->hasTests($path),
            'has_service_provider' => $this->hasServiceProvider($path, $moduleName),
            'service_provider_registered' => $this->isServiceProviderRegistered($moduleName),
            'routes_registered' => $this->areRoutesRegistered($moduleName),
            'namespace_configured' => $this->isNamespaceConfigured(),
        ];

        $passed = count(array_filter($checks));
        $total = count($checks);
        $score = round(($passed / $total) * 100);

        $issues = [];
        foreach ($checks as $check => $result) {
            if (!$result) {
                $issues[] = str_replace('_', ' ', ucfirst($check));
            }
        }

        return [
            'score' => $score,
            'passed' => $passed,
            'total' => $total,
            'issues' => count($issues),
            'issue_list' => $issues,
            'type' => $this->detectModuleType($path),
        ];
    }

    /**
     * Display health report for a module.
     */
    private function displayHealthReport(string $moduleName, array $health): void
    {
        $score = $health['score'];

        if ($score >= 90) {
            $this->info("✅ Health Score: {$score}% - Excellent!");
        } elseif ($score >= 70) {
            $this->warn("⚠️  Health Score: {$score}% - Needs Attention");
        } else {
            $this->error("❌ Health Score: {$score}% - Critical Issues");
        }

        $this->line('');
        $this->line("Checks Passed: {$health['passed']}/{$health['total']}");
        $this->line("Module Type: {$health['type']}");

        if (!empty($health['issue_list'])) {
            $this->line('');
            $this->warn('Issues Found:');
            foreach ($health['issue_list'] as $issue) {
                $this->line("  • {$issue}");
            }
        }

        $this->line('');
    }

    /**
     * Check if module has controllers.
     */
    private function hasControllers(string $path): bool
    {
        $controllerPath = $path . '/Controllers';
        return File::exists($controllerPath) && count(File::files($controllerPath)) > 0;
    }

    /**
     * Check if module has models.
     */
    private function hasModels(string $path): bool
    {
        $modelPath = $path . '/Models';
        return File::exists($modelPath) && count(File::files($modelPath)) > 0;
    }

    /**
     * Check if module has routes.
     */
    private function hasRoutes(string $path): bool
    {
        $routePath = $path . '/Routes';
        return File::exists($routePath) && count(File::files($routePath)) > 0;
    }

    /**
     * Check if module has views.
     */
    private function hasViews(string $path): bool
    {
        $viewPath = $path . '/Views';
        return File::exists($viewPath) && !empty(File::allFiles($viewPath));
    }

    /**
     * Check if module has migrations.
     */
    private function hasMigrations(string $path): bool
    {
        $migrationPath = $path . '/Database/Migrations';
        return File::exists($migrationPath) && count(File::files($migrationPath)) > 0;
    }

    /**
     * Check if module has tests.
     */
    private function hasTests(string $path): bool
    {
        $testPath = $path . '/Tests';
        return File::exists($testPath) && !empty(File::allFiles($testPath));
    }

    /**
     * Check if module has service provider.
     */
    private function hasServiceProvider(string $path, string $moduleName): bool
    {
        $providerPath = $path . '/Providers/' . $moduleName . 'ServiceProvider.php';
        return File::exists($providerPath);
    }

    /**
     * Check if service provider is registered.
     */
    private function isServiceProviderRegistered(string $moduleName): bool
    {
        $providersFile = base_path('bootstrap/providers.php');

        if (!File::exists($providersFile)) {
            return false;
        }

        $content = File::get($providersFile);
        $namespace = config('module-maker.namespace', 'Modules');
        $providerClass = "{$namespace}\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider";

        return strpos($content, $providerClass) !== false;
    }

    /**
     * Check if routes are registered.
     */
    private function areRoutesRegistered(string $moduleName): bool
    {
        $webRoutes = base_path('routes/web.php');

        if (!File::exists($webRoutes)) {
            return false;
        }

        $content = File::get($webRoutes);
        return strpos($content, "Module: {$moduleName}") !== false;
    }

    /**
     * Check if namespace is configured in composer.json.
     */
    private function isNamespaceConfigured(): bool
    {
        $composerFile = base_path('composer.json');

        if (!File::exists($composerFile)) {
            return false;
        }

        $composer = json_decode(File::get($composerFile), true);
        $namespace = config('module-maker.namespace', 'Modules');

        return isset($composer['autoload']['psr-4'][$namespace . '\\']);
    }

    /**
     * Detect module type.
     */
    private function detectModuleType(string $path): string
    {
        $hasLivewire = File::exists($path . '/Livewire');
        $hasControllers = File::exists($path . '/Controllers');
        $hasModels = File::exists($path . '/Models');

        if ($hasLivewire && $hasControllers && $hasModels) {
            return 'Full-Stack';
        } elseif ($hasLivewire && !$hasControllers) {
            return 'Livewire';
        } elseif ($hasControllers && $hasModels) {
            return 'API';
        }

        return 'Unknown';
    }

    /**
     * Get module path.
     */
    private function getModulePath(string $moduleName): string
    {
        return base_path(config('module-maker.path', 'modules') . '/' . $moduleName);
    }
}
