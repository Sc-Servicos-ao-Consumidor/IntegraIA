<?php

namespace App\Integrations\Botmaker;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class BotmakerService
{
    public function __construct(protected BotmakerClient $client) {}

    public function sendTyping(string $contactId): void
    {
        try {
            $this->client->sendTypingFeedback($contactId);
        } catch (RequestException $e) {
            Log::error('Botmaker typing feedback failed', [
                'contact_id' => $contactId,
                'status' => $e->response->status(),
                'body' => $e->response->body(),
            ]);
        }
    }
}
