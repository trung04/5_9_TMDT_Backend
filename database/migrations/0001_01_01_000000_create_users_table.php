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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 120);
            $table->string('email', 120)->unique();
            $table->string('phone', 20)->unique();
            $table->string('password_hash');
            $table->string('address', 255)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('favorite_region', 120)->nullable();
            $table->text('avatar_url')->nullable();
            $table->boolean('newsletter')->default(false);
            $table->boolean('sms_alerts')->default(false);
            $table->boolean('order_email')->default(true);
            $table->boolean('security_alerts')->default(true);
            $table->unsignedInteger('reward_points')->default(0);
            $table->string('reward_tier', 50)->default('Bronze');
            $table->unsignedInteger('next_tier_points')->default(500);
            $table->enum('role', ['CUSTOMER', 'ADMIN', 'WAREHOUSE_STAFF', 'SUPPLIER']);
            $table->unsignedBigInteger('admin_role_id')->nullable();
            $table->unsignedBigInteger('created_by_admin_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index(['role', 'is_active', 'is_deleted']);
            $table->index('admin_role_id');
            $table->index('created_by_admin_id');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
