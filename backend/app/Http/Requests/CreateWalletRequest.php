<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'userId' => ['required', 'integer', 'exists:users,id'],
            'initialBalance' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'userId.required' => 'User ID is required',
            'userId.exists' => 'User not found',
            'initialBalance.required' => 'Initial balance is required',
            'initialBalance.min' => 'Initial balance must be non-negative',
        ];
    }
}
