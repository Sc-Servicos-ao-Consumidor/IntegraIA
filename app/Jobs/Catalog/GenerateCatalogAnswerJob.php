<?php

namespace App\Jobs\Catalog;

use App\Models\CatalogPipelineStatus;
use App\Models\CatalogRequest;
use App\Services\Catalog\CatalogAnswerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateCatalogAnswerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60];

    public function __construct(public int $catalogRequestId) {}

    public function handle(CatalogAnswerService $answerService): void
    {
        $request = CatalogRequest::find($this->catalogRequestId);

        if (! $request) {
            return;
        }

        try {
            $answer = $answerService->answerFromContext(
                $request->question,
                $request->search_results ?? []
            );

            $request->update(['ai_answer' => $answer]);
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
