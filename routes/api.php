<?php

declare(strict_types=1);

use App\Http\Controllers\Api\DiscountApiController;
use App\Http\Controllers\Api\InventoryApiController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// --- Public API --------------------------------------------------------------
Route::post('/search', [SearchController::class, 'search'])->name('search.results');
Route::post('/book', [BookingController::class, 'store'])->name('booking.store');

// --- Admin API (JSON) --------------------------------------------------------
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/inventory/room-types', [InventoryApiController::class, 'roomTypes']);
    Route::get('/inventory', [InventoryApiController::class, 'index']);
    Route::post('/inventory', [InventoryApiController::class, 'store']);
    Route::put('/inventory/{inventory}', [InventoryApiController::class, 'update']);
    Route::delete('/inventory/{inventory}', [InventoryApiController::class, 'destroy']);

    Route::get('/discounts', [DiscountApiController::class, 'index']);
    Route::post('/discounts', [DiscountApiController::class, 'store']);
    Route::put('/discounts/{discount}', [DiscountApiController::class, 'update']);
    Route::delete('/discounts/{discount}', [DiscountApiController::class, 'destroy']);
});
