<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Content;
use App\Services\ContentChunkService;
use App\Services\PrismService;

class GenerateContentChunks extends Command
{
    protected $signature = 'embedding:chunk-contents {--limit=} {--force}';
    protected $description = 'Generate chunked embeddings for contents';

    public function handle()
    {
        $this->info('Starting content chunk generation...');

        $limit = $this->option('limit');
        $force = $this->option('force');

        $service = new ContentChunkService(new PrismService());

        $query = Content::query();
        if (!$force) {
            $query->whereDoesntHave('chunks');
        }
        if ($limit) {
            $query->limit((int) $limit);
        }

        $contents = $query->get();
        if ($contents->isEmpty()) {
            $this->info('No contents to process.');
            return Command::SUCCESS;
        }

        $progress = $this->output->createProgressBar($contents->count());
        $progress->start();
        $created = 0;

        foreach ($contents as $content) {
            $created += $service->generateChunks($content);
            $progress->advance();
        }

        $progress->finish();
        $this->newLine(2);
        $this->info("Created {$created} chunks across {$contents->count()} contents.");
        return Command::SUCCESS;
    }
}


