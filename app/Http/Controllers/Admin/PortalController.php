<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Portal;
use App\Models\PortalActivity;
use App\Models\PortalSupportEvent;
use App\Services\ActivityMonitor;
use App\Services\SubscriptionService;
use App\Services\TenantManager;
use App\Services\TenantProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class PortalController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $portals = Portal::query()
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('portal_name', 'like', "%{$q}%")
                  ->orWhere('editor_name', 'like', "%{$q}%")
                  ->orWhere('editor_email', 'like', "%{$q}%")
                  ->orWhere('editor_mobile', 'like', "%{$q}%")
                  ->orWhere('portal_reg_no', 'like', "%{$q}%");
            }))
            ->when(in_array($status, ['active', 'suspended'], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Portal::count(),
            'active' => Portal::where('status', 'active')->count(),
            'suspended' => Portal::where('status', 'suspended')->count(),
            'unprovisioned' => Portal::whereNull('provisioned_at')->count(),
        ];

        return Inertia::render('LegacyPage', ['html' => view('admin.portals.index', compact('portals', 'stats', 'q', 'status'))->render()]);
    }

    public function show(Request $request, Portal $portal, SubscriptionService $subs, ActivityMonitor $monitor)
    {
        $tab = in_array($request->query('tab'), ['overview', 'billing', 'activity'], true) ? $request->query('tab') : 'overview';

        $data = [
            'portal' => $portal,
            'tab' => $tab,
            'info' => $subs->info($portal),
            'alerts' => $monitor->alerts($portal),
            'supportEvents' => PortalSupportEvent::whereHas('supportSession', fn ($query) => $query->where('portal_id', $portal->id))
                ->with('supportSession')
                ->latest('created_at')
                ->limit(20)
                ->get(),
        ];

        if ($tab === 'overview') {
            $data['tableCount'] = null;
            $data['dbError'] = null;

            if ($portal->isProvisioned()) {
                try {
                    TenantManager::connect($portal);
                    $data['tableCount'] = count(DB::connection('tenant')->select('SHOW TABLES'));
                } catch (Throwable $e) {
                    $data['dbError'] = $e->getMessage();
                }
            }
        }

        if ($tab === 'billing') {
            $packages = Package::where('active', true)->orderBy('price')->get();
            $data['packages'] = $packages;
            $data['payments'] = $portal->payments()->with('package')->latest('paid_at')->get();
            $data['charges'] = $portal->charges()->latest()->get();
            $data['extras'] = $subs->extrasBreakdown($portal);
            $data['quotes'] = $packages->mapWithKeys(fn ($p) => [$p->id => $subs->quote($portal, $p)]);
        }

        if ($tab === 'activity') {
            $data['summary'] = $monitor->summary($portal, $monitor->since($request->query('range')));
            $data['logs'] = $monitor->filtered(PortalActivity::where('portal_id', $portal->id), $request)
                ->orderByDesc('created_at')->orderByDesc('id')
                ->paginate(25)->withQueryString();
            $data['ranges'] = ActivityMonitor::RANGES;
        }

        return Inertia::render('LegacyPage', ['html' => view('admin.portals.show', $data)->render()]);
    }

    public function update(Request $request, Portal $portal)
    {
        $data = $request->validate([
            'portal_name' => ['required', 'string', 'max:100'],
            'portal_reg_no' => ['nullable', 'string', 'max:100'],
            'editor_name' => ['nullable', 'string', 'max:100'],
            'editor_email' => ['nullable', 'email', 'max:150'],
            'editor_mobile' => ['required', 'regex:' . Portal::mobileRegex()],
        ]);
        $data['editor_mobile'] = Portal::normalizeMobile($data['editor_mobile']);

        $clash = Portal::where('portal_name', $data['portal_name'])
            ->where('editor_mobile', $data['editor_mobile'])
            ->where('id', '!=', $portal->id)->exists();

        if ($clash) {
            return back()->withInput()->withErrors(['portal_name' => 'Another portal already uses this name + mobile.']);
        }

        $portal->update($data);

        return back()->with('success', 'Portal updated.');
    }

    public function toggleStatus(Portal $portal)
    {
        $portal->update(['status' => $portal->isActive() ? 'suspended' : 'active']);

        return back()->with('success', 'Portal is now ' . $portal->status . '.');
    }

    public function reprovision(Portal $portal, TenantProvisioner $provisioner)
    {
        try {
            $provisioner->provision($portal);
        } catch (Throwable $e) {
            return back()->withErrors(['provision' => 'Provisioning failed: ' . $e->getMessage()]);
        }

        return back()->with('success', 'Tenant database is ready.');
    }

    /** Create (or replace) the public API token. The plain token is flashed once and never stored. */
    public function issueApiToken(Portal $portal)
    {
        if (!$portal->isProvisioned()) {
            return back()->withErrors(['provision' => 'Create the tenant database first, then create the API token.']);
        }

        $token = $portal->issueApiToken();

        return back()->with('success', 'New API token created. Copy it now - it cannot be shown again.')
            ->with('new_api_token', $token);
    }

    public function revokeApiToken(Portal $portal)
    {
        $portal->revokeApiToken();

        return back()->with('success', 'API token revoked. The API is now closed for this portal.');
    }

    public function destroy(Portal $portal, TenantProvisioner $provisioner)
    {
        try {
            $provisioner->destroy($portal);
        } catch (Throwable $e) {
            return back()->withErrors(['provision' => 'Could not drop the tenant database: ' . $e->getMessage()]);
        }

        $portal->delete();

        return redirect()->route('admin.portals.index')->with('success', 'Portal and its database were deleted.');
    }
}
