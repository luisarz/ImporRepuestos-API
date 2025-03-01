<?php

namespace App\Helpers;


class ApiResponse
{
    public static function success($data = null, $message = 'Request successful', $code = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => $data,
            'status' => 'success',
            'message' => $message,
            'code' => $code,
        ], $code);
    }

    public static function error($data = null, $message = 'An error occurred', $code = 500): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => $data,
            'status' => 'error',
            'message' => $message,
            'code' => $code,
        ], $code);
    }
}
