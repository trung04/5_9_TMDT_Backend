<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalWebAccess
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var User|null $user */
        $user = Auth::guard('web')->user();

        if (! $user) {
            return redirect()->guest(route('user-web.login', [
                'redirect' => $request->fullUrl(),
            ]));
        }

        if (! $user->canAuthenticate()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('user-web.login')
                ->withErrors([
                    'email' => 'Tài khoản này đã bị khóa hoặc không còn hiệu lực.',
                ]);
        }

        if ($user->role === User::ROLE_ADMIN) {
            return redirect()->route('admin-web.dashboard');
        }

        if (! in_array($user->role, $roles, true)) {
            return redirect()->route('user-web.unauthorized');
        }

        return $next($request);
    }
}
