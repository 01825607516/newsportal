<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Portal;
use App\Models\Payment;
use App\Models\PortalCharge;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    /** Manual payment: admin received money, now activate / renew the package. */
    public function recordPayment(Request $request, Portal $portal, SubscriptionService $subs)
    {
        $data = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', 'in:cash,bkash,nagad,rocket,bank,other'],
            'trx_id' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $package = Package::findOrFail($data['package_id']);

        if ($package->is_trial && $portal->subscriptions()->whereHas('package', fn ($q) => $q->where('is_trial', true))->exists()) {
            return back()->withInput()->withErrors(['package_id' => 'This portal has already used its one-time trial.']);
        }

        DB::transaction(function () use ($request, $portal, $package, $data, $subs) {
            $subscription = $subs->activate($portal, $package);

            Payment::create([
                'portal_id' => $portal->id,
                'package_id' => $package->id,
                'subscription_id' => $subscription->id,
                'amount' => $data['amount'],
                'method' => $data['method'],
                'trx_id' => $data['trx_id'] ?? null,
                'note' => $data['note'] ?? null,
                'paid_at' => now(),
                'recorded_by' => $request->session()->get('admin_id'),
            ]);

            $subs->markOneTimeChargesPaid($portal);
        });

        return back()->with('success', "{$package->name} activated. Payment recorded.");
    }

    public function addCharge(Request $request, Portal $portal)
    {
        $data = $request->validate([
            'purpose' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:1'],
            'type' => ['required', 'in:one_time,cycle'],
        ]);

        $portal->charges()->create($data);

        return back()->with('success', 'Additional charge added. It will be included in the next payment.');
    }

    public function removeCharge(Portal $portal, PortalCharge $charge)
    {
        abort_unless($charge->portal_id === $portal->id, 404);
        $charge->delete();

        return back()->with('success', 'Charge removed.');
    }

    // ---------- Package settings ----------

    public function packages()
    {
        return view('admin.packages.index', ['packages' => Package::orderBy('is_trial', 'desc')->orderBy('price')->get()]);
    }

    public function updatePackage(Request $request, Package $package)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'grace_days' => ['required', 'integer', 'min:0', 'max:365'],
        ]);
        $data['active'] = $request->boolean('active');

        $package->update($data);

        return back()->with('success', "{$package->name} updated. Existing subscriptions keep their old terms.");
    }
}
