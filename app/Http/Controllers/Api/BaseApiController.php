<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;

class BaseApiController extends Controller
{
    protected function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200
    ) {
        return ApiResponse::success($data, $message, $status);
    }

    protected function error(
        string $message = 'Error',
        mixed $errors = null,
        int $status = 400
    ) {
        return ApiResponse::error($message, $errors, $status);
    }
}