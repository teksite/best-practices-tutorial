<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureContactsAreVerifiedMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user('sanctum')?->verifiedContacts()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your contacts must be verified.'
                ], 403);
            }
            abort(403, 'Your contacts must be verified.');
        }

        return $next($request);
    }
}
