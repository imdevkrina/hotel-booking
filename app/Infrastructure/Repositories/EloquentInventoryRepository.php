<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Search\Contracts\InventoryRepositoryInterface;
use App\Models\Inventory;
use Illuminate\Support\Facades\Cache;

final class EloquentInventoryRepository implements InventoryRepositoryInterface
{
    /**
     * Short TTL (45 s) — optional micro-cache for high read traffic.
     * Availability computation ignores this cache; only price lookups use it.
     */
    private const CACHE_TTL = 45;

    public function getForRoomTypesAndDates(array $roomTypeIds, array $dates): array
    {
        $cacheKey = 'inventory_' . implode('-', $roomTypeIds) . '_' . ($dates[0] ?? '') . '_' . (end($dates) ?: '');

        $records = Cache::remember($cacheKey, self::CACHE_TTL, static function () use ($roomTypeIds, $dates): object {
            return Inventory::whereIn('room_type_id', $roomTypeIds)
                ->whereIn('date', $dates)
                ->get(['id', 'room_type_id', 'date', 'price_1_person', 'price_2_persons', 'price_3_persons']);
        });

        // Index in memory as [room_type_id => [date_string => Inventory]]
        $indexed = [];
        foreach ($records as $record) {
            $dateKey = is_string($record->date)
                ? $record->date
                : $record->date->format('Y-m-d');

            $indexed[(int) $record->room_type_id][$dateKey] = $record;
        }

        return $indexed;
    }
}
