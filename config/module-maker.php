<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Module Path
    |--------------------------------------------------------------------------
    |
    | This value is the path where modules will be generated.
    | The default is 'modules' directory in the project root.
    |
    */
    'path' => 'modules',

    /*
    |--------------------------------------------------------------------------
    | Module Namespace
    |--------------------------------------------------------------------------
    |
    | This value is the namespace prefix for generated modules.
    | The default is 'Modules'.
    |
    */
    'namespace' => 'Modules',

    /*
    |--------------------------------------------------------------------------
    | Default Module Type
    |--------------------------------------------------------------------------
    |
    | This value determines the default type of module to generate.
    | Options: 'api', 'livewire'
    |
    */
    'default_type' => 'api',

    /*
    |--------------------------------------------------------------------------
    | Auto Register Routes
    |--------------------------------------------------------------------------
    |
    | This value determines if module routes should be automatically
    | registered when the module is created.
    |
    */
    'auto_register_routes' => true,

    /*
    |--------------------------------------------------------------------------
    | Route Registration Method
    |--------------------------------------------------------------------------
    |
    | This value determines how module routes should be registered.
    | Options: 'web', 'api', 'both'
    |
    */
    'route_registration' => 'both',

    /*
    |--------------------------------------------------------------------------
    | Generate Tests
    |--------------------------------------------------------------------------
    |
    | This value determines if test files should be generated
    | along with the module.
    |
    */
    'generate_tests' => true,

    /*
    |--------------------------------------------------------------------------
    | Generate Seeders
    |--------------------------------------------------------------------------
    |
    | This value determines if seeder files should be generated
    | along with the module.
    |
    */
    'generate_seeders' => true,

    /*
    |--------------------------------------------------------------------------
    | Generate Factories
    |--------------------------------------------------------------------------
    |
    | This value determines if factory files should be generated
    | along with the module.
    |
    */
    'generate_factories' => true,
];
