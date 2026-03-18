<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Search\SearchService;
use App\Domain\Search\DTOs\RoomAvailabilityDTO;
use App\Domain\Search\DTOs\SearchRequestDTO;
use App\Http\Requests\SearchRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        private readonly SearchService $searchService,
    ) {}

    /** Render the search form. */
    public function index(): View
    {
        return view('search.index');
    }

    /**
     * Handle an availability search request.
     * Returns JSON — the Blade view fetches this via the Fetch API.
     */
    public function search(SearchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $dto = new SearchRequestDTO(
            checkIn:    Carbon::parse($validated['check_in_date']),
            checkOut:   Carbon::parse($validated['check_out_date']),
            guestCount: (int) $validated['guest_count'],
            mealPlan:   'room_only',
        );

        $results = $this->searchService->search($dto);

        $payload = array_map(
            static fn (RoomAvailabilityDTO $item): array => $item->toArray(),
            $results
        );

        return response()->json($payload);
    }
}
