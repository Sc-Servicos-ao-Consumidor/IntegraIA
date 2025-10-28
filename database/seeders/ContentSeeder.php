<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Content;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createContents();
        $this->linkContentsWithRecipes();
    }

    private function createContents()
    {
        $contents = $this->getContentData();
        
        foreach ($contents as $contentData) {
            Content::create($contentData);
        }
    }

    private function linkContentsWithRecipes()
    {
        $recipes = Recipe::limit(3)->get();
        $products = Product::limit(5)->get();
        $contents = Content::all();

        if ($recipes->count() > 0 && $contents->count() > 0) {
            // Link first content with first recipe
            $contents[0]->recipes()->attach($recipes[0]->id, [
                'order' => 1
            ]);

            // Link second content with multiple recipes
            if ($recipes->count() > 1) {
                $contents[1]->recipes()->attach($recipes[1]->id, [
                    'order' => 1
                ]);
            }
            
            if ($recipes->count() > 2) {
                $contents[1]->recipes()->attach($recipes[2]->id, [
                    'order' => 2
                ]);
            }
        }

        // Link contents with products
        if ($products->count() > 0 && $contents->count() > 0) {
            // Link first content with products (Massas Artesanais)
            if ($products->count() > 0) {
                $contents[0]->products()->attach($products[0]->id, [
                    'featured' => 'sim',
                    'order' => 1,
                    'notes' => 'Produto principal para massas artesanais'
                ]);
            }
            
            if ($products->count() > 1) {
                $contents[0]->products()->attach($products[1]->id, [
                    'featured' => 'nao',
                    'order' => 2,
                    'notes' => 'Ingrediente complementar'
                ]);
            }

            // Link second content with products (Sobremesas Veganas)
            if ($products->count() > 2) {
                $contents[1]->products()->attach($products[2]->id, [
                    'featured' => 'sim',
                    'order' => 1,
                    'notes' => 'Base para sobremesas veganas'
                ]);
            }

            // Link third content with products (Gestão Buffets)
            if ($products->count() > 3) {
                $contents[2]->products()->attach($products[3]->id, [
                    'featured' => 'sim',
                    'order' => 1,
                    'notes' => 'Produto de alto rendimento para buffets'
                ]);
            }
        }
    }

    private function getContentData()
    {
        $tenantId = Tenant::where('slug', 'unilever')->value('id');

        return [
            [
                'tenant_id' => $tenantId,
                'nome_conteudo' => 'Guia Completo de Massas Artesanais',
                'content_code' => 'CONT001',
                'descricao_tabela_nutricional' => 'Informações nutricionais detalhadas das massas artesanais, incluindo valores calóricos, carboidratos, proteínas e fibras.',
                'descricao_lista_ingredientes' => 'Lista completa dos ingredientes para preparação de massas frescas: farinha de trigo especial, ovos frescos, azeite extra virgem.',
                'descricao_modos_preparo' => 'Passo a passo detalhado para preparação de massas artesanais: técnicas de sovamento, tempo de descanso da massa.',
                'descricao_rendimentos' => 'Rendimento para 4-6 porções. Tempo de preparo: 45 minutos + 30 minutos de descanso.',
                'tipo_conteudo' => 'artigos',
                'pilares' => 'treinamento',
                'canal' => 'padaria',
                'links_conteudo' => [
                    ['nome' => 'Vídeo Tutorial', 'url' => 'https://youtube.com/watch?v=exemplo1']
                ],
                'cozinheiro' => true,
                'comprador' => false,
                'administrador' => true,
                'status' => true,
                'descricao_conteudo' => 'Conteúdo educativo sobre preparação de massas artesanais para padarias.',
            ],
            [
                'tenant_id' => $tenantId,
                'nome_conteudo' => 'Sobremesas Veganas Premium',
                'content_code' => 'CONT002',
                'descricao_tabela_nutricional' => 'Análise nutricional de sobremesas veganas premium, destacando benefícios dos ingredientes plant-based.',
                'descricao_lista_ingredientes' => 'Ingredientes selecionados para sobremesas veganas: leites vegetais, adoçantes naturais, farinhas alternativas.',
                'descricao_modos_preparo' => 'Técnicas específicas para sobremesas veganas: métodos de aeração sem ovos, uso de aquafaba.',
                'descricao_rendimentos' => 'Rendimento para 8-10 porções. Tempo de preparo: 60 minutos + 4 horas de refrigeração.',
                'tipo_conteudo' => 'ebook',
                'pilares' => 'inspiracao',
                'canal' => 'ala-carte',
                'links_conteudo' => [
                    ['nome' => 'Receitas Complementares', 'url' => 'https://exemplo.com/receitas-veganas']
                ],
                'cozinheiro' => true,
                'comprador' => true,
                'administrador' => false,
                'status' => true,
                'descricao_conteudo' => 'Material especializado em sobremesas veganas premium.',
            ],
            [
                'tenant_id' => $tenantId,
                'nome_conteudo' => 'Gestão de Custos em Buffets',
                'content_code' => 'CONT003',
                'descricao_tabela_nutricional' => 'Análise de custo-benefício nutricional por porção.',
                'descricao_lista_ingredientes' => 'Ingredientes estratégicos para buffets: proteínas de alto rendimento, vegetais sazonais.',
                'descricao_modos_preparo' => 'Métodos de preparação em larga escala: técnicas de batch cooking.',
                'descricao_rendimentos' => 'Cálculos para 50-200 pessoas. ROI projetado: 35-45%.',
                'tipo_conteudo' => 'ufs-academy',
                'pilares' => 'comprar',
                'canal' => 'buffet',
                'links_conteudo' => [
                    ['nome' => 'Planilha de Custos', 'url' => 'https://exemplo.com/planilha-custos.xlsx']
                ],
                'cozinheiro' => false,
                'comprador' => true,
                'administrador' => true,
                'status' => true,
                'descricao_conteudo' => 'Treinamento focado em gestão financeira para buffets.',
            ],
            [
                'tenant_id' => $tenantId,
                'nome_conteudo' => 'Cardápio do Futuro: Tendências 2025',
                'content_code' => 'CONT004',
                'descricao_tabela_nutricional' => 'Perfil nutricional das tendências alimentares: proteínas alternativas, superalimentos.',
                'descricao_lista_ingredientes' => 'Ingredientes inovadores: proteínas vegetais texturizadas, algas marinhas, cogumelos funcionais.',
                'descricao_modos_preparo' => 'Técnicas culinárias emergentes: fermentação controlada, sous vide vegetal.',
                'descricao_rendimentos' => 'Projeções de mercado: 40% aumento na demanda por plant-based.',
                'tipo_conteudo' => 'menu-futuro',
                'pilares' => 'inspiracao',
                'canal' => 'industrial',
                'links_conteudo' => [
                    ['nome' => 'Relatório Completo', 'url' => 'https://exemplo.com/relatorio-tendencias.pdf']
                ],
                'cozinheiro' => true,
                'comprador' => true,
                'administrador' => true,
                'status' => true,
                'descricao_conteudo' => 'Estudo prospectivo sobre tendências culinárias do futuro.',
            ],
            [
                'tenant_id' => $tenantId,
                'nome_conteudo' => 'Atendimento de Excelência em SAC',
                'content_code' => 'CONT005',
                'descricao_tabela_nutricional' => 'Não aplicável para conteúdo de atendimento.',
                'descricao_lista_ingredientes' => 'Não aplicável para conteúdo de atendimento.',
                'descricao_modos_preparo' => 'Protocolos de atendimento: escuta ativa, identificação de necessidades.',
                'descricao_rendimentos' => 'Métricas de sucesso: 95% satisfação do cliente, tempo médio de resolução <24h.',
                'tipo_conteudo' => 'dicas',
                'pilares' => 'sac',
                'canal' => 'lanchonete',
                'links_conteudo' => [
                    ['nome' => 'Manual de Atendimento', 'url' => 'https://exemplo.com/manual-sac.pdf']
                ],
                'cozinheiro' => false,
                'comprador' => false,
                'administrador' => true,
                'status' => true,
                'descricao_conteudo' => 'Treinamento completo para equipes de atendimento ao cliente.',
            ]
        ];
    }
}
