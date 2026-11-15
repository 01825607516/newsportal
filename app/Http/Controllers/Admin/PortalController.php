<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Portal;
use App\Services\TenantManager;
use App\Services\SubscriptionService;
use App\Services\TenantProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        return view('admin.portals.index', compact('portals', 'stats', 'q', 'status'));
    }

    public function show(Portal $portal, SubscriptionService $subs)
    {
        $tableCount = null;
        $dbError = null;

        if ($portal->isProvisioned()) {
            try {
                TenantManager::connect($portal);
                $tableCount = count(DB::connection('tenant')->select('SHOW TABLES'));
            } catch (Throwable $e) {
                $dbError = $e->getMessage();
            }
        }

        $info = $subs->info($portal);
        $packages = Package::where('active', true)->orderBy('price')->get();
        $payments = $portal->payments()->with('package')->latest('paid_at')->get();
        $charges = $portal->charges()->latest()->get();
        $extras = $subs->extrasBreakdown($portal);
        $quotes = $packages->mapWithKeys(fn ($p) => [$p->id => $subs->quote($portal, $p)]);

        return view('admin.portals.show', compact('portal', 'tableCount', 'dbError', 'info', 'packages', 'payments', 'charges', 'extras', 'quotes'));
    }

    public function update(Request $request, Portal $portal)
    {
        $data = $request->validate([
            'portal_name' => ['required', 'string', 'max:100'],
            'portal_reg_no' => ['nullable', 'string', 'max:100'],
            'editor_name' => ['nullable', 'string', 'max:100'],
            'editor_email' => ['nullable', 'email', 'max:150'],
            'editor_mobile' => ['required', 'regex:' . Portal::MOBILE_REGEX],
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
