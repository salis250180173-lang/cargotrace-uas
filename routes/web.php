<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CarrierController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentExceptionController;
use App\Http\Controllers\TrackingEventController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

// ── Authentication ───────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ── Authenticated Routes ─────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route(auth()->user()->role . '.dashboard');
    })->name('dashboard');

    // Role Dashboards
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::get('/operator/dashboard', [DashboardController::class, 'operator'])
        ->middleware('role:operator')
        ->name('operator.dashboard');

    Route::get('/customer/dashboard', [DashboardController::class, 'customer'])
        ->middleware('role:customer')
        ->name('customer.dashboard');

    // Shipments & Export CSV
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/export/csv', [ShipmentController::class, 'exportCsv'])->name('shipments.export.csv');
    Route::get('/shipments/create', [ShipmentController::class, 'create'])
        ->middleware('role:admin,operator')
        ->name('shipments.create');
    Route::post('/shipments', [ShipmentController::class, 'store'])
        ->middleware('role:admin,operator')
        ->name('shipments.store');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
    Route::get('/shipments/{shipment}/edit', [ShipmentController::class, 'edit'])
        ->middleware('role:admin,operator')
        ->name('shipments.edit');
    Route::put('/shipments/{shipment}', [ShipmentController::class, 'update'])
        ->middleware('role:admin,operator')
        ->name('shipments.update');
    Route::patch('/shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])
        ->middleware('role:admin,operator')
        ->name('shipments.status.update');
    Route::delete('/shipments/{shipment}', [ShipmentController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('shipments.destroy');

    // Tracking
    Route::get('/tracking', [TrackingEventController::class, 'index'])->name('tracking.index');
    Route::post('/shipments/{shipment}/tracking-events', [TrackingEventController::class, 'store'])
        ->middleware('role:admin,operator')
        ->name('shipments.tracking-events.store');

    // Exceptions
    Route::get('/exceptions', [ShipmentExceptionController::class, 'index'])->name('exceptions.index');
    Route::post('/shipments/{shipment}/exceptions', [ShipmentExceptionController::class, 'store'])
        ->middleware('role:admin,operator')
        ->name('shipments.exceptions.store');
    Route::patch('/exceptions/{exception}/status', [ShipmentExceptionController::class, 'updateStatus'])
        ->middleware('role:admin,operator')
        ->name('exceptions.status.update');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    // Admin Master Data, Users & Audit Logs
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('carriers', CarrierController::class)->except(['show']);
        Route::resource('locations', LocationController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });
});
