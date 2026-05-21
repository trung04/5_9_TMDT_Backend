<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'address')) {
                $table->string('address', 255)->nullable()->after('password_hash');
            }

            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city', 120)->nullable()->after('address');
            }

            if (! Schema::hasColumn('users', 'favorite_region')) {
                $table->string('favorite_region', 120)->nullable()->after('city');
            }

            if (! Schema::hasColumn('users', 'avatar_url')) {
                $table->text('avatar_url')->nullable()->after('favorite_region');
            }

            if (! Schema::hasColumn('users', 'newsletter')) {
                $table->boolean('newsletter')->default(false)->after('avatar_url');
            }

            if (! Schema::hasColumn('users', 'sms_alerts')) {
                $table->boolean('sms_alerts')->default(false)->after('newsletter');
            }

            if (! Schema::hasColumn('users', 'order_email')) {
                $table->boolean('order_email')->default(true)->after('sms_alerts');
            }

            if (! Schema::hasColumn('users', 'security_alerts')) {
                $table->boolean('security_alerts')->default(true)->after('order_email');
            }

            if (! Schema::hasColumn('users', 'reward_points')) {
                $table->unsignedInteger('reward_points')->default(0)->after('security_alerts');
            }

            if (! Schema::hasColumn('users', 'reward_tier')) {
                $table->string('reward_tier', 50)->default('Bronze')->after('reward_points');
            }

            if (! Schema::hasColumn('users', 'next_tier_points')) {
                $table->unsignedInteger('next_tier_points')->default(500)->after('reward_tier');
            }
        });
    }

    public function down(): void
    {
    }
};
