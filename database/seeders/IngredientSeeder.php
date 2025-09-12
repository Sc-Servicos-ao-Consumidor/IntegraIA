<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = [
            // Vegetais
            'Cebola', 'Alho', 'Tomate', 'Cenoura', 'Batata', 'Pimentão', 'Pepino', 'Alface',
            'Espinafre', 'Couve', 'Brócolis', 'Couve-flor', 'Abobrinha', 'Berinjela', 'Cogumelo',
            'Salsão', 'Salsa', 'Coentro', 'Manjericão', 'Orégano', 'Tomilho', 'Alecrim',

            // Carnes
            'Peito de Frango', 'Carne Moída', 'Bisteca de Porco', 'Bacon', 'Presunto', 'Peru',
            'Cordeiro', 'Linguiça', 'Salmão', 'Atum', 'Camarão', 'Bacalhau', 'Tilápia',

            // Laticínios e Ovos
            'Leite', 'Queijo', 'Manteiga', 'Ovos', 'Iogurte', 'Creme de Leite', 'Creme Azedo',
            'Cheddar', 'Mussarela', 'Parmesão', 'Ricota', 'Queijo Cottage',

            // Grãos e Pães
            'Arroz', 'Macarrão', 'Pão', 'Farinha', 'Aveia', 'Quinoa', 'Cevada',
            'Tortilha', 'Naan', 'Baguete', 'Pão Integral',

            // Leguminosas
            'Feijão Preto', 'Feijão Vermelho', 'Grão-de-bico', 'Lentilhas', 'Ervilhas',
            'Feijão Carioca', 'Feijão Branco', 'Ervilha Partida',

            // Frutas
            'Maçã', 'Banana', 'Laranja', 'Limão', 'Lima', 'Morango',
            'Mirtilo', 'Framboesa', 'Abacaxi', 'Manga', 'Abacate',

            // Nozes e Sementes
            'Amêndoas', 'Nozes', 'Pecãs', 'Castanhas de Caju', 'Amendoim', 'Sementes de Girassol',
            'Sementes de Chia', 'Sementes de Linhaça', 'Sementes de Abóbora',

            // Óleos e Gorduras
            'Azeite de Oliva', 'Óleo Vegetal', 'Óleo de Coco', 'Óleo de Gergelim',
            'Óleo de Canola', 'Óleo de Abacate',

            // Condimentos e Molhos
            'Sal', 'Pimenta-do-Reino', 'Molho de Soja', 'Ketchup', 'Mostarda',
            'Maionese', 'Molho Picante', 'Molho Inglês', 'Vinagre',
            'Vinagre Balsâmico', 'Vinagre de Maçã',

            // Adoçantes
            'Açúcar', 'Açúcar Mascavo', 'Mel', 'Xarope de Bordo', 'Néctar de Agave',
            'Estévia', 'Açúcar de Confeiteiro',

            // Especiarias e Ervas
            'Canela', 'Noz-moscada', 'Gengibre', 'Cúrcuma', 'Cominho', 'Páprica',
            'Pimenta em Pó', 'Pimenta-caiena', 'Folhas de Louro', 'Sálvia',
            'Endro', 'Hortelã', 'Cebolinha', 'Estragão',

            // Panificação
            'Bicarbonato de Sódio', 'Fermento em Pó', 'Extrato de Baunilha', 'Fermento Biológico',
            'Amido de Milho', 'Cacau em Pó', 'Gotas de Chocolate',

            // Caldos e Fundos
            'Caldo de Frango', 'Caldo de Carne', 'Caldo de Legumes', 'Caldo de Peixe',

            // Outros
            'Extrato de Tomate', 'Molho de Tomate', 'Leite de Coco', 'Leite Evaporado',
            'Leite Condensado', 'Farinha de Rosca', 'Panko', 'Fubá'
        ];

        foreach ($ingredients as $ingredientName) {
            Ingredient::create([
                'name' => $ingredientName,
            ]);
        }
    }
}
