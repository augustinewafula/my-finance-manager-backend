<?php

namespace App\Http\Controllers;

use App\Actions\IdentifyMpesaTransactionCategory;
use App\Http\Requests\CreateMpesaTransactionRequest;
use App\Http\Requests\CreateTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Services\MpesaTransactionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $transactions = Transaction::currentUser()
            ->with(['transactionCategory', 'transactionSubCategory'])
            ->latest('date')
            ->get()
            ->groupBy(function($transaction) {
                return $transaction->date->format('Y-m-d');
            });

        $currentPage = (int) $request->get('page', 1);
        $perPage = 10;
        $items = $transactions->forPage($currentPage, $perPage);
        $paginator = new LengthAwarePaginator($items, $transactions->count(), $perPage);

        return response()->json($paginator);
    }

    public function store(CreateTransactionRequest $request): JsonResponse
    {
        $transaction = Transaction::create([
            'transaction_category_id' => $request->transaction_category_id,
            'transaction_sub_category_id' => $request->transaction_sub_category_id,
            'type' => $request->transaction_type,
            'amount' => $request->amount,
            'date' => $request->transaction_date,
            'subject' => $request->name,
        ]);

        return response()->json($transaction, 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateMpesaTransactionRequest $request
     * @param IdentifyMpesaTransactionCategory $identifyMpesaTransactionCategory
     * @param MpesaTransactionService $mpesaTransactionService
     * @return JsonResponse
     * @throws JsonException
     */
    public function storeMpesaTransaction(
        CreateMpesaTransactionRequest    $request,
        IdentifyMpesaTransactionCategory $identifyMpesaTransactionCategory,
        MpesaTransactionService          $mpesaTransactionService): JsonResponse
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

        $mpesa_transaction = $mpesaTransactionService->store(
            $request->message,
            $decoded_mpesa_transaction_message,
            $identifyMpesaTransactionCategory
        );
        return response()->json($mpesa_transaction, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTransactionRequest $request
     * @param Transaction $transaction
     * @return JsonResponse
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $updatedFields = [];

        if ($request->filled('transaction_category_id')) {
            $transaction->transaction_category_id = $request->transaction_category_id;
            $updatedFields[] = 'transaction_category_id';
        }

        if ($request->filled('transaction_sub_category_id')) {
            $transaction->transaction_sub_category_id = $request->transaction_sub_category_id;
            $updatedFields[] = 'transaction_sub_category_id';
        }

        if ($request->filled('amount')) {
            $transaction->amount = $request->amount;
            $updatedFields[] = 'amount';
        }

        if ($request->filled('date')) {
            $transaction->date = $request->transaction_date;
            $updatedFields[] = 'date';
        }

        if ($request->filled('name')) {
            $transaction->subject = $request->name;
            $updatedFields[] = 'subject';
        }

        $transaction->save();

        return response()->json([
            'message' => 'Transaction updated successfully',
            'updated_fields' => $updatedFields,
            'transaction' => $transaction
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Transaction $transaction
     * @return JsonResponse
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        $transaction->delete();

        return response()->json([
            'message' => 'Transaction deleted successfully'
        ], 204);
    }
}
