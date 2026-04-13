<?php

namespace Modules\Main\Services;

use Illuminate\Http\JsonResponse;

class ResponseJson
{

    public static function Success($data = [], string $message = "", int $code = 200): JsonResponse
    {
        return response()->json([
            'data'        => $data,
            'message'     => $message,
            'status_code' => $code,
            'errors'      => [],
        ])->setStatusCode($code);
    }

    public static function Failed(array $errors = [], string $message = "", int $code = 500, array $data = []): JsonResponse
    {
        return response()->json([
            'errors'      => $errors,
            'message'     => $message,
            'status_code' => $code,
            'data'        => $data,
        ])->setStatusCode($code);
    }
}
