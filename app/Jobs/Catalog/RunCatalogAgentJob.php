<?php

namespace App\Jobs\Catalog;

use App\Ai\Agents\CatalogAnswerAgent;
use App\Integrations\Vendas\VendasProductService;
use App\Models\CatalogPipelineStatus;
use App\Models\CatalogRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunCatalogAgentJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60];

    public function __construct(public int $catalogRequestId) {}

    public function handle(VendasProductService $vendasProductService): void
    {
        $request = CatalogRequest::find($this->catalogRequestId);

        if (! $request) {
            return;
        }

        $processingStatus = CatalogPipelineStatus::firstOrCreate(['name' => 'processing']);

        $request->update([
            'started_at' => now(),
            'catalog_pipeline_status_id' => $processingStatus->id,
        ]);

        try {
            $agent = new CatalogAnswerAgent(
                vendasProductService: $vendasProductService,
                tenantId: $request->tenant_id,
                contactId: $request->contact_id,
            );

            $response = $agent->prompt($request->question);

            $request->update(['ai_answer' => $response->text]);
        } catch (\Throwable $e) {
            $failedStatus = CatalogPipelineStatus::firstOrCreate(['name' => 'failed']);

            $request->update([
                'catalog_pipeline_status_id' => $failedStatus->id,
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }
}
