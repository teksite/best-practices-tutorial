<?php

namespace Modules\Auth\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Actions\AuthTokenAction;
use Modules\Auth\Http\Requests\Auth\VerifyRequest;
use Modules\User\Actions\MarkVerifyUser;
use Modules\User\Actions\ResetUserPassword;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Http\Requests\Auth\CheckUserRequest;
use Modules\Auth\Http\Requests\Auth\ForgotPasswordRequest;
use Modules\Auth\Http\Requests\Auth\LoginRequest;
use Modules\Auth\Http\Requests\Auth\RegisterRequest;
use Modules\Auth\Services\VerificationTokenService;
use Modules\Main\Services\ApiResponse;
use Modules\User\Actions\CreateUser;
use Modules\User\Models\User;
use Modules\User\Transformers\UserResource;

class AuthenticationController extends Controller
{
    public function __construct(private readonly AuthTokenAction $authAction, private VerificationTokenService $tokenServicee)
    {
    }

    public function checkUser(CheckUserRequest $request)
    {
        $username = $request->validated('username');
        $user = User::query()->where('email', $username)->orWhere('phone', $username)->first();
        if ($user) {
            return ApiResponse::success(message: 'user exists');
        }

        ApiResponse::failed(['username' => __('auth::validation.no_user_found')], status: 404);
    }

    public function register(RegisterRequest $request)
    {

        try {
            $user = (new CreateUser())->handle($request);
            $authToken = $this->authAction->create($user);
            $this->tokenServicee->forget($request->validated('token'));
        } catch (\Exception $exception) {
            Log::error($exception);
            return ApiResponse::failed(['server' => __('auth::validation.server_error')], status: 500);
        }

        $this->tokenServicee->forget($request->validated('token'));

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
            $authToken = $this->authAction->create($user);

        } catch (\Exception $exception) {
            Log::error($exception);
            return ApiResponse::failed(['server' => __('auth::validation.server_error')], status: 500);
        }


        if ($request->recipientType == AuthIdentifierType::Email) {
            $user->markEmailAsVerified();
        }
        if ($request->recipientType == AuthIdentifierType::Phone) {
            $user->markPhoneAsVerified();
        }

        $this->tokenServicee->forget($request->validated('token'));

        return ApiResponse::success([
            'user' => (new UserResource($user)),
            'token' => $authToken,
        ], 201)->withCookie(cookie(
            name: 'x_web_token',
            value: $authToken,
            minutes: 30 * 60 * 24,
            path: '/',
            domain: config('session.domain'),
        ));

    }

    public function forget(ForgotPasswordRequest $request)
    {

        try {
            $user = $request->user;
            (new ResetUserPassword())->handle($user, $request->validated('password'));
        } catch (\Exception $exception) {
            Log::error($exception);
            return ApiResponse::failed(['server' => __('auth::validation.server_error')], status: 500);
        }

        $this->tokenServicee->forget($request->validated('token'));

        return ApiResponse::success([], 201);

    }

    public function verify(VerifyRequest $request)
    {
        try {
            $verifier = (new MarkVerifyUser());
            $user = auth('sanctum')->user();
            if ($request->validated('phone')) $verifier->handle($user, 'phone');
            if ($request->validated('email')) $verifier->handle($user, 'email');
            $this->tokenServicee->forget($request->validated('token'));

            return ApiResponse::success(['user' => $user]);
        } catch (\Exception $exception) {
            Log::error($exception);
            return ApiResponse::failed(['server' => __('auth::validation.server_error')], status: 500);
        }
    }


    public function who()
    {
        return ApiResponse::success([
            'user' => (new UserResource(auth()->user())),
        ]);

    }
}
