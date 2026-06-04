<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminWebAccess
{
    public function handle(Request $request, Closure $next, string ...$permissionKeys): Response
    {
        /** @var User|null $user */
        $user = Auth::guard('web')->user();

        if (! $user) {
            return redirect()->guest(route('admin-web.login'));
        }

        if (! $user->isAdmin() || ! $user->canAuthenticate()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin-web.login')
                ->withErrors([
                    'email' => 'Your account is not allowed to access the admin workspace.',
                ]);
        }

        return $next($request);
    }
}
