<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shipping_carriers')) {
            Schema::create('shipping_carriers', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 40)->unique();
                $table->string('name', 120);
                $table->string('provider', 40)->default('MANUAL');
                $table->string('tracking_url_template', 255)->nullable();
                $table->unsignedInteger('default_weight')->default(1000);
                $table->unsignedInteger('default_length')->default(20);
                $table->unsignedInteger('default_width')->default(20);
                $table->unsignedInteger('default_height')->default(10);
                $table->unsignedTinyInteger('default_service_type_id')->default(2);
                $table->unsignedTinyInteger('default_payment_type_id')->default(1);
                $table->string('default_required_note', 40)->default('KHONGCHOXEMHANG');
                $table->string('pickup_name', 120)->nullable();
                $table->string('pickup_phone', 20)->nullable();
                $table->string('pickup_address', 255)->nullable();
                $table->string('pickup_ward_code', 30)->nullable();
                $table->string('pickup_ward_name', 120)->nullable();
                $table->unsignedInteger('pickup_district_id')->nullable();
                $table->string('pickup_district_name', 120)->nullable();
                $table->unsignedInteger('pickup_province_id')->nullable();
                $table->string('pickup_province_name', 120)->nullable();
                $table->json('settings')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_deleted')->default(false);
                $table->timestamps();

                $table->index(['provider', 'is_active', 'is_deleted']);
            });
        }

        if (! Schema::hasTable('order_shipments')) {
            Schema::create('order_shipments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
                $table->foreignId('shipping_carrier_id')->constrained('shipping_carriers')->restrictOnDelete();
                $table->string('provider', 40);
                $table->string('status', 60)->default('created');
                $table->string('tracking_code', 120)->nullable();
                $table->string('tracking_url', 255)->nullable();
                $table->unsignedTinyInteger('service_type_id')->nullable();
                $table->unsignedTinyInteger('payment_type_id')->nullable();
                $table->string('required_note', 40)->nullable();
                $table->unsignedInteger('weight')->nullable();
                $table->unsignedInteger('length')->nullable();
                $table->unsignedInteger('width')->nullable();
                $table->unsignedInteger('height')->nullable();
                $table->decimal('shipping_fee', 15, 2)->nullable();
                $table->decimal('cod_amount', 15, 2)->nullable();
                $table->timestamp('expected_delivery_time')->nullable();
                $table->json('raw_request')->nullable();
                $table->json('raw_response')->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['provider', 'status']);
                $table->index('tracking_code');
            });
        }

        Schema::table('user_addresses', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_addresses', 'ghn_province_id')) {
                $table->unsignedInteger('ghn_province_id')->nullable()->after('city');
            }
            if (! Schema::hasColumn('user_addresses', 'ghn_province_name')) {
                $table->string('ghn_province_name', 120)->nullable()->after('ghn_province_id');
            }
            if (! Schema::hasColumn('user_addresses', 'ghn_district_id')) {
                $table->unsignedInteger('ghn_district_id')->nullable()->after('ghn_province_name');
            }
            if (! Schema::hasColumn('user_addresses', 'ghn_district_name')) {
                $table->string('ghn_district_name', 120)->nullable()->after('ghn_district_id');
            }
            if (! Schema::hasColumn('user_addresses', 'ghn_ward_code')) {
                $table->string('ghn_ward_code', 30)->nullable()->after('ghn_district_name');
            }
            if (! Schema::hasColumn('user_addresses', 'ghn_ward_name')) {
                $table->string('ghn_ward_name', 120)->nullable()->after('ghn_ward_code');
            }
        });

        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'shipping_line1')) {
                $table->string('shipping_line1', 255)->nullable()->after('shipping_address');
            }
            if (! Schema::hasColumn('orders', 'shipping_province_id')) {
                $table->unsignedInteger('shipping_province_id')->nullable()->after('shipping_line1');
            }
            if (! Schema::hasColumn('orders', 'shipping_province_name')) {
                $table->string('shipping_province_name', 120)->nullable()->after('shipping_province_id');
            }
            if (! Schema::hasColumn('orders', 'shipping_district_id')) {
                $table->unsignedInteger('shipping_district_id')->nullable()->after('shipping_province_name');
            }
            if (! Schema::hasColumn('orders', 'shipping_district_name')) {
                $table->string('shipping_district_name', 120)->nullable()->after('shipping_district_id');
            }
            if (! Schema::hasColumn('orders', 'shipping_ward_code')) {
                $table->string('shipping_ward_code', 30)->nullable()->after('shipping_district_name');
            }
            if (! Schema::hasColumn('orders', 'shipping_ward_name')) {
                $table->string('shipping_ward_name', 120)->nullable()->after('shipping_ward_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $columns = [
                'shipping_line1',
                'shipping_province_id',
                'shipping_province_name',
                'shipping_district_id',
                'shipping_district_name',
                'shipping_ward_code',
                'shipping_ward_name',
            ];
            $existing = array_values(array_filter($columns, fn (string $column): bool => Schema::hasColumn('orders', $column)));

            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });

        Schema::table('user_addresses', function (Blueprint $table): void {
            $columns = [
                'ghn_province_id',
                'ghn_province_name',
                'ghn_district_id',
                'ghn_district_name',
                'ghn_ward_code',
                'ghn_ward_name',
            ];
            $existing = array_values(array_filter($columns, fn (string $column): bool => Schema::hasColumn('user_addresses', $column)));

            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });

        Schema::dropIfExists('order_shipments');
        Schema::dropIfExists('shipping_carriers');
    }
};
