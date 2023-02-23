<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\BankAccount;
use App\Models\BankAccountCard;
use App\Models\Transaction;
use App\Models\TransactionWage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(10)->create();
        foreach ($users as $user) {
            $BankAccount = BankAccount::factory()->for($user)->create();
            BankAccountCard::factory(2)
                ->for($user)
                ->for($BankAccount)
                ->create();
        }

        $bankAccountCards = BankAccountCard::pluck('id')->toArray();

        Transaction::factory(100)
            ->has(TransactionWage::factory())
            ->create([
                'sender_card_id' => array_rand($bankAccountCards),
                'receiver_card_id' => array_rand($bankAccountCards),
            ]);
    }
}
