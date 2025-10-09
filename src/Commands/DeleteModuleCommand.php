<?php

namespace PhpSamurai\LaravelModuleMaker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PhpSamurai\LaravelModuleMaker\Services\NavigationManager;

class DeleteModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:module {name : The name of the module to delete}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a module and clean up all related files and registrations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $moduleName = $this->argument('name');
        $force = $this->option('force');

        // Validate module name
        if (!$this->isValidModuleName($moduleName)) {
            $this->error('Invalid module name. Module name must contain only letters, numbers, and underscores.');
            return Command::FAILURE;
        }

        // Check if module exists
        $modulePath = $this->getModulePath($moduleName);
        if (!File::exists($modulePath)) {
            $this->error("Module '{$moduleName}' does not exist.");
            return Command::FAILURE;
        }

        // Show what will be deleted
        $this->info("The following will be deleted:");
        $this->line("  • Module directory: {$modulePath}");
        $this->line("  • Service provider registration");
        $this->line("  • Route registrations");
        $this->line('');

        $this->warn("⚠️  Warning: This action cannot be undone!");
        $this->line('');

        // Confirm deletion
        if (!$force) {
            if (!$this->confirm("Are you sure you want to delete the '{$moduleName}' module?", false)) {
                $this->info('Module deletion cancelled.');
                return Command::SUCCESS;
            }

            // Double confirmation for safety
            if (!$this->confirm("This will permanently delete all files. Are you absolutely sure?", false)) {
                $this->info('Module deletion cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info("Deleting module: {$moduleName}");

        try {
            // 1. Remove service provider registration
            $this->removeServiceProvider($moduleName);

            // 2. Remove route registrations
            $this->removeRouteRegistrations($moduleName);

            // 3. Remove navigation link
            $this->removeNavigationLink($moduleName);

            // 4. Delete module directory
            $this->deleteModuleDirectory($modulePath);

            // 4. Run composer dump-autoload
            $this->runComposerDumpAutoload();

            // 5. Clear caches
            $this->clearCaches();

            $this->info('');
            $this->info("✅ Module '{$moduleName}' has been successfully deleted!");
            $this->info('');
            $this->comment('💡 Tip: If you had migrations for this module, you may want to:');
            $this->comment('   1. Rollback the migrations first (if data needs to be preserved)');
            $this->comment('   2. Or manually drop the database tables');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to delete module: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Validate module name.
     */
    private function isValidModuleName(string $name): bool
    {
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $name);
    }

    /**
     * Get module path.
     */
    private function getModulePath(string $moduleName): string
    {
        return base_path(config('module-maker.path', 'modules') . '/' . $moduleName);
    }

    /**
     * Remove service provider registration.
     */
    private function removeServiceProvider(string $moduleName): void
    {
        $providersFile = base_path('bootstrap/providers.php');

        if (!File::exists($providersFile)) {
            $this->warn('bootstrap/providers.php not found. Skipping service provider removal.');
            return;
        }

        $content = File::get($providersFile);
        $namespace = config('module-maker.namespace', 'Modules');
        $providerClass = "{$namespace}\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider::class";

        // Remove the service provider line
        $lines = explode("\n", $content);
        $filteredLines = array_filter($lines, function($line) use ($providerClass) {
            return strpos($line, $providerClass) === false;
        });

        // Remove empty lines that might be left
        $newContent = implode("\n", $filteredLines);

        // Clean up multiple empty lines
        $newContent = preg_replace("/\n{3,}/", "\n\n", $newContent);

        File::put($providersFile, $newContent);
        $this->line("  ✓ Removed service provider registration");
    }

    /**
     * Remove route registrations.
     */
    private function removeRouteRegistrations(string $moduleName): void
    {
        $routeFiles = ['web.php', 'api.php'];

        foreach ($routeFiles as $routeFile) {
            $routePath = base_path("routes/{$routeFile}");

            if (!File::exists($routePath)) {
                continue;
            }

            $content = File::get($routePath);

            // Remove the module route include and its comment
            $pattern = "/\n\/\/ Module: {$moduleName}\nrequire_once[^\n]+;\n?/";
            $content = preg_replace($pattern, "\n", $content);

            // Clean up multiple empty lines
            $content = preg_replace("/\n{3,}/", "\n\n", $content);

            File::put($routePath, $content);
        }

        $this->line("  ✓ Removed route registrations");
    }

    /**
     * Remove navigation link.
     */
    private function removeNavigationLink(string $moduleName): void
    {
        $navigationManager = new NavigationManager();
        $navigationManager->removeNavigationLink($moduleName);
        $this->line("  ✓ Removed navigation link");
    }

    /**
     * Delete module directory.
     */
    private function deleteModuleDirectory(string $modulePath): void
    {
        if (File::exists($modulePath)) {
            File::deleteDirectory($modulePath);
            $this->line("  ✓ Deleted module directory");
        }
    }

    /**
     * Run composer dump-autoload.
     */
    private function runComposerDumpAutoload(): void
    {
        $this->comment('  ⏳ Running composer dump-autoload...');

        $composerPath = $this->findComposer();
        exec("{$composerPath} dump-autoload -q", $output, $returnCode);

        if ($returnCode === 0) {
            $this->line("  ✓ Composer autoload regenerated");
        } else {
            $this->warn('  ⚠ Could not run composer dump-autoload automatically. Please run it manually.');
        }
    }

    /**
     * Clear Laravel caches.
     */
    private function clearCaches(): void
    {
        $this->comment('  ⏳ Clearing caches...');

        $this->call('optimize:clear');
        $this->line("  ✓ Caches cleared");
    }

    /**
     * Find the composer binary.
     */
    private function findComposer(): string
    {
        $composerPath = getcwd() . '/composer.phar';

        if (file_exists($composerPath)) {
            return '"' . PHP_BINARY . '" ' . $composerPath;
        }

        return 'composer';
    }
}
