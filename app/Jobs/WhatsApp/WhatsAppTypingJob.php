<?php

namespace App\Jobs\WhatsApp;

use App\Integrations\Botmaker\BotmakerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class WhatsAppTypingJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public string $contactId) {}

    public function handle(BotmakerService $botmakerService): void
    {
        $botmakerService->sendTyping($this->contactId);
    }
}
