<?php

namespace Modules\Auth\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Actions\CreateUser;
use Modules\Auth\Actions\AuthTokenAction;
use Modules\Auth\Http\Requests\Auth\CheckUserRequest;
use Modules\Auth\Http\Requests\Auth\LoginRequest;
use Modules\Auth\Http\Requests\Auth\RegisterRequest;
use Modules\Auth\Services\VerificationTokenService;
use Modules\Main\Services\ApiResponse;
use Modules\User\Models\User;
use Modules\User\Transformers\UserResource;

class AuthenticationController extends Controller
{
    public function __construct(private AuthTokenAction $authToken)
    {
    }

    public function checkUser(CheckUserRequest $request)
    {
        $username = $request->validated('username');
        $user = User::query()->where('email', $username)->orWhere('phone', $username)->first();
        if ($user) return ApiResponse::success(message: 'user exists');
        ApiResponse::failed(['username' => __('auth::validation.no_user_found')], status: 404);
    }

    public function register(RegisterRequest $request)
    {

        try {
            $user = (new CreateUser())->handle($request);
            $authToken = $this->authToken->create($user);
        } catch (\Exception $exception) {
            return ApiResponse::failed(['server' => __('auth::validation.server_error')], status: 500);
        }

        (new VerificationTokenService())->forget($request->validated('token'));

        return ApiResponse::success([
            'user' => (new UserResource($user)),
            'token' => $authToken,
        ], 201)->withCookie(cookie(
            name: 'x_web_token',
            value: $authToken,
            minutes: 30 * 3600 * 24,
            path: '/',
            domain: config('session.domain'),
        ));
    }

    public function login(LoginRequest $request)
    {

        try {
            $user = $request->user;
            $authToken = $this->authToken->create($user);
        } catch (\Exception $exception) {
            return ApiResponse::failed(['server' => __('auth::validation.server_error')], status: 500);
        }

        (new VerificationTokenService())->forget($request->validated('token'));

        return ApiResponse::success([
            'user' => (new UserResource($user)),
            'token' => $authToken,
        ], 201)->withCookie(cookie(
            name: 'x_web_token',
            value: $authToken,
            minutes: 30 * 3600 * 24,
            path: '/',
            domain: config('session.domain'),
        ));

    }


    public function who()
    {
        return ApiResponse::success([
            'user' => (new UserResource(auth()->user())),
        ]);

    }
}
