<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('store_name', 150)->default('Heritage Harvest');
            $table->string('support_email', 120)->nullable();
            $table->string('support_phone', 20)->nullable();
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->unsignedInteger('dashboard_refresh_seconds')->default(60);
            $table->boolean('order_auto_confirm')->default(false);
            $table->boolean('send_daily_summary')->default(true);
            $table->boolean('maintenance_mode')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
