<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    // thực hiện format chuẩn cho response API
    public static function success($data = [], int $code = 200, $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => $message
        ], $code);
    }

    public static function error($message = 'Error', int $code = 200, $data = []): JsonResponse
    {
        return response()->json([
            'status' => false,
            'data' => $data,
            'message' => $message
        ], $code);
    }
}
