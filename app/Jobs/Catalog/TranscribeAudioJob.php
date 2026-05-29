<?php

namespace App\Jobs\Catalog;

use App\Models\CatalogRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Transcription;

class TranscribeAudioJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60];

    public function __construct(public int $catalogRequestId) {}

    public function handle(): void
    {
        $request = CatalogRequest::find($this->catalogRequestId);

        if (! $request || ! $request->audio_url) {
            return;
        }

        $tempPath = 'catalog-audio/'.uniqid().'.'.$this->extensionFrom($request->audio_url);

        try {
            $audioContent = Http::timeout(30)->get($request->audio_url)->body();
            Storage::put($tempPath, $audioContent);

            $transcript = Transcription::fromStorage($tempPath)
                ->language('pt')
                ->generate();

            $request->update(['question' => (string) $transcript]);
        } finally {
            Storage::delete($tempPath);
        }
    }

    private function extensionFrom(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        return pathinfo($path, PATHINFO_EXTENSION) ?: 'ogg';
    }
}
