<?php

namespace Modules\Auth\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Services\AuthTokenService;
use Modules\Auth\Services\VerificationTokenService;
use Modules\Main\Services\ResponseJson;
use Modules\User\Transformers\UserResource;


class LoginController extends Controller
{
    public function __construct(protected VerificationTokenService $verificationTokenService, protected AuthTokenService $authService)
    {
    }


    public function login(LoginRequest $request)
    {
        $token = $request->validated('token');
        $this->verificationTokenService->forget($token);

        $user = $request->user;

        try {
            if (!!$user) {
                $user->verifyingContactType($request->contactType);

                $apiToken = $this->authService->create($user);
                return ResponseJson::Success(
                    [
                        'user'  => UserResource::make($user),
                        'token' => $this->authService->create($user),
                    ],
                    trans('main::messages.global.create_success', ['attribute' => __('user')]))
                                   ->withCookie(cookie('x_web_token', $apiToken, 24 * 28 * 60, config('session.domain'), null, true, true));

            }
            throw new \Exception(trans('main::messages.global.server_wrong'));
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return ResponseJson::Failed(
                ['server_error' => trans('main::messages.global.server_wrong'),],
                trans('main::messages.global.server_wrong'));
        }
    }

}
