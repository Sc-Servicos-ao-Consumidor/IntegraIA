<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecipeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_recipe_successfully()
    {
        $response = $this->post('/recipes', [
            'id' => 1,
            'recipe_name' => 'Test Recipe',
            'recipe_description' => 'Test recipe description...',
        ]);

        $response->assertRedirect(); // Inertia redirect after form post

        $this->assertDatabaseHas('recipes', [
            'recipe_name' => 'Test Recipe',
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->post('/recipes', []);
        $response->assertSessionHasErrors(['recipe_name']);
    }
}
