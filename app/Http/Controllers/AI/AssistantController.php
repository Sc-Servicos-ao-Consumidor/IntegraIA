<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Jobs\UpdateAssistantLog;
use App\Models\Assistant;
use App\Models\AssistantLog;
use App\Services\AIToolService;
use App\Services\EmbeddingService;
use App\Services\PrismService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class AssistantController extends Controller
{
    public function assistant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|min:1',
            'context' => 'nullable',
            'use_tools' => 'nullable|boolean',
            'tenant_id' => 'integer|exists:tenants,id',
            'assistant_slug' => 'nullable|string|exists:assistants,slug',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $assistant = Assistant::where('slug', $request->assistant_slug)
            ->where('tenant_id', $request->tenant_id)
            ->first();

        // Fallback to the tenant's default assistant if none was found
        $assistant ??= Assistant::firstWhere([
            'tenant_id' => $request->tenant_id,
            'default' => true,
        ]);

        if (! $assistant) {
            return response()->json([
                'response' => 'Assistant not found.',
            ], 404);
        }

        $prism = new PrismService;

        $text = $prism->buildMessages([
            [
                'type' => 'user',
                'content' => $request->text,
            ],
        ]);

        $context = $this->cleanResultFromEmbeddings($request->context);
        $useTools = $request->use_tools ?? false;

        try {
            $tools = [];

            if ($useTools) {
                $aiToolService = new AIToolService($prism, new EmbeddingService($prism));

                $assistantTools = is_array($assistant->tools) ? array_values($assistant->tools) : [];

                $tools = $aiToolService->getTools(count($assistantTools) > 0 ? $assistantTools : []);
            }

            $tenantId = (int) ($request->input('tenant_id'));
            $tenantBasePrompt = null;

            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                $tenantBasePrompt = $tenant->base_prompt;
            }

            // Build base prompt prioritizing the Assistant's system_prompt, then tenant base prompt
            $assistantPrompt = $assistant->system_prompt ?? null;
            $basePrompt = trim(($assistantPrompt ? $assistantPrompt."\n\n" : '').($tenantBasePrompt ?? '')) ?: null;

            $sessionId = $request->hasSession() ? $request->session()->getId() : null;
            $queryText = $request->text;
            $assistantId = $assistant->id;
            $assistantSlug = $assistant->slug;
            $interaction = AssistantLog::create([
                'tenant_id' => $tenantId ?: null,
                'assistant_id' => $assistantId,
                'session_id' => $sessionId,
                'query' => $queryText,
                'response' => '',
                'meta' => [
                    'use_tools' => (bool) $useTools,
                    'tenant_id' => $tenantId ?? null,
                    'assistant_slug' => $assistantSlug,
                    'streamed' => true,
                ],
            ]);
            $interactionId = $interaction->id;

            return response()->stream(function () use (
                $prism,
                $text,
                $context,
                $tools,
                $basePrompt,
                $interactionId
            ) {
                $streamedText = '';

                $response = $prism->getStreamedResponse(
                    $text,
                    $context,
                    $tools,
                    $basePrompt,
                    function (string $delta) use (&$streamedText) {
                        $streamedText .= $delta;
                        echo $delta;

                        if (function_exists('ob_flush')) {
                            @ob_flush();
                        }
                        flush();
                    }
                );

                if (($response['status'] ?? 'error') !== 'success') {
                    $errorText = $response['response'] ?? 'Erro ao processar a solicitação';
                    echo $errorText;
                    $streamedText = $streamedText !== '' ? $streamedText : $errorText;
                }

                AssistantLog::whereKey($interactionId)->update([
                    'response' => $streamedText,
                ]);
            }, 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Cache-Control' => 'no-cache, no-transform',
                'X-Accel-Buffering' => 'no',
                'X-Interaction-Id' => (string) $interactionId,
            ]);

        } catch (\Exception $e) {
            Log::error('Assistant error:', ['error' => $e->getMessage()]);
            return response()->json([
                'response' => 'Assistant failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function assistantFeedback(Request $request)
    {
        $request->validate([
            'interaction_id' => 'nullable|integer|exists:assistant_logs,id',
            'query' => 'nullable|string',
            'response' => 'nullable|string',
            'rating' => 'nullable|string|in:up,down',
            'comment' => 'nullable|string',
        ]);

        $sessionId = $request->hasSession() ? $request->session()->getId() : null;

        UpdateAssistantLog::dispatch(
            $request->input('interaction_id'),
            $sessionId,
            $request->input('query'),
            $request->input('response'),
            $request->input('rating'),
            $request->input('comment')
        )->onQueue('store_intent_ai');

        return response()->json([
            'status' => 'queued',
        ]);
    }

    private function cleanResultFromEmbeddings($data)
    {
        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                if ($key === 'embedding') {
                    continue;
                }
                if (is_array($value)) {
                    $cleaned[$key] = $this->cleanResultFromEmbeddings($value);
                } else {
                    $cleaned[$key] = $value;
                }
            }

            return $cleaned;
        }

        return $data;
    }
}
