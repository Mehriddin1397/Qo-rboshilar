<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardStatsService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardStatsService $stats)
    {
    }

    public function index(): View
    {
        return view('admin.dashboard', [
            'title' => 'Dashboard',
            'stats' => $this->stats->get(),
        ]);
    }
}
