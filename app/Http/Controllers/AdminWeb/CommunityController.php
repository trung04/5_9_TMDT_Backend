<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Requests\Admin\StoreSupplierInvitationRequest;
use App\Services\AdminInsightService;
use Illuminate\Http\RedirectResponse;

class CommunityController extends AdminWebController
{
    public function __construct(
        \App\Support\AdminNavigation $navigation,
        private readonly AdminInsightService $adminInsightService
    ) {
        parent::__construct($navigation);
    }

    public function index()
    {
        return $this->render('admin-web.community.index', [
            'payload' => $this->adminInsightService->communityPayload(),
        ]);
    }

    public function createInvitation()
    {
        return $this->render('admin-web.community.invitations.create');
    }

    public function storeInvitation(StoreSupplierInvitationRequest $request): RedirectResponse
    {
        $this->adminInsightService->createSupplierInvitation(
            $request->validated(),
            $this->adminUser(),
        );

        return redirect()->to(
            $this->adminUser()->hasAdminPermission('admin.community.view')
                ? route('admin-web.community.index')
                : route('admin-web.community.invitations.create')
        )
            ->with('status', 'Đã tạo lời mời nhà cung cấp thành công.');
    }
}
