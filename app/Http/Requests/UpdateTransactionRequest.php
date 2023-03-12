<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'transaction_date' => 'nullable|date',
            'transaction_type' => 'nullable|integer',
            'transaction_category_id' => 'nullable|string',
            'transaction_sub_category_id' => 'nullable|string',
        ];
    }
}
