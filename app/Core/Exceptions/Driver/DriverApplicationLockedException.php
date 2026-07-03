<?php

namespace App\Core\Exceptions\Driver;

use App\Core\Exceptions\BusinessException;

class DriverApplicationLockedException extends BusinessException
{
    protected int $statusCode = 403;
    
    protected $message = 'This application can no longer be modified.';
}