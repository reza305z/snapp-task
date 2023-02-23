<?php

namespace App\Services;

use App\Exceptions\SameAccountTransactionException;
use App\Models\BankAccountCard;
use App\Models\Enums\TransactionStatusEnum;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\DepositTransactionNotification;
use App\Notifications\WithdrawTransactionNotification;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    public function create(
        User $senderUser,
        BankAccountCard $senderBankAccountCard,
        string $receiverCardNumber,
        int $amount
    ): Transaction {

        $receiverBankAccountCard = BankAccountCard::firstWhere('card_number', $receiverCardNumber);

        throw_if($receiverBankAccountCard->bankAccount->is($senderBankAccountCard->bankAccount), SameAccountTransactionException::class);

        try {
            DB::beginTransaction();
            $transaction = Transaction::create([
                'sender_card_id' => $senderBankAccountCard->id,
                'receiver_card_id' => $receiverBankAccountCard->id,
                'amount' => $amount,
                'status' => TransactionStatusEnum::Done,
            ]);
            $transaction->transactionWage()->create([
                'amount' => 5_000,
            ]);
            $senderBankAccountCard->bankAccount()->decrement('balance', $amount);
            $receiverBankAccountCard->bankAccount()->increment('balance', $amount);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::critical('An error occurred when doing the transaction.', ['exception' => $exception->getMessage()]);
            throw new Exception();
        }

        $senderUser->notify(new WithdrawTransactionNotification(
            accountNumber: $senderBankAccountCard->bankAccount->account_number,
            amount: number_format($amount),
            method: 'درگاه پرداخت',
            balance: $senderBankAccountCard->bankAccount->balance,
            dateTime: $dateTime = verta($transaction->created_at)->format('Y/m/d H:i')
        ));

        $receiverBankAccountCard->user->notify(new DepositTransactionNotification(
            accountNumber: $receiverBankAccountCard->bankAccount->account_number,
            amount: number_format($amount),
            method: 'درگاه پرداخت',
            balance: $receiverBankAccountCard->bankAccount->balance + $amount,
            dateTime: $dateTime
        ));

        return $transaction;
    }
}
