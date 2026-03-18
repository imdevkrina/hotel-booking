<?php

declare(strict_types=1);

namespace App\Domain\Search\Contracts;

interface InventoryRepositoryInterface
{
    /**
     * Fetch inventory records for given room-type IDs and date list in a
     * single bulk query, indexed as [room_type_id => [date_string => Inventory]].
     *
     * @param  int[]    $roomTypeIds
     * @param  string[] $dates          ISO-8601 date strings (Y-m-d)
     * @return array<int, array<string, \App\Models\Inventory>>
     */
    public function getForRoomTypesAndDates(array $roomTypeIds, array $dates): array;
}
