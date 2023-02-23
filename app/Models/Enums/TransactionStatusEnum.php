<?php

namespace App\Models\Enums;

enum TransactionStatusEnum: int
{
    case Pending = 1;
    case Done = 100;
    case Failed = -100;

    public function displayTitle(): string
    {
        return match ($this) {
            static::Pending => __('strings.transaction.status.pending'),
            static::Done => __('strings.transaction.status.done'),
            static::Failed => __('strings.transaction.status.failed'),
        };
    }
}
