<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where(function ($q) {
                    $q->where('type', 'expense');
                }),
            ],
            'cash_bank_account_id' => [
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

