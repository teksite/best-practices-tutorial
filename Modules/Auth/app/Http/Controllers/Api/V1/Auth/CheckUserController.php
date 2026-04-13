<?php

namespace Modules\Auth\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Modules\Auth\Http\Requests\CheckUserFormRequest;
use Modules\Main\Services\ResponseJson;
use Modules\User\Models\User;

class CheckUserController extends Controller
{
    public function check(CheckUserFormRequest $request)
    {
        $user = User::query()->where($request->contactType?->value, $request->contactValue)->exists();

        return ResponseJson::Success([
            'message' =>$user ?  trans(['auth::messages.auth.user_exist']) : trans(['auth::messages.user_not_found']),
            'data'=>[],
        ]);

    }
}
