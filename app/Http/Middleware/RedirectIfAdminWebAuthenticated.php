<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\AdminNavigation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdminWebAuthenticated
{
    public function __construct(private readonly AdminNavigation $navigation)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::guard('web')->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->isAdmin() || ! $user->canAuthenticate()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $next($request);
        }

        return redirect()->to(
            $this->navigation->firstAccessibleUrl($user, route('admin-web.dashboard'))
        );
    }
}
