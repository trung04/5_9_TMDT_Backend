<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('prices')) {
            Schema::create('prices', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
                $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
                $table->decimal('cost_price', 15, 2);
                $table->dateTime('effective_from');
                $table->dateTime('effective_to')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('product_id');
                $table->index('supplier_id');
            });
        }

        if (! Schema::hasTable('inventories')) {
            Schema::create('inventories', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 120)->unique();
                $table->string('location', 255)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_item_id')->unique()->constrained('order_items')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
                $table->unsignedTinyInteger('rating');
                $table->text('comment')->nullable();
                $table->boolean('is_visible')->default(true);
                $table->timestamps();

                $table->index('user_id');
                $table->index('product_id');
            });
        }

        if (! Schema::hasTable('inventory_items')) {
            Schema::create('inventory_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('inventory_id')->constrained('inventories')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
                $table->unsignedInteger('quantity_on_hand')->default(0);
                $table->unsignedInteger('reorder_level')->default(0);
                $table->unsignedInteger('safety_stock')->default(0);
                $table->dateTime('last_counted_at')->nullable();
                $table->timestamps();

                $table->index('inventory_id');
                $table->index('product_id');
            });
        }

        if (! Schema::hasTable('supply_orders')) {
            Schema::create('supply_orders', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
                $table->string('order_no', 50)->unique();
                $table->string('status', 30)->default('PENDING');
                $table->date('expected_date')->nullable();
                $table->date('received_date')->nullable();
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index('supplier_id');
                $table->index('created_by_user_id');
            });
        }

        if (! Schema::hasTable('supply_order_items')) {
            Schema::create('supply_order_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('supply_order_id')->constrained('supply_orders')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
                $table->unsignedInteger('quantity');
                $table->decimal('unit_cost', 15, 2);
                $table->decimal('line_total', 15, 2);
                $table->timestamps();

                $table->index('supply_order_id');
                $table->index('product_id');
            });
        }

        if (! Schema::hasTable('delivery_requests')) {
            Schema::create('delivery_requests', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
                $table->unsignedInteger('requested_qty');
                $table->string('reason', 255)->nullable();
                $table->string('status', 30)->default('PENDING');
                $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index('requested_by_user_id');
                $table->index('product_id');
                $table->index('approved_by_user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_requests');
        Schema::dropIfExists('supply_order_items');
        Schema::dropIfExists('supply_orders');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('prices');
    }
};
