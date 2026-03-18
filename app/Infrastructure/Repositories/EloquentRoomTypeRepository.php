<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Search\Contracts\RoomTypeRepositoryInterface;
use App\Models\RoomType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class EloquentRoomTypeRepository implements RoomTypeRepositoryInterface
{
    private const CACHE_KEY = 'room_types_all';
    private const CACHE_TTL = 3600; // 1 hour — static data, safe to cache

    public function getAllCached(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, static fn () => RoomType::all());
    }
}
