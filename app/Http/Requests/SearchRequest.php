<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'check_in_date'  => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date_format:Y-m-d', 'after:check_in_date'],
            'guest_count'    => ['required', 'integer', 'min:1', 'max:3'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'check_in_date.after_or_equal' => 'Check-in date must be today or in the future.',
            'check_out_date.after'         => 'Check-out date must be after check-in date.',
            'guest_count.max'              => 'Maximum 3 adults per room.',
        ];
    }
}
