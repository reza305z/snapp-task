<?php

use App\Http\Controllers\Api\v1\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::post('/transaction/{bank_account_card:unique_id}', [TransactionController::class, 'create'])->can('create', 'bank_account_card');
    Route::get('/user/index/most-transaction', [TransactionController::class, 'usersWithMostTransactions']);
});