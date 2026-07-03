<?php

namespace App\Core\Exceptions\Driver;

use App\Core\Exceptions\BusinessException;

class DriverApplicationNotFoundException extends BusinessException
{
    protected int $statusCode = 404;

    protected $message = 'Driver application not found.';
}
