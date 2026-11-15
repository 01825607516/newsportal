<?php

return [
    // Tenant database server (supervisor will provide these values via .env)
    'connection' => [
        'driver' => env('TENANT_DB_DRIVER', 'mysql'),
        'host' => env('TENANT_DB_HOST', '127.0.0.1'),
        'port' => env('TENANT_DB_PORT', '3306'),
        'username' => env('TENANT_DB_USERNAME', 'root'),
        'password' => env('TENANT_DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
        'prefix' => '',
        'strict' => false,
        'engine' => null,
    ],

    // Each portal gets a database named  <prefix><portal id>, e.g. tenant_12
    'database_prefix' => env('TENANT_DB_PREFIX', 'tenant_'),

    // Schema (and optionally sample data) imported into every new tenant DB
    'schema_file' => database_path('tenant/schema.sql'),
    'import_sample_data' => (bool) env('TENANT_IMPORT_SAMPLE_DATA', false),
];
