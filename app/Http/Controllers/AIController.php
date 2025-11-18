<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateAssistantLog;
use App\Models\Assistant;
use App\Models\Recipe;
use App\Models\Product;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\AssistantLog;
use App\Services\EmbeddingService;
use App\Services\AIToolService;
use App\Services\PrismService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Pgvector\Laravel\Distance;

class AIController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
            'type' => 'nullable|string|in:recipes,products,content,all',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        $query = $request->query('query');
        $type = $request->query('type', 'all');
        $limit = $request->query('limit', 2);

        try {
            $prism = new PrismService();
            $response = $prism->getEmbedding($query);

            $embedding = $prism->extractEmbeddingFromResponse($response);

            if (!$embedding || !is_array($embedding)) {
                return response()->json(['error' => 'Failed to generate embedding for query.'], 500);
            }

            $results = [];

            if ($type === 'all' || $type === 'recipes') {
                $recipes = Recipe::query()
                    ->nearestNeighbors('embedding', $embedding, Distance::Cosine)
                    ->take($limit)
                    ->with(['products'])
                    ->get()
                    ->map(function ($recipe) {
                        return collect($recipe)->except('embedding')->toArray();
                    });
                $results['recipes'] = $recipes;
            }

            if ($type === 'all' || $type === 'products') {
                $products = Product::query()
                    ->nearestNeighbors('embedding', $embedding, Distance::Cosine)
                    ->take($limit)
                    ->where('status', true)
                    ->with(['groupProduct'])
                    ->get()
                    ->map(function ($product) {
                        return collect($product)->except('embedding')->toArray();
                    });
                $results['products'] = $products;
            }

            if ($type === 'all' || $type === 'content') {
                $contents = Content::query()
                    ->nearestNeighbors('embedding', $embedding, Distance::Cosine)
                    ->take($limit)
                    ->where('status', true)
                    ->get()
                    ->map(function ($content) {
                        return collect($content)->except('embedding')->toArray();
                    });
                $results['contents'] = $contents;
            }

            return response()->json($results);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Search failed: ' . $e->getMessage()], 500);
        }
    }

    public function assistant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|min:1',
            'context' => 'nullable',
            'use_tools' => 'nullable|boolean',
            'tenant_id' => 'integer|exists:tenants,id',
            'assistant_slug' => 'string|exists:assistants,slug',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $assistant = Assistant::where('slug', $request->assistant_slug)
            ->where('tenant_id', $request->tenant_id)
            ->first();

        // Fallback to the tenant's default assistant if none was found
        $assistant ??= Assistant::firstWhere([
            'tenant_id' => $request->tenant_id,
            'is_default' => true,
        ]);

        if (!$assistant) {
            return response()->json([
                'message' => 'Assistant not found.',
            ], 404);
        }

        $prism = new PrismService();

        $text = $prism->buildMessages([
            [
                'type' => 'user',
                'content' => $request->text
            ]
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
            $basePrompt = trim(($assistantPrompt ? $assistantPrompt . "\n\n" : '') . ($tenantBasePrompt ?? '')) ?: null;

            $response = $prism->getResponse($text, $context, $tools, $basePrompt);

            $assistantText = $response['response'] ?? (is_string($response) ? $response : null);

            $interaction = AssistantLog::create([
                'tenant_id' => $tenantId ?: null,
                'assistant_id' => $assistant->id,
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'query' => $request->text,
                'response' => $assistantText,
                'meta' => [
                    'use_tools' => (bool) $useTools,
                    'tenant_id' => $tenantId ?? null,
                    'assistant_slug' => $assistant->slug,
                ],
            ]);

            $ok = isset($response['status']) ? (bool)$response['status'] : ($assistantText !== null);

            if ($ok) {
                return response()->json([
                    'response' => $assistantText ?? 'Sem resposta',
                    'interaction_id' => $interaction->id,
                ], 200);
            } else {
                return response()->json([
                    'response' => $assistantText ?? 'Erro ao processar a solicitação',
                    'interaction_id' => $interaction->id,
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'response' => 'Assistant failed: ' . $e->getMessage()
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
            'expected_response' => 'nullable|string',
        ]);

        $sessionId = $request->hasSession() ? $request->session()->getId() : null;

        UpdateAssistantLog::dispatch(
            $request->input('interaction_id'),
            $sessionId,
            $request->input('query'),
            $request->input('response'),
            $request->input('rating'),
            $request->input('expected_response')
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
