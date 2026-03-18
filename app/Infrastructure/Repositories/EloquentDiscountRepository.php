<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Search\Contracts\DiscountRepositoryInterface;
use App\Models\Discount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class EloquentDiscountRepository implements DiscountRepositoryInterface
{
    private const CACHE_KEY = 'discounts_active';
    private const CACHE_TTL = 3600; // 1 hour — discount rules change infrequently

    public function getAllActiveCached(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, static fn () => Discount::where('active', true)->get());
    }
}
