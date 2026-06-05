<?php

use App\Http\Controllers\AdminWeb\AdminAccountController as AdminWebAdminAccountController;
use App\Http\Controllers\AdminWeb\AuthController as AdminWebAuthController;
use App\Http\Controllers\AdminWeb\CatalogController as AdminWebCatalogController;
use App\Http\Controllers\AdminWeb\CommunityController as AdminWebCommunityController;
use App\Http\Controllers\AdminWeb\DashboardController as AdminWebDashboardController;
use App\Http\Controllers\AdminWeb\OrderController as AdminWebOrderController;
use App\Http\Controllers\AdminWeb\PostController as AdminWebPostController;
use App\Http\Controllers\AdminWeb\SettingsController as AdminWebSettingsController;
use App\Http\Controllers\AdminWeb\ShippingCarrierController as AdminWebShippingCarrierController;
use App\Http\Controllers\AdminWeb\UserController as AdminWebUserController;
use App\Http\Controllers\Api\Admin\TestController;
use App\Http\Controllers\UserWeb\AccountController as UserWebAccountController;
use App\Http\Controllers\UserWeb\AuthController as UserWebAuthController;
use App\Http\Controllers\UserWeb\CartController as UserWebCartController;
use App\Http\Controllers\UserWeb\CheckoutController as UserWebCheckoutController;
use App\Http\Controllers\UserWeb\PortalController as UserWebPortalController;
use App\Http\Controllers\UserWeb\StorefrontController as UserWebStorefrontController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/backend-status', function () {
    return response()->json([
        'name' => '5_9_TMDT Backend',
        'status' => 'ok',
        'api_base' => '/api',
    ]);
});
Route::get('test/{id}', [TestController::class, 'index']);

Route::middleware('web')->group(function (): void {
    Route::name('user-web.')->group(function (): void {
        Route::get('/', [UserWebStorefrontController::class, 'home'])->name('home');
        Route::get('/products', [UserWebStorefrontController::class, 'catalog'])->name('products.index');
        Route::get('/products/{slug}', [UserWebStorefrontController::class, 'product'])->name('products.show');
        Route::get('/story', [UserWebStorefrontController::class, 'story'])->name('story');
        Route::post('/story/{post}/likes', [UserWebStorefrontController::class, 'togglePostLike'])->middleware('customer.web')->name('story.likes');
        Route::post('/story/{post}/comments', [UserWebStorefrontController::class, 'comment'])->middleware('customer.web')->name('story.comments');
        Route::get('/regions', [UserWebStorefrontController::class, 'regions'])->name('regions');
        Route::post('/newsletter-subscriptions', [UserWebStorefrontController::class, 'newsletter'])->name('newsletter.store');
        Route::get('/product-test', [UserWebStorefrontController::class, 'productTest'])->name('product-test');
        Route::get('/unauthorized', [UserWebPortalController::class, 'unauthorized'])->name('unauthorized');

        Route::get('/login', [UserWebAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [UserWebAuthController::class, 'login'])->name('login.store');
        Route::get('/register', [UserWebAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [UserWebAuthController::class, 'register'])->name('register.store');
        Route::match(['GET', 'POST'], '/logout', [UserWebAuthController::class, 'logout'])->name('logout');

        Route::post('/cart/items', [UserWebCartController::class, 'store'])->name('cart.items.store');
        Route::patch('/cart/items/{cartItem}', [UserWebCartController::class, 'update'])->name('cart.items.update');
        Route::delete('/cart/items/{cartItem}', [UserWebCartController::class, 'destroy'])->name('cart.items.destroy');
        Route::get('/checkout', [UserWebCheckoutController::class, 'show'])->name('checkout.show');
        Route::post('/checkout', [UserWebCheckoutController::class, 'place'])->name('checkout.place');

        Route::middleware('customer.web')->group(function (): void {
            Route::get('/checkout/success/{order}', [UserWebCheckoutController::class, 'success'])->name('checkout.success');
            Route::patch('/checkout/success/{order}/bank-transfer-submitted', [UserWebCheckoutController::class, 'confirmTransfer'])->name('checkout.confirm-transfer');

            Route::get('/account', fn () => redirect()->route('user-web.account.profile'))->name('account.index');
            Route::get('/account/profile', [UserWebAccountController::class, 'profile'])->name('account.profile');
            Route::put('/account/profile', [UserWebAccountController::class, 'updateProfile'])->name('account.profile.update');
            Route::get('/account/security', [UserWebAccountController::class, 'security'])->name('account.security');
            Route::patch('/account/security/password', [UserWebAccountController::class, 'updatePassword'])->name('account.security.password');
            Route::get('/account/notifications', [UserWebAccountController::class, 'notifications'])->name('account.notifications');
            Route::patch('/account/notifications/{notification}/read', [UserWebAccountController::class, 'markNotificationRead'])->name('account.notifications.read');
            Route::get('/account/addresses', [UserWebAccountController::class, 'addresses'])->name('account.addresses');
            Route::post('/account/addresses', [UserWebAccountController::class, 'storeAddress'])->name('account.addresses.store');
            Route::put('/account/addresses/{address}', [UserWebAccountController::class, 'updateAddress'])->name('account.addresses.update');
            Route::delete('/account/addresses/{address}', [UserWebAccountController::class, 'destroyAddress'])->name('account.addresses.destroy');
            Route::patch('/account/addresses/{address}/default', [UserWebAccountController::class, 'setDefaultAddress'])->name('account.addresses.default');
            Route::get('/account/rewards', [UserWebAccountController::class, 'rewards'])->name('account.rewards');
            Route::post('/account/rewards/redeem', [UserWebAccountController::class, 'redeemReward'])->name('account.rewards.redeem');
            Route::get('/account/disputes', [UserWebAccountController::class, 'disputes'])->name('account.disputes');
            Route::post('/account/disputes', [UserWebAccountController::class, 'storeDispute'])->name('account.disputes.store');
            Route::get('/account/wishlist', [UserWebAccountController::class, 'wishlist'])->name('account.wishlist');
            Route::post('/account/wishlist/items', [UserWebAccountController::class, 'addWishlist'])->name('account.wishlist.store');
            Route::delete('/account/wishlist/items/{product}', [UserWebAccountController::class, 'removeWishlist'])->name('account.wishlist.destroy');
            Route::get('/account/orders', [UserWebAccountController::class, 'orders'])->name('account.orders');
            Route::get('/account/orders/{order}', [UserWebAccountController::class, 'orders'])->name('account.orders.show');
            Route::post('/account/orders/{order}/reorder', [UserWebAccountController::class, 'reorder'])->name('account.orders.reorder');
            Route::patch('/account/orders/{order}/cancel', [UserWebAccountController::class, 'cancelOrder'])->name('account.orders.cancel');
            Route::patch('/account/orders/{order}/bank-transfer-submitted', [UserWebAccountController::class, 'confirmTransfer'])->name('account.orders.confirm-transfer');
            Route::patch('/account/orders/{order}/confirm-delivery', [UserWebAccountController::class, 'confirmDelivery'])->name('account.orders.confirm-delivery');
        });

        Route::get('/supplier', fn () => redirect()->route('user-web.supplier.inventory'))->middleware('portal.web:'.User::ROLE_SUPPLIER)->name('supplier.index');
        Route::middleware('portal.web:'.User::ROLE_SUPPLIER)->prefix('supplier')->name('supplier.')->group(function (): void {
            Route::get('/inventory', [UserWebPortalController::class, 'supplierInventory'])->name('inventory');
            Route::get('/inventory/export', [UserWebPortalController::class, 'exportInventory'])->name('inventory.export');
            Route::get('/requisitions', [UserWebPortalController::class, 'supplierRequisitions'])->name('requisitions');
            Route::get('/processing', [UserWebPortalController::class, 'supplierProcessing'])->name('processing');
            Route::get('/orders', [UserWebPortalController::class, 'supplierOrders'])->name('orders');
            Route::get('/help', [UserWebPortalController::class, 'supplierHelp'])->name('help');
        });

        Route::get('/warehouse', fn () => redirect()->route('user-web.warehouse.inventory'))->middleware('portal.web:'.User::ROLE_WAREHOUSE_STAFF)->name('warehouse.index');
        Route::middleware('portal.web:'.User::ROLE_WAREHOUSE_STAFF)->prefix('warehouse')->name('warehouse.')->group(function (): void {
            Route::get('/inventory', [UserWebPortalController::class, 'warehouseInventory'])->name('inventory');
            Route::get('/inventory/export', [UserWebPortalController::class, 'exportInventory'])->name('inventory.export');
            Route::get('/requisitions', [UserWebPortalController::class, 'warehouseRequisitions'])->name('requisitions');
            Route::get('/fulfillment', [UserWebPortalController::class, 'warehouseFulfillment'])->name('fulfillment');
            Route::get('/supplier-orders', [UserWebPortalController::class, 'warehouseSupplierOrders'])->name('supplier-orders');
            Route::get('/help', [UserWebPortalController::class, 'warehouseHelp'])->name('help');
        });

        Route::middleware('portal.web:'.User::ROLE_SUPPLIER.','.User::ROLE_WAREHOUSE_STAFF)->group(function (): void {
            Route::post('/operations/requisitions', [UserWebPortalController::class, 'storeRequisition'])->name('operations.requisitions.store');
            Route::patch('/operations/requisitions/{id}/status', [UserWebPortalController::class, 'updateRequisitionStatus'])->name('operations.requisitions.status');
            Route::patch('/operations/orders/{order}/delivery-status', [UserWebPortalController::class, 'updateOrderDeliveryStatus'])->name('operations.orders.delivery-status');
            Route::patch('/operations/fulfillment-tasks/{order}/advance', [UserWebPortalController::class, 'advanceFulfillmentTask'])->name('operations.fulfillment.advance');
            Route::post('/support-tickets/{channel}', [UserWebPortalController::class, 'storeSupportTicket'])->whereIn('channel', ['supplier', 'warehouse'])->name('support-tickets.store');
            Route::patch('/support-tickets/{ticket}/resolve', [UserWebPortalController::class, 'resolveSupportTicket'])->name('support-tickets.resolve');
        });
    });

    Route::prefix('admin-web')->name('admin-web.')->group(function (): void {
        Route::middleware('admin.web.guest')->group(function (): void {
            Route::get('/login', [AdminWebAuthController::class, 'showLogin'])->name('login');
            Route::post('/login', [AdminWebAuthController::class, 'login'])->name('login.store');
        });

        Route::middleware('admin.web')->group(function (): void {
            Route::post('/logout', [AdminWebAuthController::class, 'logout'])->name('logout');
            Route::get('/', [AdminWebDashboardController::class, 'home'])->name('dashboard');
            Route::get('/dashboard', fn () => redirect()->route('admin-web.dashboard'))->name('dashboard.legacy');
            Route::get('/dashboard/view', [AdminWebDashboardController::class, 'show'])->name('dashboard.view');
            Route::get('/repository', fn () => redirect()->route('admin-web.products.index'))->name('repository');

            Route::get('/community', [AdminWebCommunityController::class, 'index'])->name('community.index');
            Route::get('/community/invitations/create', [AdminWebCommunityController::class, 'createInvitation'])->name('community.invitations.create');
            Route::post('/community/invitations', [AdminWebCommunityController::class, 'storeInvitation'])->name('community.invitations.store');

            Route::get('/posts', [AdminWebPostController::class, 'index'])->name('posts.index');
            Route::get('/posts/create', [AdminWebPostController::class, 'create'])->name('posts.create');
            Route::post('/posts', [AdminWebPostController::class, 'store'])->name('posts.store');
            Route::get('/posts/{post}/edit', [AdminWebPostController::class, 'edit'])->name('posts.edit');
            Route::put('/posts/{post}', [AdminWebPostController::class, 'update'])->name('posts.update');
            Route::delete('/posts/{post}', [AdminWebPostController::class, 'destroy'])->name('posts.destroy');
            Route::patch('/posts/comments/{comment}/visibility', [AdminWebPostController::class, 'updateCommentVisibility'])->name('posts.comments.visibility');

            Route::get('/products', [AdminWebCatalogController::class, 'productsIndex'])->name('products.index');
            Route::get('/products/create', [AdminWebCatalogController::class, 'productsCreate'])->name('products.create');
            Route::post('/products', [AdminWebCatalogController::class, 'productsStore'])->name('products.store');
            Route::get('/products/{id}/edit', [AdminWebCatalogController::class, 'productsEdit'])->name('products.edit');
            Route::put('/products/{id}', [AdminWebCatalogController::class, 'productsUpdate'])->name('products.update');
            Route::delete('/products/{id}', [AdminWebCatalogController::class, 'productsDestroy'])->name('products.destroy');

            Route::get('/categories', [AdminWebCatalogController::class, 'categoriesIndex'])->name('categories.index');
            Route::get('/categories/create', [AdminWebCatalogController::class, 'categoriesCreate'])->name('categories.create');
            Route::post('/categories', [AdminWebCatalogController::class, 'categoriesStore'])->name('categories.store');
            Route::get('/categories/{category}/edit', [AdminWebCatalogController::class, 'categoriesEdit'])->name('categories.edit');
            Route::put('/categories/{category}', [AdminWebCatalogController::class, 'categoriesUpdate'])->name('categories.update');
            Route::delete('/categories/{category}', [AdminWebCatalogController::class, 'categoriesDestroy'])->name('categories.destroy');

            Route::get('/suppliers', [AdminWebCatalogController::class, 'suppliersIndex'])->name('suppliers.index');
            Route::get('/suppliers/create', [AdminWebCatalogController::class, 'suppliersCreate'])->name('suppliers.create');
            Route::post('/suppliers', [AdminWebCatalogController::class, 'suppliersStore'])->name('suppliers.store');
            Route::get('/suppliers/{supplier}/edit', [AdminWebCatalogController::class, 'suppliersEdit'])->name('suppliers.edit');
            Route::put('/suppliers/{supplier}', [AdminWebCatalogController::class, 'suppliersUpdate'])->name('suppliers.update');
            Route::delete('/suppliers/{supplier}', [AdminWebCatalogController::class, 'suppliersDestroy'])->name('suppliers.destroy');

            Route::get('/shipping-carriers', [AdminWebShippingCarrierController::class, 'index'])->name('shipping-carriers.index');
            Route::get('/shipping-carriers/create', [AdminWebShippingCarrierController::class, 'create'])->name('shipping-carriers.create');
            Route::post('/shipping-carriers', [AdminWebShippingCarrierController::class, 'store'])->name('shipping-carriers.store');
            Route::get('/shipping-carriers/{carrier}/edit', [AdminWebShippingCarrierController::class, 'edit'])->name('shipping-carriers.edit');
            Route::put('/shipping-carriers/{carrier}', [AdminWebShippingCarrierController::class, 'update'])->name('shipping-carriers.update');
            Route::delete('/shipping-carriers/{carrier}', [AdminWebShippingCarrierController::class, 'destroy'])->name('shipping-carriers.destroy');

            Route::get('/users', [AdminWebUserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [AdminWebUserController::class, 'create'])->name('users.create');
            Route::post('/users', [AdminWebUserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [AdminWebUserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [AdminWebUserController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [AdminWebUserController::class, 'destroy'])->name('users.destroy');
            Route::get('/users/{user}/orders', [AdminWebUserController::class, 'orders'])->name('users.orders.index');

            Route::get('/logistics', [AdminWebOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [AdminWebOrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/bulk-status', [AdminWebOrderController::class, 'bulkUpdateStatus'])->name('orders.bulk-status');
            Route::patch('/orders/{order}/status', [AdminWebOrderController::class, 'updateStatus'])->name('orders.status.update');
            Route::post('/orders/{order}/shipment', [AdminWebOrderController::class, 'storeShipment'])->name('orders.shipment.store');
            Route::post('/orders/{order}/shipment/sync', [AdminWebOrderController::class, 'syncShipment'])->name('orders.shipment.sync');
            Route::delete('/orders/{order}/shipment', [AdminWebOrderController::class, 'destroyShipment'])->name('orders.shipment.destroy');
            Route::patch('/orders/{order}/payment-status', [AdminWebOrderController::class, 'updatePaymentStatus'])->name('orders.payment.update');

            Route::get('/settings', [AdminWebSettingsController::class, 'show'])->name('settings.show');
            Route::put('/settings', [AdminWebSettingsController::class, 'update'])->name('settings.update');

            Route::get('/admins', [AdminWebAdminAccountController::class, 'index'])->name('admins.index');
            Route::get('/admins/create', [AdminWebAdminAccountController::class, 'create'])->name('admins.create');
            Route::post('/admins', [AdminWebAdminAccountController::class, 'store'])->name('admins.store');
            Route::get('/admins/{admin}/edit', [AdminWebAdminAccountController::class, 'edit'])->name('admins.edit');
            Route::put('/admins/{admin}', [AdminWebAdminAccountController::class, 'update'])->name('admins.update');
            Route::patch('/admins/{admin}/status', [AdminWebAdminAccountController::class, 'updateStatus'])->name('admins.status');
            Route::patch('/admins/{admin}/password', [AdminWebAdminAccountController::class, 'updatePassword'])->name('admins.password');
        });
    });

    Route::get('/admin/login', fn () => redirect()->route('admin-web.login'));
});
