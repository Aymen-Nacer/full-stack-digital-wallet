<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fromWalletId' => ['required', 'integer'],
            'toWalletId' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'idempotencyKey' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'fromWalletId.required' => 'Source wallet ID is required',
            'toWalletId.required' => 'Destination wallet ID is required',
            'amount.required' => 'Amount is required',
            'amount.min' => 'Transfer amount must be greater than zero',
        ];
    }
}
