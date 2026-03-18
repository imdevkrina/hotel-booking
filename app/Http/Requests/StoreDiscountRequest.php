<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'                => ['required', 'in:long_stay,last_minute'],
            'min_nights'          => ['nullable', 'integer', 'min:1'],
            'days_before_checkin' => ['nullable', 'integer', 'min:1'],
            'percentage'          => ['required', 'numeric', 'min:0', 'max:100'],
            'active'              => ['sometimes', 'boolean'],
        ];
    }
}
