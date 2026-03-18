<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date'            => ['sometimes', 'date_format:Y-m-d'],
            'price_1_person'  => ['sometimes', 'numeric', 'min:0'],
            'price_2_persons' => ['sometimes', 'numeric', 'min:0'],
            'price_3_persons' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
