<?php

use App\Http\Controllers\MpesaTransactionController;
use App\Http\Controllers\TransactionCategoryController;
use App\Http\Controllers\TransactionSubCategoryController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::post('login', [UserController::class, 'login']);
    Route::group(['middleware' => ['auth:sanctum']], static function () {
        Route::get('user', [UserController::class, 'user']);
        Route::post('register', [UserController::class, 'register']);
        Route::get('logout', [UserController::class, 'logout']);
        Route::post('mpesa-transaction', [MpesaTransactionController::class, 'store']);
        Route::apiResource('transaction-category', TransactionCategoryController::class);
        Route::apiResource('transaction-sub-category', TransactionSubCategoryController::class);
    });
});
Route::fallback(static function () {
    return response()->json([
        'message' => 'Route not found.'], 404);
});
