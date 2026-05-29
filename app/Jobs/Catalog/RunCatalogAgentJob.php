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

        $request->update([
            'started_at' => now(),
            'catalog_pipeline_status_id' => CatalogPipelineStatus::idFor('processing'),
        ]);

        try {
            $agent = new CatalogAnswerAgent(
                vendasProductService: $vendasProductService,
                tenantId: $request->tenant_id,
                contactId: $request->contact_id,
                sessionId: $request->session_id,
                catalogRequestId: $request->id,
            );

            $response = $agent->prompt($request->question);

            $request->update(['ai_answer' => $response->text]);
        } catch (\Throwable $e) {
            $request->update([
                'catalog_pipeline_status_id' => CatalogPipelineStatus::idFor('failed'),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }
}
