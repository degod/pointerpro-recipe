<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class ResponseService
{
    public function success(int $status = 200, string $message = '', array $data = []): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function error(int $status = 400, string $message = '', array $errors = []): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
