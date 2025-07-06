<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Recipe;
use Pgvector\Laravel\Vector;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        $recipes = [
            [
                'title' => 'Spaghetti Carbonara',
                'raw_text' => <<<TEXT
Ingredients:
- 200g spaghetti
- 100g pancetta
- 2 eggs
- 50g grated Parmesan
- Salt and black pepper

Instructions:
1. Cook spaghetti until al dente.
2. Fry pancetta until crispy.
3. Beat eggs and mix with cheese.
4. Combine all with pasta off heat to avoid scrambling eggs.
TEXT,
                'tags' => ['Italian', 'Quick', 'Comfort Food'],
                'embedding' => new Vector(array_fill(0, 1536, 0.001)), // Dummy data for now
            ],
            [
                'title' => 'Vegan Buddha Bowl',
                'raw_text' => <<<TEXT
Ingredients:
- Quinoa
- Chickpeas
- Roasted sweet potato
- Avocado
- Tahini dressing

Instructions:
1. Cook quinoa.
2. Roast sweet potato.
3. Assemble bowl with ingredients and drizzle with tahini.
TEXT,
                'tags' => ['Vegan', 'Healthy', 'Bowl'],
                'embedding' => new Vector(array_fill(0, 1536, 0.002)),
            ],
            [
                'title' => 'Chicken Tikka Masala',
                'raw_text' => <<<TEXT
Ingredients:
- Chicken breast
- Yogurt
- Garam masala
- Tomato paste
- Cream

Instructions:
1. Marinate chicken in spices and yogurt.
2. Grill or pan fry.
3. Simmer sauce and combine.
TEXT,
                'tags' => ['Indian', 'Spicy', 'Dinner'],
                'embedding' => new Vector (array_fill(0, 1536, 0.003)),
            ],
        ];

        foreach ($recipes as $data) {
            Recipe::create($data);
        }
    }
}