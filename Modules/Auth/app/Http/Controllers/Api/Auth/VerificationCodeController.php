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

class VerificationCodeController extends Controller
{
    /**
     * @throws RandomException
     */
    public function send(SendVerificationCodeRequest $request)
    {
      $action=$request->validated('action');
      $username=$request->validated('username');
      $usernameType=$request->validated('usernameType');

      $user=User::query()->where('phone',$username)->orWhere('email' ,$username)->first();

        $service =(new VerificationCodeService);
        $service->handle($user , VerificationUsernameType::from($usernameType) , VerificationActionType::from($action));
        $key =$service->getKey();
        dd($key);
        //as username (sms or email)
        //send code
        return 'vlidated code';
    }
}
