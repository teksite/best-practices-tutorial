<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Auth\Emails\VerificationCodeEmail;
use Modules\Auth\Enums\VerificationActionType;

class SendCodeService
{
    public function viaSMS(string $to, string $code, VerificationActionType $action, ?string $expireAt=null): bool
    {
        $apiKey = config('services.msgway');
        $params = [
            "mobile"     => $to,
            "method"     => "sms",
            "templateID" => 16625,
            "params"     => [
                $code,
                $expireAt
            ]
        ];
        try {
            $res = Http::withHeaders(['apiKey'=> $apiKey, 'Accept' => 'application/json'])
                ->post('https://api.msgway.com/send', $params);
            if ($res->successful()) return true;
            throw new \Exception($res->clientError());
        } catch (\Exception $exception) {
            (new VerificationCodeService())->forget($to, $action);
            Log::error($exception);
            return false;
        }
    }
    public function viaEmail(string $to, string $code, VerificationActionType $action, ?string $expireAt=null): bool
    {

        try {
            $res = Mail::to($to)->send(new VerificationCodeEmail($code, $expireAt));
           if (!!$res) return true;
            throw new \Exception();
        } catch (\Exception $exception) {
            (new VerificationCodeService())->forget($to, $action);
            Log::error($exception);
            return false;
        }
    }


}

