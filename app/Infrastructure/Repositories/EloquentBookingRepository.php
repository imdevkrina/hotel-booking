<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Search\Contracts\BookingRepositoryInterface;
use App\Models\Booking;
use Carbon\Carbon;

final class EloquentBookingRepository implements BookingRepositoryInterface
{
    // Bookings are NOT cached — must reflect live data to avoid double-booking.

    public function getOverlappingByRoomType(array $roomTypeIds, Carbon $checkIn, Carbon $checkOut): array
    {
        $checkInStr  = $checkIn->format('Y-m-d');
        $checkOutStr = $checkOut->format('Y-m-d');

        // Single query — overlap condition: booking.check_in < our_check_out
        //                                  AND booking.check_out >= our_check_in
        $bookings = Booking::whereIn('room_type_id', $roomTypeIds)
            ->where('check_in', '<', $checkOutStr)
            ->where('check_out', '>=', $checkInStr)
            ->get();

        // Group in memory — no further DB calls
        $grouped = [];
        foreach ($bookings as $booking) {
            $grouped[(int) $booking->room_type_id][] = $booking;
        }

        return $grouped;
    }
}
