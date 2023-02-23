<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\InsufficientBalanceTransactionException;
use App\Exceptions\SameAccountTransactionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\TransactionCreateRequest;
use App\Http\Resources\Api\v1\TransactionResource;
use App\Http\Resources\Api\v1\UserResource;
use App\Models\BankAccountCard;
use App\Models\User;
use App\Services\TransactionService;
use Exception;

class TransactionController extends Controller
{
    public function create(
        TransactionCreateRequest $request,
        BankAccountCard $bankAccountCard,
        TransactionService $transactionService
    ) {
        try {
            $transaction = $transactionService->create(
                senderUser: $request->user(),
                senderBankAccountCard: $bankAccountCard,
                receiverCardNumber: $request->validated()['receiver_card_number'],
                amount: $request->validated()['amount']
            );
        } catch (SameAccountTransactionException|InsufficientBalanceTransactionException $exception) {
            return $this->jsonResponse(message: $exception->getMessage(), status: $exception->getCode());
        } catch (Exception $exception) {
            return $this->jsonResponse(message: __('message.transaction.server_error'), status: 500);
        }

        return $this->jsonResponse(data: new TransactionResource($transaction), message: __('message.transaction.successful'));
    }

    public function usersWithMostTransactions()
    {
        return $this->jsonResponse(
            data: UserResource::collection(
                User::usersWithMostTransactions(
                    userNumber: 3,
                    transactionNumber: 10
                )->get()
            )
        );
    }
}
