<?php

use App\Http\Controllers\Analytics\BondAnalyticsController;
use App\Http\Controllers\BondController;
use App\Http\Controllers\TransactionController;
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
    Route::post('register', [UserController::class, 'register']);
    Route::group(['middleware' => ['auth:sanctum']], static function () {
        Route::get('user', [UserController::class, 'user']);
        Route::get('logout', [UserController::class, 'logout']);
        Route::apiResource('transactions', TransactionController::class);
        Route::post('mpesa-transactions', [TransactionController::class, 'storeMpesaTransaction']);
        Route::apiResource('transaction-categories/{categoryId}/subcategories', TransactionSubCategoryController::class);
        Route::apiResource('transaction-categories', TransactionCategoryController::class);
        Route::apiResource('bonds', BondController::class);
        Route::get('analytics/bonds/interest-data', [BondAnalyticsController::class, 'getUserInterestData']);
        Route::get('analytics/bonds/monthly-interest-data/{year}', [BondAnalyticsController::class, 'getMonthlyInterestGraphData']);
        Route::get('analytics/bonds/interest-data-years', [BondAnalyticsController::class, 'getUniqueInterestDateYears']);
        Route::get('analytics/bonds/upcoming-interest-data', [BondAnalyticsController::class, 'getUpcomingInterests']);
        Route::get('analytics/bonds/bonds-data', [BondAnalyticsController::class, 'getBondsData']);
    });
});
Route::fallback(static function () {
    return response()->json([
        'message' => 'Route not found.'], 404);
});
