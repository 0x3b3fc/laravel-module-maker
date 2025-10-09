<?php

namespace PhpSamurai\LaravelModuleMaker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PhpSamurai\LaravelModuleMaker\Services\ModuleGenerator;

class MakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name : The name of the module}
                            {--type= : Module type (api or livewire)}
                            {--force : Force overwrite existing files}
                            {--no-tests : Skip generating test files}
                            {--no-seeders : Skip generating seeder files}
                            {--no-factories : Skip generating factory files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new modular HMVC structure (API, Livewire, or Full-Stack)';

    /**
     * The module generator service.
     *
     * @var ModuleGenerator
     */
    protected ModuleGenerator $generator;

    /**
     * Create a new command instance.
     */
    public function __construct(ModuleGenerator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $moduleName = $this->argument('name');
        $force = $this->option('force');

        // Validate module name
        if (!$this->isValidModuleName($moduleName)) {
            $this->error('Module name must contain only letters, numbers, and underscores.');
            return Command::FAILURE;
        }

        // Check if module already exists
        if (!$force && $this->generator->moduleExists($moduleName)) {
            $this->error("Module '{$moduleName}' already exists. Use --force to overwrite.");
            return Command::FAILURE;
        }

        // Ask for module type if not provided
        $moduleType = $this->option('type');
        if (!$moduleType) {
            $moduleType = $this->choice(
                'What type of module would you like to generate?',
                ['full', 'api', 'livewire'],
                0
            );
        }

        // Validate module type
        if (!in_array($moduleType, ['api', 'livewire', 'full'])) {
            $this->error('Module type must be "api", "livewire", or "full".');
            return Command::FAILURE;
        }

        $typeDescription = match($moduleType) {
            'full' => 'Full-Stack (API + Livewire)',
            'api' => 'API (Backend)',
            'livewire' => 'Livewire (UI Only)',
            default => $moduleType
        };

        $this->info("Generating {$typeDescription} module: {$moduleName}");

        try {
            $this->generator->setOptions([
                'force' => $force,
                'type' => $moduleType,
                'generate_tests' => !$this->option('no-tests'),
                'generate_seeders' => !$this->option('no-seeders'),
                'generate_factories' => !$this->option('no-factories'),
            ]);

            $result = $this->generator->generate($moduleName);

            if ($result['success']) {
                $this->info('Module generated successfully!');
                $this->displayGeneratedFiles($result['files']);

                // Auto-register everything
                $this->info('');
                $this->info('🔧 Setting up module automatically...');

                $this->registerServiceProvider($moduleName);
                $this->updateComposerAutoload();
                $this->runComposerDumpAutoload();

                if (config('module-maker.auto_register_routes')) {
                    $this->info('✅ Routes have been automatically registered.');
                }

                $this->info('✅ Service provider registered automatically.');
                $this->info('✅ Composer autoload updated automatically.');

                if (in_array($moduleType, ['livewire', 'full'])) {
                    $this->info('✅ Navigation link added automatically.');
                }

                $this->info('');
                $this->info('🎉 Module is ready to use!');

                $this->displayNextSteps($moduleName, $moduleType);
                return Command::SUCCESS;
            } else {
                $this->error('Failed to generate module: ' . $result['error']);
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
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
     * Register service provider in bootstrap/providers.php
     */
    private function registerServiceProvider(string $moduleName): void
    {
        $namespace = config('module-maker.namespace', 'Modules');
        $providerClass = "{$namespace}\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider::class";

        $providersFile = base_path('bootstrap/providers.php');

        if (!File::exists($providersFile)) {
            $this->warn('bootstrap/providers.php not found. Creating it...');
            $this->createProvidersFile();
        }

        $content = File::get($providersFile);

        // Check if already registered
        if (strpos($content, $providerClass) !== false) {
            $this->comment('Service provider already registered.');
            return;
        }

        // Add the provider before the closing bracket
        $pattern = '/(\s*)(return\s*\[)/';
        if (preg_match($pattern, $content)) {
            // Find the last item in the array and add after it
            $lines = explode("\n", $content);
            $insertIndex = -1;
            $bracketCount = 0;

            foreach ($lines as $index => $line) {
                if (strpos($line, 'return [') !== false) {
                    $bracketCount = 1;
                } elseif ($bracketCount > 0) {
                    if (strpos($line, '];') !== false) {
                        $insertIndex = $index;
                        break;
                    }
                }
            }

            if ($insertIndex > 0) {
                // Add comment if not exists
                $comment = "    // Module Service Providers";
                $hasComment = false;
                foreach ($lines as $line) {
                    if (strpos($line, 'Module Service Providers') !== false) {
                        $hasComment = true;
                        break;
                    }
                }

                if (!$hasComment) {
                    array_splice($lines, $insertIndex, 0, [$comment]);
                    $insertIndex++;
                }

                array_splice($lines, $insertIndex, 0, ["    {$providerClass},"]);
                $content = implode("\n", $lines);
                File::put($providersFile, $content);
            }
        }
    }

    /**
     * Create bootstrap/providers.php if it doesn't exist
     */
    private function createProvidersFile(): void
    {
        $content = <<<'PHP'
<?php

return [
    App\Providers\AppServiceProvider::class,
];

PHP;

        File::put(base_path('bootstrap/providers.php'), $content);
    }

    /**
     * Update composer.json with Modules namespace
     */
    private function updateComposerAutoload(): void
    {
        $composerFile = base_path('composer.json');

        if (!File::exists($composerFile)) {
            $this->error('composer.json not found!');
            return;
        }

        $composer = json_decode(File::get($composerFile), true);
        $namespace = config('module-maker.namespace', 'Modules');
        $path = config('module-maker.path', 'modules');

        // Check if already exists
        if (isset($composer['autoload']['psr-4'][$namespace . '\\'])) {
            $this->comment('Composer autoload already configured.');
            return;
        }

        // Add the namespace
        if (!isset($composer['autoload'])) {
            $composer['autoload'] = [];
        }

        if (!isset($composer['autoload']['psr-4'])) {
            $composer['autoload']['psr-4'] = [];
        }

        $composer['autoload']['psr-4'][$namespace . '\\'] = $path . '/';

        // Write back with pretty print
        File::put($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }

    /**
     * Run composer dump-autoload
     */
    private function runComposerDumpAutoload(): void
    {
        $this->comment('Running composer dump-autoload...');

        $composerPath = $this->findComposer();

        exec("{$composerPath} dump-autoload -q", $output, $returnCode);

        if ($returnCode !== 0) {
            $this->warn('Could not run composer dump-autoload automatically. Please run it manually.');
        }
    }

    /**
     * Find the composer binary
     */
    private function findComposer(): string
    {
        $composerPath = getcwd() . '/composer.phar';

        if (file_exists($composerPath)) {
            return '"' . PHP_BINARY . '" ' . $composerPath;
        }

        return 'composer';
    }

    /**
     * Display generated files.
     */
    private function displayGeneratedFiles(array $files): void
    {
        $this->line('');
        $this->info('Generated files:');
        foreach ($files as $file) {
            $this->line("  • {$file}");
        }
    }

    /**
     * Display next steps for the user.
     */
    private function displayNextSteps(string $moduleName, string $moduleType): void
    {
        $this->line('');
        $this->info('📋 Next steps:');

        if ($moduleType === 'full') {
            $this->line("  1. Ensure Livewire is installed: composer require livewire/livewire");
            $this->line("  2. Add @livewireStyles and @livewireScripts to your layout");
            $this->line("  3. Run migrations: php artisan migrate");
            $this->line("  4. Web UI: http://your-app.test/" . strtolower(\Illuminate\Support\Str::plural($moduleName)));
            $this->line("  5. API: http://your-app.test/api/" . strtolower(\Illuminate\Support\Str::plural($moduleName)));
            $this->line('');
            $this->comment('💡 Your Full-Stack module is ready! API + Livewire with all backend and frontend.');
        } elseif ($moduleType === 'livewire') {
            $this->line("  1. Ensure Livewire is installed: composer require livewire/livewire");
            $this->line("  2. Add @livewireStyles and @livewireScripts to your layout");
            $this->line("  3. Connect to existing API/backend for data");
            $this->line("  4. Visit: http://your-app.test/" . strtolower(\Illuminate\Support\Str::plural($moduleName)));
            $this->line('');
            $this->comment('💡 Your Livewire UI module is ready! (UI components only, no backend)');
        } else {
            $this->line("  1. Run migrations: php artisan migrate");
            $this->line("  2. Test API: http://your-app.test/api/" . strtolower(\Illuminate\Support\Str::plural($moduleName)));
            $this->line('');
            $this->comment('💡 Your API module is ready! Backend with models, migrations, and API routes.');
        }

        $this->line('');
        $this->comment('Customize the generated files in modules/' . $moduleName . '/');
        $this->comment('Tip: Publish stub templates with: php artisan vendor:publish --tag=module-maker-stubs');
    }
}
