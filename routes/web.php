<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\AssistantLogController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\GroupProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    return redirect()->route('semantic-search');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/recipes/search', [AIController::class, 'search']);
    Route::get('/recipes/search-ingredients', [RecipeController::class, 'searchIngredients']);
    Route::get('/recipes/search-cuisines', [RecipeController::class, 'searchCuisines']);
    Route::get('/recipes/search-allergens', [RecipeController::class, 'searchAllergens']);
    Route::get('/semantic-search', function () {
        return Inertia::render('Recipes/SemanticSearch');
    })->name('semantic-search');
    Route::resource('recipes', RecipeController::class);
    Route::post('/recipes/assistant', [AIController::class, 'assistant']);
    Route::post('/recipes/assistant/feedback', [AIController::class, 'assistantFeedback']);

    // Products management
    Route::resource('products', ProductController::class);

    // Product Groups management
    Route::resource('group-products', GroupProductController::class);

    // Contents management
    Route::resource('contents', ContentController::class);

    // Tenant switching
    Route::post('/tenant/switch', [TenantController::class, 'switch'])->name('tenant.switch');

	// Assistant Logs
	Route::get('/assistant-logs', [AssistantLogController::class, 'index'])->name('assistant-logs.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
