<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => [
                Rule::requiredIf((float) $this->input('amount', 0) > 0),
                'nullable',
                'string',
                Rule::in(['cash', 'jazzcash', 'easypaisa', 'bank_mezzan']),
            ],
        ];
    }
}
