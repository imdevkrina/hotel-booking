<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Prices from the Excel data (identical for both room types per the file).
     * Columns: price_1_person | price_2_persons | price_3_persons
     */
    private const PRICES = [
        'Standard' => ['price_1_person' => 1500.00, 'price_2_persons' => 2000.00, 'price_3_persons' => 2500.00],
        'Deluxe'   => ['price_1_person' => 2000.00, 'price_2_persons' => 2500.00, 'price_3_persons' => 3000.00],
    ];

    public function run(): void
    {
        $roomTypes = RoomType::all()->keyBy('name');
        $today     = Carbon::today();
        $records   = [];

        foreach (self::PRICES as $typeName => $prices) {
            $roomType = $roomTypes->get($typeName);

            if (! $roomType) {
                continue;
            }

            for ($i = 0; $i < 30; $i++) {
                $date      = $today->copy()->addDays($i)->format('Y-m-d');
                $records[] = [
                    'room_type_id'    => $roomType->id,
                    'date'            => $date,
                    'price_1_person'  => $prices['price_1_person']+100,
                    'price_2_persons' => $prices['price_2_persons']+50,
                    'price_3_persons' => $prices['price_3_persons']+25,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
        }

        Inventory::upsert(
            $records,
            ['room_type_id', 'date'],
            ['price_1_person', 'price_2_persons', 'price_3_persons', 'updated_at']
        );
    }
}
