<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'stock_deducted')) {
                $table->boolean('stock_deducted')->default(false)->after('total_amount');
            }

            if (! Schema::hasColumn('orders', 'stock_deducted_at')) {
                $table->dateTime('stock_deducted_at')->nullable()->after('stock_deducted');
            }

            if (! Schema::hasColumn('orders', 'shipping_carrier')) {
                $table->string('shipping_carrier', 80)->nullable()->after('stock_deducted_at');
            }

            if (! Schema::hasColumn('orders', 'shipping_code')) {
                $table->string('shipping_code', 80)->nullable()->after('shipping_carrier');
            }

            if (! Schema::hasColumn('orders', 'shipped_at')) {
                $table->dateTime('shipped_at')->nullable()->after('shipping_code');
            }

            if (! Schema::hasColumn('orders', 'delivered_at')) {
                $table->dateTime('delivered_at')->nullable()->after('shipped_at');
            }

            if (! Schema::hasColumn('orders', 'cancelled_at')) {
                $table->dateTime('cancelled_at')->nullable()->after('delivered_at');
            }

            $table->index('delivered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'stock_deducted',
                'stock_deducted_at',
                'shipping_carrier',
                'shipping_code',
                'shipped_at',
                'delivered_at',
                'cancelled_at',
            ];

            $existing = array_values(array_filter($columns, fn (string $column) => Schema::hasColumn('orders', $column)));

            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
