<?php

namespace App\Jobs\Catalog;

use App\Models\CatalogPipelineStatus;
use App\Models\CatalogRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $tenantId = $request->tenant_id;

        // TODO: call WhatsApp send message API
        // Parameters: $contactId (recipient), $aiAnswer (message payload), $tenantId (tenant context)

        $request->update([
            'catalog_pipeline_status_id' => CatalogPipelineStatus::idFor('completed'),
            'completed_at' => now(),
        ]);
    }
}
