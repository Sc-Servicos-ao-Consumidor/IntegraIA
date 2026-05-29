<?php

namespace App\Jobs\Catalog;

use App\Models\CatalogRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
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
        // sys_get_temp_dir() retorna o diretório temporário do sistema (/tmp no Linux).
        // Usamos ele para gravar arquivos temporários que não precisam persistir após o job.
        $inputPath = sys_get_temp_dir()."/{$id}_input";
        $mp3Path = sys_get_temp_dir()."/{$id}.mp3";

        try {
            $audioContent = Http::timeout(30)->get($request->audio_url)->body();
            file_put_contents($inputPath, $audioContent);

            // Process::run() é o facade do Laravel para executar comandos shell.
            // Substitui o exec() nativo do PHP e permite fazer Process::fake() nos testes.
            // O ffmpeg converte o AAC (formato real do Botmaker) para MP3, que o Whisper aceita.
            $result = Process::run("ffmpeg -y -i {$inputPath} -ar 16000 -ac 1 -b:a 64k {$mp3Path}");

            if ($result->failed()) {
                throw new RuntimeException('ffmpeg conversion failed: '.$result->errorOutput());
            }

            $transcript = Transcription::fromPath($mp3Path)->language('pt')->generate();

            $request->update(['question' => (string) $transcript]);
        } finally {
            @unlink($inputPath);
            @unlink($mp3Path);
        }
    }
}
