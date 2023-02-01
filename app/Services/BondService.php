<?php

namespace App\Services;

use App\Models\Bond;
use App\Models\BondInterestPayingDate;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BondService
{
    function areValidDates(array $dates): bool
    {
        foreach ($dates as $date) {
            $formats = ['d/m/Y', 'Y-m-d'];
            $carbon = null;
            foreach ($formats as $format) {
                try {
                    $carbon = Carbon::createFromFormat($format, $date);
                    break;
                } catch (\InvalidArgumentException $e) {
                    Log::info('Invalid date: ' . $date. ' with format: ' . $format);
                    continue;
                }
            }
            if (!$carbon) {
                return false;
            }
        }
        return true;
    }

    /**
     * @throws Exception
     */
    public function storeBond(string $issue_number, float $coupon_rate, float $amount_invested, array $dates): Bond
    {
        try {
            DB::beginTransaction();
            $bond = Bond::create([
                'issue_number' => $issue_number,
                'coupon_rate' => $coupon_rate,
                'amount_invested' => $amount_invested
            ]);
            $this->storeBondInterestPayingDates($bond->id, $dates);
            DB::commit();

            return $bond;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            throw $e;
        }
    }

    private function storeBondInterestPayingDates(string $bondId, array $dates): void
    {

        foreach ($dates as $date) {
            BondInterestPayingDate::create([
                'bond_id' => $bondId,
                'date' => Carbon::createFromFormat('d/m/Y', $date)
            ]);
        }
    }

    public function updateBond($request, $bond)
    {
        $bond->update([
            'issue_number' => $request->issue_number,
            'coupon_rate' => $request->coupon_rate,
            'amount_invested' => $request->amount_invested
        ]);

        return $bond;
    }

}
