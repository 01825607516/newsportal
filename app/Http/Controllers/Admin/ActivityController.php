<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portal;
use App\Models\PortalActivity;
use App\Services\ActivityMonitor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityController extends Controller
{
    public function index(Request $request, ActivityMonitor $monitor)
    {
        $query = PortalActivity::with('portal:id,portal_name');

        if ($portalId = $request->query('portal_id')) {
            $query->where('portal_id', $portalId);
        }

        $logs = $monitor->filtered($query, $request)
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate(30)->withQueryString();

        return Inertia::render('LegacyPage', ['html' => view('admin.activity.index', [
            'logs' => $logs,
            'ranges' => ActivityMonitor::RANGES,
            'portals' => Portal::orderBy('portal_name')->limit(300)->get(['id', 'portal_name']),
            'flagged' => $monitor->flaggedPortals(),
        ])->render()]);
    }
}
