<?php

namespace App\Http\Controllers\AdminWeb;

use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends AdminWebController
{
    public function __construct(
        \App\Support\AdminNavigation $navigation,
        private readonly OrderService $orderService
    ) {
        parent::__construct($navigation);
    }

    public function index(Request $request)
    {
        return $this->render('admin-web.users.index', [
            'users' => $this->customerQuery($request)
                ->paginate((int) $request->integer('per_page', 20))
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        return $this->render('admin-web.users.create', [
            'user' => new User([
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $user = User::query()->create([
            ...$this->profileAttributes($validated),
            'email' => Str::lower($validated['email']),
            'password_hash' => Hash::make($validated['password']),
            'role' => User::ROLE_CUSTOMER,
            'is_active' => $validated['is_active'] ?? true,
            'is_deleted' => false,
        ]);

        return redirect()->to(
            $this->adminUser()->hasAdminPermission('admin.users.update')
                ? route('admin-web.users.edit', $user)
                : ($this->adminUser()->hasAdminPermission('admin.users.view')
                    ? route('admin-web.users.index')
                    : route('admin-web.users.create'))
        )
            ->with('status', 'Đã tạo tài khoản khách hàng thành công.');
    }

    public function edit(User $user)
    {
        $this->ensureCustomerAccount($user);

        return $this->render('admin-web.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureCustomerAccount($user);

        $validated = $request->validate($this->rules($user->id, true));
        $nextIsActive = $validated['is_active'] ?? $user->is_active;
        $nextIsDeleted = $validated['is_deleted'] ?? ($nextIsActive ? false : $user->is_deleted);

        $user->update([
            ...$this->profileAttributes($validated),
            'email' => Str::lower($validated['email']),
            'is_active' => $nextIsActive,
            'is_deleted' => $nextIsDeleted,
        ]);

        return redirect()
            ->route('admin-web.users.edit', $user)
            ->with('status', 'Đã cập nhật tài khoản khách hàng thành công.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureCustomerAccount($user);
        $user->markInactive();

        return redirect()
            ->route('admin-web.users.index')
            ->with('status', 'Đã ngừng kích hoạt tài khoản khách hàng thành công.');
    }

    public function orders(Request $request, User $user)
    {
        $this->ensureCustomerAccount($user);

        return $this->render('admin-web.users.orders', [
            'customer' => $user->loadCount('orders'),
            'orders' => $this->orderService->listAdminOrders([
                'user_id' => $user->id,
                'status' => $request->query('status'),
                'keyword' => $request->query('keyword'),
            ], (int) $request->integer('per_page', 10)),
        ]);
    }

    private function customerQuery(Request $request)
    {
        $query = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->withCount('orders')
            ->orderByDesc('id');

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->query('keyword'));

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('full_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        return $query;
    }

    private function rules(?int $ignoreUserId = null, bool $updating = false): array
    {
        return [
            'full_name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'string',
                'email',
                'max:120',
                Rule::unique('users', 'email')->ignore($ignoreUserId),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($ignoreUserId),
            ],
            'password' => [$updating ? 'prohibited' : 'required', 'string', 'min:8'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'favorite_region' => ['nullable', 'string', 'max:120'],
            'avatar_url' => ['nullable', 'string', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'is_deleted' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function profileAttributes(array $validated): array
    {
        return [
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'favorite_region' => $validated['favorite_region'] ?? null,
            'avatar_url' => $validated['avatar_url'] ?? null,
        ];
    }

    private function ensureCustomerAccount(User $user): void
    {
        abort_if($user->role !== User::ROLE_CUSTOMER, 404, 'Chỉ có thể quản lý tài khoản khách hàng tại màn hình này.');
    }
}
