<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Requests\CategoryRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\SupplierRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Region;
use App\Models\Supplier;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CatalogController extends AdminWebController
{
    public function __construct(
        \App\Support\AdminNavigation $navigation,
        private readonly CategoryService $categoryService
    ) {
        parent::__construct($navigation);
    }

    public function productsIndex(Request $request)
    {
        return $this->render('admin-web.catalog.products.index', [
            'products' => $this->products($request),
            ...$this->productFormOptions(),
        ]);
    }

    public function productsCreate()
    {
        return $this->render('admin-web.catalog.products.create', [
            'product' => new Product([
                'is_active' => true,
                'stock_quantity' => 0,
                'sale_price' => 0,
                'certifications' => [],
                'gallery' => [],
            ]),
            ...$this->productFormOptions(),
        ]);
    }

    public function productsStore(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_deleted'] = $data['is_deleted'] ?? false;

        $product = Product::query()->create($data);

        return redirect()->to(
            $this->adminUser()->hasAdminPermission('admin.products.update')
                ? route('admin-web.products.edit', $product->id)
                : ($this->adminUser()->hasAdminPermission('admin.products.view')
                    ? route('admin-web.products.index')
                    : route('admin-web.products.create'))
        )
            ->with('status', 'Đã tạo sản phẩm thành công.');
    }

    public function productsEdit(int $id)
    {
        return $this->render('admin-web.catalog.products.edit', [
            'product' => Product::query()->findOrFail($id),
            ...$this->productFormOptions(),
        ]);
    }

    public function productsUpdate(UpdateProductRequest $request, int $id): RedirectResponse
    {
        $product = Product::query()->findOrFail($id);
        $data = $request->validated();

        if (($data['is_active'] ?? false) === true && ! array_key_exists('is_deleted', $data)) {
            $data['is_deleted'] = false;
        }

        $product->update($data);

        return redirect()
            ->route('admin-web.products.edit', $product->id)
            ->with('status', 'Đã cập nhật sản phẩm thành công.');
    }

    public function productsDestroy(int $id): RedirectResponse
    {
        $product = Product::query()->findOrFail($id);
        $product->markDeleted();

        return redirect()
            ->route('admin-web.products.index')
            ->with('status', 'Đã xóa sản phẩm thành công.');
    }

    public function categoriesIndex(Request $request)
    {
        return $this->render('admin-web.catalog.categories.index', [
            'categories' => $this->categories($request),
        ]);
    }

    public function categoriesCreate()
    {
        return $this->render('admin-web.catalog.categories.create', [
            'category' => new Category([
                'is_active' => true,
            ]),
        ]);
    }

    public function categoriesStore(CategoryRequest $request): RedirectResponse
    {
        $category = $this->categoryService->createCategory($request->validated());

        return redirect()->to(
            $this->adminUser()->hasAdminPermission('admin.categories.update')
                ? route('admin-web.categories.edit', $category)
                : route('admin-web.categories.index')
        )
            ->with('status', 'Đã tạo danh mục thành công.');
    }

    public function categoriesEdit(Category $category)
    {
        return $this->render('admin-web.catalog.categories.edit', [
            'category' => $category,
        ]);
    }

    public function categoriesUpdate(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categoryService->updateCategory($category, $request->validated());

        return redirect()
            ->route('admin-web.categories.edit', $category)
            ->with('status', 'Đã cập nhật danh mục thành công.');
    }

    public function categoriesDestroy(Category $category): RedirectResponse
    {
        $this->categoryService->deleteCategory($category);

        return redirect()
            ->route('admin-web.categories.index')
            ->with('status', 'Đã xóa danh mục thành công.');
    }

    public function suppliersIndex(Request $request)
    {
        return $this->render('admin-web.catalog.suppliers.index', [
            'suppliers' => $this->suppliers($request),
        ]);
    }

    public function suppliersCreate()
    {
        return $this->render('admin-web.catalog.suppliers.create', [
            'supplier' => new Supplier([
                'is_active' => true,
            ]),
        ]);
    }

    public function suppliersStore(SupplierRequest $request): RedirectResponse
    {
        $supplier = Supplier::query()->create($this->supplierPayload($request->validated()));

        return redirect()->to(
            $this->adminUser()->hasAdminPermission('admin.suppliers.update')
                ? route('admin-web.suppliers.edit', $supplier)
                : route('admin-web.suppliers.index')
        )
            ->with('status', 'Đã tạo nhà cung cấp thành công.');
    }

    public function suppliersEdit(Supplier $supplier)
    {
        return $this->render('admin-web.catalog.suppliers.edit', [
            'supplier' => $supplier,
        ]);
    }

    public function suppliersUpdate(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $attributes = $request->validated();

        if (($attributes['is_active'] ?? false) === true && ! array_key_exists('is_deleted', $attributes)) {
            $attributes['is_deleted'] = false;
        }

        $supplier->update($this->supplierPayload($attributes, $supplier));

        return redirect()
            ->route('admin-web.suppliers.edit', $supplier)
            ->with('status', 'Đã cập nhật nhà cung cấp thành công.');
    }

    public function suppliersDestroy(Supplier $supplier): RedirectResponse
    {
        $supplier->markDeleted();

        return redirect()
            ->route('admin-web.suppliers.index')
            ->with('status', 'Đã xóa nhà cung cấp thành công.');
    }

    private function products(Request $request)
    {
        $query = Product::query()->with(['category', 'supplier', 'region']);

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', (int) $request->input('region_id'));
        }

        if ($request->filled('is_active')) {
            $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }
        }

        if ($request->filled('is_deleted')) {
            $isDeleted = filter_var($request->input('is_deleted'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($isDeleted !== null) {
                $query->where('is_deleted', $isDeleted);
            }
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('name', 'like', "%{$keyword}%")
                    ->orWhere('sku', 'like', "%{$keyword}%");
            });
        }

        return $query
            ->orderByDesc('id')
            ->paginate((int) $request->integer('per_page', 20))
            ->withQueryString();
    }

    private function categories(Request $request)
    {
        return Category::query()
            ->withCount('products')
            ->orderByDesc('id')
            ->paginate((int) $request->integer('per_page', 20))
            ->withQueryString();
    }

    private function suppliers(Request $request)
    {
        return Supplier::query()
            ->withCount('products')
            ->orderByDesc('id')
            ->paginate((int) $request->integer('per_page', 20))
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function supplierPayload(array $attributes, ?Supplier $supplier = null): array
    {
        return [
            'supplier_code' => $attributes['supplier_code'] ?? $supplier?->supplier_code,
            'name' => $attributes['name'] ?? $supplier?->name,
            'contact_name' => $attributes['contact_name'] ?? $supplier?->contact_name,
            'phone' => $attributes['phone'] ?? $supplier?->phone,
            'email' => $attributes['email'] ?? $supplier?->email,
            'address' => $attributes['address'] ?? $supplier?->address,
            'is_active' => $attributes['is_active'] ?? $supplier?->is_active ?? true,
            'is_deleted' => $attributes['is_deleted'] ?? $supplier?->is_deleted ?? false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productFormOptions(): array
    {
        return [
            'categoriesForForm' => Category::query()->orderBy('name')->get(),
            'suppliersForForm' => Supplier::query()->orderBy('name')->get(),
            'regionsForForm' => Region::query()->orderBy('name')->get(),
        ];
    }
}
