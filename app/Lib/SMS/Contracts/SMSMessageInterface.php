<?php

declare(strict_types=1);

namespace App\Lib\SMS\Contracts;

use App\Lib\SMS\Messages\Payload;

interface SMSMessageInterface
{
    public function to(string $to): self;

    public function getPayload(): Payload;
}
