<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddPurchasePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', 'max:50'],
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where(function ($q) {
                    $q->whereIn('type', ['cash', 'bank']);
                }),
            ],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}

