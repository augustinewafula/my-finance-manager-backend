<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Bond;
use App\Models\BondInterestPayingDate;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BondAnalyticsController extends Controller
{
    function getUserInterestData() {
        $user_id = Auth::id();
        $current_year = Carbon::now()->year;

        // Calculate total amount invested
        $total_investment = Bond::currentUser()->sum('amount_invested');

        // Calculate total interest of current year
        $interest_per_annum = BondInterestPayingDate::whereHas('bond', function($query) use ($user_id) {
            $query->currentUser();
        })
            ->whereYear('date', $current_year)
            ->get()
            ->sum(function($bond_interest) {
                return ($bond_interest->bond->coupon_rate * $bond_interest->bond->amount_invested / 100) /2;
            });

        // Calculate average monthly interest
        $current_month = Carbon::now()->month;
        $interest_this_month = 0;
        $total_interest = 0;
        for ($month = 1; $month <= 12; $month++) {
            $interest = BondInterestPayingDate::whereHas('bond', function($query) use ($user_id) {
                $query->currentUser();
            })
                ->whereMonth('date', $month)
                ->whereYear('date', $current_year)
                ->get()
                ->sum(function($bond_interest) {
                    return $bond_interest->bond->coupon_rate * $bond_interest->bond->amount_invested / 100;
                });
            $total_interest += $interest;
            if ($month === $current_month) {
                $interest_this_month = $interest;
            }
        }
        $average_monthly_interest = ($total_interest / 12) / 2;
        $interest_this_month /= 2;

        return [
            'total_investment' => round($total_investment),
            'interest_per_annum' => round($interest_per_annum),
            'average_monthly_interest' => round($average_monthly_interest),
            'interest_this_month' => $interest_this_month,
        ];
    }

    function getMonthlyInterestGraphData($year)
    {
        $user_id = Auth::id();
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();

        // Query to fetch monthly interest for each month
        $monthlyInterest = BondInterestPayingDate::select(
            BondInterestPayingDate::raw('MONTH(date) as month'),
            BondInterestPayingDate::raw('SUM((coupon_rate * amount_invested / 100) / 2) as total_interest')
        )
            ->join('bonds', 'bonds.id', '=', 'bond_interest_paying_dates.bond_id')
            ->whereHas('bond', function ($query) use ($user_id) {
                $query->currentUser();
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy(BondInterestPayingDate::raw('MONTH(date)'))
            ->orderBy(BondInterestPayingDate::raw('MONTH(date)'))
            ->get();

        // Build the response data
        $data = [];
        foreach ($monthlyInterest as $interest) {
            $data[] = [
                'month' => $interest->month,
                'total_interest' => round($interest->total_interest)
            ];
        }

        return response()->json($data);
    }

    public function getUniqueInterestDateYears()
    {
        $user = Auth::user();
        $years = BondInterestPayingDate::selectRaw('YEAR(date) as year')
            ->join('bonds', 'bond_interest_paying_dates.bond_id', '=', 'bonds.id')
            ->where('bonds.user_id', '=', $user->id)
            ->distinct()
            ->orderBy('year', 'asc')
            ->pluck('year')
            ->toArray();
        return $years;
    }

    public function getUpcomingInterests(Request $request)
    {
        $limit = $request->get('limit', 20);
        $user = Auth::user();
        return BondInterestPayingDate::select('bonds.issue_number', 'bond_interest_paying_dates.date', DB::raw('ROUND((bonds.amount_invested * bonds.coupon_rate / 100) / 2, 2) as interest_amount'))
            ->join('bonds', 'bond_interest_paying_dates.bond_id', '=', 'bonds.id')
            ->where('bonds.user_id', '=', $user->id)
            ->where('bond_interest_paying_dates.date', '>=', Carbon::today())
            ->orderBy('bond_interest_paying_dates.date', 'asc')
            ->limit($limit)
            ->get();
    }

    public function getBondsData(): JsonResponse
    {
        $currentYear = Carbon::now()->year;

        $bonds = Bond::with(['interestPayingDates' => function ($query) use ($currentYear) {
            $query->whereYear('date', $currentYear)
                ->orderBy('date', 'asc');
        }])->currentUser()->get();

        $bondData = [];

        foreach ($bonds as $bond) {
            $totalInterest = 0;
            $interestMonths = [];

            foreach ($bond->interestPayingDates as $interest) {
                $totalInterest += (($bond->coupon_rate * $bond->amount_invested) / 100) / 2;
                $interestMonth = Carbon::parse($interest->date)->format('F');
                if (!in_array($interestMonth, $interestMonths, true)) {
                    $interestMonths[] = $interestMonth;
                }
            }
            usort($interestMonths, static function ($a, $b) {
                return Carbon::parse($a)->format('m') - Carbon::parse($b)->format('m');
            });

            $bondData[] = [
                'issue_number' => $bond->issue_number,
                'total_interest_per_annum' => $totalInterest,
                'interest_paying_months' => $interestMonths,
            ];
        }
        usort($bondData, static function ($a, $b) {
            $aMonth = $a['interest_paying_months'][0] ?? '';
            $bMonth = $b['interest_paying_months'][0] ?? '';
            return Carbon::parse($aMonth)->format('m') - Carbon::parse($bMonth)->format('m');
        });

        return response()->json($bondData);
    }


}
