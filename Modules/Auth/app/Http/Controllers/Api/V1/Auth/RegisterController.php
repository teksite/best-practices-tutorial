<?php

namespace Modules\Auth\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Enums\ContactType;
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
        $name = $request->input('name');
        $password = $request->input('password');
        $token = $request->input('token');
        $contactType = $request->contactType;
        $contactValue = $request->contactValue;
        $contactAltType = $request->contactAltType;
        $contactAltValue = $request->contactAltValue;

        $data = [
            'name'                 => $name,
            'password'             => $password, // Hash password
            $contactType->value    => $contactValue,
            $contactAltType->value => $contactAltValue,
        ];

        try {
            DB::transaction(function () use ($data, $contactType, $contactAltType, $contactAltValue, $token) {

                $user = User::query()->create($data);

                if ($contactType === ContactType::EMAIL) {
                    $user->markEmailAsVerified();
                } else {
                    $user->markPhoneAsUnverified();
                }

                // TODO: Implement sending verification email/phone functionality
                // Example: dispatch(new SendVerificationNotification($user, $contactType));

                $this->tokenService->forget($token);

                return response()->json([
                    'errors'  => [],
                    'message' => 'User created successfully.',
                    'data'    => ['user' => $user->only('id', 'name', 'email', 'phone')],
                    // Return only necessary fields
                ]);

            });
        } catch (\Throwable $exception) {
            Log::error($exception);
            return response()->json([
                'errors'  => [
                    'server_error' => 'An internal server error occurred.',
                ],
                'message' => 'Something went wrong.',
                'data'    => [],
            ], 500);
        }
    }
}
