<?php

namespace App\Core\Exceptions\Driver;

use App\Core\Exceptions\BusinessException;

class DuplicateDriverApplicationException extends BusinessException
{
    protected int $statusCode = 409;

    protected $message = 'You already have an active driver application.';
}