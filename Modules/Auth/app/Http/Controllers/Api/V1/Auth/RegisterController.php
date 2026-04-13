<?php

namespace Modules\Auth\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Requests\SendVerificationCodeRequest;
use Modules\Auth\Services\TokenService;
use Modules\User\Models\User;

class RegisterController extends Controller
{
    public function __construct(protected TokenService $tokenService)
    {
    }


    public function store(RegisterRequest $request)
    {


//        $user= User::create($request->validated());
//
//        return response()->json([
//            'errors'=>[],
//            'message'=>'User created successfully',
//            'data'=>['user'=>$user->toArray()]
//        ]);
    }

}
