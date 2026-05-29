<?php

namespace App\Jobs\Catalog;

use App\Models\CatalogRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Transcription;
use RuntimeException;

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

        $id = uniqid('audio_');
        $inputPath = sys_get_temp_dir()."/{$id}_input";
        $mp3Path = sys_get_temp_dir()."/{$id}.mp3";

        try {
            $audioContent = Http::timeout(30)->get($request->audio_url)->body();
            file_put_contents($inputPath, $audioContent);

            exec("ffmpeg -y -i {$inputPath} -ar 16000 -ac 1 -b:a 64k {$mp3Path} 2>&1", $output, $code);

            if ($code !== 0) {
                throw new RuntimeException('ffmpeg conversion failed: '.implode("\n", $output));
            }

            $transcript = Transcription::fromPath($mp3Path)->language('pt')->generate();

            $request->update(['question' => (string) $transcript]);
        } finally {
            @unlink($inputPath);
            @unlink($mp3Path);
        }
    }
}
