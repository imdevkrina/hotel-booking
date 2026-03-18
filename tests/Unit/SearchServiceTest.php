<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application\Search\SearchService;
use App\Domain\Search\Contracts\BookingRepositoryInterface;
use App\Domain\Search\Contracts\DiscountRepositoryInterface;
use App\Domain\Search\Contracts\InventoryRepositoryInterface;
use App\Domain\Search\Contracts\RoomTypeRepositoryInterface;
use App\Domain\Search\DTOs\SearchRequestDTO;
use App\Models\Booking;
use App\Models\Discount;
use App\Models\Inventory;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class SearchServiceTest extends TestCase
{
    private SearchService $service;
    private $roomTypeRepo;
    private $inventoryRepo;
    private $bookingRepo;
    private $discountRepo;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-03-18'));

        $this->roomTypeRepo  = Mockery::mock(RoomTypeRepositoryInterface::class);
        $this->inventoryRepo = Mockery::mock(InventoryRepositoryInterface::class);
        $this->bookingRepo   = Mockery::mock(BookingRepositoryInterface::class);
        $this->discountRepo  = Mockery::mock(DiscountRepositoryInterface::class);

        $this->service = new SearchService(
            $this->roomTypeRepo,
            $this->inventoryRepo,
            $this->bookingRepo,
            $this->discountRepo,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Carbon::setTestNow();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    //  Helper builders
    // -------------------------------------------------------------------------

    private function makeRoomType(array $attrs = []): RoomType
    {
        $rt = new RoomType();
        $rt->id = $attrs['id'] ?? 1;
        $rt->name = $attrs['name'] ?? 'Standard';
        $rt->total_rooms = $attrs['total_rooms'] ?? 5;
        $rt->max_adults = $attrs['max_adults'] ?? 3;
        $rt->breakfast_surcharge = $attrs['breakfast_surcharge'] ?? 200.00;
        $rt->image = $attrs['image'] ?? null;
        return $rt;
    }

    private function makeInventory(int $roomTypeId, string $date, float $p1, float $p2, float $p3): Inventory
    {
        $inv = new Inventory();
        $inv->room_type_id = $roomTypeId;
        $inv->date = $date;
        $inv->price_1_person = $p1;
        $inv->price_2_persons = $p2;
        $inv->price_3_persons = $p3;
        return $inv;
    }

    private function makeBooking(int $roomTypeId, string $checkIn, string $checkOut, int $roomsBooked = 1): Booking
    {
        $b = new Booking();
        $b->room_type_id = $roomTypeId;
        $b->check_in = $checkIn;
        $b->check_out = $checkOut;
        $b->rooms_booked = $roomsBooked;
        return $b;
    }

    private function makeDiscount(string $type, float $pct, ?int $minNights = null, ?int $daysBefore = null): Discount
    {
        $d = new Discount();
        $d->type = $type;
        $d->percentage = $pct;
        $d->min_nights = $minNights;
        $d->days_before_checkin = $daysBefore;
        $d->active = true;
        return $d;
    }

    private function setupMocks(
        array $roomTypes,
        array $inventoryIndex,
        array $bookingIndex,
        array $discounts,
    ): void {
        $this->roomTypeRepo->shouldReceive('getAllCached')
            ->once()
            ->andReturn(collect($roomTypes));

        $this->discountRepo->shouldReceive('getAllActiveCached')
            ->once()
            ->andReturn(collect($discounts));

        $this->inventoryRepo->shouldReceive('getForRoomTypesAndDates')
            ->once()
            ->andReturn($inventoryIndex);

        $this->bookingRepo->shouldReceive('getOverlappingByRoomType')
            ->once()
            ->andReturn($bookingIndex);
    }

    // -------------------------------------------------------------------------
    //  Pricing — guest count
    // -------------------------------------------------------------------------

    public function test_price_for_1_guest_uses_price_1_person(): void
    {
        $rt = $this->makeRoomType();
        $inv = [
            1 => [
                '2026-03-20' => $this->makeInventory(1, '2026-03-20', 1600, 2050, 2525),
                '2026-03-21' => $this->makeInventory(1, '2026-03-21', 1600, 2050, 2525),
            ],
        ];

        $this->setupMocks([$rt], $inv, [], []);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-22'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        // 2 nights × ₹1600 = ₹3200
        $this->assertEquals(3200.0, $results[0]->priceRoomOnly);
    }

    public function test_price_for_2_guests_uses_price_2_persons(): void
    {
        $rt = $this->makeRoomType();
        $inv = [
            1 => [
                '2026-03-20' => $this->makeInventory(1, '2026-03-20', 1600, 2050, 2525),
                '2026-03-21' => $this->makeInventory(1, '2026-03-21', 1600, 2050, 2525),
            ],
        ];

        $this->setupMocks([$rt], $inv, [], []);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-22'),
            2,
            'room_only',
        );

        $results = $this->service->search($dto);

        // 2 nights × ₹2050 = ₹4100
        $this->assertEquals(4100.0, $results[0]->priceRoomOnly);
    }

    public function test_price_for_3_guests_uses_price_3_persons(): void
    {
        $rt = $this->makeRoomType();
        $inv = [
            1 => [
                '2026-03-20' => $this->makeInventory(1, '2026-03-20', 1600, 2050, 2525),
            ],
        ];

        $this->setupMocks([$rt], $inv, [], []);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-21'),
            3,
            'room_only',
        );

        $results = $this->service->search($dto);

        // 1 night × ₹2525 = ₹2525
        $this->assertEquals(2525.0, $results[0]->priceRoomOnly);
    }

    // -------------------------------------------------------------------------
    //  Pricing — breakfast surcharge
    // -------------------------------------------------------------------------

    public function test_breakfast_surcharge_added_to_price(): void
    {
        $rt = $this->makeRoomType(['breakfast_surcharge' => 200.00]);
        $inv = [
            1 => [
                '2026-03-20' => $this->makeInventory(1, '2026-03-20', 1600, 2050, 2525),
                '2026-03-21' => $this->makeInventory(1, '2026-03-21', 1600, 2050, 2525),
            ],
        ];

        $this->setupMocks([$rt], $inv, [], []);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-22'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        // Room only: 2 × 1600 = 3200
        // Breakfast: 2 × 1600 + 2 × 200 = 3600
        $this->assertEquals(3200.0, $results[0]->priceRoomOnly);
        $this->assertEquals(3600.0, $results[0]->priceBreakfast);
    }

    public function test_deluxe_breakfast_surcharge(): void
    {
        $rt = $this->makeRoomType([
            'id'                  => 2,
            'name'                => 'Deluxe',
            'breakfast_surcharge' => 400.00,
        ]);
        $inv = [
            2 => [
                '2026-03-20' => $this->makeInventory(2, '2026-03-20', 2100, 2550, 3025),
            ],
        ];

        $this->setupMocks([$rt], $inv, [], []);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-21'),
            2,
            'room_only',
        );

        $results = $this->service->search($dto);

        // Room only: 1 × 2550 = 2550
        // Breakfast: 2550 + 1 × 400 = 2950
        $this->assertEquals(2550.0, $results[0]->priceRoomOnly);
        $this->assertEquals(2950.0, $results[0]->priceBreakfast);
    }

    // -------------------------------------------------------------------------
    //  Discounts — long stay tiers
    // -------------------------------------------------------------------------

    public function test_no_discount_for_short_stay(): void
    {
        $rt = $this->makeRoomType();
        $dates = ['2026-03-20', '2026-03-21'];
        $inv = [1 => array_combine(
            $dates,
            array_map(fn ($d) => $this->makeInventory(1, $d, 1600, 2050, 2525), $dates),
        )];

        $discounts = [
            $this->makeDiscount('long_stay', 10.0, 3),
            $this->makeDiscount('long_stay', 20.0, 6),
        ];

        $this->setupMocks([$rt], $inv, [], $discounts);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-22'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        // 2 nights — no long-stay discount
        $this->assertEquals(0.0, $results[0]->discountPercent);
        $this->assertEquals(3200.0, $results[0]->priceRoomOnly);
        $this->assertEmpty($results[0]->discountLabels);
    }

    public function test_long_stay_tier1_3_nights(): void
    {
        $rt = $this->makeRoomType();
        // 3 nights: Mar 20-22 (dates are 20,21,22)
        $dates = ['2026-03-20', '2026-03-21', '2026-03-22'];
        $inv = [1 => array_combine(
            $dates,
            array_map(fn ($d) => $this->makeInventory(1, $d, 1600, 2050, 2525), $dates),
        )];

        $discounts = [
            $this->makeDiscount('long_stay', 10.0, 3),
            $this->makeDiscount('long_stay', 20.0, 6),
        ];

        $this->setupMocks([$rt], $inv, [], $discounts);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-23'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        // 3 nights × 1600 = 4800, 10% off = 4320
        $this->assertEquals(10.0, $results[0]->discountPercent);
        $this->assertEquals(4320.0, $results[0]->priceRoomOnly);
        $this->assertEquals(4800.0, $results[0]->originalRoomOnly);
        $this->assertContains('Long Stay 10%', $results[0]->discountLabels);
    }

    public function test_long_stay_tier2_6_nights(): void
    {
        $rt = $this->makeRoomType();
        $dates = [];
        for ($i = 0; $i < 6; $i++) {
            $dates[] = Carbon::parse('2026-03-20')->addDays($i)->format('Y-m-d');
        }
        $inv = [1 => array_combine(
            $dates,
            array_map(fn ($d) => $this->makeInventory(1, $d, 1600, 2050, 2525), $dates),
        )];

        $discounts = [
            $this->makeDiscount('long_stay', 10.0, 3),
            $this->makeDiscount('long_stay', 20.0, 6),
        ];

        $this->setupMocks([$rt], $inv, [], $discounts);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-26'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        // 6 nights × 1600 = 9600, 20% off = 7680
        $this->assertEquals(20.0, $results[0]->discountPercent);
        $this->assertEquals(7680.0, $results[0]->priceRoomOnly);
        $this->assertContains('Long Stay 20%', $results[0]->discountLabels);
    }

    public function test_long_stay_tier2_supersedes_tier1(): void
    {
        $rt = $this->makeRoomType();
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $dates[] = Carbon::parse('2026-03-20')->addDays($i)->format('Y-m-d');
        }
        $inv = [1 => array_combine(
            $dates,
            array_map(fn ($d) => $this->makeInventory(1, $d, 1600, 2050, 2525), $dates),
        )];

        $discounts = [
            $this->makeDiscount('long_stay', 10.0, 3),
            $this->makeDiscount('long_stay', 20.0, 6),
        ];

        $this->setupMocks([$rt], $inv, [], $discounts);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-27'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        // 7 nights qualifies for both tiers, highest wins = 20%
        $this->assertEquals(20.0, $results[0]->discountPercent);
        // Labels should have only the winning tier
        $this->assertContains('Long Stay 20%', $results[0]->discountLabels);
        $this->assertNotContains('Long Stay 10%', $results[0]->discountLabels);
    }

    // -------------------------------------------------------------------------
    //  Discounts — last minute
    // -------------------------------------------------------------------------

    public function test_last_minute_discount_within_3_days(): void
    {
        $rt = $this->makeRoomType();
        // Check-in today (0 days away) — qualifies for last-minute
        $inv = [
            1 => [
                '2026-03-18' => $this->makeInventory(1, '2026-03-18', 1600, 2050, 2525),
            ],
        ];

        $discounts = [
            $this->makeDiscount('last_minute', 5.0, null, 3),
        ];

        $this->setupMocks([$rt], $inv, [], $discounts);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-18'),
            Carbon::parse('2026-03-19'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        // 1 night × 1600 = 1600, 5% off = 1520
        $this->assertEquals(5.0, $results[0]->discountPercent);
        $this->assertEquals(1520.0, $results[0]->priceRoomOnly);
        $this->assertContains('Last Minute 5%', $results[0]->discountLabels);
    }

    public function test_last_minute_3_days_away_still_qualifies(): void
    {
        $rt = $this->makeRoomType();
        $inv = [
            1 => [
                '2026-03-21' => $this->makeInventory(1, '2026-03-21', 1600, 2050, 2525),
            ],
        ];

        $discounts = [
            $this->makeDiscount('last_minute', 5.0, null, 3),
        ];

        $this->setupMocks([$rt], $inv, [], $discounts);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-21'),
            Carbon::parse('2026-03-22'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        // 3 days away — qualifies
        $this->assertEquals(5.0, $results[0]->discountPercent);
    }

    public function test_no_last_minute_discount_beyond_3_days(): void
    {
        $rt = $this->makeRoomType();
        $inv = [
            1 => [
                '2026-03-22' => $this->makeInventory(1, '2026-03-22', 1600, 2050, 2525),
            ],
        ];

        $discounts = [
            $this->makeDiscount('last_minute', 5.0, null, 3),
        ];

        $this->setupMocks([$rt], $inv, [], $discounts);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-22'),
            Carbon::parse('2026-03-23'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        // 4 days away — does NOT qualify
        $this->assertEquals(0.0, $results[0]->discountPercent);
    }

    // -------------------------------------------------------------------------
    //  Discounts — combined long stay + last minute
    // -------------------------------------------------------------------------

    public function test_combined_long_stay_and_last_minute(): void
    {
        $rt = $this->makeRoomType();
        // 3-night stay checking in tomorrow → long_stay 10% + last_minute 5% = 15%
        $dates = ['2026-03-19', '2026-03-20', '2026-03-21'];
        $inv = [1 => array_combine(
            $dates,
            array_map(fn ($d) => $this->makeInventory(1, $d, 1600, 2050, 2525), $dates),
        )];

        $discounts = [
            $this->makeDiscount('long_stay', 10.0, 3),
            $this->makeDiscount('long_stay', 20.0, 6),
            $this->makeDiscount('last_minute', 5.0, null, 3),
        ];

        $this->setupMocks([$rt], $inv, [], $discounts);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-19'),
            Carbon::parse('2026-03-22'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        // 3 × 1600 = 4800, discount 15%, final = 4080
        $this->assertEquals(15.0, $results[0]->discountPercent);
        $this->assertEquals(4080.0, $results[0]->priceRoomOnly);
        $this->assertEquals(4800.0, $results[0]->originalRoomOnly);
        $this->assertCount(2, $results[0]->discountLabels);
        $this->assertContains('Long Stay 10%', $results[0]->discountLabels);
        $this->assertContains('Last Minute 5%', $results[0]->discountLabels);
    }

    public function test_discount_cap_at_90_percent(): void
    {
        $rt = $this->makeRoomType();
        $inv = [
            1 => [
                '2026-03-18' => $this->makeInventory(1, '2026-03-18', 1000, 1000, 1000),
            ],
        ];

        $discounts = [
            $this->makeDiscount('long_stay', 80.0, 1),
            $this->makeDiscount('last_minute', 20.0, null, 3),
        ];

        $this->setupMocks([$rt], $inv, [], $discounts);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-18'),
            Carbon::parse('2026-03-19'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        // 80 + 20 = 100, but capped at 90
        $this->assertEquals(90.0, $results[0]->discountPercent);
        $this->assertEquals(100.0, $results[0]->priceRoomOnly); // 1000 × 0.10 = 100
    }

    // -------------------------------------------------------------------------
    //  Discounts apply to breakfast price too
    // -------------------------------------------------------------------------

    public function test_discount_applies_to_breakfast_price(): void
    {
        $rt = $this->makeRoomType(['breakfast_surcharge' => 200.00]);
        $dates = ['2026-03-18', '2026-03-19', '2026-03-20'];
        $inv = [1 => array_combine(
            $dates,
            array_map(fn ($d) => $this->makeInventory(1, $d, 1600, 2050, 2525), $dates),
        )];

        $discounts = [
            $this->makeDiscount('long_stay', 10.0, 3),
        ];

        $this->setupMocks([$rt], $inv, [], $discounts);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-18'),
            Carbon::parse('2026-03-21'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        // Room only: 3 × 1600 = 4800, 10% off = 4320
        $this->assertEquals(4320.0, $results[0]->priceRoomOnly);

        // Breakfast: 3 × 1600 + 3 × 200 = 5400, 10% off = 4860
        $this->assertEquals(4860.0, $results[0]->priceBreakfast);

        // Originals
        $this->assertEquals(4800.0, $results[0]->originalRoomOnly);
        $this->assertEquals(5400.0, $results[0]->originalBreakfast);
    }

    // -------------------------------------------------------------------------
    //  Availability
    // -------------------------------------------------------------------------

    public function test_room_available_when_no_bookings(): void
    {
        $rt = $this->makeRoomType(['total_rooms' => 5]);
        $inv = [
            1 => [
                '2026-03-20' => $this->makeInventory(1, '2026-03-20', 1600, 2050, 2525),
            ],
        ];

        $this->setupMocks([$rt], $inv, [], []);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-21'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        $this->assertTrue($results[0]->available);
    }

    public function test_room_sold_out_when_fully_booked(): void
    {
        $rt = $this->makeRoomType(['total_rooms' => 2]);
        $inv = [
            1 => [
                '2026-03-20' => $this->makeInventory(1, '2026-03-20', 1600, 2050, 2525),
            ],
        ];

        // 2 bookings covering Mar 20
        $bookings = [
            1 => [
                $this->makeBooking(1, '2026-03-19', '2026-03-21', 1),
                $this->makeBooking(1, '2026-03-20', '2026-03-22', 1),
            ],
        ];

        $this->setupMocks([$rt], $inv, $bookings, []);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-21'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        $this->assertFalse($results[0]->available);
    }

    public function test_room_available_with_partial_bookings(): void
    {
        $rt = $this->makeRoomType(['total_rooms' => 5]);
        $inv = [
            1 => [
                '2026-03-20' => $this->makeInventory(1, '2026-03-20', 1600, 2050, 2525),
            ],
        ];

        // 3 out of 5 rooms booked
        $bookings = [
            1 => [
                $this->makeBooking(1, '2026-03-19', '2026-03-21', 1),
                $this->makeBooking(1, '2026-03-20', '2026-03-21', 1),
                $this->makeBooking(1, '2026-03-18', '2026-03-22', 1),
            ],
        ];

        $this->setupMocks([$rt], $inv, $bookings, []);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-21'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        $this->assertTrue($results[0]->available);
    }

    public function test_availability_uses_worst_day_in_range(): void
    {
        $rt = $this->makeRoomType(['total_rooms' => 2]);
        $inv = [
            1 => [
                '2026-03-20' => $this->makeInventory(1, '2026-03-20', 1600, 2050, 2525),
                '2026-03-21' => $this->makeInventory(1, '2026-03-21', 1600, 2050, 2525),
            ],
        ];

        // Mar 21 fully booked (2 rooms), Mar 20 has 1 booking
        $bookings = [
            1 => [
                $this->makeBooking(1, '2026-03-20', '2026-03-21', 1),
                $this->makeBooking(1, '2026-03-21', '2026-03-22', 1),
                $this->makeBooking(1, '2026-03-21', '2026-03-23', 1),
            ],
        ];

        $this->setupMocks([$rt], $inv, $bookings, []);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-22'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        // Mar 20: 1 booked (booking 1 covers 20-21 inclusive) → available = 2-1 = 1
        // Mar 21: bookings 1 covers 20-21, booking 2 covers 21-22, booking 3 covers 21-23 → 3 booked → 2-3 = -1
        // Wait, booking.check_in <= date && booking.check_out >= date
        // Booking 1: check_in=20, check_out=21 → covers 20 (20<=20 && 21>=20 ✓), 21 (20<=21 && 21>=21 ✓)
        // Booking 2: check_in=21, check_out=22 → covers 21 (21<=21 && 22>=21 ✓)
        // Booking 3: check_in=21, check_out=23 → covers 21 (21<=21 && 23>=21 ✓)
        // Mar 20: 1 room booked → 2-1 = 1 available
        // Mar 21: 3 rooms booked → 2-3 = -1 available (full)
        // Worst day → sold out
        $this->assertFalse($results[0]->available);
    }

    // -------------------------------------------------------------------------
    //  Checkout-day overlap in availability
    // -------------------------------------------------------------------------

    public function test_checkout_day_counts_as_occupied(): void
    {
        $rt = $this->makeRoomType(['total_rooms' => 1]);
        $inv = [
            1 => [
                '2026-03-22' => $this->makeInventory(1, '2026-03-22', 1600, 2050, 2525),
            ],
        ];

        // Booking Mar 20-22, check_out = Mar 22 → still occupies Mar 22
        $bookings = [
            1 => [
                $this->makeBooking(1, '2026-03-20', '2026-03-22', 1),
            ],
        ];

        $this->setupMocks([$rt], $inv, $bookings, []);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-22'),
            Carbon::parse('2026-03-23'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        // Booking covers Mar 22 (check_in=20 <= 22 && check_out=22 >= 22) → occupied
        $this->assertFalse($results[0]->available);
    }

    // -------------------------------------------------------------------------
    //  Multiple room types returned
    // -------------------------------------------------------------------------

    public function test_returns_results_for_all_room_types(): void
    {
        $standard = $this->makeRoomType(['id' => 1, 'name' => 'Standard', 'breakfast_surcharge' => 200]);
        $deluxe = $this->makeRoomType(['id' => 2, 'name' => 'Deluxe', 'breakfast_surcharge' => 400]);

        $inv = [
            1 => ['2026-03-20' => $this->makeInventory(1, '2026-03-20', 1600, 2050, 2525)],
            2 => ['2026-03-20' => $this->makeInventory(2, '2026-03-20', 2100, 2550, 3025)],
        ];

        $this->setupMocks([$standard, $deluxe], $inv, [], []);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-21'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        $this->assertCount(2, $results);
        $this->assertEquals('Standard', $results[0]->roomType);
        $this->assertEquals('Deluxe', $results[1]->roomType);
        $this->assertEquals(1600.0, $results[0]->priceRoomOnly);
        $this->assertEquals(2100.0, $results[1]->priceRoomOnly);
    }

    // -------------------------------------------------------------------------
    //  DTO toArray
    // -------------------------------------------------------------------------

    public function test_dto_toArray_includes_discount_fields_when_available(): void
    {
        $rt = $this->makeRoomType();
        $dates = ['2026-03-18', '2026-03-19', '2026-03-20'];
        $inv = [1 => array_combine(
            $dates,
            array_map(fn ($d) => $this->makeInventory(1, $d, 1600, 2050, 2525), $dates),
        )];

        $discounts = [
            $this->makeDiscount('long_stay', 10.0, 3),
            $this->makeDiscount('last_minute', 5.0, null, 3),
        ];

        $this->setupMocks([$rt], $inv, [], $discounts);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-18'),
            Carbon::parse('2026-03-21'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);
        $arr = $results[0]->toArray();

        $this->assertArrayHasKey('discount_percent', $arr);
        $this->assertArrayHasKey('discount_labels', $arr);
        $this->assertArrayHasKey('original_room_only', $arr);
        $this->assertArrayHasKey('original_breakfast', $arr);
        $this->assertEquals(15.0, $arr['discount_percent']);
    }

    public function test_dto_toArray_shows_sold_out_status_when_unavailable(): void
    {
        $rt = $this->makeRoomType(['total_rooms' => 1]);
        $inv = [
            1 => [
                '2026-03-20' => $this->makeInventory(1, '2026-03-20', 1600, 2050, 2525),
            ],
        ];

        $bookings = [
            1 => [$this->makeBooking(1, '2026-03-20', '2026-03-21', 1)],
        ];

        $this->setupMocks([$rt], $inv, $bookings, []);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-21'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);
        $arr = $results[0]->toArray();

        $this->assertEquals('Sold Out', $arr['status']);
        $this->assertArrayNotHasKey('price_room_only', $arr);
        $this->assertArrayNotHasKey('discount_percent', $arr);
    }

    // -------------------------------------------------------------------------
    //  Missing inventory → zero price
    // -------------------------------------------------------------------------

    public function test_missing_inventory_results_in_zero_price(): void
    {
        $rt = $this->makeRoomType();

        // No inventory data at all
        $this->setupMocks([$rt], [], [], []);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-20'),
            Carbon::parse('2026-03-21'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        $this->assertEquals(0.0, $results[0]->priceRoomOnly);
    }

    // -------------------------------------------------------------------------
    //  No active discounts
    // -------------------------------------------------------------------------

    public function test_no_discount_when_no_active_discounts(): void
    {
        $rt = $this->makeRoomType();
        $dates = ['2026-03-18', '2026-03-19', '2026-03-20'];
        $inv = [1 => array_combine(
            $dates,
            array_map(fn ($d) => $this->makeInventory(1, $d, 1000, 1000, 1000), $dates),
        )];

        $this->setupMocks([$rt], $inv, [], []);

        $dto = new SearchRequestDTO(
            Carbon::parse('2026-03-18'),
            Carbon::parse('2026-03-21'),
            1,
            'room_only',
        );

        $results = $this->service->search($dto);

        $this->assertEquals(0.0, $results[0]->discountPercent);
        $this->assertEquals(3000.0, $results[0]->priceRoomOnly);
        $this->assertEquals(3000.0, $results[0]->originalRoomOnly);
    }
}
