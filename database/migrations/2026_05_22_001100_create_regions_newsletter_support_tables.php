<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('regions')) {
            Schema::create('regions', function (Blueprint $table): void {
                $table->id();
                $table->string('slug', 120)->unique();
                $table->string('name', 120)->unique();
                $table->text('description')->nullable();
                $table->text('image_url')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'region_id')) {
                $table->foreignId('region_id')->nullable()->after('supplier_id')->constrained('regions')->nullOnDelete();
            }

            if (! Schema::hasColumn('products', 'slug')) {
                $table->string('slug', 180)->nullable()->unique()->after('sku');
            }

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

        if (! Schema::hasTable('newsletter_subscriptions')) {
            Schema::create('newsletter_subscriptions', function (Blueprint $table): void {
                $table->id();
                $table->string('email', 160);
                $table->string('source', 80)->default('storefront');
                $table->timestamps();

                $table->unique(['email', 'source']);
                $table->index('email');
            });
        }

        if (! Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('subject', 180);
                $table->text('message');
                $table->string('channel', 30);
                $table->string('status', 30)->default('OPEN');
                $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['channel', 'status']);
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('newsletter_subscriptions');

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'region_id')) {
                $table->dropConstrainedForeignId('region_id');
            }

            foreach (['slug', 'short_description', 'origin', 'weight', 'shelf_life', 'certifications', 'gallery'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('regions');
    }
};
