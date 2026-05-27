<?php

use App\Http\Controllers\AI\AssistantController;
use App\Http\Controllers\AI\SearchController;
use App\Http\Controllers\AI\SpeechController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Catalog\CatalogPipelineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Issue Sanctum personal access tokens
Route::post('/auth/token', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {

    // Catalog pipeline
    Route::post('/catalog/pipeline', CatalogPipelineController::class);

});
