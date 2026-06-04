<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

class AdminNavigation
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function modules(): array
    {
        return config('admin_navigation.modules', []);
    }

    /**
     * @return array<string, string>
     */
    public function sections(): array
    {
        return config('admin_navigation.sections', []);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function accessibleModules(User $user): array
    {
        return array_values(array_filter(
            $this->modules(),
            fn (array $module): bool => $this->canAccess($user, $module)
        ));
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function accessibleSections(User $user): array
    {
        $sections = [];

        foreach ($this->accessibleModules($user) as $module) {
            $section = (string) ($module['section'] ?? 'overview');
            $sections[$section] ??= [];
            $sections[$section][] = $this->withResolvedUrl($module);
        }

        return $sections;
    }

    public function canAccess(User $user, array $module): bool
    {
        return $user->isAdmin() && $user->canAuthenticate();
    }

    public function firstAccessibleModule(User $user): ?array
    {
        foreach ($this->modules() as $module) {
            if ($this->canAccess($user, $module)) {
                return $this->withResolvedUrl($module);
            }
        }

        return null;
    }

    public function firstAccessibleUrl(User $user, ?string $fallback = null): string
    {
        $module = $this->firstAccessibleModule($user);

        if ($module && is_string($module['url'] ?? null) && $module['url'] !== '') {
            return $module['url'];
        }

        return $fallback ?? '/';
    }

    private function withResolvedUrl(array $module): array
    {
        $routeName = (string) ($module['route'] ?? '');

        $module['url'] = $routeName !== '' && Route::has($routeName)
            ? route($routeName)
            : '#';

        return $module;
    }
}
