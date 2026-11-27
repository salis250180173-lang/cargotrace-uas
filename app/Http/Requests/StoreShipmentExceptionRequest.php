<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShipmentExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['admin', 'operator']) ?? false;
    }

    public function rules(): array
    {
        return [
            'exception_type' => [
                'required',
                Rule::in([
                    'CUSTOMS_HOLD',
                    'WEATHER_DELAY',
                    'PORT_CONGESTION',
                    'DAMAGED',
                    'MISROUTED',
                    'DOCUMENTATION_ISSUE',
                ]),
            ],
            'severity' => ['required', Rule::in(['LOW', 'MEDIUM', 'HIGH'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'detected_at' => ['nullable', 'date'],
        ];
    }
}

