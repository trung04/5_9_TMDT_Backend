<?php

namespace App\Http\Controllers\AdminWeb;

use App\Services\AdminInsightService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends AdminWebController
{
    public function __construct(
        \App\Support\AdminNavigation $navigation,
        private readonly AdminInsightService $adminInsightService
    ) {
        parent::__construct($navigation);
    }

    public function home(Request $request): RedirectResponse|\Illuminate\Contracts\View\View
    {
        return $this->show($request);
    }

    public function show(Request $request)
    {
        return $this->render('admin-web.dashboard.index', [
            'payload' => $this->adminInsightService->dashboardPayload([
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
                'chart_range' => $request->query('chart_range'),
            ], $this->adminUser()),
        ]);
    }
}
