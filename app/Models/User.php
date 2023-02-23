<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'mobile',
    ];

    /**
     * Get all of the bankAccounts for the User
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * Get all of the bankAccountCards for the User
     */
    public function bankAccountCards(): HasMany
    {
        return $this->hasMany(BankAccountCard::class);
    }

    /**
     * Get all of the transactions for the User
     */
    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, BankAccountCard::class);
    }

    public function scopeUsersWithMostTransactions($query, int $userNumber, int $transactionNumber)
    {
        $query->withCount('transactions')
            ->with(['transactions' => function ($query) use ($transactionNumber) {
                $query->limit($transactionNumber);
            }])
            ->orderBy('transactions_count', 'desc')
            ->limit($userNumber);
    }
}
