<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class Recipe extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<\Database\Factories\RecipeFactory> */
    use HasFactory;
    use HasNeighbors;

    protected $fillable = [
        'tenant_id',
        'embedding',
        'recipe_code',
        'recipe_name',
        'recipe_type',
        'service_order',
        'preparation_time',
        'difficulty_level',
        'yield',
        'channel',
        'recipe_description',
        'recipe_prompt',
        'ingredients_description',
        'preparation_method',
        'usage_groups',
        'preparation_techniques',
        'consumption_occasion',
    ];

    /**
     * Get a comma-separated list of ingredient names for this recipe.
     */
    public function ingredientNamesList(): string
    {
        return $this->ingredients
            ? $this->ingredients->pluck('name')->filter()->implode(', ')
            : '';
    }

    /**
     * Get a comma-separated list of cuisine names for this recipe.
     */
    public function cuisineNamesList(): string
    {
        return $this->cuisines
            ? $this->cuisines->pluck('name')->filter()->implode(', ')
            : '';
    }

    public function getEmbeddingVector(): ?Vector
    {
        return $this->embedding ? new Vector($this->embedding) : null;
    }

    /**
     * The products that belong to this recipe.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot([
                'quantity',
                'unit',
                'ingredient_type',
                'preparation_notes',
                'optional',
                'order',
                'top_dish',
            ])
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * Get only main ingredient products.
     */
    public function mainProducts(): BelongsToMany
    {
        return $this->products()->wherePivot('ingredient_type', 'main');
    }

    /**
     * Get only supporting ingredient products.
     */
    public function supportingProducts(): BelongsToMany
    {
        return $this->products()->wherePivot('ingredient_type', 'supporting');
    }

    /**
     * The contents that belong to this recipe.
     */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class)
            ->withPivot(['order', 'top_dish'])
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * The ingredients that belong to this recipe.
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)
            ->withPivot([
                'primary_ingredient',
            ])
            ->withTimestamps()
            ->orderByPivot('primary_ingredient');
    }

    /**
     * Get only main ingredients.
     */
    public function mainIngredients(): BelongsToMany
    {
        return $this->ingredients()->wherePivot('primary_ingredient', true);
    }

    /**
     * Get only supporting ingredients.
     */
    public function supportingIngredients(): BelongsToMany
    {
        return $this->ingredients()->wherePivot('primary_ingredient', false);
    }

    /**
     * The cuisines that belong to this recipe.
     */
    public function cuisines(): BelongsToMany
    {
        return $this->belongsToMany(Cuisine::class)->withTimestamps();
    }

    /**
     * The allergens associated with this recipe.
     */
    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class)->withTimestamps();
    }

    /**
     * The semantic chunks for this recipe.
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(RecipeChunk::class);
    }
}
