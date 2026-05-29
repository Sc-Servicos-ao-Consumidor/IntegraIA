<?php

namespace App\Jobs\Catalog;

use App\Models\CatalogPipelineStatus;
use App\Models\CatalogRequest;
use App\Services\Catalog\CatalogSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CatalogSearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60];

    public function __construct(public int $catalogRequestId) {}

    public function handle(CatalogSearchService $searchService): void
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
            $results = $searchService->searchAsRagContext($request->question, $request->tenant_id);

            $request->update(['search_results' => $results]);
        } catch (\Throwable $e) {
            $request->update([
                'catalog_pipeline_status_id' => CatalogPipelineStatus::idFor('failed'),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }
}
