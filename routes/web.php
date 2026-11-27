<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $role = auth()->user()->role;

        return redirect()->route("{$role}.dashboard");
    })->name('dashboard');

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', fn () => response('Admin Dashboard'))->name('dashboard');
    });

    Route::middleware(['role:operator'])->prefix('operator')->name('operator.')->group(function () {
        Route::get('/dashboard', fn () => response('Operator Dashboard'))->name('dashboard');
    });

    Route::middleware(['role:customer'])->prefix('customer')->name('customer.')->group(function () {
        Route::get('/dashboard', fn () => response('Customer Dashboard'))->name('dashboard');
    });
});
