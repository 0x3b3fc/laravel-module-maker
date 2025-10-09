<?php

namespace PhpSamurai\LaravelModuleMaker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ListModulesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list:modules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all available modules';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $modulesPath = base_path(config('module-maker.path', 'modules'));

        if (!File::exists($modulesPath)) {
            $this->info('No modules directory found.');
            $this->comment("💡 Create your first module with: php artisan make:module YourModule");
            return Command::SUCCESS;
        }

        $modules = $this->getModules($modulesPath);

        if (empty($modules)) {
            $this->info('No modules found.');
            $this->comment("💡 Create your first module with: php artisan make:module YourModule");
            return Command::SUCCESS;
        }

        $this->info("Found " . count($modules) . " module(s):");
        $this->line('');

        $tableData = [];
        foreach ($modules as $module) {
            $tableData[] = [
                $module['name'],
                $module['type'],
                $module['routes'],
                $module['size'],
                $module['files'],
            ];
        }

        $this->table(
            ['Module', 'Type', 'Routes', 'Size', 'Files'],
            $tableData
        );

        $this->line('');
        $this->comment('💡 Tips:');
        $this->comment('   • View routes: php artisan route:list --name=modulename');
        $this->comment('   • Delete module: php artisan delete:module ModuleName');

        return Command::SUCCESS;
    }

    /**
     * Get all modules.
     */
    private function getModules(string $modulesPath): array
    {
        $modules = [];
        $directories = File::directories($modulesPath);

        foreach ($directories as $directory) {
            $moduleName = basename($directory);
            $modules[] = $this->getModuleInfo($directory, $moduleName);
        }

        return $modules;
    }

    /**
     * Get module information.
     */
    private function getModuleInfo(string $path, string $name): array
    {
        $type = $this->detectModuleType($path);
        $routes = $this->countRoutes($path);
        $size = $this->getDirectorySize($path);
        $files = $this->countFiles($path);

        return [
            'name' => $name,
            'type' => $type,
            'routes' => $routes,
            'size' => $this->formatBytes($size),
            'files' => $files,
        ];
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
    private function countRoutes(string $path): string
    {
        $webRoutes = 0;
        $apiRoutes = 0;

        $webRoutesFile = $path . '/Routes/web.php';
        if (File::exists($webRoutesFile)) {
            $content = File::get($webRoutesFile);
            $webRoutes = substr_count($content, 'Route::');
        }

        $apiRoutesFile = $path . '/Routes/api.php';
        if (File::exists($apiRoutesFile)) {
            $content = File::get($apiRoutesFile);
            $apiRoutes = substr_count($content, 'Route::');
        }

        $total = $webRoutes + $apiRoutes;

        if ($total === 0) {
            return '0';
        }

        return "{$total} ({$webRoutes}W/{$apiRoutes}A)";
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
     * Count files in directory.
     */
    private function countFiles(string $path): int
    {
        return count(File::allFiles($path));
    }

    /**
     * Format bytes to human readable format.
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
}
