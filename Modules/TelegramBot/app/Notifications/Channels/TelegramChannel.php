<?php

namespace Modules\TelegramBot\Notifications\Channels;

use DefStudio\Telegraph\Exceptions\TelegramWebhookException;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramChannel
{
    /**
     * @throws \Exception
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toTelegram')) {
            throw new \Exception('toTelegram method is not exists in ' . $notifiable::class);
        }
        $data = $notification->toTelegram($notifiable);

        if (empty($data['message'])) {
            throw new \InvalidArgumentException('message is empty in ' . $notifiable::class);
        }
        try {
           $response= Telegraph::chat($notifiable->telegramChats)->message($data['message'])
               ->keyboard(Keyboard::make()->buttons([
                   Button::make('Delete')->action('delete')->param('id', '42'),

               ]))->send();
          if (!$response->ok()) {
              $errorData=$response->json('description');
            if (Str($errorData)->contains('blocked by user')) {
                $notifiable->telegramChats()->delete();
                throw new TelegramWebhookException($errorData, $notifiable);
            }
            return;
          }
        }catch (ConnectionException $e){
            Log::error('connection error to telegram api: ' . $e->getMessage());
        }catch (\Exception $e){
            Log::error('exception: ' . $e->getMessage());
        }

    }

    /**
     * @param $notifiable
     * @param $data
     * @return Response|\GuzzleHttp\Promise\PromiseInterface
     * @throws ConnectionException
     */
    protected function viaKavenegar($notifiable, $data): Response|\GuzzleHttp\Promise\PromiseInterface
    {
        $url = str_replace('{API_KEY}', config('sms.providers.kevenegar.api_key'), config('sms.providers.kevenegar.url'));
        $receptor = $data['phone'] ?? (string)$notifiable->phone;
        $message = $data['message'] ?? (string)$notifiable->phone;
        $sender = config('sms.providers.kevenegar.sender');
        return Http::get($url, [
            'receptor' => $receptor,
            'message' => $message,
            'sender' => $sender,
        ]);
    }

}
