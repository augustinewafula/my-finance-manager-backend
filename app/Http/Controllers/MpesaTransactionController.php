<?php

namespace App\Http\Controllers;

use App\Actions\IdentifyMpesaTransactionCategory;
use App\Http\Requests\MpesaTransactionRequest;
use App\Models\MpesaTransaction;
use App\Services\MpesaTransactionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param MpesaTransactionRequest $request
     * @param MpesaTransactionService $mpesaTransactionService
     * @return JsonResponse
     * @throws Exception
     */
    public function store(MpesaTransactionRequest $request, IdentifyMpesaTransactionCategory $identifyMpesaTransactionCategory, MpesaTransactionService $mpesaTransactionService): JsonResponse
    {
        $decoded_mpesa_transaction_message = $mpesaTransactionService->decodeMpesaTransactionMessage($request->message);
        Log::info('decoded_mpesa_transaction_message: ' . json_encode($decoded_mpesa_transaction_message, JSON_THROW_ON_ERROR));
        try {
            $mpesaTransactionService->validateDecodedMpesaTransactionMessage($decoded_mpesa_transaction_message);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 405);
        }

        $mpesa_transaction = $mpesaTransactionService->store($request->message, $decoded_mpesa_transaction_message, $identifyMpesaTransactionCategory);
        return response()->json($mpesa_transaction, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MpesaTransaction  $mpesaTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(MpesaTransaction $mpesaTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MpesaTransaction  $mpesaTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MpesaTransaction $mpesaTransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MpesaTransaction  $mpesaTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(MpesaTransaction $mpesaTransaction)
    {
        //
    }
}
