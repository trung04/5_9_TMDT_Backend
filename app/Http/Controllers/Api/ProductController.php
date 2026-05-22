<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\PaginatesApiResults;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use PaginatesApiResults;

    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with(['category', 'supplier', 'region'])
            ->available();

        $categoryIds = $this->integerList($request, 'category_id');
        if ($categoryIds !== []) {
            $query->whereIn('category_id', $categoryIds);
        }

        $supplierIds = $this->integerList($request, 'supplier_id');
        if ($supplierIds !== []) {
            $query->whereIn('supplier_id', $supplierIds);
        }

        $regionIds = $this->integerList($request, 'region_id');
        if ($regionIds !== []) {
            $query->whereIn('region_id', $regionIds);
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));

            $query->where(function ($q) use ($keyword): void {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('sku', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('min_price')) {
            $query->where('sale_price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('sale_price', '<=', (float) $request->input('max_price'));
        }

        match ($request->query('sort')) {
            'price-asc' => $query->orderBy('sale_price')->orderByDesc('id'),
            'price-desc' => $query->orderByDesc('sale_price')->orderByDesc('id'),
            'newest' => $query->orderByDesc('created_at')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };

        $products = $query->paginate($this->perPage($request));

        return response()->json($products);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::query()
            ->with(['category', 'supplier', 'region'])
            ->available()
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

    /**
     * @return list<int>
     */
    private function integerList(Request $request, string $key): array
    {
        if (! $request->filled($key)) {
            return [];
        }

        return collect(explode(',', (string) $request->input($key)))
            ->map(fn (string $value): int => (int) trim($value))
            ->filter(fn (int $value): bool => $value > 0)
            ->unique()
            ->values()
            ->all();
    }
}
