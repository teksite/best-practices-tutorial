<?php

namespace Modules\Auth\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Modules\Auth\Http\Requests\CheckUserFormRequest;
use Modules\User\Models\User;

class CheckUserController extends Controller
{
    public function check(CheckUserFormRequest $request)
    {
        $user = User::query()->where($request->contactType?->value, $request->contactValue)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found, ',
            ]);
        }
        return response()->json([
            'message' => 'User found, ',
            'user' => $user,
        ]);
    }
}
