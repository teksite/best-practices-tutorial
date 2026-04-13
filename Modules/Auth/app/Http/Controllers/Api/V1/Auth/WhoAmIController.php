<?php

namespace Modules\Auth\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Main\Services\ResponseJson;
use Modules\User\Transformers\UserResource;

class WhoAmIController extends Controller
{
    public function __construct()
    {
    }


    public function whoAmI(Request $request)
    {
        dd(auth('sanctum')->check());
        return ResponseJson::Success(
            UserResource::make(auth('sanctum')->user()),
            ':)');

    }
}
