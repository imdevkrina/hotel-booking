<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DiscountManagementController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// --- Public ------------------------------------------------------------------
Route::get('/', [SearchController::class, 'index'])->name('search.index');

// --- Auth --------------------------------------------------------------------
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// --- Admin pages -------------------------------------------------------------
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/discounts', [DiscountManagementController::class, 'index'])->name('discounts.index');
});
