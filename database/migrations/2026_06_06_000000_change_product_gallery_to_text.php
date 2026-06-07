<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'gallery')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->text('gallery')->nullable()->after('image_url');
            });

            return;
        }

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE `products` MODIFY `gallery` TEXT NULL');
        }
    }

    public function down(): void
    {
        // Keep existing string gallery data intact on rollback.
    }
};
