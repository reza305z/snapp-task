<?php

namespace Tests\Unit\Services;

use App\Exceptions\SameAccountTransactionException;
use App\Models\User;
use App\Models\BankAccount;
use App\Models\BankAccountCard;
use Tests\TestCase;
use App\Services\TransactionService;
use App\Models\Enums\TransactionStatusEnum;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Notifications\DepositTransactionNotification;
use App\Notifications\WithdrawTransactionNotification;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    public function test_can_deposit_money_to_another_bank_account(): void
    {
        Notification::fake();
        $senderUser = User::factory()->create();
        $senderBankAccount = BankAccount::factory()->for($senderUser)->create();
        $senderBankAccountCard = BankAccountCard::factory()->for($senderUser)->for($senderBankAccount)->create();
        $receiverUser = User::factory()->create();
        $receiverBankAccount = BankAccount::factory()->for($receiverUser)->create();
        $receiverBankAccountCard = BankAccountCard::factory()->for($receiverUser)->for($receiverBankAccount)->create();

        $service = new TransactionService;

        $service->create(
            $senderUser,
            $senderBankAccountCard,
            $receiverBankAccountCard->card_number,
            $amount = mt_rand(10_000, 500_000_000)
        );

        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('transactions', [
            'sender_card_id' => $senderBankAccountCard->id,
            'receiver_card_id' => $receiverBankAccountCard->id,
            'amount' => $amount,
            'status' => TransactionStatusEnum::Done,
        ]);
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $senderBankAccount->id,
            'balance' => $senderBankAccount->balance - $amount,
        ]);
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $receiverBankAccount->id,
            'balance' => $receiverBankAccount->balance + $amount,
        ]);

        Notification::assertSentTo(
            [$senderUser],
            WithdrawTransactionNotification::class
        );
        Notification::assertSentTo(
            [$receiverUser],
            DepositTransactionNotification::class
        );
    }

    public function test_cant_use_same_bank_account(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $bankAccount = BankAccount::factory()->for($user)->create();
        $senderBankAccountCard = BankAccountCard::factory()->for($user)->for($bankAccount)->create();
        $receiverBankAccountCard = BankAccountCard::factory()->for($user)->for($bankAccount)->create();

        $service = new TransactionService;

        try {
            $service->create(
                $user,
                $senderBankAccountCard,
                $receiverBankAccountCard->card_number,
                mt_rand(10_000, 500_000_000)
            );
            $this->fail('The exception was not thrown.');
        } catch (SameAccountTransactionException $exception) {
            $this->assertDatabaseCount('transactions', 0);
            $this->assertDatabaseHas('bank_accounts', [
                'id' => $bankAccount->id,
                'balance' => $bankAccount->balance,
            ]);
            Notification::assertNotSentTo(
                [$user],
                WithdrawTransactionNotification::class
            );
            Notification::assertNotSentTo(
                [$user],
                DepositTransactionNotification::class
            );
        }
    }
}
