<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentChunk;
use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Vector;

class ContentChunkService
{
    protected PrismService $prismService;

    public function __construct(PrismService $prismService)
    {
        $this->prismService = $prismService;
    }

    public function generateChunks(Content $content): int
    {
        $created = 0;
        try {
            $content->chunks()->delete();

            $chunks = $this->buildChunks($content);
            foreach ($chunks as $index => $chunk) {
                $embedding = $this->prismService->getEmbedding($chunk['content']);
                ContentChunk::create([
                    'content_id' => $content->id,
                    'chunk_type' => $chunk['chunk_type'],
                    'content' => $chunk['content'],
                    'position' => $chunk['position'] ?? $index,
                    'embedding' => new Vector($embedding),
                ]);
                $created++;
            }
        } catch (\Throwable $e) {
            Log::error('Failed to generate content chunks', [
                'content_id' => $content->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $created;
    }

    protected function buildChunks(Content $content): array
    {
        $chunks = [];

        // Title & metadata
        $title = array_filter([
            $content->nome_conteudo,
            $content->tipo_conteudo,
            $content->pilares,
            $content->canal,
        ]);
        if (! empty($title)) {
            $chunks[] = [
                'chunk_type' => 'title_metadata',
                'content' => implode("\n", $title),
                'position' => 0,
            ];
        }

        // Description
        $desc = trim((string) ($content->descricao_conteudo ?? ''));
        if ($desc !== '') {
            $chunks[] = [
                'chunk_type' => 'description',
                'content' => $desc,
                'position' => 0,
            ];
        }

        // Roles flags and links
        $roles = [];
        if ($content->cozinheiro) {
            $roles[] = 'cozinheiro';
        }
        if ($content->comprador) {
            $roles[] = 'comprador';
        }
        if ($content->administrador) {
            $roles[] = 'administrador';
        }
        if (! empty($roles)) {
            $chunks[] = [
                'chunk_type' => 'roles',
                'content' => implode(', ', $roles),
                'position' => 0,
            ];
        }

        if (! empty($content->links_conteudo) && is_array($content->links_conteudo)) {
            $chunks[] = [
                'chunk_type' => 'links',
                'content' => collect($content->links_conteudo)->filter()->implode("\n"),
                'position' => 0,
            ];
        }

        return $chunks;
    }
}
