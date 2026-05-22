<?php

namespace Database\Seeders;

use App\Models\AdminSetting;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Price;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminAccessSeeder::class);

        $this->seedUsers();
        $this->seedCatalog();
        $this->seedCommerceFlow();
        $this->seedOperations();
    }

    private function seedUsers(): void
    {
        $passwordHash = Hash::make('password123');

        $users = [
            [
                'full_name' => 'Tran Thi Customer',
                'email' => 'customer1@shop.local',
                'phone' => '0900000002',
                'address' => '101 Le Duan',
                'city' => 'Ha Noi',
                'favorite_region' => 'Dong Bac',
                'reward_points' => 720,
                'reward_tier' => 'Silver',
                'next_tier_points' => 1000,
                'role' => User::ROLE_CUSTOMER,
                'is_active' => true,
                'is_deleted' => false,
            ],
            [
                'full_name' => 'Le Van Kho',
                'email' => 'warehouse@shop.local',
                'phone' => '0900000003',
                'address' => 'KCN Bac Tu Liem',
                'city' => 'Ha Noi',
                'role' => User::ROLE_WAREHOUSE_STAFF,
                'is_active' => true,
                'is_deleted' => false,
            ],
            [
                'full_name' => 'Pham Thi NCC',
                'email' => 'supplieruser@shop.local',
                'phone' => '0900000004',
                'address' => '45 Vo Van Tan',
                'city' => 'TP.HCM',
                'role' => User::ROLE_SUPPLIER,
                'is_active' => true,
                'is_deleted' => false,
            ],
            [
                'full_name' => 'Do Minh Khach',
                'email' => 'customer2@shop.local',
                'phone' => '0900000005',
                'address' => '22 Dien Bien Phu',
                'city' => 'TP.HCM',
                'favorite_region' => 'Nam Bo',
                'reward_points' => 340,
                'reward_tier' => 'Bronze',
                'next_tier_points' => 500,
                'role' => User::ROLE_CUSTOMER,
                'is_active' => true,
                'is_deleted' => false,
            ],
            [
                'full_name' => 'Inactive User',
                'email' => 'inactive@example.com',
                'phone' => '0903333333',
                'role' => User::ROLE_CUSTOMER,
                'is_active' => false,
                'is_deleted' => false,
            ],
        ];

        foreach ($users as $attributes) {
            User::query()->updateOrCreate(
                ['email' => $attributes['email']],
                [
                    ...$attributes,
                    'password_hash' => $passwordHash,
                    'newsletter' => $attributes['newsletter'] ?? false,
                    'sms_alerts' => $attributes['sms_alerts'] ?? false,
                    'order_email' => $attributes['order_email'] ?? true,
                    'security_alerts' => $attributes['security_alerts'] ?? true,
                    'reward_points' => $attributes['reward_points'] ?? 0,
                    'reward_tier' => $attributes['reward_tier'] ?? 'Bronze',
                    'next_tier_points' => $attributes['next_tier_points'] ?? 500,
                ],
            );
        }
    }

    private function seedCatalog(): void
    {
        $categories = [
            ['name' => 'Gao - Nong san dac san', 'description' => 'Gao, nong san va thuc pham kho dac san vung mien.'],
            ['name' => 'Mon an truyen thong', 'description' => 'Banh va mon an truyen thong dong goi san.'],
            ['name' => 'Mat ong - Dac san rung', 'description' => 'Mat ong va san vat tu nhien.'],
            ['name' => 'Tra - Ca phe dac san', 'description' => 'Tra, ca phe va qua tang dac san.'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['name' => $category['name']],
                [...$category, 'is_active' => true, 'is_deleted' => false],
            );
        }

        $suppliers = [
            [
                'supplier_code' => 'SUP-TN01',
                'name' => 'HTX Che Tan Cuong Thai Nguyen',
                'contact_name' => 'Nguyen Van Kien',
                'phone' => '0911000001',
                'email' => 'tancuong@dacsan.vn',
                'address' => 'Tan Cuong, Thai Nguyen',
            ],
            [
                'supplier_code' => 'SUP-ST25',
                'name' => 'Co so Gao Dac San Soc Trang',
                'contact_name' => 'Tran Quoc Minh',
                'phone' => '0911000002',
                'email' => 'st25@dacsan.vn',
                'address' => 'Soc Trang',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::query()->updateOrCreate(
                ['supplier_code' => $supplier['supplier_code']],
                [...$supplier, 'is_active' => true, 'is_deleted' => false],
            );
        }

        $teaCategory = Category::query()->where('name', 'Tra - Ca phe dac san')->firstOrFail();
        $riceCategory = Category::query()->where('name', 'Gao - Nong san dac san')->firstOrFail();
        $teaSupplier = Supplier::query()->where('supplier_code', 'SUP-TN01')->firstOrFail();
        $riceSupplier = Supplier::query()->where('supplier_code', 'SUP-ST25')->firstOrFail();

        $products = [
            [
                'category_id' => $teaCategory->id,
                'supplier_id' => $teaSupplier->id,
                'sku' => 'TRA-TC-200',
                'name' => 'Tra Tan Cuong Thai Nguyen 200g',
                'description' => 'Tra xanh Thai Nguyen huong com non, hau ngot.',
                'sale_price' => 229000,
                'stock_quantity' => 30,
            ],
            [
                'category_id' => $riceCategory->id,
                'supplier_id' => $riceSupplier->id,
                'sku' => 'GAO-ST25-5KG',
                'name' => 'Gao thom dac san ST25 5kg',
                'description' => 'Gao thom hat dai, com deo mem.',
                'sale_price' => 290000,
                'stock_quantity' => 24,
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                [...$product, 'is_active' => true, 'is_deleted' => false],
            );
        }
    }

    private function seedCommerceFlow(): void
    {
        $customer = User::query()->where('email', 'customer1@shop.local')->firstOrFail();
        $secondCustomer = User::query()->where('email', 'customer2@shop.local')->firstOrFail();
        $tea = Product::query()->where('sku', 'TRA-TC-200')->firstOrFail();
        $rice = Product::query()->where('sku', 'GAO-ST25-5KG')->firstOrFail();

        $cart = Cart::query()->updateOrCreate(
            ['user_id' => $customer->id, 'status' => Cart::STATUS_ACTIVE],
            ['status' => Cart::STATUS_ACTIVE],
        );

        CartItem::query()->updateOrCreate(
            ['cart_id' => $cart->id, 'product_id' => $tea->id],
            [
                'quantity' => 2,
                'unit_price' => $tea->sale_price,
                'line_total' => (float) $tea->sale_price * 2,
            ],
        );

        $deliveredOrder = Order::query()->updateOrCreate(
            ['order_no' => 'ORD-SEED-0001'],
            [
                'user_id' => $customer->id,
                'recipient_name' => $customer->full_name,
                'recipient_phone' => $customer->phone,
                'shipping_address' => $customer->address ?? '101 Le Duan, Ha Noi',
                'payment_method' => Order::PAYMENT_METHOD_BANK_TRANSFER,
                'status' => Order::STATUS_DELIVERED,
                'subtotal' => 458000,
                'shipping_fee' => 0,
                'discount_amount' => 0,
                'total_amount' => 458000,
                'stock_deducted' => true,
                'stock_deducted_at' => now()->subDays(4),
                'shipping_carrier' => 'GHN',
                'shipping_code' => 'GHN-SEED-0001',
                'shipped_at' => now()->subDays(3),
                'delivered_at' => now()->subDays(2),
            ],
        );

        OrderItem::query()->updateOrCreate(
            ['order_id' => $deliveredOrder->id, 'product_id' => $tea->id],
            [
                'product_name_snapshot' => $tea->name,
                'quantity' => 2,
                'unit_price' => 229000,
                'line_total' => 458000,
            ],
        );

        Payment::query()->updateOrCreate(
            ['order_id' => $deliveredOrder->id],
            [
                'transaction_code' => 'TXN-SEED-0001',
                'payment_method' => Order::PAYMENT_METHOD_BANK_TRANSFER,
                'payment_status' => Payment::STATUS_SUCCESS,
                'amount' => 458000,
                'gateway_name' => 'Demo Bank',
                'gateway_reference' => 'BANK-SEED-0001',
                'paid_at' => now()->subDays(4),
                'raw_payload' => ['seed' => true],
            ],
        );

        $pendingOrder = Order::query()->updateOrCreate(
            ['order_no' => 'ORD-SEED-0002'],
            [
                'user_id' => $secondCustomer->id,
                'recipient_name' => $secondCustomer->full_name,
                'recipient_phone' => $secondCustomer->phone,
                'shipping_address' => $secondCustomer->address ?? '22 Dien Bien Phu, TP.HCM',
                'payment_method' => Order::PAYMENT_METHOD_COD,
                'status' => Order::STATUS_PENDING,
                'subtotal' => 290000,
                'shipping_fee' => 25000,
                'discount_amount' => 0,
                'total_amount' => 315000,
                'stock_deducted' => false,
            ],
        );

        OrderItem::query()->updateOrCreate(
            ['order_id' => $pendingOrder->id, 'product_id' => $rice->id],
            [
                'product_name_snapshot' => $rice->name,
                'quantity' => 1,
                'unit_price' => 290000,
                'line_total' => 290000,
            ],
        );

        Payment::query()->updateOrCreate(
            ['order_id' => $pendingOrder->id],
            [
                'transaction_code' => 'TXN-SEED-0002',
                'payment_method' => Order::PAYMENT_METHOD_COD,
                'payment_status' => Payment::STATUS_PENDING,
                'amount' => 315000,
                'gateway_name' => null,
                'gateway_reference' => null,
                'paid_at' => null,
                'raw_payload' => ['seed' => true, 'collection' => 'cash_on_delivery'],
            ],
        );
    }

    private function seedOperations(): void
    {
        $admin = User::query()->where('email', config('admin_access.super_admin.email'))->firstOrFail();
        $warehouse = User::query()->where('email', 'warehouse@shop.local')->firstOrFail();
        $tea = Product::query()->where('sku', 'TRA-TC-200')->firstOrFail();
        $rice = Product::query()->where('sku', 'GAO-ST25-5KG')->firstOrFail();
        $teaSupplier = Supplier::query()->where('supplier_code', 'SUP-TN01')->firstOrFail();
        $riceSupplier = Supplier::query()->where('supplier_code', 'SUP-ST25')->firstOrFail();

        AdminSetting::query()->updateOrCreate(
            ['user_id' => $admin->id],
            [
                'store_name' => 'TMDT Local Demo',
                'support_email' => 'support@shop.local',
                'support_phone' => '0900000999',
                'low_stock_threshold' => 10,
                'dashboard_refresh_seconds' => 60,
                'order_auto_confirm' => false,
                'send_daily_summary' => true,
                'maintenance_mode' => false,
                'notes' => 'Seeded local admin setting.',
            ],
        );

        foreach ([[$tea, $teaSupplier, 155000], [$rice, $riceSupplier, 210000]] as [$product, $supplier, $costPrice]) {
            Price::query()->updateOrCreate(
                ['product_id' => $product->id, 'supplier_id' => $supplier->id],
                [
                    'cost_price' => $costPrice,
                    'effective_from' => now()->subMonth(),
                    'effective_to' => null,
                    'is_active' => true,
                ],
            );
        }

        DB::table('inventories')->updateOrInsert(
            ['name' => 'Kho chinh Ha Noi'],
            [
                'location' => 'Bac Tu Liem, Ha Noi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $inventoryId = (int) DB::table('inventories')->where('name', 'Kho chinh Ha Noi')->value('id');

        foreach ([[$tea, 30, 8, 12], [$rice, 24, 6, 10]] as [$product, $onHand, $reorderLevel, $safetyStock]) {
            DB::table('inventory_items')->updateOrInsert(
                ['inventory_id' => $inventoryId, 'product_id' => $product->id],
                [
                    'quantity_on_hand' => $onHand,
                    'reorder_level' => $reorderLevel,
                    'safety_stock' => $safetyStock,
                    'last_counted_at' => now()->subDay(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }

        DB::table('delivery_requests')->updateOrInsert(
            ['requested_by_user_id' => $warehouse->id, 'product_id' => $tea->id],
            [
                'requested_qty' => 12,
                'reason' => 'Bo sung hang cho dot ban cuoi tuan.',
                'status' => 'PENDING',
                'approved_by_user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }
}
