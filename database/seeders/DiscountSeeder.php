<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        // Clear and re-seed so tiers are always consistent
        Discount::truncate();

        $discounts = [
            // Long stay tier 1: 3+ nights → 10 %  (from Excel row 2)
            [
                'type'                => 'long_stay',
                'min_nights'          => 3,
                'days_before_checkin' => null,
                'percentage'          => 10.00,
                'active'              => true,
            ],
            // Long stay tier 2: 6+ nights → 20 %  (from Excel row 3; supersedes tier 1)
            [
                'type'                => 'long_stay',
                'min_nights'          => 6,
                'days_before_checkin' => null,
                'percentage'          => 20.00,
                'active'              => true,
            ],
            // Last minute: check-in within 3 days → 5 %  (from Excel row 6)
            [
                'type'                => 'last_minute',
                'min_nights'          => null,
                'days_before_checkin' => 3,
                'percentage'          => 5.00,
                'active'              => true,
            ],
        ];

        Discount::insert($discounts);
    }
}
