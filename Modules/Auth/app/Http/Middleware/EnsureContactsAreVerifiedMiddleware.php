<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Main\Services\ResponseJson;

class EnsureContactsAreVerifiedMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user('sanctum');

        if (!$user?->verifiedContacts()) {
            if ($request->expectsJson()) {
                return ResponseJson::Failed(
                    $this->getErrors($user),
                    'Your contacts must be verified.',
                    403);
            }
            abort(403, 'Your contacts must be verified.');
        }

        return $next($request);
    }

    /**
     * @param mixed $user
     * @return array
     */
    public function getErrors(mixed $user): array
    {
        $errors = [];
        if ($user->hasVerifiedEmail()) {
            $errors['email'] = trans('auth::messages.auth.contact_is_not_verified', ['attribute' => __('email')]);
        }
        if ($user->hasVerifiedPhone()) {
            $errors['pone'] = trans('auth::messages.auth.contact_is_not_verified', ['attribute' => __('pone')]);
        }
        return $errors;
    }
}
