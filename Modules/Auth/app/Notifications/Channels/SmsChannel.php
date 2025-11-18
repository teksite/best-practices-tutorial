<?php

namespace Modules\Auth\Notifications\Channels;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use function Webmozart\Assert\Tests\StaticAnalysis\string;

class SmsChannel
{
    /**
     * @throws \Exception
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toSms')) {
            throw new \Exception('viaSMs method is not exists in ' . $notifiable::class);
        }
        $data = $notification->toSms($notifiable);
        $provider = $data['provider'] ?? config('sms.default', 'kavenegar');

        if (empty($data['message'])) {
            throw new \InvalidArgumentException('message is empty in ' . $notifiable::class);
        }
        try {
            $response=match ($provider) {
                'kevenegar'=>$this->viaKavenegar($notifiable, $data),
            };
            if (!$response->ok()){
                throw new ConnectionException($response->body());
            }

        }catch (ConnectionException $e){
            Log::error('connection error: '.$provider.': ' . $e->getMessage());
        }catch (\Exception $e){
            Log::error('exception: '.$provider.': ' . $e->getMessage());
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
