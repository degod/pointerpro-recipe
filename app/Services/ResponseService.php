<?php

namespace App\Services;

class ResponseService
{
    public function success(int $status = 200, string $message = '', $data = []): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function error(int $status = 400, string $message = '', $errors = []): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
