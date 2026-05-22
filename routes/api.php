<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\Admin\AccessController as AdminAccessController;
use App\Http\Controllers\Api\Admin\CommunityController as AdminCommunityController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NewsletterSubscriptionController;
use App\Http\Controllers\Api\OperationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;

// Public Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public product APIs
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/regions', [RegionController::class, 'index']);
Route::post('/newsletter-subscriptions', [NewsletterSubscriptionController::class, 'store']);

// Public post APIs
Route::get('/posts', [PostController::class, 'index']);

// Public Category Routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/categories/{category}/products', [CategoryController::class, 'getProducts']);

// Public Supplier Routes
Route::get('/suppliers', [SupplierController::class, 'index']);
Route::get('/suppliers/{supplier}', [SupplierController::class, 'show']);
Route::get('/suppliers/{supplier}/products', [SupplierController::class, 'getProducts']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function (): void {
    // Auth Routes
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/account/profile', [AccountController::class, 'show']);
    Route::put('/account/profile', [AccountController::class, 'update']);
    Route::patch('/account/password', [AccountController::class, 'changePassword']);
    Route::post('/account/addresses', [AccountController::class, 'storeAddress']);
    Route::put('/account/addresses/{address}', [AccountController::class, 'updateAddress']);
    Route::delete('/account/addresses/{address}', [AccountController::class, 'destroyAddress']);
    Route::patch('/account/addresses/{address}/default', [AccountController::class, 'setDefaultAddress']);
    Route::post('/account/rewards/redeem', [AccountController::class, 'redeemReward']);
    Route::get('/account/wishlist', [AccountController::class, 'wishlist']);
    Route::post('/account/wishlist/items', [AccountController::class, 'storeWishlistItem']);
    Route::delete('/account/wishlist/items/{product}', [AccountController::class, 'destroyWishlistItem']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::get('/complaints', [ComplaintController::class, 'index']);
    Route::post('/complaints', [ComplaintController::class, 'store']);
    Route::get('/posts/my-likes', [PostController::class, 'myLikes']);
    Route::post('/posts/{post}/comments', [PostController::class, 'storeComment']);
    Route::post('/posts/{post}/likes', [PostController::class, 'like']);
    Route::delete('/posts/{post}/likes', [PostController::class, 'unlike']);

    // Customer Cart & Order Routes
    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart/items', [CartController::class, 'storeItem']);
    Route::patch('/cart/items/{cartItem}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroyItem']);
    Route::post('/orders/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::patch('/orders/{order}/bank-transfer-submitted', [OrderController::class, 'confirmBankTransferSubmitted']);
    Route::patch('/orders/{order}/confirm-delivery', [OrderController::class, 'confirmDelivery']);

    Route::get('/support-tickets', [SupportTicketController::class, 'index']);
    Route::post('/support-tickets', [SupportTicketController::class, 'store']);
    Route::patch('/support-tickets/{ticket}/resolve', [SupportTicketController::class, 'resolve']);

    Route::prefix('operations')->group(function (): void {
        Route::get('/inventory', [OperationController::class, 'inventory']);
        Route::get('/requisitions', [OperationController::class, 'requisitions']);
        Route::post('/requisitions', [OperationController::class, 'storeRequisition']);
        Route::patch('/requisitions/{id}/status', [OperationController::class, 'updateRequisitionStatus']);
        Route::get('/supplier-orders', [OperationController::class, 'supplierOrders']);
        Route::get('/fulfillment-tasks', [OperationController::class, 'fulfillmentTasks']);
        Route::patch('/orders/{order}/delivery-status', [OperationController::class, 'updateOrderDeliveryStatus']);
        Route::patch('/fulfillment-tasks/{order}/advance', [OperationController::class, 'advanceFulfillmentTask']);
    });

    Route::prefix('admin')->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'show']);
        Route::get('/community', [AdminCommunityController::class, 'index']);
        Route::post('/community/invitations', [AdminCommunityController::class, 'storeInvitation']);
        Route::get('/posts', [AdminPostController::class, 'index']);
        Route::post('/posts', [AdminPostController::class, 'store']);
        Route::put('/posts/{post}', [AdminPostController::class, 'update']);
        Route::delete('/posts/{post}', [AdminPostController::class, 'destroy']);
        Route::patch('/posts/comments/{comment}/visibility', [AdminPostController::class, 'updateCommentVisibility']);
        Route::get('/settings', [AdminSettingsController::class, 'show']);
        Route::put('/settings', [AdminSettingsController::class, 'update']);
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::put('/users/{user}', [AdminUserController::class, 'update']);
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);

        Route::prefix('access')->group(function (): void {
            Route::get('/permissions', [AdminAccessController::class, 'permissions']);
            Route::get('/roles', [AdminAccessController::class, 'roles']);
            Route::post('/roles', [AdminAccessController::class, 'storeRole']);
            Route::put('/roles/{role}', [AdminAccessController::class, 'updateRole']);
            Route::delete('/roles/{role}', [AdminAccessController::class, 'destroyRole']);
            Route::get('/admins', [AdminAccessController::class, 'admins']);
            Route::post('/admins', [AdminAccessController::class, 'storeAdmin']);
            Route::put('/admins/{admin}', [AdminAccessController::class, 'updateAdmin']);
            Route::patch('/admins/{admin}/status', [AdminAccessController::class, 'updateAdminStatus']);
            Route::patch('/admins/{admin}/password', [AdminAccessController::class, 'updateAdminPassword']);
        });

        Route::get('/products', [AdminProductController::class, 'index']);
        Route::get('/products/{id}', [AdminProductController::class, 'show']);
        Route::post('/products', [AdminProductController::class, 'store']);
        Route::put('/products/{id}', [AdminProductController::class, 'update']);
        Route::patch('/products/{id}/status', [AdminProductController::class, 'updateStatus']);
        Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);

        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
        Route::post('/orders/bulk-status', [AdminOrderController::class, 'bulkUpdateStatus']);
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
        Route::patch('/orders/{order}/payment-status', [AdminOrderController::class, 'updatePaymentStatus']);
    });

    // Admin Category Routes
    Route::prefix('admin/categories')->group(function (): void {
        Route::get('/', [CategoryController::class, 'adminIndex']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{category}', [CategoryController::class, 'update']);
        Route::delete('/{category}', [CategoryController::class, 'destroy']);
    });

    // Admin Supplier Routes
    Route::prefix('admin/suppliers')->group(function (): void {
        Route::get('/', [SupplierController::class, 'adminIndex']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::put('/{supplier}', [SupplierController::class, 'update']);
        Route::delete('/{supplier}', [SupplierController::class, 'destroy']);
    });
});
