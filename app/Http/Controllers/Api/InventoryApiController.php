<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexInventoryRequest;
use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use App\Models\Inventory;
use App\Models\RoomType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class InventoryApiController extends Controller
{
    /** GET /api/inventory?room_type_id=X */
    public function index(IndexInventoryRequest $request): JsonResponse
    {
        $inventory = Inventory::where('room_type_id', $request->integer('room_type_id'))
            ->orderBy('date')
            ->get(['id', 'room_type_id', 'date', 'price_1_person', 'price_2_persons', 'price_3_persons']);

        return response()->json($inventory);
    }

    /** GET /api/inventory/room-types */
    public function roomTypes(): JsonResponse
    {
        $roomTypes = RoomType::all(['id', 'name']);
        return response()->json($roomTypes);
    }

    /** POST /api/inventory */
    public function store(StoreInventoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Prevent duplicate date+room_type
        $exists = Inventory::where('room_type_id', $validated['room_type_id'])
            ->where('date', $validated['date'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'An entry for this date already exists.'], 422);
        }

        $inventory = Inventory::create($validated);

        Cache::flush(); // bust inventory cache

        return response()->json($inventory, 201);
    }

    /** PUT /api/inventory/{inventory} */
    public function update(UpdateInventoryRequest $request, Inventory $inventory): JsonResponse
    {
        $validated = $request->validated();

        $inventory->update($validated);

        Cache::flush();

        return response()->json($inventory);
    }

    /** DELETE /api/inventory/{inventory} */
    public function destroy(Inventory $inventory): JsonResponse
    {
        $inventory->delete();

        Cache::flush();

        return response()->json(['success' => true]);
    }
}
