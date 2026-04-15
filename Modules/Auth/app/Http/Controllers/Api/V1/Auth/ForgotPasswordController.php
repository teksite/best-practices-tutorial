<?php

namespace Modules\Auth\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Http\Requests\ForgotPasswordRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Services\AuthTokenService;
use Modules\Auth\Services\VerificationTokenService;
use Modules\Main\Services\ResponseJson;
use Modules\User\Logic\UserLogic;
use Modules\User\Transformers\UserResource;

class ForgotPasswordController extends Controller
{
    public function __construct(protected VerificationTokenService $verificationTokenService, protected UserLogic $Logic)
    {
    }


    public function forgot(ForgotPasswordRequest $request)
    {
        $token = $request->validated('token');
        $password = $request->validated('password');
        $user = $request->user;
        try {
            if (!!$user) {

                $this->Logic->resetPassword($user, $password);

                return ResponseJson::Success(
                    [],
                    trans('auth::messages.auth.reset_password', ['attribute' => __('user')]));

            }
            $this->verificationTokenService->forget($token);

            throw new \Exception(trans('main::messages.global.server_wrong'));
        } catch (\Exception $exception) {
            Log::error($exception);
            return ResponseJson::Failed(
                ['server_error' => trans('main::messages.global.server_wrong'),],
                trans('main::messages.global.server_wrong'));
        }
    }
}
