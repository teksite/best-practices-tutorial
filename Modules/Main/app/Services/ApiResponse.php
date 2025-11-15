<?php

namespace Modules\Main\Services;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(array $data = [], int $status = 200 , ?string $message ='success'): JsonResponse
    {
        return self::general($data,[], $status, $message);
    }

    public static function failed(array $errors=[], array $data = [], int $status = 403 , ?string $message ='failed'): JsonResponse
    {
        return self::general($data,$errors, $status, $message);
    }

    public static function general(array $data = [],array $errors=[], int $status = 200 , ?string $message ='failed'): JsonResponse
    {
        return response()->json(compact('message','errors','data','status'),$status);
    }


}
