<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Interfaces\Auth\MustVerifyPhone;
use Modules\Main\Services\ApiResponse;

class EnsureUserVerifiedMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user= auth('sanctum')->user();
        if (!$user) return $next($request);

      if ($user instanceof MustVerifyPhone && !$user->hasVerifiedPhone()) {
          if ($request->wantsJson()) {
              return ApiResponse::failed(['message' => __('auth::validation.user_not_verified')], status: 403);
          }
          return abort(403, __('auth::validation.user_not_verified'));
      }


        return $next($request);
    }


}
