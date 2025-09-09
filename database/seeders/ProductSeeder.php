<?php

namespace Database\Seeders;

use App\Models\GroupProduct;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductPackaging;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Tenant::where('slug', 'default')->value('id');

        // Get group products for relationships
        $basesGroup = GroupProduct::where('descricao', 'Knorr Professional - Bases e Molhos')->first();
        $caldosGroup = GroupProduct::where('descricao', 'Knorr Professional - Caldos e Temperos')->first();
        $carnesGroup = GroupProduct::where('descricao', 'Ingredientes Básicos - Carnes')->first();
        $vegetaisGroup = GroupProduct::where('descricao', 'Ingredientes Básicos - Vegetais e Ervas')->first();

        $productsData = [
            // Knorr Professional Products
            [
                'product' => [
                    'tenant_id' => $tenantId,
                    'ulid' => (string) Str::ulid(),
                    'slug' => 'base-tomate-knorr-750g',
                    'codigo_padrao' => 'KNR-BT-750',
                    'sku' => 'KNR001750',
                    'group_product_id' => $basesGroup->id,
                    'marca' => 'Knorr Professional',
                    'descricao' => 'Base de Tomate Knorr Professional',
                    'descricao_breve' => 'Base desidratada para molho de tomate com ingredientes selecionados',
                    'ean' => '7891234567890',
                    'status' => true,
                    // New form-specific fields
                    'prompt_uso_informacoes_produto' => 'Use 1 sachê para cada 2 litros de água',
                    'especificacao_produto' => 'Base desidratada de tomate com temperos selecionados',
                    'perfil_sabor' => 'Sabor intenso de tomate com notas de ervas aromáticas',
                    'descricao_tabela_nutricional' => 'Porção de 100g: Energia 320kcal, Proteínas 12g, Carboidratos 45g, Gorduras 8g',
                    'descricao_lista_ingredientes' => 'Tomate desidratado, sal, açúcar, cebola, alho, ervas aromáticas',
                    'descricao_modos_preparo' => '1. Dissolva o conteúdo em água quente\n2. Cozinhe por 5 minutos\n3. Adicione temperos a gosto',
                    'descricao_rendimentos' => '1 sachê rende aproximadamente 2 litros de molho pronto',
                ],
                'packaging' => [
                    'ulid' => (string) Str::ulid(),
                    'codigo_padrao' => 'KNR-BT-750-PKG',
                    'sku' => 'KNR001750PKG',
                    'descricao' => 'Embalagem Base de Tomate Knorr 750g',
                    'status' => true,
                    'prompt_especificacao_embalagens' => 'Embalagem em sachê de 750g, ideal para uso profissional',
                ],
                'images' => [
                    [
                        'image_type' => 'principal',
                        'nome' => 'Base Tomate Knorr - Imagem Principal',
                        'url' => 'https://example.com/images/base-tomate-knorr.jpg',
                        'ordem' => 1,
                        'ativo' => true,
                    ],
                    [
                        'image_type' => 'embalagem',
                        'nome' => 'Base Tomate Knorr - Embalagem',
                        'url' => 'https://example.com/images/base-tomate-knorr-embalagem.jpg',
                        'ordem' => 1,
                        'ativo' => true,
                        'valida_produtos_embalagem' => true,
                    ],
                ]
            ],
            [
                'product' => [
                    'tenant_id' => $tenantId,
                    'ulid' => (string) Str::ulid(),
                    'slug' => 'caldo-carne-knorr-1kg',
                    'codigo_padrao' => 'KNR-CC-1000',
                    'sku' => 'KNR002100',
                    'group_product_id' => $caldosGroup->id,
                    'marca' => 'Knorr Professional',
                    'descricao' => 'Caldo de Carne Knorr Professional',
                    'descricao_breve' => 'Caldo concentrado em pó sabor carne para realçar preparações',
                    'ean' => '7891234567891',
                    'status' => true,
                    // New form-specific fields
                    'escolha_embalagem' => 'pote',
                    'prompt_uso_informacoes_produto' => 'Use 1 colher de sopa para cada litro de água',
                    'especificacao_produto' => 'Caldo concentrado em pó sabor carne',
                    'perfil_sabor' => 'Sabor intenso de carne bovina com notas de legumes',
                    'descricao_tabela_nutricional' => 'Porção de 100g: Energia 280kcal, Proteínas 15g, Carboidratos 35g, Gorduras 5g',
                    'descricao_lista_ingredientes' => 'Extrato de carne bovina, sal, cebola, cenoura, aipo, temperos naturais',
                    'descricao_modos_preparo' => '1. Dissolva em água quente\n2. Adicione aos alimentos durante o cozimento\n3. Ajuste o sal conforme necessário',
                    'descricao_rendimentos' => '1kg rende aproximadamente 100 litros de caldo',
                ],
                'packaging' => [
                    'ulid' => (string) Str::ulid(),
                    'codigo_padrao' => 'KNR-CC-1000-PKG',
                    'sku' => 'KNR002100PKG',
                    'descricao' => 'Embalagem Caldo de Carne Knorr 1kg',
                    'status' => true,
                    'prompt_especificacao_embalagens' => 'Embalagem em pote de 1kg, com tampa hermética',
                ],
                'images' => [
                    [
                        'image_type' => 'principal',
                        'nome' => 'Caldo Carne Knorr - Imagem Principal',
                        'url' => 'https://example.com/images/caldo-carne-knorr.jpg',
                        'ordem' => 1,
                        'ativo' => true,
                    ],
                ]
            ],
            [
                'product' => [
                    'tenant_id' => $tenantId,
                    'ulid' => (string) Str::ulid(),
                    'slug' => 'carne-moida-bovina',
                    'codigo_padrao' => 'ING-CM-001',
                    'sku' => 'ING001001',
                    'group_product_id' => $carnesGroup->id,
                    'marca' => 'Frigorífico Premium',
                    'descricao' => 'Carne Moída Bovina',
                    'descricao_breve' => 'Carne bovina moída fresca de primeira qualidade',
                    'ean' => '7891234567892',
                    'status' => true,
                    // New form-specific fields
                    'prompt_uso_informacoes_produto' => 'Conserve refrigerado e consuma em até 3 dias',
                    'especificacao_produto' => 'Carne bovina moída com baixo teor de gordura',
                    'perfil_sabor' => 'Sabor natural de carne bovina fresca',
                    'descricao_tabela_nutricional' => 'Porção de 100g: Energia 250kcal, Proteínas 26g, Carboidratos 0g, Gorduras 15g',
                    'descricao_lista_ingredientes' => 'Carne bovina moída',
                    'descricao_modos_preparo' => '1. Tempere a gosto\n2. Refogue em fogo médio\n3. Cozinhe até dourar completamente',
                    'descricao_rendimentos' => '1kg rende aproximadamente 4 porções de 250g',
                ],
                'packaging' => [
                    'ulid' => (string) Str::ulid(),
                    'codigo_padrao' => 'ING-CM-001-PKG',
                    'sku' => 'ING001001PKG',
                    'descricao' => 'Embalagem Carne Moída Bovina 1kg',
                    'status' => true,
                    'prompt_especificacao_embalagens' => 'Embalagem em bandeja de isopor com filme plástico',
                ],
                'images' => [
                    [
                        'image_type' => 'principal',
                        'nome' => 'Carne Moída Bovina - Imagem Principal',
                        'url' => 'https://example.com/images/carne-moida.jpg',
                        'ordem' => 1,
                        'ativo' => true,
                    ],
                ]
            ],
            [
                'product' => [
                    'tenant_id' => $tenantId,
                    'ulid' => (string) Str::ulid(),
                    'slug' => 'oregano-desidratado',
                    'codigo_padrao' => 'ING-OR-001',
                    'sku' => 'ING002001',
                    'group_product_id' => $vegetaisGroup->id,
                    'marca' => 'Ervas Naturais',
                    'descricao' => 'Orégano Desidratado',
                    'descricao_breve' => 'Orégano desidratado em folhas para temperos',
                    'ean' => '7891234567893',
                    'status' => true,
                    // New form-specific fields
                    'prompt_uso_informacoes_produto' => 'Adicione no final do cozimento para preservar o aroma',
                    'especificacao_produto' => 'Orégano desidratado em folhas inteiras',
                    'perfil_sabor' => 'Aroma intenso e sabor característico do orégano mediterrâneo',
                    'descricao_tabela_nutricional' => 'Porção de 100g: Energia 265kcal, Proteínas 9g, Carboidratos 69g, Gorduras 4g',
                    'descricao_lista_ingredientes' => 'Orégano desidratado',
                    'descricao_modos_preparo' => '1. Adicione no final do cozimento\n2. Polvilhe sobre pizzas e massas\n3. Use em molhos e marinadas',
                    'descricao_rendimentos' => '50g rende aproximadamente 100 porções de 0,5g',
                ],
                'packaging' => [
                    'ulid' => (string) Str::ulid(),
                    'codigo_padrao' => 'ING-OR-001-PKG',
                    'sku' => 'ING002001PKG',
                    'descricao' => 'Embalagem Orégano Desidratado 50g',
                    'status' => true,
                    'prompt_especificacao_embalagens' => 'Embalagem em pote de vidro com tampa hermética',
                ],
                'images' => [
                    [
                        'image_type' => 'principal',
                        'nome' => 'Orégano Desidratado - Imagem Principal',
                        'url' => 'https://example.com/images/oregano.jpg',
                        'ordem' => 1,
                        'ativo' => true,
                    ],
                ]
            ],
        ];

        foreach ($productsData as $data) {
            // Create the product
            $product = Product::create($data['product']);
            
            // Create product packaging
            $packaging = $data['packaging'];
            $packaging['product_id'] = $product->id;
            ProductPackaging::create($packaging);
            
            // Create product images
            foreach ($data['images'] as $imageData) {
                $imageData['product_id'] = $product->id;
                ProductImage::create($imageData);
            }
        }
    }
}