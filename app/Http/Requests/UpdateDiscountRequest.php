<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'                => ['sometimes', 'in:long_stay,last_minute'],
            'min_nights'          => ['nullable', 'integer', 'min:1'],
            'days_before_checkin' => ['nullable', 'integer', 'min:1'],
            'percentage'          => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'active'              => ['sometimes', 'boolean'],
        ];
    }
}
