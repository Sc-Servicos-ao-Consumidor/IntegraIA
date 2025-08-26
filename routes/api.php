<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\RecipeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// User authentication endpoint (requires authentication)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Product API endpoints
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products/search', [ProductController::class, 'search']);

// Content API endpoints  
Route::get('/contents/{id}', [ContentController::class, 'show']);
Route::post('/contents/search', [ContentController::class, 'search']);

// Recipe API endpoints
Route::get('/recipes/{id}', [RecipeController::class, 'show']);
Route::post('/recipes/search', [RecipeController::class, 'search']);
