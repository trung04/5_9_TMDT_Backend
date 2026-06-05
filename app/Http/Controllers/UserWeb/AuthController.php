<?php

namespace App\Http\Controllers\UserWeb;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\CartService;
use App\Services\GuestCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly GuestCartService $guestCartService,
    ) {
    }

    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectAuthenticatedUser($request)) {
            return $redirect;
        }

        return view('user-web.auth.login', [
            'mode' => 'login',
            'redirect' => $this->safeRedirect((string) $request->query('redirect', '')),
        ]);
    }

    public function showRegister(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectAuthenticatedUser($request)) {
            return $redirect;
        }

        return view('user-web.auth.login', [
            'mode' => 'register',
            'redirect' => $this->safeRedirect((string) $request->query('redirect', '')),
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check((string) $credentials['password'], $user->password_hash)) {
            return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng.'])->withInput($request->only('email'));
        }

        if (! $user->canAuthenticate()) {
            return back()->withErrors(['email' => 'Tài khoản này đã bị khóa hoặc không còn hiệu lực.'])->withInput($request->only('email'));
        }

        if ($user->role === User::ROLE_ADMIN) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            return redirect()->route('admin-web.dashboard');
        }

        if (! in_array($user->role, [User::ROLE_CUSTOMER, User::ROLE_SUPPLIER, User::ROLE_WAREHOUSE_STAFF], true)) {
            return back()->withErrors(['email' => 'Tài khoản này không được phép đăng nhập khu vực web.'])->withInput($request->only('email'));
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        if ($user->role === User::ROLE_CUSTOMER) {
            $this->guestCartService->mergeIntoCustomer($user, $this->cartService);
        }

        return redirect()->to($this->safeRedirectForUser($user, (string) $request->input('redirect', '')));
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password_hash' => Hash::make((string) $validated['password']),
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $this->guestCartService->mergeIntoCustomer($user, $this->cartService);

        return redirect()->to($this->safeRedirectForUser($user, (string) $request->input('redirect', '')));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('user-web.home')->with('status', 'Đã đăng xuất.');
    }

    private function redirectAuthenticatedUser(Request $request): ?RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::guard('web')->user();

        if (! $user) {
            return null;
        }

        if ($user->role === User::ROLE_ADMIN && $user->canAuthenticate()) {
            return redirect()->route('admin-web.dashboard');
        }

        if ($user->role === User::ROLE_CUSTOMER && $user->canAuthenticate()) {
            return redirect()->to($this->safeRedirectForUser($user, (string) $request->query('redirect', '')));
        }

        if (in_array($user->role, [User::ROLE_SUPPLIER, User::ROLE_WAREHOUSE_STAFF], true) && $user->canAuthenticate()) {
            return redirect()->to($this->safeRedirectForUser($user, (string) $request->query('redirect', '')));
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return null;
    }

    private function safeRedirect(string $redirect): string
    {
        if ($redirect === '' || ! str_starts_with($redirect, '/')) {
            return route('user-web.account.profile');
        }

        if (str_starts_with($redirect, '//') || str_starts_with($redirect, '/admin-web')) {
            return route('user-web.account.profile');
        }

        return $redirect;
    }

    private function safeRedirectForUser(User $user, string $redirect): string
    {
        $fallback = match ($user->role) {
            User::ROLE_SUPPLIER => route('user-web.supplier.inventory'),
            User::ROLE_WAREHOUSE_STAFF => route('user-web.warehouse.inventory'),
            default => route('user-web.account.profile'),
        };

        if ($redirect === '' || ! str_starts_with($redirect, '/')) {
            return $fallback;
        }

        if (str_starts_with($redirect, '//') || str_starts_with($redirect, '/admin-web')) {
            return $fallback;
        }

        if ($user->role === User::ROLE_SUPPLIER) {
            return str_starts_with($redirect, '/supplier') ? $redirect : $fallback;
        }

        if ($user->role === User::ROLE_WAREHOUSE_STAFF) {
            return str_starts_with($redirect, '/warehouse') ? $redirect : $fallback;
        }

        if (str_starts_with($redirect, '/supplier') || str_starts_with($redirect, '/warehouse')) {
            return $fallback;
        }

        return $redirect;
    }
}
