<?php

namespace App\Jobs\WhatsApp;

use App\Models\CatalogPipelineStatus;
use App\Models\CatalogRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 60];

    public function __construct(public int $catalogRequestId) {}

    public function handle(): void
    {
        $request = CatalogRequest::find($this->catalogRequestId);

        if (! $request) {
            return;
        }

        $aiAnswer = $request->ai_answer;
        $contactId = $request->contact_id;

        // TODO: call WhatsApp send message API
        // Parameters: $contactId (recipient), $aiAnswer (message payload)

        $request->update([
            'catalog_pipeline_status_id' => CatalogPipelineStatus::idFor('completed'),
            'completed_at' => now(),
        ]);
    }
}
