<?php

namespace App\Models;

use App\Models\Enums\TransactionStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => TransactionStatusEnum::class,
    ];

    /**
     * Get the transactionWage associated with the Transaction
     */
    public function transactionWage(): HasOne
    {
        return $this->hasOne(TransactionWage::class);
    }
}
