<?php

namespace Modules\Auth\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Actions\CreateUser;
use Modules\Auth\Http\Requests\Auth\CheckUserRequest;
use Modules\Auth\Http\Requests\Auth\RegisterRequest;
use Modules\Auth\Services\VerificationTokenService;
use Modules\User\Models\User;

class AuthenticationController extends Controller
{
    public function checkUser(CheckUserRequest $request)
    {
        $username = $request->validated('username');
        $user = User::query()->where('email', $username)->orWhere('phone', $username)->first();
        if ($user) {
            return response()->json([
                'message' => 'user exists',
                'errors' => [],

            ])->status(200);
        }
        return response()->json([
            'errors' => [
                'username' => __('auth::validation.no_user_found')
            ],
            'message' => 'failed'
        ])->setStatusCode(404);
    }

    public function register(RegisterRequest $request)
    {

//        $user = (new CreateUser())->handle($request);

//        (new VerificationTokenService())->forget($request->validated('token'));

        return response()->json([
            'data' => [
//                'user' => $user->toArray()
            ],
            'message' => __('auth::validation.register_success'),
            'errors' => [],
        ])->setStatusCode(201);
    }
}
