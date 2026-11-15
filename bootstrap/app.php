<?php

use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Middleware\EnsurePortalAuthenticated;
use App\Http\Middleware\LogPortalActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        // Configure exception reporting here.
    })
    ->create();
