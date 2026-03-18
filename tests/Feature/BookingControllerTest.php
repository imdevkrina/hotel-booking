<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Inventory;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    private RoomType $standard;
    private RoomType $deluxe;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-03-18'));

        $this->standard = RoomType::create([
            'name'                => 'Standard',
            'total_rooms'         => 5,
            'max_adults'          => 3,
            'breakfast_surcharge' => 200.00,
        ]);

        $this->deluxe = RoomType::create([
            'name'                => 'Deluxe',
            'total_rooms'         => 3,
            'max_adults'          => 3,
            'breakfast_surcharge' => 400.00,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    //  Successful booking
    // -------------------------------------------------------------------------

    public function test_successful_booking_room_only(): void
    {
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-20',
            'check_out_date' => '2026-03-22',
            'guest_count'    => 2,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Booking confirmed successfully!',
            ])
            ->assertJsonStructure(['booking_id']);

        $this->assertDatabaseHas('bookings', [
            'room_type_id' => $this->standard->id,
            'check_in'     => '2026-03-20',
            'check_out'    => '2026-03-22',
            'guest_count'  => 2,
            'meal_plan'    => 'room_only',
            'rooms_booked' => 1,
        ]);
    }

    public function test_successful_booking_with_breakfast(): void
    {
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->deluxe->id,
            'check_in_date'  => '2026-03-25',
            'check_out_date' => '2026-03-28',
            'guest_count'    => 1,
            'meal_plan'      => 'breakfast_included',
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('bookings', [
            'room_type_id' => $this->deluxe->id,
            'meal_plan'    => 'breakfast_included',
            'guest_count'  => 1,
        ]);
    }

    public function test_multiple_bookings_until_capacity(): void
    {
        // Deluxe has 3 rooms — book all 3 successfully
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/book', [
                'room_type_id'   => $this->deluxe->id,
                'check_in_date'  => '2026-03-20',
                'check_out_date' => '2026-03-22',
                'guest_count'    => 1,
                'meal_plan'      => 'room_only',
            ]);
            $response->assertStatus(201);
        }

        $this->assertDatabaseCount('bookings', 3);
    }

    // -------------------------------------------------------------------------
    //  Overbooking / capacity exhausted
    // -------------------------------------------------------------------------

    public function test_overbooking_returns_409(): void
    {
        // Fill all 3 deluxe rooms
        for ($i = 0; $i < 3; $i++) {
            Booking::create([
                'room_type_id' => $this->deluxe->id,
                'check_in'     => '2026-03-20',
                'check_out'    => '2026-03-22',
                'guest_count'  => 2,
                'meal_plan'    => 'room_only',
                'rooms_booked' => 1,
            ]);
        }

        // 4th booking should fail
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->deluxe->id,
            'check_in_date'  => '2026-03-20',
            'check_out_date' => '2026-03-22',
            'guest_count'    => 1,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(409)
            ->assertJson(['success' => false])
            ->assertJsonFragment(['message' => 'Sorry, Deluxe is fully booked for Mar 20, 2026. Please choose different dates.']);
    }

    public function test_overbooking_single_overlap_night(): void
    {
        // Fill all 5 standard rooms for Mar 20-22
        for ($i = 0; $i < 5; $i++) {
            Booking::create([
                'room_type_id' => $this->standard->id,
                'check_in'     => '2026-03-20',
                'check_out'    => '2026-03-22',
                'guest_count'  => 1,
                'meal_plan'    => 'room_only',
                'rooms_booked' => 1,
            ]);
        }

        // New booking overlaps on Mar 21 (check_in Mar 21, check_out Mar 23)
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-21',
            'check_out_date' => '2026-03-23',
            'guest_count'    => 1,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(409);
    }

    // -------------------------------------------------------------------------
    //  Checkout-day overlap logic
    // -------------------------------------------------------------------------

    public function test_checkout_day_overlap_blocks_booking(): void
    {
        // Fill all standard rooms for Mar 20-22 (checkout = Mar 22)
        for ($i = 0; $i < 5; $i++) {
            Booking::create([
                'room_type_id' => $this->standard->id,
                'check_in'     => '2026-03-20',
                'check_out'    => '2026-03-22',
                'guest_count'  => 1,
                'meal_plan'    => 'room_only',
                'rooms_booked' => 1,
            ]);
        }

        // Booking that checks in on Mar 22 (same as checkout of existing)
        // The system uses inclusive check_out >= date, so Mar 22 is still occupied
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-22',
            'check_out_date' => '2026-03-24',
            'guest_count'    => 1,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(409);
    }

    public function test_non_overlapping_dates_succeed(): void
    {
        // Fill all standard rooms for Mar 20-22
        for ($i = 0; $i < 5; $i++) {
            Booking::create([
                'room_type_id' => $this->standard->id,
                'check_in'     => '2026-03-20',
                'check_out'    => '2026-03-22',
                'guest_count'  => 1,
                'meal_plan'    => 'room_only',
                'rooms_booked' => 1,
            ]);
        }

        // Book Mar 23-25 — completely after the existing bookings
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-23',
            'check_out_date' => '2026-03-25',
            'guest_count'    => 1,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(201);
    }

    public function test_partial_overlap_blocks_when_full(): void
    {
        // Fill all 3 deluxe rooms for Mar 20-25
        for ($i = 0; $i < 3; $i++) {
            Booking::create([
                'room_type_id' => $this->deluxe->id,
                'check_in'     => '2026-03-20',
                'check_out'    => '2026-03-25',
                'guest_count'  => 2,
                'meal_plan'    => 'room_only',
                'rooms_booked' => 1,
            ]);
        }

        // New booking Mar 23-28 overlaps on 23, 24, 25
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->deluxe->id,
            'check_in_date'  => '2026-03-23',
            'check_out_date' => '2026-03-28',
            'guest_count'    => 1,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(409);
    }

    public function test_different_room_type_not_affected_by_other_type_bookings(): void
    {
        // Fill all 3 deluxe rooms
        for ($i = 0; $i < 3; $i++) {
            Booking::create([
                'room_type_id' => $this->deluxe->id,
                'check_in'     => '2026-03-20',
                'check_out'    => '2026-03-22',
                'guest_count'  => 1,
                'meal_plan'    => 'room_only',
                'rooms_booked' => 1,
            ]);
        }

        // Standard rooms should still be available for the same dates
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-20',
            'check_out_date' => '2026-03-22',
            'guest_count'    => 1,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(201);
    }

    // -------------------------------------------------------------------------
    //  Validation errors
    // -------------------------------------------------------------------------

    public function test_missing_required_fields_returns_422(): void
    {
        $response = $this->postJson('/api/book', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'room_type_id',
                'check_in_date',
                'check_out_date',
                'guest_count',
                'meal_plan',
            ]);
    }

    public function test_checkout_before_checkin_returns_422(): void
    {
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-25',
            'check_out_date' => '2026-03-20',
            'guest_count'    => 1,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_out_date']);
    }

    public function test_same_checkin_checkout_returns_422(): void
    {
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-25',
            'check_out_date' => '2026-03-25',
            'guest_count'    => 1,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_out_date']);
    }

    public function test_past_checkin_date_returns_422(): void
    {
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-15',
            'check_out_date' => '2026-03-18',
            'guest_count'    => 1,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_in_date']);
    }

    public function test_invalid_room_type_returns_422(): void
    {
        $response = $this->postJson('/api/book', [
            'room_type_id'   => 999,
            'check_in_date'  => '2026-03-20',
            'check_out_date' => '2026-03-22',
            'guest_count'    => 1,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['room_type_id']);
    }

    public function test_invalid_meal_plan_returns_422(): void
    {
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-20',
            'check_out_date' => '2026-03-22',
            'guest_count'    => 1,
            'meal_plan'      => 'all_inclusive',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['meal_plan']);
    }

    public function test_guest_count_zero_returns_422(): void
    {
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-20',
            'check_out_date' => '2026-03-22',
            'guest_count'    => 0,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_count']);
    }

    public function test_guest_count_exceeds_max_returns_422(): void
    {
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-20',
            'check_out_date' => '2026-03-22',
            'guest_count'    => 4,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_count']);
    }

    public function test_invalid_date_format_returns_422(): void
    {
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '18-03-2026',
            'check_out_date' => '20/03/2026',
            'guest_count'    => 1,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_in_date', 'check_out_date']);
    }

    // -------------------------------------------------------------------------
    //  Edge cases
    // -------------------------------------------------------------------------

    public function test_booking_single_night(): void
    {
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-20',
            'check_out_date' => '2026-03-21',
            'guest_count'    => 3,
            'meal_plan'      => 'breakfast_included',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bookings', [
            'guest_count' => 3,
            'meal_plan'   => 'breakfast_included',
        ]);
    }

    public function test_booking_today_checkin(): void
    {
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-18',
            'check_out_date' => '2026-03-19',
            'guest_count'    => 1,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(201);
    }

    public function test_rooms_booked_defaults_to_one(): void
    {
        $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-20',
            'check_out_date' => '2026-03-22',
            'guest_count'    => 2,
            'meal_plan'      => 'room_only',
        ]);

        $booking = Booking::latest()->first();
        $this->assertEquals(1, $booking->rooms_booked);
    }

    public function test_availability_checked_per_night_in_range(): void
    {
        // Book 5 standard rooms for Mar 20-22 only
        for ($i = 0; $i < 5; $i++) {
            Booking::create([
                'room_type_id' => $this->standard->id,
                'check_in'     => '2026-03-20',
                'check_out'    => '2026-03-22',
                'guest_count'  => 1,
                'meal_plan'    => 'room_only',
                'rooms_booked' => 1,
            ]);
        }

        // Mar 19-21 overlaps on Mar 20 and 21 — both full → 409
        $response = $this->postJson('/api/book', [
            'room_type_id'   => $this->standard->id,
            'check_in_date'  => '2026-03-19',
            'check_out_date' => '2026-03-21',
            'guest_count'    => 1,
            'meal_plan'      => 'room_only',
        ]);

        $response->assertStatus(409);
    }
}
