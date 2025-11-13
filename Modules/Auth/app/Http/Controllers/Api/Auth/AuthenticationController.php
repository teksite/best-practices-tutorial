<?php

namespace Modules\Auth\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Requests\Auth\CheckUserRequest;
use Modules\Auth\Http\Requests\Auth\RegisterRequest;
use Modules\User\Models\User;

class AuthenticationController extends Controller
{
    public function checkUser(CheckUserRequest $request)
    {
        $username=$request->validated('username');
        $user=User::query()->where('email',$username)->orWhere('phone',$username)->first();
        if($user){
            return response()->json([
                'data'=>'user exists'
            ])->status(200);
        }
        return response()->json([
            'data'=>__('auth::validation.no_user_found')
        ])->setStatusCode(404);
    }

    public function register(RegisterRequest $request)
    {

//        $user=User::query()->create($request->validated());

        return response()->json([
            'message'=>__('auth::validation.register_success'),
            'errors'=>[],
        ])->setStatusCode(201);
    }
}
