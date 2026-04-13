<?php

namespace Modules\Auth\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Http\Requests\SendVerificationCodeRequest;
use Modules\Auth\Http\Requests\VerifyVerificationCodeRequest;
use Modules\Auth\Services\SendCodeService;
use Modules\Auth\Services\TokenService;
use Modules\Auth\Services\VerificationCodeService;

class VerificationCodeController extends Controller
{
    public function __construct(protected VerificationCodeService $codeService, protected SendCodeService $sendService, protected TokenService $tokenService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function send(SendVerificationCodeRequest $request)
    {
        $to = $request->contactValue;
        $contactType = $request->contactType;
        $actionType =$request->actionType;
        $code = $this->codeService->generate($to, $actionType);
        $res = false;
        if ($contactType === ContactType::PHONE) {
            $res = $this->sendService->viaSMS($to, $code['code'], $actionType, $code['expire_at']);
        } elseif ($contactType === ContactType::EMAIL) {
            $res = $this->sendService->viaEmail($to, $code['code'], $actionType, $code['expire_at']);
        }

        return $res ? response()->json([
            'message' => 'code send successfully',
            'error'   => [],
            'data'    => [],
        ]) : response()->json([
            'message' => 'failed to send code, please try again',
            'errors'  => ['server' => ['an error occurred',]],
            'data'    => [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function verify(VerifyVerificationCodeRequest $request)
    {
        $contact = $request->contact;
        $actionType =$request->actionType;

        $token = $this->tokenService->create($contact ,$actionType);
        return response()->json([
            'message' => trans('auth::messages.verification_code.valid'),
            'error'   => [],
            'data'    => [
                'token' => $token,
            ],
        ]);
    }
}
