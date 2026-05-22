<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\AdminAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_category_and_supplier_lists_include_inactive_records(): void
    {
        $token = $this->adminToken();

        Category::query()->create([
            'name' => 'Active Category',
            'description' => 'Visible storefront category',
            'is_active' => true,
        ]);
        Category::query()->create([
            'name' => 'Inactive Category',
            'description' => 'Hidden admin category',
            'is_active' => false,
        ]);
        Supplier::query()->create([
            'supplier_code' => 'SUP-ACTIVE',
            'name' => 'Active Supplier',
            'phone' => '0900000001',
            'is_active' => true,
        ]);
        Supplier::query()->create([
            'supplier_code' => 'SUP-INACTIVE',
            'name' => 'Inactive Supplier',
            'phone' => '0900000002',
            'is_active' => false,
        ]);

        $this->withToken($token)->getJson('/api/admin/categories?per_page=20')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Active Category', 'is_active' => true])
            ->assertJsonFragment(['name' => 'Inactive Category', 'is_active' => false]);

        $this->withToken($token)->getJson('/api/admin/suppliers?per_page=20')
            ->assertOk()
            ->assertJsonFragment(['supplier_code' => 'SUP-ACTIVE', 'is_active' => true])
            ->assertJsonFragment(['supplier_code' => 'SUP-INACTIVE', 'is_active' => false]);

        $this->getJson('/api/categories?per_page=20')
            ->assertOk()
            ->assertJsonMissing(['name' => 'Inactive Category']);

        $this->getJson('/api/suppliers?per_page=20')
            ->assertOk()
            ->assertJsonMissing(['supplier_code' => 'SUP-INACTIVE']);
    }

    public function test_soft_deleting_category_or_supplier_does_not_disable_related_products(): void
    {
        $token = $this->adminToken();
        $category = Category::query()->create([
            'name' => 'Fruit',
            'description' => 'Fresh fruit',
            'is_active' => true,
        ]);
        $supplier = Supplier::query()->create([
            'supplier_code' => 'SUP-FRUIT',
            'name' => 'Fruit Supplier',
            'phone' => '0900000003',
            'is_active' => true,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'FRUIT-001',
            'name' => 'Dried fruit',
            'description' => 'Test product',
            'sale_price' => 120000,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $this->withToken($token)->deleteJson("/api/admin/categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse($category->refresh()->is_active);
        $this->assertTrue($product->refresh()->is_active);

        $this->withToken($token)->deleteJson("/api/admin/suppliers/{$supplier->id}")
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse($supplier->refresh()->is_active);
        $this->assertTrue($product->refresh()->is_active);
    }

    private function adminToken(): string
    {
        $this->seed(AdminAccessSeeder::class);

        /** @var User $admin */
        $admin = User::query()
            ->where('email', config('admin_access.super_admin.email'))
            ->firstOrFail()
            ->load('adminRole.permissions');

        return $admin->createToken('test')->plainTextToken;
    }
}
