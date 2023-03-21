<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransactionCategoryRequest;
use App\Http\Requests\UpdateTransactionCategoryRequest;
use App\Models\TransactionCategory;
use App\Services\TransactionCategoryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Throwable;

class TransactionCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $transactionCategories = TransactionCategory::default()
            ->orWhere
            ->currentUser()
            ->with('transactionSubCategories')
            ->get();
        return response()->json($transactionCategories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateTransactionCategoryRequest $request
     * @param TransactionCategoryService $transactionCategoryService
     * @return JsonResponse
     */
    public function store(CreateTransactionCategoryRequest $request, TransactionCategoryService $transactionCategoryService): JsonResponse
    {
        $transactionCategory = $transactionCategoryService->store($request->name);
        return response()->json($transactionCategory, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param TransactionCategory $transactionCategory
     * @return JsonResponse
     */
    public function show(TransactionCategory $transactionCategory): JsonResponse
    {
        return response()->json($transactionCategory);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTransactionCategoryRequest $request
     * @param TransactionCategory $transactionCategory
     * @return JsonResponse
     */
    public function update(UpdateTransactionCategoryRequest $request, TransactionCategory $transactionCategory): JsonResponse
    {
        $transactionCategory->update($request->only('name'));

        return response()->json($transactionCategory);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TransactionCategory $transactionCategory
     * @return JsonResponse
     * @throws Throwable
     */
    public function destroy(TransactionCategory $transactionCategory): JsonResponse
    {
        throw_unless($transactionCategory->createdByCurrentUser($transactionCategory), AuthorizationException::class, 'You are not allowed to delete this category.');
        $transactionCategory->delete();

        return response()->json(['message' => 'Transaction category deleted'], 204);
    }

}
