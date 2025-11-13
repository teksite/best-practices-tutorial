<?php

namespace Modules\Auth\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use http\Exception\RuntimeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Enums\VerificationUsernameType;
use Modules\Auth\Http\Requests\Auth\CheckUserRequest;
use Modules\Auth\Http\Requests\Auth\SendVerificationCodeRequest;
use Modules\Auth\Services\VerificationCodeService;
use Modules\User\Models\User;
use Random\RandomException;
use function Pest\Laravel\put;

class VerificationCodeController extends Controller
{
    /**
     * @throws RandomException
     */
    public function send(SendVerificationCodeRequest $request)
    {
        $action = $request->validated('action');
        $recipient = $request->validated('username');


        $service = new VerificationCodeService();
        $code = $service->handle($recipient, VerificationActionType::from($action), VerificationUsernameType::detectType($recipient));

        return $service->Send($code['code'], $recipient, VerificationActionType::from($action));
    }

    public function verify(SendVerificationCodeRequest $request)
    {
        $action = $request->validated('action');
        $recipient = $request->validated('username');
        $code = $request->validated('code');


        $service = new VerificationCodeService();

        if($service->verify($recipient,  VerificationActionType::from($action), $code)){
            return response()->json([
                'message' => 'success',
                'errors' => [],
            ])->setStatusCode(20);
        }
        return response()->json([
            'message' => 'failed',
            'errors' => [
                'code'=>'wrong verification code',
            ],
        ])->setStatusCode(400);

    }
}
