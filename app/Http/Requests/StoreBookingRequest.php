<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_type_id'   => ['required', 'integer', 'exists:room_types,id'],
            'check_in_date'  => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date_format:Y-m-d', 'after:check_in_date'],
            'guest_count'    => ['required', 'integer', 'min:1', 'max:3'],
            'meal_plan'      => ['required', 'in:room_only,breakfast_included'],
        ];
    }
}
