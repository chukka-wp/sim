<?php

namespace App\ValueObjects;

class ValidationResult
{
    public function __construct(
        public readonly array $validEvents,
        public readonly array $skippedEvents,
    ) {}

    public function totalValid(): int
    {
        return count($this->validEvents);
    }

    public function totalSkipped(): int
    {
        return count($this->skippedEvents);
    }
}
