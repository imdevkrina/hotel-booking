<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Search\Contracts\BookingRepositoryInterface;
use App\Domain\Search\Contracts\DiscountRepositoryInterface;
use App\Domain\Search\Contracts\InventoryRepositoryInterface;
use App\Domain\Search\Contracts\RoomTypeRepositoryInterface;
use App\Infrastructure\Repositories\EloquentBookingRepository;
use App\Infrastructure\Repositories\EloquentDiscountRepository;
use App\Infrastructure\Repositories\EloquentInventoryRepository;
use App\Infrastructure\Repositories\EloquentRoomTypeRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bind domain interfaces to their Eloquent implementations.
     * Swapping persistence layers requires changes ONLY here.
     */
    public function register(): void
    {
        $this->app->bind(RoomTypeRepositoryInterface::class,  EloquentRoomTypeRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, EloquentInventoryRepository::class);
        $this->app->bind(BookingRepositoryInterface::class,   EloquentBookingRepository::class);
        $this->app->bind(DiscountRepositoryInterface::class,  EloquentDiscountRepository::class);
    }

    public function boot(): void
    {
        //
    }
}

