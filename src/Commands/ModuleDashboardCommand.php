<?php

namespace PhpSamurai\LaravelModuleMaker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleDashboardCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:dashboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display a comprehensive dashboard of all modules';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->displayHeader();
        $this->displayModuleStats();
        $this->displayModuleList();
        $this->displayQuickActions();

        return Command::SUCCESS;
    }

    /**
     * Display dashboard header.
     */
    private function displayHeader(): void
    {
        $this->line('');
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║                                                              ║');
        $this->line('║           🏗️  LARAVEL MODULE MAKER DASHBOARD 🏗️             ║');
        $this->line('║                                                              ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->line('');
    }

    /**
     * Display module statistics.
     */
    private function displayModuleStats(): void
    {
        $modulesPath = base_path(config('module-maker.path', 'modules'));

        if (!File::exists($modulesPath)) {
            $this->info('📊 No modules found. Create your first module to get started!');
            $this->line('');
            return;
        }

        $modules = File::directories($modulesPath);
        $totalModules = count($modules);

        if ($totalModules === 0) {
            $this->info('📊 No modules found. Create your first module to get started!');
            $this->line('');
            return;
        }

        $stats = $this->calculateStats($modules);

        $this->info('📊 MODULE STATISTICS');
        $this->line('');

        $this->line("  Total Modules:        {$totalModules}");
        $this->line("  Full-Stack Modules:   {$stats['full']}");
        $this->line("  API Modules:          {$stats['api']}");
        $this->line("  Livewire Modules:     {$stats['livewire']}");
        $this->line("  Total Routes:         {$stats['routes']}");
        $this->line("  Total Files:          {$stats['files']}");
        $this->line("  Total Size:           {$this->formatBytes($stats['size'])}");

        $this->line('');
    }

    /**
     * Calculate module statistics.
     */
    private function calculateStats(array $modules): array
    {
        $stats = [
            'full' => 0,
            'api' => 0,
            'livewire' => 0,
            'routes' => 0,
            'files' => 0,
            'size' => 0,
        ];

        foreach ($modules as $modulePath) {
            $type = $this->detectModuleType($modulePath);

            if ($type === 'Full-Stack') {
                $stats['full']++;
            } elseif ($type === 'API') {
                $stats['api']++;
            } elseif ($type === 'Livewire') {
                $stats['livewire']++;
            }

            $stats['routes'] += $this->countRoutes($modulePath);
            $stats['files'] += count(File::allFiles($modulePath));
            $stats['size'] += $this->getDirectorySize($modulePath);
        }

        return $stats;
    }

    /**
     * Display module list.
     */
    private function displayModuleList(): void
    {
        $modulesPath = base_path(config('module-maker.path', 'modules'));

        if (!File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        if (empty($modules)) {
            return;
        }

        $this->info('📦 YOUR MODULES');
        $this->line('');

        $tableData = [];
        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            $type = $this->detectModuleType($modulePath);
            $routes = $this->countRoutes($modulePath);
            $health = $this->getHealthIndicator($modulePath, $moduleName);

            $tableData[] = [
                $moduleName,
                $type,
                $routes,
                $health,
            ];
        }

        $this->table(
            ['Module', 'Type', 'Routes', 'Health'],
            $tableData
        );

        $this->line('');
    }

    /**
     * Display quick actions.
     */
    private function displayQuickActions(): void
    {
        $this->info('⚡ QUICK ACTIONS');
        $this->line('');
        $this->line('  • Create module:    php artisan make:module {name} --type=full');
        $this->line('  • List modules:     php artisan list:modules');
        $this->line('  • Delete module:    php artisan delete:module {name}');
        $this->line('  • Check health:     php artisan module:health {name}');
        $this->line('  • View routes:      php artisan route:list');
        $this->line('');
    }

    /**
     * Get health indicator.
     */
    private function getHealthIndicator(string $path, string $moduleName): string
    {
        $checks = [
            $this->hasControllers($path) || $this->hasLivewire($path),
            $this->hasModels($path) || $this->hasLivewire($path),
            $this->hasRoutes($path),
            $this->hasServiceProvider($path, $moduleName),
            $this->isServiceProviderRegistered($moduleName),
        ];

        $passed = count(array_filter($checks));
        $total = count($checks);
        $percentage = round(($passed / $total) * 100);

        if ($percentage >= 90) {
            return "✅ {$percentage}%";
        } elseif ($percentage >= 70) {
            return "⚠️  {$percentage}%";
        } else {
            return "❌ {$percentage}%";
        }
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
     * Count routes in module.
     */
    private function countRoutes(string $path): int
    {
        $count = 0;

        $webRoutesFile = $path . '/Routes/web.php';
        if (File::exists($webRoutesFile)) {
            $content = File::get($webRoutesFile);
            $count += substr_count($content, 'Route::');
        }

        $apiRoutesFile = $path . '/Routes/api.php';
        if (File::exists($apiRoutesFile)) {
            $content = File::get($apiRoutesFile);
            $count += substr_count($content, 'Route::');
        }

        return $count;
    }

    /**
     * Get directory size.
     */
    private function getDirectorySize(string $path): int
    {
        $size = 0;
        $files = File::allFiles($path);

        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    /**
     * Format bytes.
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return round($bytes / 1048576, 2) . ' MB';
        }
    }

    /**
     * Check if has controllers.
     */
    private function hasControllers(string $path): bool
    {
        $controllerPath = $path . '/Controllers';
        return File::exists($controllerPath) && count(File::files($controllerPath)) > 0;
    }

    /**
     * Check if has Livewire.
     */
    private function hasLivewire(string $path): bool
    {
        $livewirePath = $path . '/Livewire';
        return File::exists($livewirePath) && count(File::files($livewirePath)) > 0;
    }

    /**
     * Check if has models.
     */
    private function hasModels(string $path): bool
    {
        $modelPath = $path . '/Models';
        return File::exists($modelPath) && count(File::files($modelPath)) > 0;
    }

    /**
     * Check if has routes.
     */
    private function hasRoutes(string $path): bool
    {
        $routePath = $path . '/Routes';
        return File::exists($routePath) && count(File::files($routePath)) > 0;
    }

    /**
     * Check if has service provider.
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
     * Get module path.
     */
    private function getModulePath(string $moduleName): string
    {
        return base_path(config('module-maker.path', 'modules') . '/' . $moduleName);
    }
}
