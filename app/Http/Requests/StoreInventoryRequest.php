<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_type_id'    => ['required', 'integer', 'exists:room_types,id'],
            'date'            => ['required', 'date_format:Y-m-d'],
            'price_1_person'  => ['required', 'numeric', 'min:0'],
            'price_2_persons' => ['required', 'numeric', 'min:0'],
            'price_3_persons' => ['required', 'numeric', 'min:0'],
        ];
    }
}
