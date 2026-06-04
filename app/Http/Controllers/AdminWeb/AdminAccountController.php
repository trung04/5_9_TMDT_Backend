<?php

namespace App\Http\Controllers\AdminWeb;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminAccountController extends AdminWebController
{
    public function index(Request $request)
    {
        $query = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->with('createdByAdmin')
            ->orderByDesc('id');

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->query('keyword'));

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('full_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        return $this->render('admin-web.admins.index', [
            'admins' => $query
                ->paginate((int) $request->integer('per_page', 20))
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        return $this->render('admin-web.admins.create', [
            'admin' => new User([
                'is_active' => true,
                'is_deleted' => false,
            ]),
        ]);
    }

    public function edit(User $admin)
    {
        $admin = $this->findAdmin($admin->id);

        return $this->render('admin-web.admins.edit', [
            'admin' => $admin,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $admin = User::query()->create([
            'full_name' => $validated['full_name'],
            'email' => Str::lower($validated['email']),
            'phone' => $validated['phone'],
            'password_hash' => Hash::make($validated['password']),
            'role' => User::ROLE_ADMIN,
            'admin_role_id' => null,
            'created_by_admin_id' => $this->adminUser()->id,
            'is_active' => $validated['is_active'] ?? true,
            'is_deleted' => $validated['is_deleted'] ?? false,
        ]);

        return redirect()
            ->route('admin-web.admins.edit', $admin)
            ->with('status', 'Đã tạo tài khoản admin thành công.');
    }

    public function update(Request $request, User $admin): RedirectResponse
    {
        $admin = $this->findAdmin($admin->id);
        $validated = $request->validate($this->rules($admin->id, true));
        $nextIsActive = $validated['is_active'] ?? $admin->is_active;
        $nextIsDeleted = $validated['is_deleted'] ?? ($nextIsActive ? false : $admin->is_deleted);

        $this->assertNotRemovingLastActiveAdmin($admin, [
            'is_active' => $nextIsActive,
            'is_deleted' => $nextIsDeleted,
        ]);

        $admin->update([
            'full_name' => $validated['full_name'],
            'email' => Str::lower($validated['email']),
            'phone' => $validated['phone'],
            'is_active' => $nextIsActive,
            'is_deleted' => $nextIsDeleted,
        ]);

        return redirect()
            ->route('admin-web.admins.edit', $admin)
            ->with('status', 'Đã cập nhật tài khoản admin thành công.');
    }

    public function updateStatus(Request $request, User $admin): RedirectResponse
    {
        $admin = $this->findAdmin($admin->id);
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
            'is_deleted' => ['nullable', 'boolean'],
        ]);
        $nextIsDeleted = $validated['is_deleted'] ?? ($validated['is_active'] ? false : $admin->is_deleted);

        $this->assertNotRemovingLastActiveAdmin($admin, [
            'is_active' => $validated['is_active'],
            'is_deleted' => $nextIsDeleted,
        ]);

        $admin->update([
            'is_active' => $validated['is_active'],
            'is_deleted' => $nextIsDeleted,
        ]);

        return back()->with('status', 'Đã cập nhật trạng thái tài khoản admin thành công.');
    }

    public function updatePassword(Request $request, User $admin): RedirectResponse
    {
        $admin = $this->findAdmin($admin->id);
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $admin->update([
            'password_hash' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'Đã cập nhật mật khẩu tài khoản admin thành công.');
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
            'password' => [$updating ? 'sometimes' : 'required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
            'is_deleted' => ['nullable', 'boolean'],
        ];
    }

    private function findAdmin(int $id): User
    {
        $admin = User::query()
            ->whereKey($id)
            ->where('role', User::ROLE_ADMIN)
            ->with('createdByAdmin')
            ->first();

        abort_if(! $admin, 404, 'Không tìm thấy tài khoản admin.');

        return $admin;
    }

    /**
     * @param  array<string, mixed>  $nextState
     */
    private function assertNotRemovingLastActiveAdmin(User $admin, array $nextState): void
    {
        $willRemainActive = (bool) ($nextState['is_active'] ?? $admin->is_active)
            && ! (bool) ($nextState['is_deleted'] ?? $admin->is_deleted);

        if ($willRemainActive || $this->activeAdminCount() > 1) {
            return;
        }

        throw ValidationException::withMessages([
            'admin' => ['Không thể vô hiệu hóa tài khoản admin đang hoạt động cuối cùng.'],
        ]);
    }

    private function activeAdminCount(): int
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN)
            ->available()
            ->count();
    }
}
