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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => '5_9_TMDT Backend',
        'status' => 'ok',
        'api_base' => '/api',
    ]);
});
Route::get('test/{id}', [TestController::class, 'index']);

Route::middleware('web')->group(function (): void {
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
