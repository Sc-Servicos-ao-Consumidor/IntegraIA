<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        // Get products for relationships
        $baseTomateKnorr = Product::where('descricao', 'Base de Tomate Knorr Professional')->first();
        $caldoCarneKnorr = Product::where('descricao', 'Caldo de Carne Knorr Professional')->first();
        $meuTemperoKnorr = Product::where('descricao', 'Meu Tempero Knorr Professional')->first();
        $carneMoida = Product::where('descricao', 'Carne Moída Bovina')->first();
        $carneDesfiada = Product::where('descricao', 'Carne Bovina Desfiada')->first();
        $oregano = Product::where('descricao', 'Orégano Desidratado')->first();

        $recipes = [
            // Molho à Bolonhesa com Base de Tomate Knorr
            [
                'recipe_code' => 'REC-001',
                'recipe_name' => 'Molho à Bolonhesa com Base de Tomate Knorr',
                'recipe_type' => 'Molho',
                'service_order' => 'Acompanhamento',
                'preparation_time' => 30,
                'difficulty_level' => 'Médio',
                'yield' => '50 porções',
                'channel' => 'Restaurante',
                'recipe_description' => 'Molho à bolonhesa cremoso e saboroso, preparado com Base de Tomate Knorr Professional, garantindo qualidade e consistência em cada preparo.',
                'ingredients_description' => 'Base de Tomate Knorr Professional, carne moída bovina, água, Meu Tempero Knorr',
                'preparation_method' => "1. Refogue a carne moída até dourar\n2. Adicione 6L de água e 1 embalagem de Base de Tomate Knorr (750g)\n3. Adicione 100g de Meu Tempero Knorr\n4. Cozinhe em fogo baixo por 15 minutos mexendo ocasionalmente\n5. Ajuste o tempero se necessário\n6. Sirva quente",
                'usage_groups' => 'Restaurantes',
                'preparation_techniques' => 'Refogado, Cozimento lento',
                'consumption_occasion' => 'Almoço, Jantar',
            ],

            // Ragú de Carne com Base de Tomate Knorr
            [
                'recipe_code' => 'REC-002',
                'recipe_name' => 'Ragú de Carne com Base de Tomate Knorr',
                'recipe_type' => 'Molho',
                'service_order' => 'Acompanhamento',
                'preparation_time' => 45,
                'difficulty_level' => 'Médio',
                'yield' => '45 porções',
                'channel' => 'Restaurante',
                'recipe_description' => 'Ragú tradicional com carne bovina desfiada e molho de tomate encorpado, preparado com Base de Tomate Knorr Professional.',
                'ingredients_description' => 'Base de Tomate Knorr Professional, carne bovina desfiada, água, Caldo de Carne Knorr',
                'preparation_method' => "1. Aqueça a carne bovina desfiada\n2. Adicione 6L de água e 1 embalagem de Base de Tomate Knorr (750g)\n3. Adicione 160g de Caldo de Carne Knorr\n4. Cozinhe em fogo baixo por 25 minutos\n5. Mexe ocasionalmente até obter consistência cremosa\n6. Ajuste o tempero\n7. Sirva sobre massas ou risotos",
                'usage_groups' => 'Restaurantes',
                'preparation_techniques' => 'Cozimento lento, Redução',
                'consumption_occasion' => 'Almoço, Jantar',
            ],

            // Molho de Pizza com Base de Tomate Knorr
            [
                'recipe_code' => 'REC-003',
                'recipe_name' => 'Molho de Pizza com Base de Tomate Knorr',
                'recipe_type' => 'Molho',
                'service_order' => 'Base',
                'preparation_time' => 15,
                'difficulty_level' => 'Fácil',
                'yield' => '40 pizzas',
                'channel' => 'Pizzaria',
                'recipe_description' => 'Molho de pizza aromático e saboroso, preparado com Base de Tomate Knorr Professional e orégano fresco.',
                'ingredients_description' => 'Base de Tomate Knorr Professional, água, orégano desidratado',
                'preparation_method' => "1. Misture 1 embalagem de Base de Tomate Knorr (750g) com 5L de água\n2. Adicione 10g de orégano desidratado\n3. Misture bem até obter consistência homogênea\n4. Deixe repousar por 5 minutos\n5. Está pronto para usar nas pizzas",
                'usage_groups' => 'Pizzarias',
                'preparation_techniques' => 'Mistura simples',
                'consumption_occasion' => 'Almoço, Jantar, Lanche',
            ]
        ];

        foreach ($recipes as $data) {
            $recipe = Recipe::create($data);

            // Add product relationships based on recipe
            switch ($recipe->recipe_code) {
                case 'REC-001': // Molho à Bolonhesa
                    if ($baseTomateKnorr) {
                        $recipe->products()->attach($baseTomateKnorr->id, [
                            'quantity' => 750,
                            'unit' => 'g',
                            'ingredient_type' => 'main',
                            'preparation_notes' => 'Misturar com 6L de água',
                            'optional' => false,
                            'order' => 1,
                            'top_dish' => true
                        ]);
                    }
                    if ($carneMoida) {
                        $recipe->products()->attach($carneMoida->id, [
                            'quantity' => 3000,
                            'unit' => 'g',
                            'ingredient_type' => 'main',
                            'preparation_notes' => 'Refogar até dourar',
                            'optional' => false,
                            'order' => 2,
                            'top_dish' => false
                        ]);
                    }
                    if ($meuTemperoKnorr) {
                        $recipe->products()->attach($meuTemperoKnorr->id, [
                            'quantity' => 100,
                            'unit' => 'g',
                            'ingredient_type' => 'supporting',
                            'preparation_notes' => 'Adicionar no final do preparo',
                            'optional' => false,
                            'order' => 3,
                            'top_dish' => false
                        ]);
                    }
                    break;

                case 'REC-002': // Ragú de Carne
                    if ($baseTomateKnorr) {
                        $recipe->products()->attach($baseTomateKnorr->id, [
                            'quantity' => 750,
                            'unit' => 'g',
                            'ingredient_type' => 'main',
                            'preparation_notes' => 'Misturar com 6L de água',
                            'optional' => false,
                            'order' => 1,
                            'top_dish' => true
                        ]);
                    }
                    if ($carneDesfiada) {
                        $recipe->products()->attach($carneDesfiada->id, [
                            'quantity' => 3000,
                            'unit' => 'g',
                            'ingredient_type' => 'main',
                            'preparation_notes' => 'Carne já cozida e desfiada',
                            'optional' => false,
                            'order' => 2,
                            'top_dish' => false
                        ]);
                    }
                    if ($caldoCarneKnorr) {
                        $recipe->products()->attach($caldoCarneKnorr->id, [
                            'quantity' => 160,
                            'unit' => 'g',
                            'ingredient_type' => 'supporting',
                            'preparation_notes' => 'Realça o sabor da carne',
                            'optional' => false,
                            'order' => 3,
                            'top_dish' => false
                        ]);
                    }
                    break;

                case 'REC-003': // Molho de Pizza
                    if ($baseTomateKnorr) {
                        $recipe->products()->attach($baseTomateKnorr->id, [
                            'quantity' => 750,
                            'unit' => 'g',
                            'ingredient_type' => 'main',
                            'preparation_notes' => 'Misturar com 5L de água',
                            'optional' => false,
                            'order' => 1,
                            'top_dish' => true
                        ]);
                    }
                    if ($oregano) {
                        $recipe->products()->attach($oregano->id, [
                            'quantity' => 10,
                            'unit' => 'g',
                            'ingredient_type' => 'supporting',
                            'preparation_notes' => 'Adicionar para aromatizar',
                            'optional' => false,
                            'order' => 2,
                            'top_dish' => false
                        ]);
                    }
                    break;
            }
        }
    }
}