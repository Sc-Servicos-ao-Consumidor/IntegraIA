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
                'title' => 'Base de Tomate Knorr - Embalagem',
                'raw_text' => 'EMBALAGEM FRONT KNORR BASE TOMATE DESIDRATADO BAG 6X750G FRONT.jpg',
                'metadata' => [
                    'brand' => 'Knorr Professional',
                    'product' => 'Base de Tomate Knorr',
                    'type' => 'Embalagem',
                    'audience' => ['Compradores', 'Gerentes', 'Administradores'],
                    'usage' => 'É a frente da embalagem, nela contém as informações: feito com 88 tomates, preparo em 1 minuto, rende mais que 2 latas (de 3,1 kg) de molho de tomate pronto para consumo, uma embalagem rende 6,7 kg de molho.',
                ],
                'tags' => ['embalagem', 'informacao-produto', 'destaque']
            ],
            [
                'title' => 'Base de Tomate Knorr - Tabela Nutricional',
                'raw_text' => "Informação Nutricional:  \n\nPorções per embalagem: cerca de 341 \n\nPorção:2,2 g (1/2 colher de chá) - prepara 2 colheres de sopa \n\nValor energético (kcal): 37/ 100 ml**, 7/ 2,2 g, 0/ %VD* \n\nCarboidratos (g): 7,2/ 100 ml**, 1,4/ 2,2 g, 0/ %VD* \n\nAçúcares totais (g): 1,4/ 100 ml**, 0,9/ 2,2 g, 0/ %VD* \n\nAçúcares adicionados (g): 1,9/ 100 ml**, 0,4/ 2,2 g, 1/ %VD* \n\nProteínas (g): 0,9/ 100 ml**, 0,2/ 2,2 g, 0/ %VD* \n\nGorduras totais (g): 0/ 100 ml**, 0/ 2,2 g, 0/ %VD* \n\nGorduras saturadas (g): 0,3/ 100 ml**, 0/ 2,2 g, 0/ %VD* \n\nGorduras trans (g): 0/ 100 ml**, 0/ 2,2 g, 0/ %VD* \n\nFibras alimentares (g): 0,9/ 100 ml**, 0,2 / 2,2 g, 1/ %VD* \n\nSódio (mg): 228/ 100 ml**, 0/ 45 g, 2/ %VD* \n\n*Percentual de valores diários fornecidos pela porção. \n\n**No alimento pronto para consumo.",
                'metadata' => [
                    'brand' => 'Knorr Professional',
                    'product' => 'Base de Tomate Knorr',
                    'type' => 'Tabela Nutricional',
                    'audience' => ['Compradores', 'Gerentes', 'Administradores'],
                    'usage' => 'Essa informação pode ser utilizada caso perguntem sobre algum nutriente ou informação da tabela nutricional específico.',
                ],
                'tags' => ['nutricao', 'informacao-tecnica', 'especificacoes']
            ],
            [
                'title' => 'Base de Tomate Knorr - Dicas de uso',
                'raw_text' => "Como substituir outros tipos de atomatados: \n\n1 Embalagem de Base de Tomate de 750g: \n\nTextura de molho: use 6L de água \n\nTextura de passata, molho mais denso: 4,5 L  \n\nTextura de Extrato, mais concentrado: 3,4 L",
                'metadata' => [
                    'brand' => 'Knorr Professional',
                    'product' => 'Base de Tomate Knorr',
                    'type' => 'Dicas de uso',
                    'audience' => ['Cozinheiros'],
                    'usage' => 'Essa informação pode ser útil se o cozinheiro utilizava outro atomatado na receita, como extrato ou passata, aqui temos a indicação de quanto usar de água por embalagem de produto para obter diferentes texturas de molho.',
                ],
                'tags' => ['dicas-preparo', 'substituicao', 'culinaria']
            ],
            [
                'title' => 'Base de Tomate Knorr - Dicas de uso',
                'raw_text' => "Como substituir outros tipos de atomatados: \n\n1 Embalagem de Base de Tomate de 750g: \n\nTextura de molho: use 6L de água \n\nTextura de passata, molho mais denso: 4,5 L  \n\nTextura de Extrato, mais concentrado: 3,4 L",
                'metadata' => [    
                    'brand' => 'Knorr Professional',
                    'product' => 'Base de Tomate Knorr',
                    'type' => 'Dicas de uso',
                    'audience' => ['Compradores', 'Gerentes', 'Administradores'],
                    'usage' => 'Caso a pessoa esteja interessada em substituir um outro atomatado pela Base de Tomate Knorr essa informação pode ser útil para ela saber quanto precisa comprar.',
                ],
                'tags' => ['comparacao', 'compra', 'estoque']
            ],
            [
                'title' => 'Base de Tomate Knorr - Dicas de uso',
                'raw_text' => "Variação de preparo - molho a bolonhesa: 1 embalagem de Base de Tomate (750g), 6 L de água, 3 kg Carne Moído e 100 g de Meu Tempero Knorr.",
                'metadata' => [
                    'brand' => 'Knorr Professional',
                    'product' => 'Base de Tomate Knorr',
                    'type' => 'Dicas de uso',
                    'audience' => ['Cozinheiros'],
                    'usage' => 'Proporções para fazer um molho a bolonhesa',
                ],
                'tags' => ['receita', 'bolonhesa', 'culinaria']
            ],
            [
                'title' => 'Base de Tomate Knorr - Dicas de uso',
                'raw_text' => "Variação de preparo- ragu: 1 embalagem de Base de Tomate (750g), 6 L de água, 3 kg Carne Bovina Desfiada e 160 g de Caldo de Carne  Knorr",
                'metadata' => [
                    'brand' => 'Knorr Professional',
                    'product' => 'Base de Tomate Knorr',
                    'type' => 'Dicas de uso',
                    'audience' => ['Cozinheiros'],
                    'usage' => 'Proporções para fazer ragu',
                ],
                'tags' => ['receita', 'ragu', 'culinaria']
            ],
            [
                'title' => 'Base de Tomate Knorr - Dicas de uso',
                'raw_text' => "Variação - molho de pizza: 1 embalagem de Base de Tomate (750g), 5 L de água e 10 g de orégano.",
                'metadata' => [
                    'brand' => 'Knorr Professional',
                    'product' => 'Base de Tomate Knorr',
                    'type' => 'Dicas de uso',
                    'audience' => ['Cozinheiros'],
                    'usage' => 'Proporções para molho de pizza',
                ],
                'tags' => ['receita', 'pizza', 'culinaria']
            ],
            [
                'title' => 'Base de Tomate Knorr - Ingredientes',
                'raw_text' => "Ingredientes: tomate desidratado, farinha de ervilha, açúcar, sal, gordura vegetal, alho, cebola, acidulante ácido cítrico e antiumectante dióxido de silício, Contém glúten. Alérgicos: contén derivados de soja. Pode conter aveia, centeio, cevada, crustáceos (camarão), triticale, leite, moluscos, peixes e trigo.",
                'metadata' => [
                    'brand' => 'Knorr Professional',
                    'product' => 'Base de Tomate Knorr',
                    'type' => 'Ingredientes',
                    'audience' => ['Compradores', 'Gerentes', 'Administradores'],
                    'usage' => 'Ingredientes escritos em texto, pode ser útil se perguntarem sobre algum item específico.',
                ],
                'tags' => ['composicao', 'alergicos', 'informacao-produto']
            ],
            [
                'title' => 'Base de Tomate Knorr - Argumento de venda',
                'raw_text' => "Nossas embalagens ficam no estoque seco, ocupando menos espaço que as latas tradicionais. E na hora do preparo, dão um show: 750 g fazem 6,7 kg de molho! 1 pacote de Base de Tomate rende mais que duas latas de 3,1 Kg.",
                'metadata' => [
                    'brand' => 'Knorr Professional',
                    'product' => 'Base de Tomate Knorr',
                    'type' => 'Argumento de venda',
                    'audience' => ['Compradores', 'Gerentes', 'Administradores'],
                    'usage' => 'Vantagem de ocupar menos espaço e sobre o rendimento do produto',
                ],
                'tags' => ['vantagens', 'estoque', 'rendimento']
            ],
            [
                'title' => 'Base de Tomate Knorr - Argumento de venda',
                'raw_text' => "Molho pronto em 1 minuto, trazendo mais economia para você: \n\nNo tempo de preparo:,  \n\nNo gás,  \n\nNa área de estoque",
                'metadata' => [
                    'brand' => 'Knorr Professional',
                    'product' => 'Base de Tomate Knorr',
                    'type' => 'Argumento de venda',
                    'audience' => ['Compradores', 'Gerentes', 'Administradores'],
                    'usage' => 'Resposta para caso perguntem sobre o que é possível economizar usando o produto.',
                ],
                'tags' => ['economia', 'praticidade', 'beneficios']
            ],
            [
                'title' => 'Base de Tomate Knorr - Argumento de venda',
                'raw_text' => "Dê adeus para a oscilação na qualidade: controle a acidez , a cor e a textura do molho com a Base de Tomate Desidratado Knorr Professional",
                'metadata' => [  
                    'brand' => 'Knorr Professional',
                    'product' => 'Base de Tomate Knorr',
                    'type' => 'Argumento de venda',
                    'audience' => ['Compradores', 'Gerentes', 'Administradores'],
                    'usage' => 'argumento sobre manter padrão.',
                ],
                'tags' => ['qualidade', 'consistencia', 'padronizacao']
            ],
            [
                'title' => 'Base de Tomate Knorr - Argumento de venda',
                'raw_text' => "VÍDEO GERAL _base de tomate.mp4",
                'metadata' => [
                    'brand' => 'Knorr Professional',
                    'product' => 'Base de Tomate Knorr',
                    'type' => 'Argumento de venda',
                    'audience' => ['Compradores', 'Gerentes', 'Administradores'],
                    'usage' => 'Caso queiram conhecer o produto, de forma geral, este vídeo pode ser enviado, fala que é feita com 88 tomates, que tem 100% de aproveitamento, 0 desperdício, pronto em 1 minuto, mostra o preparo',
                ],
                'tags' => ['video', 'apresentacao', 'destaque']
            ]
        ];

        foreach ($recipes as $data) {
            Recipe::create($data);
        }
    }
}