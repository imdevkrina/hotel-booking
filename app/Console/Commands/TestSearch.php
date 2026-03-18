<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Search\SearchService;
use App\Domain\Search\DTOs\SearchRequestDTO;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestSearch extends Command
{
    protected $signature   = 'hotel:test-search';
    protected $description = 'Run a quick sanity-check on the SearchService';

    public function handle(SearchService $service): int
    {
        $this->info('=== Test 1: Deluxe sold-out window (today+5 → today+9) ===');
        $results = $service->search(new SearchRequestDTO(
            checkIn:    Carbon::today()->addDays(5),
            checkOut:   Carbon::today()->addDays(9),
            guestCount: 2,
            mealPlan:   'room_only',
        ));
        foreach ($results as $r) {
            $this->line(json_encode($r->toArray(), JSON_PRETTY_PRINT));
        }

        $this->newLine();
        $this->info('=== Test 2: No-conflict window (today+25 → today+32, breakfast) ===');
        $results2 = $service->search(new SearchRequestDTO(
            checkIn:    Carbon::today()->addDays(25),
            checkOut:   Carbon::today()->addDays(32),
            guestCount: 2,
            mealPlan:   'breakfast_included',
        ));
        foreach ($results2 as $r) {
            $this->line(json_encode($r->toArray(), JSON_PRETTY_PRINT));
        }

        $this->newLine();
        $this->info('=== Test 3: Last-minute (today → today+2, room_only) ===');
        $results3 = $service->search(new SearchRequestDTO(
            checkIn:    Carbon::today()->addDays(1),
            checkOut:   Carbon::today()->addDays(3),
            guestCount: 1,
            mealPlan:   'room_only',
        ));
        foreach ($results3 as $r) {
            $this->line(json_encode($r->toArray(), JSON_PRETTY_PRINT));
        }

        $this->newLine();
        $this->info('=== Test 4: Long-stay + last-minute combined ===');
        $results4 = $service->search(new SearchRequestDTO(
            checkIn:    Carbon::today()->addDays(2),
            checkOut:   Carbon::today()->addDays(10),
            guestCount: 3,
            mealPlan:   'breakfast_included',
        ));
        foreach ($results4 as $r) {
            $this->line(json_encode($r->toArray(), JSON_PRETTY_PRINT));
        }

        return self::SUCCESS;
    }
}
