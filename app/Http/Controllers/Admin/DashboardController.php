<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portal;
use App\Models\PortalActivity;
use App\Services\ActivityMonitor;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(ActivityMonitor $monitor)
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total' => Portal::count(),
                'active' => Portal::where('status', 'active')->count(),
                'suspended' => Portal::where('status', 'suspended')->count(),
                'requests24' => PortalActivity::where('event', 'request')->where('created_at', '>=', now()->subDay())->count(),
            ],
            'flagged' => $monitor->flaggedPortals(),
            'recentPortals' => Portal::latest()->limit(5)->get(),
            'recent' => PortalActivity::with('portal:id,portal_name')->orderByDesc('created_at')->orderByDesc('id')->limit(10)->get(),
        ]);
    }
}
