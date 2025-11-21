<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_recipe_successfully()
    {
        // seed an allergen
        \Illuminate\Support\Facades\DB::table('allergens')->insert(['name' => 'Glúten']);
        $allergenId = \Illuminate\Support\Facades\DB::table('allergens')->where('name', 'Glúten')->value('id');

        $response = $this->post('/recipes', [
            'id' => 1,
            'recipe_name' => 'Test Recipe',
            'recipe_description' => 'Test recipe description...',
            'recipe_type' => 'doce',
            'preparation_method' => 'Modo',
            'selected_allergens' => [
                ['allergen_id' => $allergenId],
            ],
        ]);

        $response->assertRedirect(); // Inertia redirect after form post

        $this->assertDatabaseHas('recipes', [
            'recipe_name' => 'Test Recipe',
        ]);
        $this->assertDatabaseHas('allergens', ['name' => 'Glúten']);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->post('/recipes', []);
        $response->assertSessionHasErrors(['recipe_name']);
    }
}
