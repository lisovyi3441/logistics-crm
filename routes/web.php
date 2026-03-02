<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status.update');
    Route::put('/orders/{order}/assign-truck', [OrderController::class, 'assignTruck'])->name('orders.assign_truck');

    // Admin only routes
    Route::resource('companies', \App\Http\Controllers\CompanyController::class)->middleware('role:admin');
    Route::resource('users', \App\Http\Controllers\UserController::class)->middleware('role:admin');
    Route::resource('trucks', \App\Http\Controllers\TruckController::class)->middleware('role:admin');
    Route::get('tariffs', [\App\Http\Controllers\TariffController::class, 'edit'])->name('tariffs.edit')->middleware('role:admin');
    Route::put('tariffs', [\App\Http\Controllers\TariffController::class, 'update'])->name('tariffs.update')->middleware('role:admin');
});

require __DIR__.'/settings.php';
