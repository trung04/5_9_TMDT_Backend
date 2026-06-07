<?php

namespace App\Http\Controllers\UserWeb;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\NewsletterSubscription;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\Product;
use App\Models\Region;
use App\Models\Supplier;
use App\Models\User;
use App\Support\UserWeb\UserWebPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function __construct(private readonly UserWebPresenter $presenter)
    {
    }

    public function home(): View
    {
        if (!Schema::hasTable('products')) {
            return view('user-web.storefront.home', [
                'featuredProducts' => collect(),
                'categories' => collect(),
                'regions' => collect(),
            ]);
        }
        return view('user-web.storefront.home', [
            'featuredProducts' => Product::take(6)->get(),
            'categories' => Category::orderBy('name')->take(6)->get(),
            'regions' => Region::orderBy('id')->where('is_active', 1)->take(6)->get(),
        ]);
    }

    public function catalog(Request $request): View
    {
        $categoryIds = $this->csvIds($request->query('categories'));
        $supplierIds = $this->csvIds($request->query('suppliers'));
        $regionIds = $this->csvIds($request->query('regions'));
        $priceLimit = (float) $request->query('price', 30000000);
        $ratingMin = (float) $request->query('ratingMin', 0);
        $search = trim((string) $request->query('search', ''));
        $sort = (string) $request->query('sort', 'popular');
        $view = in_array($request->query('view'), ['grid', 'list'], true)
            ? (string) $request->query('view')
            : (string) $request->session()->get('user_web.catalog_view', 'grid');

        if (!in_array($view, ['grid', 'list'], true)) {
            $view = 'grid';
        }

        $request->session()->put('user_web.catalog_view', $view);

        $query = $this->baseProductQuery();

        if ($categoryIds !== []) {
            $query->whereIn('category_id', $categoryIds);
        }

        if ($supplierIds !== []) {
            $query->whereIn('supplier_id', $supplierIds);
        }

        if ($regionIds !== []) {
            $query->whereIn('region_id', $regionIds);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($priceLimit > 0) {
            $query->where('sale_price', '<=', $priceLimit);
        }

        match ($sort) {
            'price-asc' => $query->orderBy('sale_price')->orderByDesc('id'),
            'price-desc' => $query->orderByDesc('sale_price')->orderByDesc('id'),
            'newest' => $query->orderByDesc('created_at')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };

        $products = $query->paginate(15)->withQueryString();

        if ($ratingMin > 0) {
            $products->setCollection($products->getCollection()->filter(fn() => 4.8 >= $ratingMin)->values());
        }

        return view('user-web.storefront.catalog', [
            'products' => $products,
            'categories' => Category::query()->available()->orderBy('name')->get(),
            'suppliers' => Supplier::query()->available()->orderBy('name')->get(),
            'regions' => Region::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => compact('categoryIds', 'supplierIds', 'regionIds', 'priceLimit', 'ratingMin', 'search', 'sort'),
            'catalogView' => $view,
        ]);
    }

    public function product(string $slug): View
    {
        $product = $this->resolveProduct($slug);
        if (!$product) {
            abort(404);
        }
        $relatedProducts = $this->baseProductQuery()
            ->whereKeyNot($product->id)->where(function ($query) use ($product): void {
                $query->where('category_id', $product->category_id)
                    ->orWhere('supplier_id', $product->supplier_id);
            })
            ->take(4)
            ->get();
        return view('user-web.storefront.product-detail', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function story(): View
    {
        /** @var User|null $user */
        $user = Auth::guard('web')->user();

        return view('user-web.storefront.story', [
            'posts' => Post::query()
                ->published()
                ->with(['author:id,full_name,email', 'visibleComments.author:id,full_name,email'])
                ->withCount(['likes', 'visibleComments as comments_count'])
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->paginate(10),
            'likedPostIds' => $user && $user->role === User::ROLE_CUSTOMER
                ? PostLike::query()->where('user_id', $user->id)->pluck('post_id')->all()
                : [],
        ]);
    }

    public function togglePostLike(Request $request, Post $post): RedirectResponse
    {
        $this->assertCustomer();

        /** @var User $user */
        $user = Auth::guard('web')->user();
        $liked = PostLike::query()
            ->where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($liked) {
            PostLike::query()
                ->where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->delete();
        } else {
            PostLike::query()->firstOrCreate([
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);
        }

        return back()->with('status', $liked ? 'Đã bỏ thích bài viết.' : 'Đã thích bài viết.');
    }

    public function comment(Request $request, Post $post): RedirectResponse
    {
        $this->assertCustomer();

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        /** @var User $user */
        $user = Auth::guard('web')->user();

        $post->comments()->create([
            'user_id' => $user->id,
            'content' => $validated['content'],
            'status' => PostComment::STATUS_VISIBLE,
        ]);

        return back()->with('status', 'Đã gửi bình luận.');
    }

    public function regions(): View
    {
        return view('user-web.storefront.regions', [
            'regions' => Region::query()
                ->where('is_active', true)
                ->withCount(['products' => fn($query) => $query->available()])
                ->orderBy('id')
                ->get(),
            'productsByRegion' => Product::query()
                ->available()
                ->with(['category', 'supplier', 'region'])
                ->whereNotNull('region_id')
                ->orderByDesc('id')
                ->get()
                ->groupBy('region_id'),
        ]);
    }

    public function productTest(): View
    {
        return view('user-web.storefront.product-test');
    }

    public function newsletter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:160'],
            'source' => ['nullable', 'string', 'max:80'],
        ]);

        NewsletterSubscription::query()->updateOrCreate([
            'email' => mb_strtolower(trim((string) $validated['email'])),
            'source' => $validated['source'] ?? 'storefront',
        ]);

        return back()->with('status', 'Đã lưu đăng ký bản tin.');
    }

    private function resolveProduct(string $slug): ?Product
    {
        $product = $this->baseProductQuery()->where('slug', $slug)->first();

        if ($product) {
            return $product;
        }

        if (preg_match('/-(\d+)$/', $slug, $matches) !== 1) {
            return null;
        }

        return $this->baseProductQuery()->find((int) $matches[1]);
    }

    private function baseProductQuery()
    {
        return Product::query()
            ->available()
            ->with(['category', 'supplier', 'region']);
    }

    /**
     * @return list<int>
     */
    private function csvIds(mixed $value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn(mixed $item): int => (int) $item)
                ->filter(fn(int $item): bool => $item > 0)
                ->unique()
                ->values()
                ->all();
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn(string $item): int => (int) trim($item))
            ->filter(fn(int $item): bool => $item > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function assertCustomer(): void
    {
        /** @var User|null $user */
        $user = Auth::guard('web')->user();

        if (!$user || $user->role !== User::ROLE_CUSTOMER || !$user->canAuthenticate()) {
            abort(403);
        }
    }
}
