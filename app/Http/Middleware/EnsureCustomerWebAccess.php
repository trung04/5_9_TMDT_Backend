<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerWebAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::guard('web')->user();

        if (! $user) {
            return redirect()->guest(route('user-web.login', [
                'redirect' => $request->fullUrl(),
            ]));
        }

        if ($user->role !== User::ROLE_CUSTOMER || ! $user->canAuthenticate()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('user-web.login')
                ->withErrors([
                    'email' => 'Tài khoản này không được phép truy cập khu vực khách hàng.',
                ]);
        }

        return $next($request);
    }
}
