<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;

class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        $id = $request->session()->get('admin_id');
        $admin = $id ? Admin::find($id) : null;

        if (!$admin) {
            $request->session()->forget('admin_id');
            return redirect()->route('admin.login');
        }

        view()->share('currentAdmin', $admin);

        return $next($request);
    }
}
