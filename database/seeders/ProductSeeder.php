<?php

namespace Database\Seeders;

use App\Models\GroupProduct;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get group products for relationships
        $basesGroup = GroupProduct::where('descricao', 'Knorr Professional - Bases e Molhos')->first();
        $caldosGroup = GroupProduct::where('descricao', 'Knorr Professional - Caldos e Temperos')->first();
        $carnesGroup = GroupProduct::where('descricao', 'Ingredientes Básicos - Carnes')->first();
        $vegetaisGroup = GroupProduct::where('descricao', 'Ingredientes Básicos - Vegetais e Ervas')->first();

        $productsData = [
            // Knorr Professional Products
            [
                'product' => [
                    'ulid' => (string) Str::ulid(),
                    'slug' => 'base-tomate-knorr-750g',
                    'codigo_padrao' => 'KNR-BT-750',
                    'sku' => 'KNR001750',
                    'group_product_id' => $basesGroup->id,
                    'marca' => 'Knorr Professional',
                    'descricao' => 'Base de Tomate Knorr Professional',
                    'status' => true,
                ],
                'details' => [
                    'especificacao_produto' => 'Base desidratada para molho de tomate com ingredientes selecionados',
                    'perfil_sabor' => 'Sabor intenso de tomate com notas de ervas mediterrâneas',
                    'descricao_tabela_nutricional' => 'Rica em licopeno, fonte de vitaminas A e C',
                    'descricao_lista_ingredientes' => 'Tomate desidratado (88%), sal, açúcar, cebola, alho, especiarias naturais',
                    'descricao_modos_preparo' => 'Misture 1 colher de sopa em 200ml de água quente. Cozinhe por 1 minuto.',
                    'descricao_rendimentos' => 'Cada embalagem rende aproximadamente 3,1kg de molho pronto',
                    'embalagem_tipo' => 'bag',
                    'embalagem_descricao' => '6x750g',
                    'quantidade_caixa' => '6',
                    'peso_liquido' => 750.00,
                    'peso_bruto' => 780.00,
                    'validade' => '24 meses',
                    'valor' => 89.90,
                    'informacao_adicional' => 'Feita com 88 tomates, preparo em 1 minuto, rende mais que 2 latas de 3,1 kg de molho de tomate pronto.',
                    'catalogo' => true,
                    'lancamento' => false,
                    'status' => true,
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
                    'ulid' => (string) Str::ulid(),
                    'slug' => 'caldo-carne-knorr-1kg',
                    'codigo_padrao' => 'KNR-CC-1000',
                    'sku' => 'KNR002100',
                    'group_product_id' => $caldosGroup->id,
                    'marca' => 'Knorr Professional',
                    'descricao' => 'Caldo de Carne Knorr Professional',
                    'status' => true,
                ],
                'details' => [
                    'especificacao_produto' => 'Caldo concentrado em pó sabor carne para realçar preparações',
                    'perfil_sabor' => 'Sabor robusto de carne bovina com notas defumadas',
                    'descricao_tabela_nutricional' => 'Rico em proteínas e aminoácidos essenciais',
                    'descricao_lista_ingredientes' => 'Sal, extrato de carne bovina, gordura vegetal, cebola, especiarias',
                    'descricao_modos_preparo' => 'Dissolva 1 colher de chá em 250ml de água quente',
                    'descricao_rendimentos' => 'Cada embalagem rende aproximadamente 200 porções',
                    'embalagem_tipo' => 'pote',
                    'embalagem_descricao' => '12x1kg',
                    'quantidade_caixa' => '12',
                    'peso_liquido' => 1000.00,
                    'peso_bruto' => 1050.00,
                    'validade' => '18 meses',
                    'valor' => 45.90,
                    'informacao_adicional' => 'Caldo concentrado para realçar o sabor de carnes e preparações.',
                    'catalogo' => true,
                    'lancamento' => false,
                    'status' => true,
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
                    'ulid' => (string) Str::ulid(),
                    'slug' => 'carne-moida-bovina',
                    'codigo_padrao' => 'ING-CM-001',
                    'sku' => 'ING001001',
                    'group_product_id' => $carnesGroup->id,
                    'marca' => null,
                    'descricao' => 'Carne Moída Bovina',
                    'status' => true,
                ],
                'details' => [
                    'especificacao_produto' => 'Carne bovina moída fresca de primeira qualidade',
                    'perfil_sabor' => 'Sabor característico da carne bovina',
                    'descricao_tabela_nutricional' => 'Alta fonte de proteína, ferro e vitaminas do complexo B',
                    'descricao_lista_ingredientes' => '100% carne bovina moída',
                    'descricao_modos_preparo' => 'Tempere e cozinhe em fogo médio até dourar',
                    'descricao_rendimentos' => 'Rendimento de aproximadamente 4 porções por kg',
                    'embalagem_tipo' => 'kg',
                    'embalagem_descricao' => 'Por kg',
                    'quantidade_caixa' => '1',
                    'peso_liquido' => 1.00,
                    'peso_bruto' => 1.00,
                    'validade' => '3 dias refrigerado',
                    'valor' => 32.90,
                    'informacao_adicional' => 'Carne bovina moída com baixo teor de gordura.',
                    'catalogo' => true,
                    'lancamento' => false,
                    'status' => true,
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
                    'ulid' => (string) Str::ulid(),
                    'slug' => 'oregano-desidratado',
                    'codigo_padrao' => 'ING-OR-001',
                    'sku' => 'ING002001',
                    'group_product_id' => $vegetaisGroup->id,
                    'marca' => null,
                    'descricao' => 'Orégano Desidratado',
                    'status' => true,
                ],
                'details' => [
                    'especificacao_produto' => 'Orégano desidratado em folhas para temperos',
                    'perfil_sabor' => 'Aroma intenso e sabor característico de orégano',
                    'descricao_tabela_nutricional' => 'Rico em antioxidantes e óleos essenciais',
                    'descricao_lista_ingredientes' => '100% orégano desidratado',
                    'descricao_modos_preparo' => 'Adicione no final do cozimento para preservar o aroma',
                    'descricao_rendimentos' => 'Cada embalagem tempera até 50 preparações',
                    'embalagem_tipo' => 'pote',
                    'embalagem_descricao' => '12x50g',
                    'quantidade_caixa' => '12',
                    'peso_liquido' => 50.00,
                    'peso_bruto' => 65.00,
                    'validade' => '12 meses',
                    'valor' => 8.90,
                    'informacao_adicional' => 'Orégano desidratado para temperos e molhos.',
                    'catalogo' => true,
                    'lancamento' => false,
                    'status' => true,
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
            
            // Create product details
            $details = $data['details'];
            $details['product_id'] = $product->id;
            ProductDetail::create($details);
            
            // Create product images
            foreach ($data['images'] as $imageData) {
                $imageData['product_id'] = $product->id;
                ProductImage::create($imageData);
            }
        }
    }
}