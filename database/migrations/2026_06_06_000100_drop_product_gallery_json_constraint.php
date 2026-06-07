<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'gallery')) {
            return;
        }

        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        foreach ([
            'ALTER TABLE `products` DROP CONSTRAINT `products.gallery`',
            'ALTER TABLE `products` DROP CHECK `products.gallery`',
        ] as $statement) {
            try {
                DB::statement($statement);
            } catch (\Throwable) {
                // The exact drop syntax differs between MySQL and MariaDB.
            }
        }

        DB::statement('ALTER TABLE `products` MODIFY `gallery` TEXT NULL');
    }

    public function down(): void
    {
        // Do not restore the JSON check constraint; gallery is stored as a pipe-separated string.
    }
};
