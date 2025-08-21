<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class Recipe extends Model
{
    /** @use HasFactory<\Database\Factories\RecipeFactory> */
    use HasFactory;
    use HasNeighbors;

    protected $fillable = [
        'embedding',
        'recipe_code',
        'recipe_name', 
        'cuisine',
        'recipe_type',
        'service_order',
        'preparation_time',
        'difficulty_level',
        'yield',
        'channel',
        'recipe_description',
        'ingredients_description',
        'preparation_method',
        'main_ingredients',
        'supporting_ingredients',
        'usage_groups',
        'preparation_techniques',
        'consumption_occasion',
        'general_images_link',
        'product_code',
        'content_code'
    ];

    protected $casts = [
        'main_ingredients' => 'array',
        'supporting_ingredients' => 'array',
        'usage_groups' => 'array',
        'preparation_techniques' => 'array',
        'consumption_occasion' => 'array',
    ];

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
                'order'
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
            ->withPivot(['order'])
            ->withTimestamps()
            ->orderByPivot('order');
    }
}