<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransactionSubCategoryRequest;
use App\Http\Requests\UpdateTransactionSubCategoryRequest;
use App\Models\TransactionCategory;
use App\Models\TransactionSubCategory;
use App\Services\TransactionSubCategoryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class TransactionSubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param TransactionCategory $category
     * @return JsonResponse
     */
    public function index(TransactionCategory $category): JsonResponse
    {
        $transactionSubCategories = TransactionSubCategory::where('transaction_category_id', $category->id)->get();

        return response()->json($transactionSubCategories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateTransactionSubCategoryRequest $request
     * @param TransactionSubCategoryService $transactionCategoryService
     * @param $transactionCategoryId
     * @return JsonResponse
     */
    public function store(CreateTransactionSubCategoryRequest $request, TransactionSubCategoryService $transactionCategoryService, $transactionCategoryId): JsonResponse
    {
        $transactionSubCategory = $transactionCategoryService->store($request->name, $transactionCategoryId);

        return response()->json($transactionSubCategory, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param TransactionSubCategory $transactionSubCategory
     * @return JsonResponse
     */
    public function show(TransactionSubCategory $transactionSubCategory): JsonResponse
    {
        return response()->json($transactionSubCategory);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTransactionSubCategoryRequest $request
     * @param TransactionSubCategory $transactionSubCategory
     * @return JsonResponse
     */
    public function update(UpdateTransactionSubCategoryRequest $request, TransactionSubCategory $transactionSubCategory): JsonResponse
    {
        $transactionSubCategory->update($request->validated());

        return response()->json($transactionSubCategory);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $transactionCategoryID
     * @param $transactionSubCategoryId
     * @return JsonResponse
     * @throws Throwable
     */
    public function destroy($transactionCategoryID, $transactionSubCategoryId): JsonResponse
    {
        $transactionSubCategory = TransactionSubCategory::findOrFail($transactionSubCategoryId);
        throw_unless($transactionSubCategory->createdByCurrentUser($transactionSubCategory), AuthorizationException::class, 'You are not allowed to delete this subcategory.');
        $transactionSubCategory->delete();

        return response()->json(['message' => 'Transaction subcategory deleted'], 204);
    }

}
