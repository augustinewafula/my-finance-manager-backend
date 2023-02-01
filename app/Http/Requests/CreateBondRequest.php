<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBondRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'issue_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('bonds')->where(function ($query) {
                    return $query->where('user_id', auth()->id())
                        ->where('issue_number', $this->all()['issue_number']);
                })
            ],
            'coupon_rate' => ['required', 'numeric', 'min:0'],
            'amount_invested' => ['required', 'numeric', 'min:0'],
            'interest_payment_dates' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'issue_number.unique' => 'You already have a bond with this issue number',
        ];
    }
}
