<?php

use App\Support\MySqlScriptRunner;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:import-ecommerce-mysql {--schema=ecommerce_schema_mysql.sql} {--seed=ecommerce_seed_data.sql}', function (): int {
    $schemaPath = $this->option('schema');
    $seedPath = $this->option('seed');

    $schemaPath = str_starts_with($schemaPath, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\/', $schemaPath)
        ? $schemaPath
        : base_path($schemaPath);

    $seedPath = str_starts_with($seedPath, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\/', $seedPath)
        ? $seedPath
        : base_path($seedPath);

    $config = [
        'host' => (string) config('database.connections.mysql.host', '127.0.0.1'),
        'port' => (int) config('database.connections.mysql.port', 3306),
        'username' => (string) config('database.connections.mysql.username', 'root'),
        'password' => (string) config('database.connections.mysql.password', ''),
        'database' => (string) config('database.connections.mysql.database', 'ecommerce_db'),
        'charset' => (string) config('database.connections.mysql.charset', 'utf8mb4'),
    ];

    /** @var MySqlScriptRunner $runner */
    $runner = app(MySqlScriptRunner::class);
    $runner->import($schemaPath, $seedPath, $config);

    $this->components->info("Imported schema and seed into MySQL database [{$config['database']}].");
    $this->line("Schema: {$schemaPath}");
    $this->line("Seed: {$seedPath}");

    return 0;
})->purpose('Create ecommerce_db from the bundled schema and seed SQL files');
