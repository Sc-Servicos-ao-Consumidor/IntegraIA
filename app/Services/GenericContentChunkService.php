<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Vector;
use App\Models\GenericContentChunk;
use App\Models\GenericContent;

class GenericContentChunkService
{
    protected PrismService $prismService;

    public function __construct(PrismService $prismService)
    {
        $this->prismService = $prismService;
    }

    /**
     * Regenerate chunks (and embeddings) for a generic content row.
     * Returns the number of chunks created.
     */
    public function generateChunks(GenericContent $genericContent): int
    {
        $created = 0;

        try {
            // Clean existing chunks
            $genericContent->chunks()->delete();

            $chunks = $this->buildChunks($genericContent);
            foreach ($chunks as $index => $chunk) {
                $embedding = $this->prismService->getEmbedding($chunk['content']);

                GenericContentChunk::create([
                    'generic_content_id' => $genericContent->id,
                    'chunk_type' => $chunk['chunk_type'],
                    'content' => $chunk['content'],
                    'position' => $chunk['position'] ?? $index,
                    'embedding' => new Vector($embedding),
                ]);
                $created++;
            }
        } catch (\Throwable $e) {
            Log::error('Failed to generate generic content chunks', [
                'id' => $genericContent->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $created;
    }

    protected function buildChunks(GenericContent $genericContent): array
    {
        $chunks = [];

        // Title & type
        $title = array_filter([
            $genericContent->title,
            $genericContent->type,
        ]);
        if (! empty($title)) {
            $chunks[] = [
                'chunk_type' => 'title_type',
                'content' => implode("\n", array_map(fn ($v) => trim((string) $v), $title)),
                'position' => 0,
            ];
        }

        // Main content
        $content = trim((string) ($genericContent->content ?? ''));
        if ($content !== '') {
            $chunks[] = [
                'chunk_type' => 'content',
                'content' => $content,
                'position' => 0,
            ];
        }

        // Data JSON
        if (! empty($genericContent->data) && is_array($genericContent->data)) {
            $flat = $this->flattenJsonForEmbedding($genericContent->data);
            if ($flat !== '') {
                $chunks[] = [
                    'chunk_type' => 'data',
                    'content' => $flat,
                    'position' => 0,
                ];
            }
        }

        // Meta JSON
        if (! empty($genericContent->meta) && is_array($genericContent->meta)) {
            $flat = $this->flattenJsonForEmbedding($genericContent->meta);
            if ($flat !== '') {
                $chunks[] = [
                    'chunk_type' => 'meta',
                    'content' => $flat,
                    'position' => 0,
                ];
            }
        }

        return $chunks;
    }

    /**
     * Flatten a nested array/object to a newline-joined string.
     *
     * @param mixed $value
     */
    protected function flattenJsonForEmbedding($value): string
    {
        if (is_scalar($value)) {
            return (string) $value;
        }
        if (is_array($value)) {
            return collect($value)
                ->map(function ($v) {
                    if (is_array($v) || is_object($v)) {
                        return $this->flattenJsonForEmbedding($v);
                    }
                    return (is_string($v) || is_numeric($v)) ? (string) $v : '';
                })
                ->filter()
                ->implode("\n");
        }
        if (is_object($value)) {
            return $this->flattenJsonForEmbedding((array) $value);
        }
        return '';
    }
}


