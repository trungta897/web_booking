<?php

namespace App\Services;

class PaymentResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $clientSecret = null,
        public readonly ?string $error = null,
        public readonly ?string $paymentUrl = null
    ) {}
}
