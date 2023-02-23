<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    /**
     * Get all of the bankAccountCards for the BankAccount
     */
    public function bankAccountCards(): HasMany
    {
        return $this->hasMany(BankAccountCard::class);
    }

    public function scopeWhereCardNumberIs($query, string $cardNumber)
    {
        $query->whereHas('bankAccountCard', function (Builder $query) use ($cardNumber) {
            $query->where('card_number', $cardNumber);
        });
    }
}
