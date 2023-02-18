<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use JsonSerializable;

class BondResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        $interestPayingDates = BondInterestPayingDateResource::collection(
            $this->whenLoaded('interestPayingDates'));

        $maturityDate = null;
        foreach ($interestPayingDates as $interestPayingDate) {
            $date = $interestPayingDate->date;
            if ($maturityDate === null || $date > $maturityDate) {
                $maturityDate = $date;
            }
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'issue_number' => $this->issue_number,
            'coupon_rate' => $this->coupon_rate,
            'amount_invested' => $this->amount_invested,
            'maturity_date' => $maturityDate->toDateString(),
            'created_at' => $this->created_at,
            'interest_paying_dates' => $interestPayingDates,
        ];
    }

}
