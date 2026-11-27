<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['admin', 'operator']) ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'sometimes',
                Rule::exists('users', 'id')->where('role', 'customer'),
            ],
            'carrier_id' => ['sometimes', 'exists:carriers,id'],
            'origin_location_id' => ['sometimes', 'exists:locations,id'],
            'destination_location_id' => ['sometimes', 'exists:locations,id', 'different:origin_location_id'],
            'cargo_type' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'volume' => ['nullable', 'numeric', 'min:0'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['sometimes', 'string', 'size:3'],
            'expected_departure' => ['nullable', 'date'],
            'expected_arrival' => ['nullable', 'date'],
            'estimated_arrival' => ['nullable', 'date'],
            'current_status' => ['sometimes', Rule::in(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'])],
        ];
    }
}

