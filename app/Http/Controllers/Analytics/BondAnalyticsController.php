<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Bond;
use App\Models\BondInterestPayingDate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'total_investment' => $total_investment,
            'interest_per_annum' => round($interest_per_annum),
            'average_monthly_interest' => round($average_monthly_interest),
            'interest_this_month' => $interest_this_month,
        ];
    }
}
