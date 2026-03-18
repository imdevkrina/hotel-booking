<?php

declare(strict_types=1);

namespace App\Domain\Search\Contracts;

use Carbon\Carbon;

interface BookingRepositoryInterface
{
    /**
     * Fetch bookings that overlap with [checkIn, checkOut) for the given
     * room-type IDs in a single query, indexed as [room_type_id => Booking[]].
     *
     * Overlap condition: booking.check_in < checkOut AND booking.check_out > checkIn
     *
     * @param  int[]  $roomTypeIds
     * @return array<int, \App\Models\Booking[]>
     */
    public function getOverlappingByRoomType(array $roomTypeIds, Carbon $checkIn, Carbon $checkOut): array;
}
