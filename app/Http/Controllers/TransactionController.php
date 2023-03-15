<?php

namespace App\Http\Controllers;

use App\Actions\IdentifyMpesaTransactionCategory;
use App\Http\Requests\CreateMpesaTransactionRequest;
use App\Http\Requests\CreateTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\IdentifiedTransactionCategory;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Services\MpesaTransactionService;
use Carbon\Carbon;
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
        $currentPage = (int) $request->get('page', 1);
        $fromDate = $request->get('from_date', now()->subDays(30)->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));
        $perPage = 10;

        $fromDate = Carbon::createFromFormat('Y-m-d', $fromDate)->startOfDay();
        $toDate = Carbon::createFromFormat('Y-m-d', $toDate)->endOfDay();

        $transactions = Transaction::currentUser()
            ->with(['transactionCategory', 'transactionSubCategory'])
            ->whereBetween('date', [$fromDate, $toDate])
            ->latest('date')
            ->get()
            ->groupBy(function($transaction) {
                return $transaction->date->format('Y-m-d');
            });

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
            ], 422);
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
            $newCategoryId = $request->transaction_category_id;
            $newCategory = TransactionCategory::findOrFail($newCategoryId);

            if ($transaction->transaction_sub_category_id && $transaction->transactionSubCategory->transaction_category_id != $newCategoryId) {
                $transaction->transaction_sub_category_id = null;
                $updatedFields[] = 'transaction_sub_category_id';
            }

            $transaction->transaction_category_id = $newCategoryId;
            $updatedFields[] = 'transaction_category_id';
        }

        if ($request->filled('transaction_sub_category_id')) {
            $newSubCategoryId = $request->transaction_sub_category_id;
            $currentCategoryId = $transaction->transaction_category_id;

            if ($request->filled('transaction_category_id')) {
                $currentCategoryId = $request->transaction_category_id;
            }

            $categorySubCategories = TransactionCategory::findOrFail($currentCategoryId)->transactionSubCategories;
            $subCategory = $categorySubCategories->find($newSubCategoryId);

            if (!$subCategory) {
                return response()->json(
                    ['message' => 'Transaction sub category does not belong to transaction category'], 422
                );
            }

            $transaction->transaction_sub_category_id = $newSubCategoryId;
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

        DB::transaction(function () use ($transaction, $request) {
            $transaction->save();
            if ($request->filled('transaction_category_id') || $request->filled('transaction_sub_category_id')) {
                IdentifiedTransactionCategory::updateOrCreate(['subject' => $transaction->subject], [
                    'transaction_category_id' => $transaction->transaction_category_id,
                    'transaction_sub_category_id' => $transaction->transaction_sub_category_id,
                ]);
            }
        });

        $transaction = Transaction::where('id', $transaction->id)
            ->with(['transactionCategory', 'transactionSubCategory'])
            ->first();

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
