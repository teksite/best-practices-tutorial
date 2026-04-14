<?php

namespace Modules\Auth\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Services\AuthTokenService;
use Modules\Auth\Services\VerificationTokenService;


class LoginController extends Controller
{
    public function __construct(protected VerificationTokenService $verificationTokenService, protected AuthTokenService $authService)
    {
    }


    public function login(LoginRequest $request)
    {
        $token= $request->validated('token');

        $this->verificationTokenService->forget($token);

        dd($request->toArray());
    }
}
