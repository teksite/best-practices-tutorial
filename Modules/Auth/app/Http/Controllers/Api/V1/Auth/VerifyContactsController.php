<?php

namespace Modules\Auth\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Http\Requests\VerifyContactsRequest;
use Modules\Main\Services\ResponseJson;


class VerifyContactsController extends Controller
{
    public function __construct()
    {
    }

    public function verify(VerifyContactsRequest $request)
    {
        $contactValue = $request->contactValue;
        $contactType = $request->contactType;
        $user = $request->user;

        try {

            $user->verifyingContactType($contactType);
            return ResponseJson::Success($user, trans('auth::messages.auth.contact_verified_success' ,['attribute'=>$contactType->value]));

        } catch (\Exception $e) {

            Log::error($e);
            return ResponseJson::Failed([
                'server' => trans('auth::messages.auth.contact_verified_failed' ,['attribute'=>$contactType->value]),
            ], trans('auth::messages.auth.contact_verified_failed' ,['attribute'=>$contactType->value]));


        }

    }
}
