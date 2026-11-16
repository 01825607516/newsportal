<?php

namespace App\Http\Controllers;

use App\Models\Portal;
use App\Services\ActivityMonitor;
use App\Services\SubscriptionService;
use App\Services\TenantProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

    public function login(Request $request, TenantProvisioner $provisioner, SubscriptionService $subs, ActivityMonitor $monitor)
    {
        $data = $request->validate([
            'portal_name' => ['required', 'string', 'max:100'],
            'portal_reg_no' => ['nullable', 'string', 'max:100'],
            'editor_name' => ['nullable', 'string', 'max:100'],
            'editor_email' => ['nullable', 'email', 'max:150'],
            'editor_mobile' => ['required', 'regex:' . Portal::mobileRegex()],
            'password' => ['required', 'string', 'min:6', 'max:100'],
            'password_confirmation' => ['nullable', 'string'],
        ], [
            'editor_mobile.regex' => 'Enter a valid Bangladeshi mobile number (e.g. 017XXXXXXXX).',
        ]);

        $mobile = Portal::normalizeMobile($data['editor_mobile']);
        $name = trim($data['portal_name']);

        $portal = Portal::where('portal_name', $name)->where('editor_mobile', $mobile)->first();

        // Existing portal with a password: verify it.
        if ($portal && $portal->password !== null && !Hash::check($data['password'], $portal->password)) {
            $monitor->log($request, $portal, 'login_failed', 401);
            return back()->withInput($request->except('password', 'password_confirmation'))->withErrors([
                'password' => 'Incorrect password.',
            ]);
        }

        // New portal, or an old portal that has no password yet: the password is being set now,
        // so it must be confirmed.
        $settingPassword = !$portal || $portal->password === null;
        if ($settingPassword && ($data['password_confirmation'] ?? '') !== $data['password']) {
            return back()->withInput($request->except('password', 'password_confirmation'))->withErrors([
                'password_confirmation' => 'Password and confirm password do not match.',
            ]);
        }

        if ($portal && !$portal->isActive()) {
            $monitor->log($request, $portal, 'login_blocked', 403);
            return back()->withInput($request->except('password', 'password_confirmation'))->withErrors([
                'editor_mobile' => 'This portal has been suspended. Please contact the administrator.',
            ]);
        }

        if (!$portal) {
            $portal = Portal::create([
                'portal_name' => $name,
                'editor_mobile' => $mobile,
                'password' => $data['password'],
                'status' => 'active',
            ]);
        } elseif ($portal->password === null) {
            $portal->password = $data['password'];
            $portal->save();
        }

        // New portals get the one-time 7 day trial automatically
        $subs->ensureTrial($portal);

        if (!$subs->isUsable($portal)) {
            $monitor->log($request, $portal, 'login_blocked', 403);
            return back()->withInput($request->except('password', 'password_confirmation'))->withErrors([
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
                return back()->withInput($request->except('password', 'password_confirmation'))->withErrors([
                    'portal_name' => 'Could not set up your portal database right now. Please try again or contact support.',
                ]);
            }
        }

        $portal->last_login_at = now();
        $portal->save();

        $request->session()->regenerate();
        $request->session()->put('portal_id', $portal->id);
        $monitor->log($request, $portal, 'login', 302);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('portal_id');
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
