<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBondRequest;
use App\Http\Resources\BondResource;
use App\Models\Bond;
use App\Services\BondService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BondController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return BondResource::collection(Bond::currentUser()->with('interestPayingDates')->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateBondRequest $request
     * @param BondService $bondService
     * @return JsonResponse
     * @throws Exception
     */
    public function store(CreateBondRequest $request, BondService $bondService): JsonResponse
    {
        $dates = $this->transformDatesStringToArray($request->interest_payment_dates);

        if (count($dates) > 0 && $bondService->areValidDates($dates)) {
            $dates = $bondService->convertDatesToCarbon($dates);
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
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Error creating bond'
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Invalid dates'
        ], 422);

    }

    private function transformDatesStringToArray(string $dates): array
    {
        $dates = str_replace(',', ' ', $dates);
        $separatedDates = [];
        $dateFragments = explode(' ', $dates);

        foreach ($dateFragments as $dateFragment) {
            $dateFragment = trim($dateFragment);
            if (strlen($dateFragment) === 10) {
                $separatedDates[] = $dateFragment;
            } elseif (strlen($dateFragment) > 10) {
                $subFragments = preg_split('/[\s,]+/', $dateFragment);
                foreach ($subFragments as $subFragment) {
                    if (strlen($subFragment) === 10) {
                        $separatedDates[] = $subFragment;
                    }
                }
            }
        }

        return $separatedDates;
    }

    /**
     * Display the specified resource.
     *
     * @param Bond $bond
     * @return Response
     */
    public function show(Bond $bond)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Bond $bond
     * @param BondService $bondService
     * @return JsonResponse
     */
    public function update(Request $request, Bond $bond, BondService $bondService): JsonResponse
    {
        $dates = $this->transformDatesStringToArray($request->interest_payment_dates);

        if (count($dates) > 0 && $bondService->areValidDates($dates)) {
            $dates = $bondService->convertDatesToCarbon($dates);
            try {
                $bond = $bondService->updateBond(
                    $bond,
                    $request->issue_number,
                    $request->coupon_rate,
                    $request->amount_invested,
                    $dates
                );

                return response()->json([
                    'message' => 'Bond updated successfully',
                    'bond' => $bond
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Error updating bond'
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Invalid dates'
        ], 422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Bond $bond
     * @return JsonResponse
     */
    public function destroy(Bond $bond): JsonResponse
    {
        $bond->delete();

        return response()->json([
            'message' => 'Bond deleted successfully'
        ]);
    }
}
