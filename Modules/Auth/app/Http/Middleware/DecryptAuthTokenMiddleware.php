<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class DecryptAuthTokenMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $encryptedToken = $this->extractEncryptedToken($request);

            if ($encryptedToken) {
                // 2) Clean Bearer prefix
                $encryptedToken = $this->cleanBearerPrefix($encryptedToken);

                // 3) Decrypt
                $decrypted = Crypt::decryptString($encryptedToken);

                // 4) Unserialize (because Encrypt() serializes automatically)
                $token = $this->safeUnserialize($decrypted);

                // 5) Put decrypted token back into request as valid Authorization header
                $request->headers->set('Authorization', 'Bearer '.$token);
            }

        } catch (\Throwable $exception) {
            Log::error('Token decrypt failed: '.$exception->getMessage());
            abort(403);
        }

        return $next($request);
    }

    private function extractEncryptedToken(Request $request): ?string
    {
        if ($request->headers->has('Authorization')) {
            return $request->header('Authorization');
        }

        if ($request->cookie('x_web_token')) {
            return $request->cookie('x_web_token');
        }

        return null;
    }

    private function cleanBearerPrefix(string $token): string
    {
        return preg_replace('/^Bearer\s+/i', '', $token);
    }

    private function safeUnserialize(string $data): mixed
    {
        // اگر serialize نبود، مستقیم مقدار را برگردان
        if (!str_starts_with($data, 's:') && !str_contains($data, '";')) {
            return $data;
        }

        return @unserialize($data) ?: $data;
    }
}
