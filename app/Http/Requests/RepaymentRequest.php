<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RepaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'farmer_id' => ['required', 'integer', 'exists:farmers,id'],
            'commodity_kg' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
