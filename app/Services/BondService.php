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
    public function areValidDates(array $dates): bool
    {
        foreach ($dates as $date) {
            $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y'];
            $carbon = null;
            foreach ($formats as $format) {
                try {
                    $carbon = Carbon::createFromFormat($format, $date);
                    break;
                } catch (\InvalidArgumentException $e) {
                    continue;
                }
            }
            if (!$carbon) {
                return false;
            }
        }
        return true;
    }

    public function convertDatesToCarbon(array $dates): array
    {
        $carbonDates = [];
        foreach ($dates as $date) {
            $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y'];
            $carbon = null;
            foreach ($formats as $format) {
                try {
                    $carbon = Carbon::createFromFormat($format, $date);
                    break;
                } catch (\InvalidArgumentException $e) {
                    continue;
                }
            }
            if ($carbon) {
                $carbonDates[] = $carbon;
            }
        }
        return $carbonDates;
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

    /**
     * @throws Exception
     */
    public function updateBond(
        Bond $bond,
        string $issue_number,
        float $coupon_rate,
        float $amount_invested,
        array $dates
    ): Bond
    {
        try {
            DB::beginTransaction();
            $bond->update([
                'issue_number' => $issue_number,
                'coupon_rate' => $coupon_rate,
                'amount_invested' => $amount_invested
            ]);
            $this->deleteInterestPayingDates($bond->id);
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
                'date' => $date
            ]);
        }
    }

    private function deleteInterestPayingDates(string $bondId): void
    {
        BondInterestPayingDate::where('bond_id', $bondId)->delete();
    }

}
