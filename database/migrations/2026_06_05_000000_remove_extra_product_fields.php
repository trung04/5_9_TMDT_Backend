<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            foreach (['short_description', 'origin', 'weight', 'shelf_life', 'certifications'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'short_description')) {
                $table->text('short_description')->nullable()->after('description');
            }

            if (! Schema::hasColumn('products', 'origin')) {
                $table->string('origin', 180)->nullable()->after('image_url');
            }

            if (! Schema::hasColumn('products', 'weight')) {
                $table->string('weight', 80)->nullable()->after('origin');
            }

            if (! Schema::hasColumn('products', 'shelf_life')) {
                $table->string('shelf_life', 120)->nullable()->after('weight');
            }

            if (! Schema::hasColumn('products', 'certifications')) {
                $table->json('certifications')->nullable()->after('shelf_life');
            }

            if (! Schema::hasColumn('products', 'gallery')) {
                $table->text('gallery')->nullable()->after('certifications');
            }
        });
    }
};
