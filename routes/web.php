<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BillingController as AdminBillingController;
use App\Http\Controllers\Admin\PortalController as AdminPortalController;
use App\Http\Controllers\PortalAuthController;
use Illuminate\Support\Facades\Route;

// ---------- Portal (tenant) side ----------
Route::get('/login', [PortalAuthController::class, 'showLogin'])->name('portal.login');
Route::post('/login', [PortalAuthController::class, 'login'])->name('portal.login.submit');
Route::post('/logout', [PortalAuthController::class, 'logout'])->name('portal.logout');

Route::middleware('portal.auth')->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    Route::view('/dashboard/project-management', 'dashboard')->name('project-management');
});

// ---------- Admin panel ----------
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');

    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/', fn () => redirect()->route('admin.portals.index'))->name('home');
        Route::get('/portals', [AdminPortalController::class, 'index'])->name('portals.index');
        Route::get('/portals/{portal}', [AdminPortalController::class, 'show'])->name('portals.show');
        Route::put('/portals/{portal}', [AdminPortalController::class, 'update'])->name('portals.update');
        Route::post('/portals/{portal}/toggle', [AdminPortalController::class, 'toggleStatus'])->name('portals.toggle');
        Route::post('/portals/{portal}/provision', [AdminPortalController::class, 'reprovision'])->name('portals.provision');
        Route::delete('/portals/{portal}', [AdminPortalController::class, 'destroy'])->name('portals.destroy');

        // Billing (manual payments)
        Route::post('/portals/{portal}/payments', [AdminBillingController::class, 'recordPayment'])->name('portals.payments.store');
        Route::post('/portals/{portal}/charges', [AdminBillingController::class, 'addCharge'])->name('portals.charges.store');
        Route::delete('/portals/{portal}/charges/{charge}', [AdminBillingController::class, 'removeCharge'])->name('portals.charges.destroy');

        Route::get('/packages', [AdminBillingController::class, 'packages'])->name('packages.index');
        Route::put('/packages/{package}', [AdminBillingController::class, 'updatePackage'])->name('packages.update');
    });
});
