<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Jobs\Catalog\RunCatalogAgentJob;
use App\Jobs\WhatsApp\SendWhatsAppMessageJob;
use App\Jobs\WhatsApp\WhatsAppTypingJob;
use App\Models\CatalogPipelineStatus;
use App\Models\CatalogRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class CatalogPipelineController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:1'],
            'contact_id' => ['required', 'string'],
            'session_id' => ['required', 'string'],
            'tenant' => ['required', 'integer'],
        ]);

        $catalogRequest = CatalogRequest::create([
            'tenant_id' => $validated['tenant'],
            'catalog_pipeline_status_id' => CatalogPipelineStatus::idFor('pending'),
            'contact_id' => $validated['contact_id'],
            'session_id' => $validated['session_id'],
            'question' => $validated['question'],
        ]);

        WhatsAppTypingJob::dispatch($validated['contact_id']);

        Bus::chain([
            new RunCatalogAgentJob($catalogRequest->id),
            new SendWhatsAppMessageJob($catalogRequest->id),
        ])->dispatch();

        return response()->json(['request_id' => $catalogRequest->id], 202);
    }
}
