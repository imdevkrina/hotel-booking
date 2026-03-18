<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiscountRequest;
use App\Http\Requests\UpdateDiscountRequest;
use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class DiscountApiController extends Controller
{
    /** GET /api/discounts */
    public function index(): JsonResponse
    {
        $discounts = Discount::orderBy('type')->orderBy('min_nights')->get();
        return response()->json($discounts);
    }

    /** POST /api/discounts */
    public function store(StoreDiscountRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $discount = Discount::create($validated);

        Cache::forget('discounts_active');

        return response()->json($discount, 201);
    }

    /** PUT /api/discounts/{discount} */
    public function update(UpdateDiscountRequest $request, Discount $discount): JsonResponse
    {
        $validated = $request->validated();

        $discount->update($validated);

        Cache::forget('discounts_active');

        return response()->json($discount);
    }

    /** DELETE /api/discounts/{discount} */
    public function destroy(Discount $discount): JsonResponse
    {
        $discount->delete();

        Cache::forget('discounts_active');

        return response()->json(['success' => true]);
    }
}
