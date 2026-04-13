<?php

namespace Modules\Auth\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Requests\SendVerificationCodeRequest;
use Modules\Auth\Services\TokenService;
use Modules\Main\Services\ResponseJson;
use Modules\User\Logic\UserLogic;
use Modules\User\Transformers\UserResource;

class RegisterController extends Controller
{
    public function __construct(protected TokenService $tokenService, protected UserLogic $Logic)
    {
    }


    public function store(RegisterRequest $request)
    {
        $name = $request->input('name');
        $password = $request->input('password');
        $token = $request->input('token');
        $contactType = $request->contactType;
        $contactValue = $request->contactValue;
        $contactAltType = $request->contactAltType;
        $contactAltValue = $request->contactAltValue;

        $data = [
            'name'                 => $name,
            'password'             => $password,
            $contactType->value    => $contactValue,
            $contactAltType->value => $contactAltValue,
        ];

        if ($contactType === ContactType::EMAIL) {
            $data['email_verified_at'] = now();
        } elseif ($contactType === ContactType::PHONE) {
            $data['phone_verified_at'] = now();
        }

        try {
            $user = DB::transaction(function () use ($data, $contactType, $contactAltType, $contactAltValue, $token) {
                $user = $this->Logic->register($data);
                $this->tokenService->forget($token);
                return $user;
            });

            // TODO: Implement sending verification email/phone functionality
            // Example: dispatch(new SendVerificationNotification($user, $contactType));


            if (!!$user) {
                return ResponseJson::Success(['user' => UserResource::make($user)], trans('main::messages.global.create_success', ['attribute' => __('user')]));
            }
            throw new \Exception(trans('main::messages.global.create_failed', ['attribute' => __('user')]));
        } catch (\Throwable $exception) {

            Log::error($exception);

            return ResponseJson::Failed([
                'server_error' => trans('main::messages.global.server_wrong'),
            ], trans('main::messages.global.server_wrong'));

        }
    }
}
