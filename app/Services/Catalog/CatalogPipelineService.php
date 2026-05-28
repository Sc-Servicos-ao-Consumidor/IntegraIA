<?php

namespace App\Services\Catalog;

use App\Jobs\Catalog\RunCatalogAgentJob;
use App\Jobs\WhatsApp\SendWhatsAppMessageJob;
use App\Jobs\WhatsApp\WhatsAppTypingJob;
use App\Models\CatalogPipelineStatus;
use App\Models\CatalogRequest;
use Illuminate\Support\Facades\Bus;
use InvalidArgumentException;

class CatalogPipelineService
{
    public function dispatch(string $question, string $contactId, string $sessionId, int $tenantId): CatalogRequest
    {
        if (trim($question) === '') {
            throw new InvalidArgumentException('Question cannot be empty.');
        }

        $request = CatalogRequest::create([
            'tenant_id' => $tenantId,
            'catalog_pipeline_status_id' => CatalogPipelineStatus::idFor('pending'),
            'contact_id' => $contactId,
            'session_id' => $sessionId,
            'question' => $question,
        ]);

        WhatsAppTypingJob::dispatch($contactId);

        Bus::chain([
            new RunCatalogAgentJob($request->id),
            new SendWhatsAppMessageJob($request->id),
        ])->dispatch();

        return $request;
    }
}
