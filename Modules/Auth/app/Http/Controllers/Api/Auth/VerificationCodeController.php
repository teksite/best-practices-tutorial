<?php

namespace Modules\Auth\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
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
        $recipientType = $request->validated('usernameType');
        $recipient = $request->validated('username');


        $service = new VerificationCodeService();
        $service->send($recipient , VerificationActionType::from($action) , VerificationUsernameType::from($recipientType));

        dd($service->getKey($action ,VerificationActionType::from($action) ));



        return 'vlidated code';
    }
}
