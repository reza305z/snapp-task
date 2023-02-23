<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankAccountCard;
use App\Models\Enums\TransactionStatusEnum;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\DepositTransactionNotification;
use App\Notifications\WithdrawTransactionNotification;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionService
{
    public function create(BankAccountCard $bankAccountCard, string $receiverCardNumber, int $amount)
    {
        try {
            DB::beginTransaction();
            $transaction = Transaction::create([
                'sender_card_id' => $bankAccountCard->id,
                'receiver_card_id' => $receiverCardNumber,
                'amount' => $amount,
                'status' => TransactionStatusEnum::Done,
            ]);
            $transaction->transactionWage()->create([
                'amount' => 5_000,
            ]);
            $bankAccountCard->bankAccount()->decrement('balance', $amount);
            $receiverBankAccount = BankAccount::whereCardNumberIs($receiverCardNumber)->increment('balance', $amount);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::critical('An error occurred when doing the transaction.', ['exception' => $exception->getMessage()]);
            throw new Exception();
        }

        auth()->user()->notify(new WithdrawTransactionNotification(
            accountNumber: $bankAccountCard->bankAccount->account_number,
            amount: number_format($amount),
            method: 'درگاه پرداخت',
            balance: $bankAccountCard->bankAccount->balance,
            dateTime: $dateTime = verta($transaction->created_at)->format('Y/m/d H:i')
        ));

        User::whereCardNumberIs(cardNumber: $receiverCardNumber)->first()->notify(new DepositTransactionNotification(
            accountNumber: $receiverBankAccount->account_number,
            amount: number_format($amount),
            method: 'درگاه پرداخت',
            balance: $receiverBankAccount->balance + $amount,
            dateTime: $dateTime
        ));

        return $transaction;
    }
}
