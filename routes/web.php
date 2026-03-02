<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ElectricityController;
use App\Http\Controllers\WaterController;
use App\Http\Controllers\GlampingController;
use App\Http\Controllers\ExportController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Export PDF Routes
Route::prefix('export')->name('export.')->group(function () {
    Route::get('/electricity', [ExportController::class, 'electricityPdf'])->name('electricity');
    Route::get('/water', [ExportController::class, 'waterPdf'])->name('water');
    Route::get('/glamping', [ExportController::class, 'glampingPdf'])->name('glamping');

    // Export Excel Routes
    Route::get('/electricity-excel', [ExportController::class, 'electricityExcel'])->name('electricity.excel');
    Route::get('/water-excel', [ExportController::class, 'waterExcel'])->name('water.excel');
    Route::get('/glamping-excel', [ExportController::class, 'glampingExcel'])->name('glamping.excel');
});

// Checklist Listrik
Route::prefix('electricity')->name('electricity.')->group(function () {
    Route::get('/', [ElectricityController::class, 'index'])->name('index');
    Route::get('/create', [ElectricityController::class, 'create'])->name('create');
    Route::post('/', [ElectricityController::class, 'store'])->name('store');
    Route::get('/{date}/edit', [ElectricityController::class, 'edit'])->name('edit');
    Route::put('/{date}', [ElectricityController::class, 'update'])->name('update');
    Route::delete('/{date}', [ElectricityController::class, 'destroy'])->name('destroy');
});

// Checklist Air
Route::prefix('water')->name('water.')->group(function () {
    Route::get('/', [WaterController::class, 'index'])->name('index');
    Route::get('/create', [WaterController::class, 'create'])->name('create');
    Route::post('/', [WaterController::class, 'store'])->name('store');
    Route::get('/{date}/edit', [WaterController::class, 'edit'])->name('edit');
    Route::put('/{date}', [WaterController::class, 'update'])->name('update');
    Route::delete('/{date}', [WaterController::class, 'destroy'])->name('destroy');
});

// Checklist Glamping
Route::prefix('glamping')->name('glamping.')->group(function () {
    Route::get('/', [GlampingController::class, 'index'])->name('index');
    Route::get('/create', [GlampingController::class, 'create'])->name('create');
    Route::post('/', [GlampingController::class, 'store'])->name('store');
    Route::get('/{date}/edit', [GlampingController::class, 'edit'])->name('edit');
    Route::put('/{date}', [GlampingController::class, 'update'])->name('update');
    Route::delete('/{date}', [GlampingController::class, 'destroy'])->name('destroy');
});
