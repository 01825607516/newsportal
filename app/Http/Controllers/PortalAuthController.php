<?php

namespace App\Http\Controllers;

use App\Models\Portal;
use App\Services\SubscriptionService;
use App\Services\TenantProvisioner;
use Illuminate\Http\Request;
use Throwable;

class PortalAuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->session()->has('portal_id')) {
            return redirect()->route('dashboard');
        }

        return view('portal.login');
    }

    public function login(Request $request, TenantProvisioner $provisioner, SubscriptionService $subs)
    {
        $data = $request->validate([
            'portal_name' => ['required', 'string', 'max:100'],
            'portal_reg_no' => ['nullable', 'string', 'max:100'],
            'editor_name' => ['nullable', 'string', 'max:100'],
            'editor_email' => ['nullable', 'email', 'max:150'],
            'editor_mobile' => ['required', 'regex:' . Portal::MOBILE_REGEX],
        ], [
            'editor_mobile.regex' => 'Enter a valid Bangladeshi mobile number (e.g. 017XXXXXXXX).',
        ]);

        $mobile = Portal::normalizeMobile($data['editor_mobile']);
        $name = trim($data['portal_name']);

        $portal = Portal::where('portal_name', $name)->where('editor_mobile', $mobile)->first();

        if ($portal && !$portal->isActive()) {
            return back()->withInput()->withErrors([
                'editor_mobile' => 'This portal has been suspended. Please contact the administrator.',
            ]);
        }

        if (!$portal) {
            $portal = Portal::create([
                'portal_name' => $name,
                'editor_mobile' => $mobile,
                'status' => 'active',
            ]);
        }

        // New portals get the one-time 7 day trial automatically
        $subs->ensureTrial($portal);

        if (!$subs->isUsable($portal)) {
            return back()->withInput()->withErrors([
                'editor_mobile' => 'Your subscription has expired. Please contact the administrator to renew.',
            ]);
        }

        // Fill/update optional info if the user gave it
        foreach (['portal_reg_no', 'editor_name', 'editor_email'] as $field) {
            if (!empty($data[$field])) {
                $portal->{$field} = $data[$field];
            }
        }

        // First login (or an earlier failed attempt): create the tenant database
        if (!$portal->isProvisioned()) {
            try {
                $provisioner->provision($portal);
            } catch (Throwable $e) {
                return back()->withInput()->withErrors([
                    'portal_name' => 'Could not set up your portal database right now. Please try again or contact support.',
                ]);
            }
        }

        $portal->last_login_at = now();
        $portal->save();

        $request->session()->regenerate();
        $request->session()->put('portal_id', $portal->id);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('portal_id');
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
