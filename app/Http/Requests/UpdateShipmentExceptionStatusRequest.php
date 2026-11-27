<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShipmentExceptionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['admin', 'operator']) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['OPEN', 'INVESTIGATING', 'RESOLVED'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

