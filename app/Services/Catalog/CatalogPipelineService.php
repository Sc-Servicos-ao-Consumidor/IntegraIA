<?php

namespace App\Services\Catalog;

use App\Jobs\Catalog\CatalogSearchJob;
use App\Jobs\Catalog\GenerateCatalogAnswerJob;
use App\Jobs\Catalog\SendWhatsAppMessageJob;
use App\Jobs\Catalog\WhatsAppTypingJob;
use App\Models\CatalogPipelineStatus;
use App\Models\CatalogRequest;
use Illuminate\Support\Facades\Bus;
use InvalidArgumentException;

class CatalogPipelineService
{
    public function dispatch(string $question, string $contactId, int $tenantId): CatalogRequest
    {
        if (trim($question) === '') {
            throw new InvalidArgumentException('Question cannot be empty.');
        }

        $pendingStatus = CatalogPipelineStatus::firstOrCreate(['name' => 'pending']);

        $request = CatalogRequest::create([
            'tenant_id' => $tenantId,
            'catalog_pipeline_status_id' => $pendingStatus->id,
            'contact_id' => $contactId,
            'question' => $question,
        ]);

        WhatsAppTypingJob::dispatch($contactId, $tenantId);

        Bus::chain([
            new CatalogSearchJob($request->id),
            new GenerateCatalogAnswerJob($request->id),
            new SendWhatsAppMessageJob($request->id),
        ])->dispatch();

        return $request;
    }
}
