<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Portal;
use App\Models\PortalCharge;
use App\Models\Subscription;

class SubscriptionService
{
    /** Give a brand-new portal its one-time trial. Does nothing if it ever had a subscription. */
    public function ensureTrial(Portal $portal): void
    {
        if ($portal->subscriptions()->exists()) {
            return;
        }

        $trial = Package::where('is_trial', true)->where('active', true)->first();

        if ($trial) {
            $this->activate($portal, $trial);
        }
    }

    /**
     * Start a subscription. If the current one is still running, the new one begins the day
     * after it ends (no days are lost on early renewal); otherwise it begins today.
     */
    public function activate(Portal $portal, Package $package): Subscription
    {
        $current = $portal->subscriptions()->orderByDesc('ends_at')->first();
        $start = today();

        if ($current && $current->ends_at->gte($start)) {
            $start = $current->ends_at->copy()->addDay();
        }

        return Subscription::create([
            'portal_id' => $portal->id,
            'package_id' => $package->id,
            'starts_at' => $start,
            'ends_at' => $start->copy()->addDays($package->duration_days - 1),
            'grace_days' => $package->grace_days,
            'price' => $package->price,
        ]);
    }

    /**
     * state: none | active | grace | expired
     * active  = within paid period
     * grace   = period ended but still inside grace days (portal works, warning shown)
     * expired = grace over, login blocked
     */
    public function info(Portal $portal): array
    {
        $sub = $portal->subscriptions()->with('package')->orderByDesc('ends_at')->first();

        if (!$sub) {
            return ['state' => 'none', 'subscription' => null, 'days_left' => null, 'grace_days_left' => null, 'grace_ends_at' => null];
        }

        $today = today();
        $ends = $sub->ends_at->copy()->startOfDay();
        $graceEnds = $ends->copy()->addDays($sub->grace_days);

        $state = 'expired';
        $daysLeft = null;
        $graceLeft = null;

        if ($today->lte($ends)) {
            $state = 'active';
            $daysLeft = (int) $today->diffInDays($ends) + 1;
        } elseif ($today->lte($graceEnds)) {
            $state = 'grace';
            $graceLeft = (int) $today->diffInDays($graceEnds) + 1;
        }

        return [
            'state' => $state,
            'subscription' => $sub,
            'days_left' => $daysLeft,
            'grace_days_left' => $graceLeft,
            'grace_ends_at' => $graceEnds,
        ];
    }

    public function isUsable(Portal $portal): bool
    {
        return in_array($this->info($portal)['state'], ['active', 'grace'], true);
    }

    /** Extra charges that are added on top of the package price at the next payment. */
    public function extrasDue(Portal $portal): float
    {
        return (float) $portal->charges()
            ->where(fn ($q) => $q->where('type', 'cycle')->orWhereNull('paid_at'))
            ->sum('amount');
    }

    public function markOneTimeChargesPaid(Portal $portal): void
    {
        PortalCharge::where('portal_id', $portal->id)
            ->where('type', 'one_time')
            ->whereNull('paid_at')
            ->update(['paid_at' => now()]);
    }
}
