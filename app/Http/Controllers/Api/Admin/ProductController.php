<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\UpdateProductStatusRequest;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    private function ensureAdmin(Request $request): ?JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user->role !== User::ROLE_ADMIN) {
            return response()->json([
                'message' => 'Bạn không có quyền truy cập chức năng này.',
            ], 403);
        }

        if (! $user->canAuthenticate()) {
            return response()->json([
                'message' => 'Tài khoản của bạn không được phép thực hiện chức năng này.',
            ], 403);
        }

        return null;
    }

    public function index(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $query = Product::query()->with(['category', 'supplier']);

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('is_active')) {
            $isActive = filter_var(
                $request->input('is_active'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));

            $query->where(function ($q) use ($keyword): void {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('sku', 'like', "%{$keyword}%");
            });
        }

        $products = $query->orderByDesc('id')->get();

        return response()->json([
            'message' => 'Lấy danh sách sản phẩm thành công.',
            'data' => $products,
        ], 200);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $product = Product::query()
            ->with(['category', 'supplier'])
            ->find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Không tìm thấy sản phẩm.',
            ], 404);
        }

        return response()->json([
            'message' => 'Lấy chi tiết sản phẩm thành công.',
            'data' => $product,
        ], 200);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;

        $product = Product::query()->create($data);

        return response()->json([
            'message' => 'Tạo sản phẩm thành công.',
            'data' => $product->load(['category', 'supplier']),
        ], 201);
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $product = Product::query()->find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Không tìm thấy sản phẩm.',
            ], 404);
        }

        $product->update($request->validated());

        return response()->json([
            'message' => 'Cập nhật sản phẩm thành công.',
            'data' => $product->load(['category', 'supplier']),
        ], 200);
    }

    public function updateStatus(UpdateProductStatusRequest $request, int $id): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $product = Product::query()->find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Không tìm thấy sản phẩm.',
            ], 404);
        }

        $product->update([
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json([
            'message' => 'Cập nhật trạng thái sản phẩm thành công.',
            'data' => $product->load(['category', 'supplier']),
        ], 200);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $product = Product::query()->find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Không tìm thấy sản phẩm.',
            ], 404);
        }

        $hasRelatedData =
            DB::table('cart_items')->where('product_id', $id)->exists() ||
            DB::table('order_items')->where('product_id', $id)->exists() ||
            DB::table('reviews')->where('product_id', $id)->exists() ||
            DB::table('complaints')->where('product_id', $id)->exists() ||
            DB::table('inventory_items')->where('product_id', $id)->exists() ||
            DB::table('supply_order_items')->where('product_id', $id)->exists() ||
            DB::table('delivery_requests')->where('product_id', $id)->exists() ||
            DB::table('prices')->where('product_id', $id)->exists();

        if ($hasRelatedData) {
            return response()->json([
                'message' => 'Không thể xóa sản phẩm vì đã phát sinh dữ liệu liên quan. Hãy chuyển sản phẩm sang trạng thái ngừng hoạt động.',
            ], 422);
        }

        $product->delete();

        return response()->json([
            'message' => 'Xóa sản phẩm thành công.',
        ], 200);
    }
}
