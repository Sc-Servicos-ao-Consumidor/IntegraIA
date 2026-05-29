<?php

namespace App\Jobs;

use App\Models\Recipe;
use App\Services\PrismService;
use App\Services\RecipeChunkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateRecipeChunks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $recipeId;

    public function __construct(int $recipeId)
    {
        $this->recipeId = $recipeId;
    }

    public function handle(): void
    {
        $recipe = Recipe::find($this->recipeId);
        if (! $recipe) {
            return;
        }

        $chunkService = new RecipeChunkService(new PrismService);
        $chunkService->generateChunks($recipe);
    }
}
