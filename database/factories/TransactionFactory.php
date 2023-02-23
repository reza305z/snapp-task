<?php

namespace Database\Factories;

use App\Models\Enums\TransactionStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->numberBetween(10_000, 500_000_000),
            'status' => fake()->randomElement(array_column(TransactionStatusEnum::cases(), 'value')),
        ];
    }
}
