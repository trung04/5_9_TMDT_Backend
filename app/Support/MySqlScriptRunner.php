<?php

namespace App\Support;

use mysqli;
use mysqli_result;
use RuntimeException;

class MySqlScriptRunner
{
    /**
     * Import the ecommerce schema and seed data into the configured MySQL server.
     *
     * @param  array{host:string,port:int,username:string,password:string,database:string,charset:string}  $config
     */
    public function import(string $schemaPath, string $seedPath, array $config): void
    {
        $this->assertReadable($schemaPath);
        $this->assertReadable($seedPath);

        $this->runScript(
            file_get_contents($schemaPath) ?: '',
            $config,
            null,
            'schema'
        );

        $this->runScript(
            file_get_contents($seedPath) ?: '',
            $config,
            $config['database'],
            'seed'
        );
    }

    private function assertReadable(string $path): void
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException("SQL file is not readable: {$path}");
        }
    }

    /**
     * @param  array{host:string,port:int,username:string,password:string,database:string,charset:string}  $config
     */
    private function runScript(string $sql, array $config, ?string $database, string $label): void
    {
        if (trim($sql) === '') {
            throw new RuntimeException("The {$label} SQL file is empty.");
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $connection = new mysqli();
        $connection->real_connect(
            $config['host'],
            $config['username'],
            $config['password'],
            $database,
            $config['port']
        );
        $connection->set_charset($config['charset']);

        try {
            if (! $connection->multi_query($sql)) {
                throw new RuntimeException("Unable to execute the {$label} SQL file.");
            }

            do {
                $result = $connection->store_result();

                if ($result instanceof mysqli_result) {
                    $result->free();
                }
            } while ($connection->more_results() && $connection->next_result());

            if ($connection->errno !== 0) {
                throw new RuntimeException("MySQL import failed while executing {$label}: {$connection->error}");
            }
        } finally {
            $connection->close();
        }
    }
}
