<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Admin;
use App\Models\GraceRequest;
use App\Models\PurchaseRequest;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    public function handle(Request $request, Closure $next): Response
    {
        $response = parent::handle($request, $next);

        if ($request->routeIs('portal.login', 'portal.signup', 'admin.login')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        return $response;
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name', 'News Portal'),
            'csrfToken' => fn () => $request->session()->token(),
            'billingMethods' => config('billing.methods'),
            'currentAdmin' => fn () => $request->session()->has('admin_id')
                ? Admin::find($request->session()->get('admin_id'))
                : null,
            'currentPortal' => fn () => $request->attributes->get('portal'),
            'pendingRequestCount' => fn () => $request->session()->has('admin_id')
                ? GraceRequest::where('status', 'pending')->count() + PurchaseRequest::where('status', 'pending')->count()
                : 0,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'warning' => fn () => $request->session()->get('warning'),
                'status' => fn () => $request->session()->get('status'),
                'billing_success' => fn () => $request->session()->get('billing_success'),
                'new_api_token' => fn () => $request->session()->get('new_api_token'),
            ],
        ];
    }
}
