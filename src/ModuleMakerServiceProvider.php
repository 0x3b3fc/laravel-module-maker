<?php

namespace PhpSamurai\LaravelModuleMaker;

use Illuminate\Support\ServiceProvider;
use PhpSamurai\LaravelModuleMaker\Commands\MakeModuleCommand;
use PhpSamurai\LaravelModuleMaker\Commands\DeleteModuleCommand;
use PhpSamurai\LaravelModuleMaker\Commands\ListModulesCommand;
use PhpSamurai\LaravelModuleMaker\Commands\ModuleHealthCommand;
use PhpSamurai\LaravelModuleMaker\Commands\ModuleDashboardCommand;
use PhpSamurai\LaravelModuleMaker\Commands\MakeModuleWithRelationsCommand;

class ModuleMakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/module-maker.php', 'module-maker'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModuleCommand::class,
                DeleteModuleCommand::class,
                ListModulesCommand::class,
                ModuleHealthCommand::class,
                ModuleDashboardCommand::class,
                MakeModuleWithRelationsCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/module-maker.php' => config_path('module-maker.php'),
            ], 'module-maker-config');

            $this->publishes([
                __DIR__.'/../stubs' => resource_path('stubs/module-maker'),
            ], 'module-maker-stubs');
        }
    }
}
