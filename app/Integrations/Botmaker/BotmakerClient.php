<?php

namespace App\Integrations\Botmaker;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class BotmakerClient
{
    protected function request(): PendingRequest
    {
        return Http::withHeaders(['access-token' => config('services.botmaker.token')])
            ->baseUrl(config('services.botmaker.base_url'))
            ->acceptJson()
            ->contentType('application/json')
            ->connectTimeout(3)
            ->timeout(30);
    }

    /**
     * @throws RequestException
     */
    public function sendTypingFeedback(string $contactId): void
    {
        $this->request()
            ->post('/chats-actions/send-read-typing-feedback', [
                'channelId' => config('services.botmaker.channel_id'),
                'contactId' => $contactId,
                'typing' => true,
            ])
            ->throw();
    }
}
