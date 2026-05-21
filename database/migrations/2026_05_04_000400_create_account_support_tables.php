<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_addresses')) {
            Schema::create('user_addresses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('label', 120);
                $table->string('recipient', 120);
                $table->string('phone', 20);
                $table->string('line1', 255);
                $table->string('city', 120);
                $table->text('note')->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('reward_redemptions')) {
            Schema::create('reward_redemptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('title', 255);
                $table->unsignedInteger('points_used');
                $table->string('status', 50)->default('COMPLETED');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('title', 255);
                $table->text('message');
                $table->string('channel', 50)->default('SYSTEM');
                $table->string('status', 50)->default('PENDING');
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('complaints')) {
            Schema::create('complaints', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->text('reason');
                $table->text('content');
                $table->text('image_url')->nullable();
                $table->string('status', 50)->default('OPEN');
                $table->text('resolution_note')->nullable();
                $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('supplier_invitations')) {
            Schema::create('supplier_invitations', function (Blueprint $table) {
                $table->id();
                $table->string('supplier_name', 255);
                $table->string('contact_name', 120);
                $table->string('email', 120);
                $table->json('categories')->nullable();
                $table->text('note')->nullable();
                $table->string('status', 50)->default('DRAFT');
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_invitations');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('reward_redemptions');
        Schema::dropIfExists('user_addresses');
    }
};
