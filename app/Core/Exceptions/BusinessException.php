<?php

namespace App\Core\Exceptions;

use Exception;

abstract class BusinessException extends Exception
{
    /**
     * HTTP status code.
     */
    protected int $statusCode = 400;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}