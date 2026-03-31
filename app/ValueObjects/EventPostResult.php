<?php

namespace App\ValueObjects;

class EventPostResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $error = null,
        public readonly ?array $responseData = null,
    ) {}
}
