<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends AdminWebController
{
    public function showLogin()
    {
        return view('admin-web.auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = [
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
        ];

        if (! Auth::guard('web')->attempt($credentials, false)) {
            return back()
                ->withErrors(['email' => 'Email hoặc mật khẩu không đúng.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if (! $user->isAdmin()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Chỉ tài khoản quản trị mới có thể đăng nhập tại đây.'])
                ->onlyInput('email');
        }

        if (! $user->canAuthenticate()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Tài khoản của bạn hiện không được phép đăng nhập.'])
                ->onlyInput('email');
        }

        return redirect()->intended(route('admin-web.dashboard'));
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()
            ->route('admin-web.login')
            ->with('status', 'Đăng xuất thành công.');
    }
}
