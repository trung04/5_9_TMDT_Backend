<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Requests\Admin\UpdateAdminSettingsRequest;
use App\Services\AdminSettingsService;
use Illuminate\Http\RedirectResponse;

class SettingsController extends AdminWebController
{
    public function __construct(
        \App\Support\AdminNavigation $navigation,
        private readonly AdminSettingsService $settingsService
    ) {
        parent::__construct($navigation);
    }

    public function show()
    {
        return $this->render('admin-web.settings.index', [
            'settings' => $this->settingsService->resolve($this->adminUser()),
            'payload' => $this->settingsService->payload($this->adminUser()),
        ]);
    }

    public function update(UpdateAdminSettingsRequest $request): RedirectResponse
    {
        $this->settingsService->update($this->adminUser(), $request->validated());

        return redirect()
            ->route('admin-web.settings.show')
            ->with('status', 'Đã cập nhật cài đặt quản trị thành công.');
    }
}
