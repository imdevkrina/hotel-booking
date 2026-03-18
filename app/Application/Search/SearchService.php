<?php

declare(strict_types=1);

namespace App\Application\Search;

use App\Domain\Search\Contracts\BookingRepositoryInterface;
use App\Domain\Search\Contracts\DiscountRepositoryInterface;
use App\Domain\Search\Contracts\InventoryRepositoryInterface;
use App\Domain\Search\Contracts\RoomTypeRepositoryInterface;
use App\Domain\Search\DTOs\RoomAvailabilityDTO;
use App\Domain\Search\DTOs\SearchRequestDTO;
use App\Models\Booking;
use App\Models\Discount;
use App\Models\Inventory;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class SearchService
{
    public function __construct(
        private readonly RoomTypeRepositoryInterface  $roomTypeRepository,
        private readonly InventoryRepositoryInterface $inventoryRepository,
        private readonly BookingRepositoryInterface   $bookingRepository,
        private readonly DiscountRepositoryInterface  $discountRepository,
    ) {}

    /**
     * Execute room availability + pricing search.
     *
     * Performance guarantees:
     *  - Exactly 2 DB queries (inventory + bookings); room_types & discounts from cache.
     *  - All availability and pricing computed in memory — no N+1.
     *
     * @return RoomAvailabilityDTO[]
     */
    public function search(SearchRequestDTO $dto): array
    {
        $roomTypes = $this->roomTypeRepository->getAllCached();
        $discounts = $this->discountRepository->getAllActiveCached();

        $dates       = $this->buildDateRange($dto->checkIn, $dto->checkOut);
        $roomTypeIds = $roomTypes->pluck('id')->map(fn ($id) => (int) $id)->all();

        // --- Single query each — no loops ---
        $inventoryIndex = $this->inventoryRepository->getForRoomTypesAndDates($roomTypeIds, $dates);
        $bookingIndex   = $this->bookingRepository->getOverlappingByRoomType($roomTypeIds, $dto->checkIn, $dto->checkOut);

        $nights = count($dates);

        $results = [];
        foreach ($roomTypes as $roomType) {
            $roomTypeId = (int) $roomType->id;

            $available = $this->computeAvailability(
                $roomType,
                $dates,
                $bookingIndex[$roomTypeId] ?? []
            );

            $priceRoomOnly = $this->computeFinalPrice(
                $roomType, $dates, $nights,
                new SearchRequestDTO($dto->checkIn, $dto->checkOut, $dto->guestCount, 'room_only'),
                $inventoryIndex[$roomTypeId] ?? [], $discounts
            );

            $priceBreakfast = $this->computeFinalPrice(
                $roomType, $dates, $nights,
                new SearchRequestDTO($dto->checkIn, $dto->checkOut, $dto->guestCount, 'breakfast_included'),
                $inventoryIndex[$roomTypeId] ?? [], $discounts
            );

            $results[] = new RoomAvailabilityDTO(
                roomTypeId:       $roomTypeId,
                roomType:         $roomType->name,
                available:        $available,
                priceRoomOnly:    $priceRoomOnly['final'],
                priceBreakfast:   $priceBreakfast['final'],
                originalRoomOnly: $priceRoomOnly['original'],
                originalBreakfast:$priceBreakfast['original'],
                discountPercent:  $priceRoomOnly['discount_percent'],
                discountLabels:   $priceRoomOnly['discount_labels'],
                imageUrl:         $roomType->image
                    ? asset('storage/' . $roomType->image)
                    : null,
            );
        }

        return $results;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Generate the list of night dates: [checkIn, checkOut).
     * e.g. checkIn=2026-03-18, checkOut=2026-03-20 → ['2026-03-18', '2026-03-19']
     *
     * @return string[]
     */
    private function buildDateRange(Carbon $checkIn, Carbon $checkOut): array
    {
        $dates   = [];
        $current = $checkIn->copy()->startOfDay();
        $end     = $checkOut->copy()->startOfDay();

        while ($current->lt($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $dates;
    }

    /**
     * Compute minimum available rooms across the full date range.
     * available_rooms = total_rooms − SUM(rooms_booked for overlapping bookings on that date)
     * MIN across all dates → worst-case availability.
     *
     * @param  string[]  $dates
     * @param  Booking[] $bookings  Already filtered to this room type + date window
     */
    private function computeAvailability(RoomType $roomType, array $dates, array $bookings): bool
    {
        $minAvailable = PHP_INT_MAX;

        foreach ($dates as $date) {
            $bookedRooms = 0;

            foreach ($bookings as $booking) {
                $bookingCheckIn  = $booking->check_in instanceof Carbon
                    ? $booking->check_in->format('Y-m-d')
                    : (string) $booking->check_in;

                $bookingCheckOut = $booking->check_out instanceof Carbon
                    ? $booking->check_out->format('Y-m-d')
                    : (string) $booking->check_out;

                // A booking covers `date` when: check_in <= date <= check_out
                if ($bookingCheckIn <= $date && $bookingCheckOut >= $date) {
                    $bookedRooms += (int) $booking->rooms_booked;
                }
            }

            $availableOnDate = (int) $roomType->total_rooms - $bookedRooms;
            $minAvailable    = min($minAvailable, $availableOnDate);
        }

        return $minAvailable > 0;
    }

    /**
     * Compute final price after guest-count-based nightly rate, meal-plan
     * surcharge, and discounts.
     *
     * Pricing formula (per Excel data):
     *   base_per_night = price_X_person  based on guest_count (1 | 2 | 3)
     *   base_total     = SUM(base_per_night) across all nights
     *   meal_adj       = breakfast_surcharge × nights  (if breakfast_included)
     *   subtotal       = base_total + meal_adj
     *
     * Discounts (applied to subtotal):
     *   long_stay      = HIGHEST matching tier percentage (3+ nights → 10%, 6+ nights → 20%)
     *   last_minute    = 5 % if check_in ≤ 3 days away
     *   Both combined  : long_stay + last_minute (if both apply), capped at 90 %
     *
     *   final = subtotal × (1 − total_discount / 100)
     *
     * @param  string[]                 $dates
     * @param  array<string, Inventory> $inventoryByDate
     */
    private function computeFinalPrice(
        RoomType         $roomType,
        array            $dates,
        int              $nights,
        SearchRequestDTO $dto,
        array            $inventoryByDate,
        Collection       $discounts,
    ): array {
        // --- Guest-count-based nightly price ---
        $priceColumn = match (true) {
            $dto->guestCount >= 3 => 'price_3_persons',
            $dto->guestCount === 2 => 'price_2_persons',
            default               => 'price_1_person',
        };

        $basePrice = 0.0;
        foreach ($dates as $date) {
            /** @var Inventory|null $inv */
            $inv = $inventoryByDate[$date] ?? null;
            $basePrice += $inv !== null ? (float) $inv->{$priceColumn} : 0.0;
        }

        // --- Meal plan adjustment ---
        if ($dto->mealPlan === 'breakfast_included') {
            $basePrice += (float) $roomType->breakfast_surcharge * $nights;
        }

        // --- Discount calculation ---
        $daysUntilCheckIn = (int) now()->startOfDay()->diffInDays($dto->checkIn->copy()->startOfDay(), false);

        // Long-stay: tiered — take the HIGHEST applicable percentage (not cumulative between tiers)
        $longStayPercent  = 0.0;
        $lastMinutePercent = 0.0;

        /** @var Discount $discount */
        foreach ($discounts as $discount) {
            if (
                $discount->type === 'long_stay'
                && $discount->min_nights !== null
                && $nights >= $discount->min_nights
            ) {
                // Keep only the highest-tier match (the seeder orders tiers ascending,
                // so iterating and overwriting with a higher value is safe)
                $longStayPercent = max($longStayPercent, (float) $discount->percentage);
            }

            if (
                $discount->type === 'last_minute'
                && $discount->days_before_checkin !== null
                && $daysUntilCheckIn >= 0
                && $daysUntilCheckIn <= $discount->days_before_checkin
            ) {
                $lastMinutePercent = max($lastMinutePercent, (float) $discount->percentage);
            }
        }

        // Combine both discount types; cap at 90 % to avoid zero/negative pricing
        $totalDiscountPercent = min($longStayPercent + $lastMinutePercent, 90.0);

        $finalPrice = $basePrice * (1 - $totalDiscountPercent / 100);

        $labels = [];
        if ($longStayPercent > 0) {
            $labels[] = "Long Stay {$longStayPercent}%";
        }
        if ($lastMinutePercent > 0) {
            $labels[] = "Last Minute {$lastMinutePercent}%";
        }

        return [
            'original'         => round($basePrice, 2),
            'final'            => round($finalPrice, 2),
            'discount_percent' => $totalDiscountPercent,
            'discount_labels'  => $labels,
        ];
    }
}
