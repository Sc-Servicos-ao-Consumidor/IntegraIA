<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Services\PrismService;
use Illuminate\Console\Command;
use NunoMaduro\Collision\Provider;
use Prism\Prism\Prism;

class GenerateRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:generate {count=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate example recipes using AI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');

        $this->info("Generating $count recipes...");

        for ($i = 0; $i < $count; $i++) {
            $prompt = "Create a realistic cooking recipe in this format:\n\nTitle:\nTags:\nInstructions:";

            $prismService = new PrismService;
            $response = $prismService->getResponse($prompt);

            $content = $response->content;

            $title = $this->extractSection($content, 'Title');
            $tags = explode(',', $this->extractSection($content, 'Tags'));
            $rawText = $this->extractSection($content, 'Instructions');

            $embeddingInput = "$title\n" . implode(', ', $tags) . "\n$rawText";

            $embeddingResponse = $prismService->getEmbedding($embeddingInput);

            Recipe::create([
                'title' => trim($title),
                'raw_text' => trim($rawText),
                'tags' => array_map('trim', $tags),
                'embedding' => $embeddingResponse->embeddings[0]->embedding,
            ]);

            $this->info("✓ Added: $title");
        }

        $this->info("Done.");
    }

    protected function extractSection(string $text, string $label): string
    {
        preg_match("/$label:\s*(.*?)\n(?:\w+:|$)/s", $text . "\n", $matches);
        return $matches[1] ?? '';
    }
}
