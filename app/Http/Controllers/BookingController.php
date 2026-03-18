<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $checkIn  = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $roomTypeId = (int) $validated['room_type_id'];

        return DB::transaction(function () use ($validated, $checkIn, $checkOut, $roomTypeId) {
            // Lock the room type row to prevent concurrent overbooking
            $roomType = RoomType::lockForUpdate()->findOrFail($roomTypeId);

            // Check availability across every night in the range
            $current = $checkIn->copy();
            while ($current->lt($checkOut)) {
                $date = $current->format('Y-m-d');

                $bookedRooms = Booking::where('room_type_id', $roomTypeId)
                    ->where('check_in', '<=', $date)
                    ->where('check_out', '>=', $date)
                    ->sum('rooms_booked');

                if (($roomType->total_rooms - $bookedRooms) < 1) {
                    return response()->json([
                        'success' => false,
                        'message' => "Sorry, {$roomType->name} is fully booked for " . Carbon::parse($date)->format('M j, Y') . '. Please choose different dates.',
                    ], 409);
                }

                $current->addDay();
            }

            $booking = Booking::create([
                'room_type_id' => $roomTypeId,
                'check_in'     => $validated['check_in_date'],
                'check_out'    => $validated['check_out_date'],
                'guest_count'  => $validated['guest_count'],
                'meal_plan'    => $validated['meal_plan'],
                'rooms_booked' => 1,
            ]);

            return response()->json([
                'success'    => true,
                'booking_id' => $booking->id,
                'message'    => 'Booking confirmed successfully!',
            ], 201);
        });
    }
}
