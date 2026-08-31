<?php

namespace App\Core\Exceptions;

use Exception;

abstract class BusinessException extends Exception
{
    protected int $statusCode = 400;

    protected array $context = [];

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function context(): array
    {
        return $this->context;
    }
}