<?php

use App\Http\Controllers\Admin\ActivityController as AdminActivityController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BillingController as AdminBillingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PortalController as AdminPortalController;
use App\Http\Controllers\Admin\PortalSupportController as AdminPortalSupportController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FallbackController;
use App\Http\Controllers\ImageResizeController;
use App\Http\Controllers\PortalAuthController;
use App\Http\Controllers\PortalBillingController;
use Illuminate\Support\Facades\Route;

Route::get('/media/{path}', [ImageResizeController::class, 'show'])->where('path', '.*')->name('media.show');

// ---------- Portal (tenant) side ----------
Route::get('/login', [PortalAuthController::class, 'showLogin'])->name('portal.login');
Route::post('/login', [PortalAuthController::class, 'login'])->name('portal.login.submit');
Route::get('/signup', [PortalAuthController::class, 'showSignup'])->name('portal.signup');
Route::post('/signup', [PortalAuthController::class, 'signup'])->name('portal.signup.submit');
Route::post('/logout', [PortalAuthController::class, 'logout'])->name('portal.logout');
// Logging out is POST only. Opening /logout as a normal page (typed, bookmarked, restored by Back/Forward)
// just goes to the dashboard, which sends visitors who are not logged in to the login page.
Route::get('/logout', FallbackController::class);

Route::middleware(['portal.auth', 'portal.log'])->group(function () {
    Route::get('/', [PortalBillingController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/project-management', [PortalBillingController::class, 'dashboard'])->name('project-management');

    // Category module (3-level tree in the portal's own database)
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/data', [CategoryController::class, 'data'])->name('categories.data');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->whereNumber('id')->name('categories.update');
    Route::post('/categories/{id}/toggle', [CategoryController::class, 'toggle'])->whereNumber('id')->name('categories.toggle');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->whereNumber('id')->name('categories.destroy');

    Route::post('/subscription/grace', [PortalBillingController::class, 'requestGrace'])->name('portal.grace.request');
    Route::post('/subscription/purchase', [PortalBillingController::class, 'requestPurchase'])->name('portal.purchase.request');
});

// ---------- Admin panel ----------
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::get('/logout', FallbackController::class);

    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/activity', [AdminActivityController::class, 'index'])->name('activity.index');
        Route::get('/portals', [AdminPortalController::class, 'index'])->name('portals.index');
        Route::get('/portals/{portal}', [AdminPortalController::class, 'show'])->name('portals.show');
        Route::post('/portals/{portal}/support-session', [AdminPortalSupportController::class, 'start'])->name('portals.support.start');
        Route::get('/support-sessions/{supportSession}', [AdminPortalSupportController::class, 'show'])->name('support.show');
        Route::post('/support-sessions/{supportSession}/end', [AdminPortalSupportController::class, 'end'])->name('support.end');
        Route::post('/support-sessions/{supportSession}/subscription/grace', [AdminPortalSupportController::class, 'requestGrace'])->name('support.grace.request');
        Route::post('/support-sessions/{supportSession}/subscription/purchase', [AdminPortalSupportController::class, 'requestPurchase'])->name('support.purchase.request');
        Route::put('/portals/{portal}', [AdminPortalController::class, 'update'])->name('portals.update');
        Route::post('/portals/{portal}/toggle', [AdminPortalController::class, 'toggleStatus'])->name('portals.toggle');
        Route::post('/portals/{portal}/provision', [AdminPortalController::class, 'reprovision'])->name('portals.provision');
        Route::delete('/portals/{portal}', [AdminPortalController::class, 'destroy'])->name('portals.destroy');

        // Billing (manual payments)
        Route::post('/portals/{portal}/payments', [AdminBillingController::class, 'recordPayment'])->name('portals.payments.store');
        Route::post('/portals/{portal}/charges', [AdminBillingController::class, 'addCharge'])->name('portals.charges.store');
        Route::delete('/portals/{portal}/charges/{charge}', [AdminBillingController::class, 'removeCharge'])->name('portals.charges.destroy');

        // Portal requests (grace period / renewal)
        Route::get('/requests', [AdminRequestController::class, 'index'])->name('requests.index');
        Route::post('/requests/grace/{graceRequest}/approve', [AdminRequestController::class, 'approveGrace'])->name('requests.grace.approve');
        Route::post('/requests/grace/{graceRequest}/reject', [AdminRequestController::class, 'rejectGrace'])->name('requests.grace.reject');
        Route::post('/requests/purchase/{purchaseRequest}/approve', [AdminRequestController::class, 'approvePurchase'])->name('requests.purchase.approve');
        Route::post('/requests/purchase/{purchaseRequest}/reject', [AdminRequestController::class, 'rejectPurchase'])->name('requests.purchase.reject');

        Route::get('/packages', [AdminBillingController::class, 'packages'])->name('packages.index');
        Route::post('/packages', [AdminBillingController::class, 'storePackage'])->name('packages.store');
        Route::put('/packages/{package}', [AdminBillingController::class, 'updatePackage'])->name('packages.update');
    });
});

// ---------- Anything else ----------
// An address that does not exist lands on the right home page instead of a 404 error page (see FallbackController).
Route::fallback(FallbackController::class);
