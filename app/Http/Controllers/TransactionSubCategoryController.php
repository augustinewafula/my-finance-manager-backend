<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransactionSubCategoryRequest;
use App\Models\TransactionSubCategory;
use App\Services\TransactionSubCategoryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Throwable;

class TransactionSubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $transactionSubCategories = TransactionSubCategory::default()
            ->orWhere
            ->forCurrentUser()
            ->get();
        return response()->json($transactionSubCategories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateTransactionSubCategoryRequest $request
     * @param TransactionSubCategoryService $transactionCategoryService
     * @return JsonResponse
     */
    public function store(CreateTransactionSubCategoryRequest $request, TransactionSubCategoryService $transactionCategoryService): JsonResponse
    {
        $transactionSubCategory = $transactionCategoryService->store($request->name, $request->transaction_category_id);
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
     * Remove the specified resource from storage.
     *
     * @param TransactionSubCategory $transactionSubCategory
     * @return JsonResponse
     * @throws Throwable
     */
    public function destroy(TransactionSubCategory $transactionSubCategory): JsonResponse
    {
        throw_unless($this->createdByCurrentUser($transactionSubCategory), AuthorizationException::class, 'You are not allowed to delete this subcategory.');
        $transactionSubCategory->delete();

        return response()->json(['message' => 'Transaction subcategory deleted']);
    }

    public function createdByCurrentUser($transactionSubCategory):bool
    {
        return $transactionSubCategory->created_by === auth()->user()->id;
    }
}
