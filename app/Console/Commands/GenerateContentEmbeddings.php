<?php

namespace App\Console\Commands;

use App\Models\Content;
use App\Services\EmbeddingService;
use App\Services\PrismService;
use Illuminate\Console\Command;

class GenerateContentEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'embedding:generate-content 
                            {--limit= : Limit the number of content items to process}
                            {--force : Regenerate embeddings even if they already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for content using the AI service';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting content embedding generation...');

        $limit = $this->option('limit');
        $force = $this->option('force');

        try {
            $embeddingService = new EmbeddingService(new PrismService());

            $query = Content::query();

            if (!$force) {
                $query->whereNull('embedding');
            }

            if ($limit) {
                $query->limit((int) $limit);
            }

            $contents = $query->get();

            if ($contents->isEmpty()) {
                $this->info('No content items to process.');
                return Command::SUCCESS;
            }

            $this->info("Processing {$contents->count()} content items...");

            $progressBar = $this->output->createProgressBar($contents->count());
            $progressBar->start();

            $successCount = 0;
            $errorCount = 0;

            foreach ($contents as $content) {
                if ($embeddingService->generateEmbedding($content)) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $this->warn("\nFailed to generate embedding for content ID: {$content->id}");
                }
                
                $progressBar->advance();
            }

            $progressBar->finish();

            $this->newLine(2);
            $this->info("Content embedding generation completed!");
            $this->table(
                ['Status', 'Count'],
                [
                    ['Success', $successCount],
                    ['Failed', $errorCount],
                    ['Total', $contents->count()]
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error generating content embeddings: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}