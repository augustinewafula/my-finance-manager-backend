<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBondRequest;
use App\Models\Bond;
use App\Services\BondService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BondController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(Bond::currentUser()->with('interestPayingDates')->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateBondRequest $request
     * @param BondService $bondService
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(CreateBondRequest $request, BondService $bondService): JsonResponse
    {
        $dates = $this->transformDatesStringToArray($request->interest_payment_dates);

        if ($bondService->areValidDates($dates)) {
            try {
                $bond = $bondService->storeBond(
                    $request->issue_number,
                    $request->coupon_rate,
                    $request->amount_invested,
                    $dates
                );

                return response()->json([
                    'message' => 'Bond created successfully',
                    'bond' => $bond
                ], 201);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error creating bond'
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Invalid dates'
        ], 400);

    }

    private function transformDatesStringToArray(string $dates): array
    {
        $dates = Str::of($dates)->trim()->toString();
        $dates = str_replace(PHP_EOL, ' ', $dates);
        $separatedDates = [];
        $dateFragment = "";
        for ($i = 0, $iMax = strlen($dates); $i < $iMax; $i++) {
            $char = $dates[$i];
            if ($char === " ") {
                if ($dateFragment !== "") {
                    $separatedDates[] = $dateFragment;
                }
                $dateFragment = "";
                continue;
            }
            $dateFragment .= $char;
            $dateFragment = Str::of($dateFragment)->trim()->toString();
            if (strlen($dateFragment) === 10) {
                $separatedDates[] = $dateFragment;
                $dateFragment = "";
            }
        }

        return $separatedDates;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bond  $bond
     * @return \Illuminate\Http\Response
     */
    public function show(Bond $bond)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bond  $bond
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bond $bond)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bond  $bond
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bond $bond)
    {
        //
    }
}
