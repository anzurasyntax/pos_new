<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'new_customer_name' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $cid = $this->input('customer_id');
            $name = trim((string) $this->input('new_customer_name', ''));

            if (empty($cid) && $name === '') {
                $validator->errors()->add('customer_id', 'Select a customer or enter a new name.');
            }
            if (! empty($cid) && $name !== '') {
                $validator->errors()->add('customer_id', 'Use either an existing customer or a new name, not both.');
            }
        });
    }
}
