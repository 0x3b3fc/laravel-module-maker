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
                            {--force : Force overwrite existing files}
                            {--no-tests : Skip generating test files}
                            {--no-seeders : Skip generating seeder files}
                            {--no-factories : Skip generating factory files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new modular HMVC structure';

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

        $this->info("Generating module: {$moduleName}");

        try {
            $this->generator->setOptions([
                'force' => $force,
                'generate_tests' => !$this->option('no-tests'),
                'generate_seeders' => !$this->option('no-seeders'),
                'generate_factories' => !$this->option('no-factories'),
            ]);

            $result = $this->generator->generate($moduleName);

            if ($result['success']) {
                $this->info('Module generated successfully!');
                $this->displayGeneratedFiles($result['files']);

                if (config('module-maker.auto_register_routes')) {
                    $this->info('📝 Routes have been automatically registered.');
                }

                $this->displayNextSteps($moduleName);
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
    private function displayNextSteps(string $moduleName): void
    {
        $this->line('');
        $this->info('Next steps:');
        $this->line("  1. Review the generated files in modules/{$moduleName}/");
        $this->line("  2. Customize your routes in modules/{$moduleName}/Routes/");
        $this->line("  3. Update your model in modules/{$moduleName}/Models/");
        $this->line("  4. Run migrations: php artisan migrate");
        $this->line("  5. Create your views in modules/{$moduleName}/Views/");
        $this->line('');
        $this->comment('Tip: You can customize the stub templates by publishing them with:');
        $this->comment('   php artisan vendor:publish --tag=module-maker-stubs');
    }
}
