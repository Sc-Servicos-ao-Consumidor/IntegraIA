<?php

use App\Jobs\Catalog\TranscribeAudioJob;
use App\Models\CatalogRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Laravel\Ai\Transcription;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('transcribes audio and saves question on catalog request', function () {
    Http::fake(['*' => Http::response('fake-audio-bytes', 200)]);
    Process::fake(['ffmpeg *' => Process::result(exitCode: 0)]);
    Transcription::fake(['3 fardos de arroz Tio João 1kg']);

    $request = CatalogRequest::factory()->create([
        'audio_url' => 'https://storage.googleapis.com/botmaker/audio.ogg',
        'question' => null,
    ]);

    (new TranscribeAudioJob($request->id))->handle();

    expect($request->fresh()->question)->toBe('3 fardos de arroz Tio João 1kg');
});

it('does nothing when catalog request does not exist', function () {
    Process::fake();
    Transcription::fake();

    (new TranscribeAudioJob(99999))->handle();

    Process::assertNothingRan();
    Transcription::assertNothingGenerated();
});

it('does nothing when catalog request has no audio url', function () {
    Process::fake();
    Transcription::fake();

    $request = CatalogRequest::factory()->create(['audio_url' => null]);

    (new TranscribeAudioJob($request->id))->handle();

    Process::assertNothingRan();
    Transcription::assertNothingGenerated();
});

it('throws exception when ffmpeg conversion fails', function () {
    Http::fake(['*' => Http::response('fake-audio-bytes', 200)]);
    Process::fake([
        'ffmpeg *' => Process::result(errorOutput: 'Invalid data found', exitCode: 1),
    ]);
    Transcription::fake();

    $request = CatalogRequest::factory()->create([
        'audio_url' => 'https://storage.googleapis.com/botmaker/audio.ogg',
        'question' => null,
    ]);

    expect(fn () => (new TranscribeAudioJob($request->id))->handle())
        ->toThrow(RuntimeException::class, 'ffmpeg conversion failed');
});

it('downloads audio from the correct url', function () {
    Http::fake(['*' => Http::response('fake-audio-bytes', 200)]);
    Process::fake(['ffmpeg *' => Process::result(exitCode: 0)]);
    Transcription::fake(['produto encontrado']);

    $audioUrl = 'https://storage.googleapis.com/botmaker/test.ogg';
    $request = CatalogRequest::factory()->create([
        'audio_url' => $audioUrl,
        'question' => null,
    ]);

    (new TranscribeAudioJob($request->id))->handle();

    Http::assertSent(fn ($http) => $http->url() === $audioUrl);
});
