<?php

namespace App\Jobs\Catalog;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WhatsAppTypingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public string $contactId, public int $tenantId) {}

    public function handle(): void
    {
        // TODO: call WhatsApp typing API
        // Parameters: $this->contactId (recipient), $this->tenantId (tenant context)
    }
}
