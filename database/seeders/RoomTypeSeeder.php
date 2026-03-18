<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $roomTypes = [
            [
                'name'                => 'Standard',
                'total_rooms'         => 5,
                'max_adults'          => 3,
                'breakfast_surcharge' => 200.00,
                'image'               => 'standard-room.jpg',
            ],
            [
                'name'                => 'Deluxe',
                'total_rooms'         => 5,
                'max_adults'          => 3,
                'breakfast_surcharge' => 400.00,
                'image'               => 'deluxe-room.jpg',
            ],
        ];

        foreach ($roomTypes as $data) {
            RoomType::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}
