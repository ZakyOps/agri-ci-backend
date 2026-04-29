<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FarmerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $farmerId = $this->route('farmer')?->id;

        return [
            'identifier' => ['required', 'string', 'max:255', Rule::unique('farmers', 'identifier')->ignore($farmerId)],
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', Rule::unique('farmers', 'phone')->ignore($farmerId)],
            'credit_limit_fcfa' => ['required', 'integer', 'min:0'],
        ];
    }
}
