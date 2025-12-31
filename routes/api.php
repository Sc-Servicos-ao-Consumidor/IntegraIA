<?php

use App\Http\Controllers\AI\AssistantController;
use App\Http\Controllers\AI\SearchController;
use App\Http\Controllers\AI\SpeechController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Issue Sanctum personal access tokens
Route::post('/auth/token', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {

    // Semantic search
    Route::get('/semantic-search', [SearchController::class, 'search']);

    // Assistant
    Route::post('/assistant', [AssistantController::class, 'assistant']);

    Route::post('/text-to-speech', [SpeechController::class, 'textToSpeech']);

    Route::post('/speech-to-text', [SpeechController::class, 'speechToText']);
});
