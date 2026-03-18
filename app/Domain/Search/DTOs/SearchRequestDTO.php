<?php

declare(strict_types=1);

namespace App\Domain\Search\DTOs;

use Carbon\Carbon;

final readonly class SearchRequestDTO
{
    public function __construct(
        public Carbon $checkIn,
        public Carbon $checkOut,
        public int    $guestCount,
        public string $mealPlan,
    ) {}
}
