<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExceptionApiController;
use App\Http\Controllers\Api\ResourceApiController;
use App\Http\Controllers\Api\ShipmentApiController;
use App\Http\Controllers\Api\TrackingApiController;
use Illuminate\Support\Facades\Route;

// ── Public Auth ──────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ── Protected Sanctum Routes ─────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Shipments
    Route::get('/shipments', [ShipmentApiController::class, 'index']);
    Route::post('/shipments', [ShipmentApiController::class, 'store'])->middleware('role:admin,operator');
    Route::get('/shipments/{shipment}', [ShipmentApiController::class, 'show']);
    Route::match(['put', 'patch'], '/shipments/{shipment}', [ShipmentApiController::class, 'update'])
        ->middleware('role:admin,operator');
    Route::delete('/shipments/{shipment}', [ShipmentApiController::class, 'destroy'])
        ->middleware('role:admin');

    // Tracking & Exceptions
    Route::get('/shipments/{shipment}/tracking', [TrackingApiController::class, 'index']);
    Route::post('/shipments/{shipment}/tracking', [TrackingApiController::class, 'store'])
        ->middleware('role:admin,operator');

    Route::get('/shipments/{shipment}/exceptions', [ExceptionApiController::class, 'index']);
    Route::post('/shipments/{shipment}/exceptions', [ExceptionApiController::class, 'store'])
        ->middleware('role:admin,operator');
    Route::patch('/exceptions/{exception}', [ExceptionApiController::class, 'update'])
        ->middleware('role:admin,operator');

    // Master Resources
    Route::get('/carriers', [ResourceApiController::class, 'carriers']);
    Route::post('/carriers', [ResourceApiController::class, 'storeCarrier'])->middleware('role:admin');

    Route::get('/locations', [ResourceApiController::class, 'locations']);
    Route::post('/locations', [ResourceApiController::class, 'storeLocation'])->middleware('role:admin');
});
