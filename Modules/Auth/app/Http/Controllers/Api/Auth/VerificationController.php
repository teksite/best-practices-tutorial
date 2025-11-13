<?php

namespace Modules\Auth\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use http\Exception\RuntimeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Http\Requests\Auth\CheckUserRequest;
use Modules\Auth\Http\Requests\Auth\SendVerificationCodeRequest;
use Modules\Auth\Services\TokenService;
use Modules\Auth\Services\VerificationCodeService;
use Modules\User\Models\User;
use Random\RandomException;
use function Pest\Laravel\put;

class VerificationController extends Controller
{

    public function __construct(private readonly VerificationCodeService $verificationCodeService ,private readonly TokenService $tokenService)
    {
    }

    /**
     * @throws RandomException
     */
    public function send(SendVerificationCodeRequest $request)
    {
        $action = $request->validated('action');
        $recipient = $request->validated('username');

        $code = $this->verificationCodeService->handle($recipient, VerificationActionType::from($action), AuthIdentifierType::detectType($recipient));

        return $this->verificationCodeService->Send($code['code'], $recipient, VerificationActionType::from($action));
    }



    public function verify(SendVerificationCodeRequest $request)
    {
        $action = $request->validated('action');
        $recipient = $request->validated('username');
        $code = $request->validated('code');


        if ($this->verificationCodeService->verify($recipient, VerificationActionType::from($action), $code)) {
            return response()->json([
                'message' => 'success',
                'errors' => [],
                'token'=>$this->tokenService->createVerificationToken($action, $recipient)
            ])->setStatusCode(200);
        }
        return response()->json([
            'message' => 'failed',
            'errors' => [
                'code' => 'wrong verification code',
            ],
        ])->setStatusCode(400);

    }


}
