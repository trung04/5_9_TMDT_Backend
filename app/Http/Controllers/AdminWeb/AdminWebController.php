<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminNavigation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

abstract class AdminWebController extends Controller
{
    public function __construct(protected readonly AdminNavigation $navigation)
    {
    }

    protected function adminUser(): User
    {
        /** @var User $user */
        $user = Auth::guard('web')->user();

        $user->loadMissing(['createdByAdmin', 'adminSetting']);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function render(string $view, array $data = []): View
    {
        $user = $this->adminUser();

        return view($view, array_merge([
            'adminUser' => $user,
            'adminNavSections' => $this->navigation->accessibleSections($user),
            'adminNavLabels' => $this->navigation->sections(),
            'currentRouteName' => request()->route()?->getName(),
        ], $data));
    }
}
