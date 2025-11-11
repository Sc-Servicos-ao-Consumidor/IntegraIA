<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIController;
use App\Http\Controllers\Api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Issue Sanctum personal access tokens
Route::post('/auth/token', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {

    // Semantic search
    Route::get('/semantic-search', [AIController::class, 'search']);

    // Assistant
    Route::post('/assistant', [AIController::class, 'assistant']);
});
