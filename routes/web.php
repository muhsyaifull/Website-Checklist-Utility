<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ElectricityController;
use App\Http\Controllers\WaterController;
use App\Http\Controllers\GlampingController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Electricity (Checklist Listrik)
Route::prefix('electricity')->name('electricity.')->group(function () {
    Route::get('/', [ElectricityController::class, 'index'])->name('index');
    Route::get('/create', [ElectricityController::class, 'create'])->name('create');
    Route::post('/', [ElectricityController::class, 'store'])->name('store');
    Route::get('/{date}/edit', [ElectricityController::class, 'edit'])->name('edit');
    Route::put('/{date}', [ElectricityController::class, 'update'])->name('update');
    Route::delete('/{date}', [ElectricityController::class, 'destroy'])->name('destroy');
});

// Water (Checklist Air)
Route::prefix('water')->name('water.')->group(function () {
    Route::get('/', [WaterController::class, 'index'])->name('index');
    Route::get('/create', [WaterController::class, 'create'])->name('create');
    Route::post('/', [WaterController::class, 'store'])->name('store');
    Route::get('/{date}/edit', [WaterController::class, 'edit'])->name('edit');
    Route::put('/{date}', [WaterController::class, 'update'])->name('update');
    Route::delete('/{date}', [WaterController::class, 'destroy'])->name('destroy');
});

// Glamping Token (Checklist Glamping)
Route::prefix('glamping')->name('glamping.')->group(function () {
    Route::get('/', [GlampingController::class, 'index'])->name('index');
    Route::get('/create', [GlampingController::class, 'create'])->name('create');
    Route::post('/', [GlampingController::class, 'store'])->name('store');
    Route::get('/{date}/edit', [GlampingController::class, 'edit'])->name('edit');
    Route::put('/{date}', [GlampingController::class, 'update'])->name('update');
    Route::delete('/{date}', [GlampingController::class, 'destroy'])->name('destroy');
});
