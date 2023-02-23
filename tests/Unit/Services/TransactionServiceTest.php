<?php

namespace Tests\Unit\Services;

use App\Models\BankAccount;
use App\Models\BankAccountCard;
use App\Models\Enums\TransactionStatusEnum;
use App\Models\User;
use App\Services\TransactionService;
use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

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
        $senderBankAccountCard = BankAccountCard::factory()->for($senderUser)->for(
            BankAccount::factory()->for($senderUser)
        )->create();
        $receiverUser = User::factory()->create();
        $receiverBankAccountCard = BankAccountCard::factory()->for($receiverUser)->for(
            BankAccount::factory()->for($receiverUser)
        )->create();

        $service = new TransactionService;

        $service->create($senderBankAccountCard, $receiverBankAccountCard->card_number, mt_rand(10_000, 500_000_000));

        $this->assertDatabaseCount('transactions', 1);

        $this->assertDatabaseHas('transactions', [
            
            'status' => TransactionStatusEnum::Done,
        ]);

        $this->assertTrue(true);
    }
}
