<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_super')->default(false);
            $table->boolean('is_system')->default(false);
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('admin_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 120)->unique();
            $table->string('name', 120);
            $table->string('group', 80);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_role_permission', function (Blueprint $table) {
            $table->foreignId('admin_role_id')->constrained('admin_roles')->cascadeOnDelete();
            $table->foreignId('admin_permission_id')->constrained('admin_permissions')->cascadeOnDelete();
            $table->primary(['admin_role_id', 'admin_permission_id']);
        });

        $canAlterForeignKeys = Schema::getConnection()->getDriverName() !== 'sqlite';

        Schema::table('users', function (Blueprint $table) use ($canAlterForeignKeys) {
            if (! Schema::hasColumn('users', 'admin_role_id')) {
                $table->foreignId('admin_role_id')->nullable()->after('role')->constrained('admin_roles')->nullOnDelete();
            } elseif ($canAlterForeignKeys) {
                $table->foreign('admin_role_id')->references('id')->on('admin_roles')->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'created_by_admin_id')) {
                $table->foreignId('created_by_admin_id')->nullable()->after('admin_role_id')->constrained('users')->nullOnDelete();
            } elseif ($canAlterForeignKeys) {
                $table->foreign('created_by_admin_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::dropIfExists('admin_role_permission');
            Schema::dropIfExists('admin_permissions');
            Schema::dropIfExists('admin_roles');

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by_admin_id']);
            $table->dropForeign(['admin_role_id']);
        });

        Schema::dropIfExists('admin_role_permission');
        Schema::dropIfExists('admin_permissions');
        Schema::dropIfExists('admin_roles');
    }
};
