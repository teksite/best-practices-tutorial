<?php

namespace Modules\TelegramBot\Webhooks;

use Carbon\Carbon;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Facades\Log;
use Modules\User\Models\User;
use Symfony\Component\CssSelector\Exception\ExpressionErrorException;

class TelegramWebhook extends WebhookHandler
{
    public function start()
    {
       $this->chat->message('welcome, this is teksite world')->send();
    }

    public function login(string $token)
    {
        try {
            $decryptedToken=decrypt($token);
            $token=explode("::",$decryptedToken);
            Log::info($token);
            $userId=$token[0];
            $userEmail=$token[1];
            $timestamp=$token[2];

            if (Carbon::parse($timestamp)->addMinutes(10)->lessThanOrEqualTo(Carbon::now())) {
                throw new ExpressionErrorException('the token is expired, retry and get new one from yout panel');
            }
            $user=User::find($userId);
            if (!$user || $user->email !== $userEmail) {
                throw new \Exception('the user is not a valid telegram user');
            }
            $user->forceFill([
                'telegraph_chat_id' => $this->chat->id,
            ])->save();


            $this->chat->message('you are signed successfully')->send();
        }catch (\Exception $exception){
            $this->chat->message($exception->getMessage() ?? 'invalid script, retry with the correct one get in your user panel')->send();
        }
    }
}
