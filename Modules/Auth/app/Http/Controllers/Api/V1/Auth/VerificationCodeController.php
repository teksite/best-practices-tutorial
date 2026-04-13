<?php

namespace Modules\Auth\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Http\Requests\SendVerificationCodeRequest;
use Modules\Auth\Http\Requests\VerifyVerificationCodeRequest;
use Modules\Auth\Services\SendCodeService;
use Modules\Auth\Services\VerificationTokenService;
use Modules\Auth\Services\VerificationCodeService;
use Modules\Main\Services\ResponseJson;

class VerificationCodeController extends Controller
{
    public function __construct(protected VerificationCodeService $codeService, protected SendCodeService $sendService, protected VerificationTokenService $tokenService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function send(SendVerificationCodeRequest $request)
    {
        $to = $request->contactValue;
        $contactType = $request->contactType;
        $actionType = $request->actionType;
        $code = $this->codeService->generate($to, $actionType);
        $res = false;
        if ($contactType === ContactType::PHONE) {
            $res = $this->sendService->viaSMS($to, $code['code'], $actionType, $code['expire_at']);
        } elseif ($contactType === ContactType::EMAIL) {
            $res = $this->sendService->viaEmail($to, $code['code'], $actionType, $code['expire_at']);
        }

        return $res ? ResponseJson::success('data', 'code send successfully') : ResponseJson::Failed(['server' => ['an error occurred']], 'auth::messages.verification_code.failed');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function verify(VerifyVerificationCodeRequest $request)
    {
        $contact = $request->contact;
        $actionType = $request->actionType;

        $token = $this->tokenService->create($contact, $actionType);

        return ResponseJson::success(['token' => $token], trans('auth::messages.verification_code.sent_successfully'));


    }
}
