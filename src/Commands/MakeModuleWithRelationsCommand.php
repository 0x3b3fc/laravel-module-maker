<?php

namespace PhpSamurai\LaravelModuleMaker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PhpSamurai\LaravelModuleMaker\Services\ModuleGenerator;

class MakeModuleWithRelationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module-with-relations {name : The name of the module}
                            {--type= : Module type (api, livewire, or full)}
                            {--belongs-to=* : BelongsTo relationships (e.g., User, Category)}
                            {--has-many=* : HasMany relationships (e.g., Comment, Tag)}
                            {--force : Force overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a module with predefined relationships';

    /**
     * The module generator service.
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
        $belongsTo = $this->option('belongs-to');
        $hasMany = $this->option('has-many');

        $this->info("🔗 Generating module with relationships: {$moduleName}");
        $this->line('');

        // Display relationships
        if (!empty($belongsTo)) {
            $this->line('📎 BelongsTo Relationships:');
            foreach ($belongsTo as $relation) {
                $this->line("  • {$relation}");
            }
            $this->line('');
        }

        if (!empty($hasMany)) {
            $this->line('📎 HasMany Relationships:');
            foreach ($hasMany as $relation) {
                $this->line("  • {$relation}");
            }
            $this->line('');
        }

        // First, create the basic module
        $this->call('make:module', [
            'name' => $moduleName,
            '--type' => $this->option('type') ?? 'full',
            '--force' => $this->option('force'),
        ]);

        // Then add relationships to the model
        if (!empty($belongsTo) || !empty($hasMany)) {
            $this->addRelationshipsToModel($moduleName, $belongsTo, $hasMany);
            $this->addRelationshipMigrations($moduleName, $belongsTo);
        }

        $this->line('');
        $this->info('✅ Module with relationships created successfully!');
        $this->line('');
        $this->comment('💡 Next steps:');
        $this->comment('   1. Review the generated relationships in the model');
        $this->comment('   2. Update migration files with foreign keys');
        $this->comment('   3. Run migrations: php artisan migrate');

        return Command::SUCCESS;
    }

    /**
     * Add relationships to model.
     */
    private function addRelationshipsToModel(string $moduleName, array $belongsTo, array $hasMany): void
    {
        $modelPath = base_path(config('module-maker.path', 'modules') . "/{$moduleName}/Models/{$moduleName}.php");

        if (!File::exists($modelPath)) {
            $this->warn("Model file not found: {$modelPath}");
            return;
        }

        $content = File::get($modelPath);

        // Add relationship methods before the closing brace
        $relationships = '';

        foreach ($belongsTo as $relation) {
            $relationMethod = lcfirst($relation);
            $relationships .= "\n    /**\n";
            $relationships .= "     * Get the {$relation} that owns this " . strtolower($moduleName) . ".\n";
            $relationships .= "     */\n";
            $relationships .= "    public function {$relationMethod}()\n";
            $relationships .= "    {\n";
            $relationships .= "        return \$this->belongsTo(\\Modules\\{$relation}\\Models\\{$relation}::class);\n";
            $relationships .= "    }\n";
        }

        foreach ($hasMany as $relation) {
            $relationMethod = lcfirst(\Illuminate\Support\Str::plural($relation));
            $relationships .= "\n    /**\n";
            $relationships .= "     * Get the {$relation}s for this " . strtolower($moduleName) . ".\n";
            $relationships .= "     */\n";
            $relationships .= "    public function {$relationMethod}()\n";
            $relationships .= "    {\n";
            $relationships .= "        return \$this->hasMany(\\Modules\\{$relation}\\Models\\{$relation}::class);\n";
            $relationships .= "    }\n";
        }

        // Insert before the last closing brace
        $content = preg_replace('/}\s*$/', $relationships . "}\n", $content);

        File::put($modelPath, $content);
        $this->line("  ✓ Added relationships to {$moduleName} model");
    }

    /**
     * Add relationship columns to migration.
     */
    private function addRelationshipMigrations(string $moduleName, array $belongsTo): void
    {
        $migrationPath = base_path(config('module-maker.path', 'modules') . "/{$moduleName}/Database/Migrations");

        if (!File::exists($migrationPath)) {
            return;
        }

        $migrations = File::files($migrationPath);

        if (empty($migrations)) {
            return;
        }

        // Get the latest migration file
        $migrationFile = end($migrations)->getPathname();
        $content = File::get($migrationFile);

        // Add foreign key columns before timestamps
        $foreignKeys = '';
        foreach ($belongsTo as $relation) {
            $columnName = strtolower($relation) . '_id';
            $foreignKeys .= "            \$table->foreignId('{$columnName}')->nullable()->constrained('" . \Illuminate\Support\Str::snake(\Illuminate\Support\Str::plural($relation)) . "')->onDelete('cascade');\n";
        }

        // Insert before timestamps
        $content = str_replace('$table->timestamps();', $foreignKeys . '            $table->timestamps();', $content);

        File::put($migrationFile, $content);
        $this->line("  ✓ Added foreign key columns to migration");
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
     * Count routes.
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
     * Check various module components.
     */
    private function hasControllers(string $path): bool
    {
        return File::exists($path . '/Controllers') && count(File::files($path . '/Controllers')) > 0;
    }

    private function hasLivewire(string $path): bool
    {
        return File::exists($path . '/Livewire') && count(File::files($path . '/Livewire')) > 0;
    }

    private function hasModels(string $path): bool
    {
        return File::exists($path . '/Models') && count(File::files($path . '/Models')) > 0;
    }

    private function hasRoutes(string $path): bool
    {
        return File::exists($path . '/Routes') && count(File::files($path . '/Routes')) > 0;
    }

    private function hasServiceProvider(string $path, string $moduleName): bool
    {
        return File::exists($path . '/Providers/' . $moduleName . 'ServiceProvider.php');
    }

    private function isServiceProviderRegistered(string $moduleName): bool
    {
        $providersFile = base_path('bootstrap/providers.php');

        if (!File::exists($providersFile)) {
            return false;
        }

        $content = File::get($providersFile);
        return strpos($content, $moduleName . 'ServiceProvider') !== false;
    }

    /**
     * Get module path.
     */
    private function getModulePath(string $moduleName): string
    {
        return base_path(config('module-maker.path', 'modules') . '/' . $moduleName);
    }
}
