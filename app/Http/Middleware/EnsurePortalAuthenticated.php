<?php

namespace App\Http\Middleware;

use App\Models\Portal;
use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;

class EnsurePortalAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        $id = $request->session()->get('portal_id');
        $portal = $id ? Portal::find($id) : null;

        if (!$portal || !$portal->isActive() || !$portal->isProvisioned()) {
            $request->session()->forget('portal_id');
            return redirect()->route('portal.login')
                ->withErrors(['editor_mobile' => 'Session expired or portal is not active.']);
        }

        // From here on, DB::connection('tenant') is this portal's own database.
        TenantManager::connect($portal);

        $request->attributes->set('portal', $portal);
        view()->share('currentPortal', $portal);

        return $next($request);
    }
}
