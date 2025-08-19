<?php

use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ContentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/recipes/search', [RecipeController::class, 'search']);
    Route::resource('recipes', RecipeController::class);
    Route::post('/recipes/assistant', [RecipeController::class, 'assistant']);
    
    // Products management
    Route::resource('products', ProductController::class);
    Route::post('/products/groups', [ProductController::class, 'storeGroup'])->name('products.groups.store');
    
    // Contents management
    Route::resource('contents', ContentController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
