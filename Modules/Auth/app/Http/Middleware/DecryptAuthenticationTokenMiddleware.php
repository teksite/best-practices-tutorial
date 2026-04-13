<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Services\AuthTokenService;
use Modules\Main\Services\ResponseJson;

class DecryptAuthenticationTokenMiddleware
{
    const string CLIENT_TOKEN_PREFIX = "Bearer ";

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {

        $headerToken = $request->header('authorization');
        $cookieToken = $request->cookies->get(AuthTokenService::PREFIX);

        if (!!$headerToken || !!$cookieToken) {

            $res = false;
            if (!!$headerToken) {
                $token = str_replace(self::CLIENT_TOKEN_PREFIX, '', $headerToken);
                $res = $this->setInHeader($request, $token);

            } elseif (is_null($headerToken) && !!$cookieToken) {
                $res = $this->setInHeader($request, $cookieToken);
            }

            if (!$res) {
                return ResponseJson::Failed(['server_error' => trans('auth::messages.verification_code.wrong_auth_token')]);
            }
        }


        return $next($request);


    }

    /**
     * @param Request $request
     * @param $token
     * @return bool
     */
    private function setInHeader(Request $request, $token): bool
    {
        try {
            $token = AuthTokenService::ENCRYPTING_TOKEN ? Crypt::decrypt($token) : $token;
            $request->headers->set('Authorization', "Bearer $token");
            return true;
        } catch (\Exception $exception) {
            if (app()->isLocal()) Log::error($exception);
            return false;
        }
    }
}
