<?php

namespace Modules\Auth\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Http\Requests\Auth\SendVerificationCodeRequest;
use Modules\Auth\Services\VerificationTokenService;
use Modules\Auth\Services\VerificationCodeService;
use Modules\Main\Services\ApiResponse;
use Random\RandomException;

class VerificationController extends Controller
{

    public function __construct(private readonly VerificationCodeService $verificationCodeService, private readonly VerificationTokenService $tokenService)
    {
    }

    /**
     * @throws RandomException
     */
    public function send(SendVerificationCodeRequest $request)
    {
        $action = $request->validated('action');
        $recipient = $request->validated('username');
        $recipientType = AuthIdentifierType::detectType($recipient);

        $code = $this->verificationCodeService->handle($recipient, VerificationActionType::from($action), $recipientType);

        $response = $this->verificationCodeService->Send($code['code'], $recipient, VerificationActionType::from($action));

        if (!$response) {
            $this->verificationCodeService->forget($recipient, $recipientType);
            return ApiResponse::failed(['unknown' => __('error end sending code')]);
        }

        return ApiResponse::success();

    }


    public function verify(SendVerificationCodeRequest $request)
    {
        $action = $request->validated('action');
        $recipient = $request->validated('username');
        $code = $request->validated('code');


        if ($this->verificationCodeService->verify($recipient, VerificationActionType::from($action), $code)) {
            $token = $this->tokenService->createVerificationToken(VerificationActionType::from($action), $recipient, [$request->userAgent(), $request->ip()]);

            return ApiResponse::success([
                'token' => $token
            ]);
        }
        return ApiResponse::success([['code' => __('auth::validation.wrong_code'),],], 400);


    }


}
