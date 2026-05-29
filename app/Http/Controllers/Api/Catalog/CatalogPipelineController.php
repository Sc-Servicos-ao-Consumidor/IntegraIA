<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Jobs\Catalog\RunCatalogAgentJob;
use App\Jobs\Catalog\TranscribeAudioJob;
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
            'question' => ['required_without:audio_url', 'nullable', 'string', 'min:1'],
            'audio_url' => ['required_without:question', 'nullable', 'url'],
            'contact_id' => ['required', 'string'],
            'session_id' => ['required', 'string'],
            'tenant' => ['required', 'integer'],
        ]);

        $catalogRequest = CatalogRequest::create([
            'tenant_id' => $validated['tenant'],
            'catalog_pipeline_status_id' => CatalogPipelineStatus::idFor('pending'),
            'contact_id' => $validated['contact_id'],
            'session_id' => $validated['session_id'],
            'audio_url' => $validated['audio_url'] ?? null,
            'question' => $validated['question'] ?? null,
        ]);

        WhatsAppTypingJob::dispatch($validated['contact_id']);

        $jobs = isset($validated['audio_url'])
            ? [
                new TranscribeAudioJob($catalogRequest->id),
                new RunCatalogAgentJob($catalogRequest->id),
                new SendWhatsAppMessageJob($catalogRequest->id),
            ]
            : [new RunCatalogAgentJob($catalogRequest->id), new SendWhatsAppMessageJob($catalogRequest->id)];

        Bus::chain($jobs)->dispatch();

        return response()->json(['request_id' => $catalogRequest->id], 202);
    }
}
