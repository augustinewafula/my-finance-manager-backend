<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'amount' => 'required|numeric',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|integer',
            'transaction_category_id' => 'required|string',
            'transaction_sub_category_id' => 'nullable|string',
        ];
    }
}
