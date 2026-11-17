<?php

use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Middleware\EnsurePortalAuthenticated;
use App\Http\Middleware\LogPortalActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php')
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'portal.auth' => EnsurePortalAuthenticated::class,
            'admin.auth' => EnsureAdminAuthenticated::class,
            'portal.log' => LogPortalActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // A dashboard left open longer than the session lifetime fails the CSRF check (419) when the
        // person clicks Sign out. Logging out is what they wanted anyway, so go to the login page.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() === 419 && $request->routeIs('portal.logout')) {
                $request->session()->forget('portal_id');

                return redirect()->route('portal.login');
            }
        });
    })
    ->create();
