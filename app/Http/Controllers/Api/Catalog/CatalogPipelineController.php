<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Services\Catalog\CatalogPipelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogPipelineController extends Controller
{
    public function __invoke(Request $request, CatalogPipelineService $pipeline): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:1'],
            'contact_id' => ['required', 'string'],
            'tenant' => ['required', 'integer'],
        ]);


        $catalogRequest = $pipeline->dispatch(
            $validated['question'],
            $validated['contact_id'],
            $validated['tenant'],
        );

        return response()->json(['request_id' => $catalogRequest->id], 202);
    }
}
