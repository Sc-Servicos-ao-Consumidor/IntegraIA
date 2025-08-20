<template>
    <Head title="Cadastro de Produtos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Cadastro de Produtos</h1>
            </div>

            <!-- Product Form -->
            <form @submit.prevent="submit" class="bg-white rounded-lg shadow-sm border border-gray-200">
                <!-- Form Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ form.id ? '✏️ Editar Produto' : '➕ Cadastrar Novo Produto' }}
                    </h2>
                </div>

                <div class="p-6 space-y-8">
                    <!-- Informações do Produto -->
                    <section class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Informações do Produto</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Código Padrão -->
                            <div>
                                <label for="codigo_padrao" class="block text-sm font-medium text-gray-700 mb-1">Código Padrão</label>
                                <input
                                    v-model="form.codigo_padrao"
                                    id="codigo_padrao"
                                    type="text"
                                    placeholder="Código interno"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                />
                            </div>

                            <!-- Marca -->
                            <div>
                                <label for="marca" class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                                <input
                                    v-model="form.marca"
                                    id="marca"
                                    type="text"
                                    placeholder="Nome da marca"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                />
                            </div>

                            <!-- Grupo de Produtos -->
                            <div>
                                <label for="group_product_id" class="block text-sm font-medium text-gray-700 mb-1">Grupo de Produtos*</label>
                                <select
                                    v-model="form.group_product_id"
                                    id="group_product_id"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                >
                                    <option value="">Selecione</option>
                                    <option v-for="group in props.groupProducts" :key="group.id" :value="group.id">
                                        {{ group.descricao }}
                                    </option>
                                </select>
                            </div>

                            <!-- Descrição do Produto -->
                            <div>
                                <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição do Produto*</label>
                                <input
                                    v-model="form.descricao"
                                    id="descricao"
                                    type="text"
                                    placeholder="Nome do produto"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    required
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Embalagens -->
                    <section class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Embalagens</h3>
                        
                        <div class="space-y-4">
                            <!-- Escolha a Embalagem -->
                            <div>
                                <label for="escolha_embalagem" class="block text-sm font-medium text-gray-700 mb-1">Escolha a Embalagem</label>
                                <select
                                    v-model="form.escolha_embalagem"
                                    id="escolha_embalagem"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                >
                                    <option value="">Inserir Imagem...</option>
                                    <option value="bag">Bag/Sachê</option>
                                    <option value="pote">Pote</option>
                                    <option value="lata">Lata</option>
                                    <option value="caixa">Caixa</option>
                                    <option value="frasco">Frasco</option>
                                </select>
                            </div>

                            <!-- Embalagens Cadastradas -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Embalagens Cadastradas</label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div v-for="i in 4" :key="i" class="border border-gray-300 rounded-md p-3 text-center bg-gray-100">
                                        <div class="w-full h-20 bg-gray-200 rounded mb-2 flex items-center justify-center">
                                            <span class="text-gray-500 text-xs">📦</span>
                                        </div>
                                        <p class="text-xs text-gray-600">Nome da Embalagem (válida de produtos embalagem)</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Prompt para Especificação das Embalagens -->
                            <div>
                                <label for="prompt_especificacao_embalagens" class="block text-sm font-medium text-gray-700 mb-1">Prompt de Especificação das Embalagens</label>
                                <textarea
                                    v-model="form.prompt_especificacao_embalagens"
                                    id="prompt_especificacao_embalagens"
                                    rows="4"
                                    placeholder="Descreva as especificações das embalagens..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none cursor-not-allowed"
                                    disabled
                                ></textarea>
                            </div>

                            <!-- Prompt para uso das informações do Produto -->
                            <div>
                                <label for="prompt_uso_informacoes_produto" class="block text-sm font-medium text-gray-700 mb-1">Prompt para uso das informações do Produto</label>
                                <textarea
                                    v-model="form.prompt_uso_informacoes_produto"
                                    id="prompt_uso_informacoes_produto"
                                    rows="4"
                                    placeholder="Descreva como usar as informações do produto..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none cursor-not-allowed"
                                    disabled
                                ></textarea>
                            </div>
                        </div>
                    </section>

                    <!-- Product Specifications -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Especificação do Produto -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label for="especificacao_produto" class="block text-sm font-medium text-gray-700 mb-2">Especificação do Produto</label>
                                <textarea
                                    v-model="form.especificacao_produto"
                                    id="especificacao_produto"
                                    rows="6"
                                    placeholder="Especificações técnicas do produto..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
                                ></textarea>
                            </div>

                            <!-- Descrição Tabela da Nutricional -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label for="descricao_tabela_nutricional" class="block text-sm font-medium text-gray-700 mb-2">Descrição Tabela da Nutricional</label>
                                <textarea
                                    v-model="form.descricao_tabela_nutricional"
                                    id="descricao_tabela_nutricional"
                                    rows="4"
                                    placeholder="Informações nutricionais do produto..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
                                ></textarea>
                            </div>

                            <!-- Imagem Tabela Nutricional -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Imagem Tabela Nutricional</label>
                                <div class="border border-dashed border-gray-300 rounded-md p-4 text-center">
                                    <input
                                        v-model="nutritionalImageUrl"
                                        type="url"
                                        placeholder="Inserir Imagem..."
                                        class="w-full border-0 text-center text-sm focus:outline-none cursor-not-allowed"
                                        disabled
                                    />
                                </div>
                                
                                <!-- Imagens Cadastradas -->
                                <div class="mt-3">
                                    <p class="text-xs text-gray-600 mb-2">Imagens Cadastradas</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div v-for="i in 2" :key="i" class="border border-gray-300 rounded p-2 text-center bg-gray-100">
                                            <div class="w-full h-12 bg-gray-200 rounded mb-1 flex items-center justify-center">
                                                <span class="text-gray-500 text-xs">🖼️</span>
                                            </div>
                                            <p class="text-xs text-gray-600">Nome da Imagem (configurado na inserção)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Descrição dos Modos de Preparo -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label for="descricao_modos_preparo" class="block text-sm font-medium text-gray-700 mb-2">Descrição dos Modos de Preparo</label>
                                <textarea
                                    v-model="form.descricao_modos_preparo"
                                    id="descricao_modos_preparo"
                                    rows="4"
                                    placeholder="Instruções de preparo..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
                                ></textarea>
                            </div>

                            <!-- Descrição dos Rendimentos -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label for="descricao_rendimentos" class="block text-sm font-medium text-gray-700 mb-2">Descrição dos Rendimentos</label>
                                <textarea
                                    v-model="form.descricao_rendimentos"
                                    id="descricao_rendimentos"
                                    rows="4"
                                    placeholder="Informações sobre rendimento..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Perfil de Sabor -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label for="perfil_sabor" class="block text-sm font-medium text-gray-700 mb-2">Perfil de Sabor</label>
                                <textarea
                                    v-model="form.perfil_sabor"
                                    id="perfil_sabor"
                                    rows="6"
                                    placeholder="Características de sabor do produto..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
                                ></textarea>
                            </div>

                            <!-- Descrição Lista de Ingredientes -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label for="descricao_lista_ingredientes" class="block text-sm font-medium text-gray-700 mb-2">Descrição Lista de Ingredientes</label>
                                <textarea
                                    v-model="form.descricao_lista_ingredientes"
                                    id="descricao_lista_ingredientes"
                                    rows="4"
                                    placeholder="Lista de ingredientes do produto..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
                                ></textarea>
                            </div>

                            <!-- Imagem Lista de Ingredientes -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Imagem Lista de Ingredientes</label>
                                <div class="border border-dashed border-gray-300 rounded-md p-4 text-center">
                                    <input
                                        v-model="ingredientsImageUrl"
                                        type="url"
                                        placeholder="Inserir Imagem..."
                                        class="w-full border-0 text-center text-sm focus:outline-none cursor-not-allowed"
                                        disabled
                                    />
                                </div>
                                
                                <!-- Imagens Cadastradas -->
                                <div class="mt-3">
                                    <p class="text-xs text-gray-600 mb-2">Imagens Cadastradas</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div v-for="i in 2" :key="i" class="border border-gray-300 rounded p-2 text-center bg-gray-100">
                                            <div class="w-full h-12 bg-gray-200 rounded mb-1 flex items-center justify-center">
                                                <span class="text-gray-500 text-xs">🖼️</span>
                                            </div>
                                            <p class="text-xs text-gray-600">Nome da Imagem (configurado na inserção)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Imagem dos Modos de Preparo -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Imagem dos Modos de Preparo</label>
                                <div class="border border-dashed border-gray-300 rounded-md p-4 text-center">
                                    <input
                                        v-model="preparationImageUrl"
                                        type="url"
                                        placeholder="Inserir Imagem..."
                                        class="w-full border-0 text-center text-sm focus:outline-none cursor-not-allowed"
                                        disabled
                                    />
                                </div>
                                
                                <!-- Imagens Cadastradas -->
                                <div class="mt-3">
                                    <p class="text-xs text-gray-600 mb-2">Imagens Cadastradas</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div v-for="i in 2" :key="i" class="border border-gray-300 rounded p-2 text-center bg-gray-100">
                                            <div class="w-full h-12 bg-gray-200 rounded mb-1 flex items-center justify-center">
                                                <span class="text-gray-500 text-xs">🖼️</span>
                                            </div>
                                            <p class="text-xs text-gray-600">Nome da Imagem (configurado na inserção)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Imagem dos Rendimentos -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Imagem dos Rendimentos</label>
                                <div class="border border-dashed border-gray-300 rounded-md p-4 text-center">
                                    <input
                                        v-model="yieldsImageUrl"
                                        type="url"
                                        placeholder="Inserir Imagem..."
                                        class="w-full border-0 text-center text-sm focus:outline-none cursor-not-allowed"
                                        disabled
                                    />
                                </div>
                                
                                <!-- Imagens Cadastradas -->
                                <div class="mt-3">
                                    <p class="text-xs text-gray-600 mb-2">Imagens Cadastradas</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div v-for="i in 2" :key="i" class="border border-gray-300 rounded p-2 text-center bg-gray-100">
                                            <div class="w-full h-12 bg-gray-200 rounded mb-1 flex items-center justify-center">
                                                <span class="text-gray-500 text-xs">🖼️</span>
                                            </div>
                                            <p class="text-xs text-gray-600">Nome da Imagem (configurado na inserção)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vincular Receitas e Conteúdos -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Vincular Receitas -->
                        <section class="bg-gray-50 p-6 rounded-lg">
                            <div class="bg-blue-100 px-4 py-2 rounded-md mb-4">
                                <h3 class="text-sm font-semibold text-blue-900">+ Receitas (N para N)</h3>
                            </div>
                            
                            <div class="space-y-3">
                                <p class="text-sm font-medium text-gray-700">Receitas Cadastradas</p>
                                
                                <div class="space-y-2 max-h-40 overflow-y-auto">
                                    <div v-for="recipe in props.recipes" :key="recipe.id" class="flex items-center justify-between p-3 bg-white rounded border">
                                        <div class="flex items-center">
                                            <input
                                                v-model="selectedRecipes"
                                                :value="recipe.id"
                                                type="checkbox"
                                                class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded mr-3"
                                            />
                                            <span class="text-sm text-gray-900">{{ recipe.descricao }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="!props.recipes?.length" class="text-center py-4 text-gray-500 text-sm">
                                    Nenhuma receita cadastrada
                                </div>
                            </div>
                        </section>

                        <!-- Vincular Conteúdos -->
                        <section class="bg-gray-50 p-6 rounded-lg">
                            <div class="bg-green-100 px-4 py-2 rounded-md mb-4">
                                <h3 class="text-sm font-semibold text-green-900">+ Conteúdo (N para N)</h3>
                            </div>
                            
                            <div class="space-y-3">
                                <p class="text-sm font-medium text-gray-700">Conteúdos Cadastrados</p>
                                
                                <div class="space-y-2 max-h-40 overflow-y-auto">
                                    <div v-for="content in props.contents" :key="content.id" class="flex items-center justify-between p-3 bg-white rounded border">
                                        <div class="flex items-center">
                                            <input
                                                v-model="selectedContents"
                                                :value="content.id"
                                                type="checkbox"
                                                class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded mr-3"
                                            />
                                            <span class="text-sm text-gray-900">{{ content.descricao }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="!props.contents?.length" class="text-center py-4 text-gray-500 text-sm">
                                    Nenhum conteúdo cadastrado
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Additional Product Information -->
                    <section class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Informações Adicionais</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- SKU -->
                            <div>
                                <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                <input
                                    v-model="form.sku"
                                    id="sku"
                                    type="text"
                                    placeholder="Stock Keeping Unit"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                />
                            </div>

                            <!-- EAN -->
                            <div>
                                <label for="ean" class="block text-sm font-medium text-gray-700 mb-1">EAN</label>
                                <input
                                    v-model="form.ean"
                                    id="ean"
                                    type="text"
                                    placeholder="Código de Barras"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                />
                            </div>

                            <!-- Peso Líquido -->
                            <div>
                                <label for="peso_liquido" class="block text-sm font-medium text-gray-700 mb-1">Peso Líquido</label>
                                <input
                                    v-model="form.peso_liquido"
                                    id="peso_liquido"
                                    type="number"
                                    step="0.01"
                                    placeholder="0.00"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                />
                            </div>

                            <!-- Peso Bruto -->
                            <div>
                                <label for="peso_bruto" class="block text-sm font-medium text-gray-700 mb-1">Peso Bruto</label>
                                <input
                                    v-model="form.peso_bruto"
                                    id="peso_bruto"
                                    type="number"
                                    step="0.01"
                                    placeholder="0.00"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                />
                            </div>

                            <!-- Validade -->
                            <div>
                                <label for="validade" class="block text-sm font-medium text-gray-700 mb-1">Validade</label>
                                <input
                                    v-model="form.validade"
                                    id="validade"
                                    type="text"
                                    placeholder="ex: 12 meses"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                />
                            </div>

                            <!-- Valor -->
                            <div>
                                <label for="valor" class="block text-sm font-medium text-gray-700 mb-1">Preço (R$)</label>
                                <input
                                    v-model="form.valor"
                                    id="valor"
                                    type="number"
                                    step="0.01"
                                    placeholder="0.00"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                />
                            </div>
                        </div>

                        <!-- Status Checkboxes -->
                        <div class="mt-6 space-y-3">
                            <h4 class="text-sm font-medium text-gray-700">Status do Produto</h4>
                            <div class="flex flex-wrap gap-6">
                                <div class="flex items-center">
                                    <input
                                        v-model="form.catalogo"
                                        id="catalogo"
                                        type="checkbox"
                                        class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                                    />
                                    <label for="catalogo" class="ml-2 text-sm text-gray-700">Mostrar no Catálogo</label>
                                </div>
                                
                                <div class="flex items-center">
                                    <input
                                        v-model="form.lancamento"
                                        id="lancamento"
                                        type="checkbox"
                                        class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                                    />
                                    <label for="lancamento" class="ml-2 text-sm text-gray-700">Produto em Lançamento</label>
                                </div>
                                
                                <div class="flex items-center">
                                    <input
                                        v-model="form.status"
                                        id="status"
                                        type="checkbox"
                                        class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                                    />
                                    <label for="status" class="ml-2 text-sm text-gray-700">Produto Ativo</label>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Action Buttons -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="resetForm"
                            class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                        >
                            {{ form.id ? 'Cancelar' : 'Limpar' }}
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ form.processing ? 'Salvando...' : (form.id ? 'Atualizar Produto' : 'Salvar Produto') }}
                        </button>
                    </div>
                </div>
            </form>

            <!-- Saved Products Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">📋 Produtos Cadastrados</h2>
                
                <div v-if="props.products?.length" class="space-y-3">
                    <div
                        v-for="product in props.products"
                        :key="product.id"
                        class="border border-gray-200 rounded-md p-4 bg-gray-50"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="font-medium text-gray-900">{{ product.descricao }}</h3>
                                    <span v-if="product.marca" class="bg-purple-50 text-purple-700 px-2 py-1 rounded text-xs border border-purple-200">
                                        {{ product.marca }}
                                    </span>
                                    <span v-if="product.group_product" class="bg-blue-50 text-blue-700 px-2 py-1 rounded text-xs border border-blue-200">
                                        {{ product.group_product.descricao }}
                                    </span>
                                    <span v-if="product.detail?.lancamento" class="bg-green-50 text-green-700 px-2 py-1 rounded text-xs border border-green-200">
                                        NOVO
                                    </span>
                                    <span v-if="!product.status" class="bg-red-50 text-red-700 px-2 py-1 rounded text-xs border border-red-200">
                                        INATIVO
                                    </span>
                                </div>
                                
                                <div class="text-xs text-gray-500 space-y-1">
                                    <div v-if="product.codigo_padrao || product.sku">
                                        <span v-if="product.codigo_padrao">Código: {{ product.codigo_padrao }}</span>
                                        <span v-if="product.codigo_padrao && product.sku"> • </span>
                                        <span v-if="product.sku">SKU: {{ product.sku }}</span>
                                    </div>
                                    <div v-if="product.detail?.valor">
                                        Preço: R$ {{ parseFloat(product.detail.valor).toFixed(2) }}
                                    </div>
                                    <div v-if="product.detail?.peso_liquido || product.detail?.embalagem_tipo">
                                        <span v-if="product.detail?.peso_liquido">{{ product.detail.peso_liquido }}{{ product.detail?.embalagem_tipo || 'kg' }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 ml-4">
                                <button 
                                    @click="editProduct(product)" 
                                    class="text-sm text-orange-600 hover:text-orange-800 font-medium"
                                >
                                    Editar
                                </button>
                                <button 
                                    @click="deleteProduct(product)" 
                                    class="text-sm text-red-600 hover:text-red-800 font-medium"
                                >
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-8 text-gray-500">
                    <p>Nenhum produto cadastrado</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { router, useForm, Head, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

const breadcrumbs = [
    {
        title: 'Produtos',
        href: '/products',
    },
]

const props = defineProps({
    products: Array,
    groupProducts: Array,
    recipes: Array,
    contents: Array
})

// Form data
const form = useForm({
    id: null,
    // Basic product info
    descricao: '',
    codigo_padrao: '',
    sku: '',
    group_product_id: '',
    marca: '',
    status: true,
    
    // Product details
    escolha_embalagem: '',
    prompt_especificacao_embalagens: '',
    prompt_uso_informacoes_produto: '',
    especificacao_produto: '',
    perfil_sabor: '',
    descricao_tabela_nutricional: '',
    descricao_lista_ingredientes: '',
    descricao_modos_preparo: '',
    descricao_rendimentos: '',
    embalagem_tipo: '',
    embalagem_descricao: '',
    quantidade_caixa: '',
    peso_liquido: null,
    peso_bruto: null,
    validade: '',
    valor: null,
    desconto: null,
    informacao_adicional: '',
    ean: '',
    qr_code: '',
    url_rede_social: '',
    catalogo: true,
    lancamento: false,
    
    // Images will be handled separately
    images: [],
    
    // Relationships
    recipe_ids: [],
    content_ids: [],
})

// Separate refs for image URLs
const nutritionalImageUrl = ref('')
const ingredientsImageUrl = ref('')
const preparationImageUrl = ref('')
const yieldsImageUrl = ref('')

// Selected relationships
const selectedRecipes = ref([])
const selectedContents = ref([])

function submit() {
    // Prepare images data
    const images = []
    
    if (nutritionalImageUrl.value) {
        images.push({
            image_type: 'tabela_nutricional',
            url: nutritionalImageUrl.value,
            nome: 'Tabela Nutricional',
            ordem: 1,
            ativo: true
        })
    }
    
    if (ingredientsImageUrl.value) {
        images.push({
            image_type: 'lista_ingredientes',
            url: ingredientsImageUrl.value,
            nome: 'Lista de Ingredientes',
            ordem: 1,
            ativo: true
        })
    }
    
    if (preparationImageUrl.value) {
        images.push({
            image_type: 'modos_preparo',
            url: preparationImageUrl.value,
            nome: 'Modos de Preparo',
            ordem: 1,
            ativo: true
        })
    }
    
    if (yieldsImageUrl.value) {
        images.push({
            image_type: 'rendimentos',
            url: yieldsImageUrl.value,
            nome: 'Rendimentos',
            ordem: 1,
            ativo: true
        })
    }
    
    // Add images and relationships to form data
    form.images = images
    form.recipe_ids = selectedRecipes.value
    form.content_ids = selectedContents.value
    
    // Debug: Log what we're sending
    console.log('Submitting form with data:', {
        form: form.data(),
        selectedRecipes: selectedRecipes.value,
        selectedContents: selectedContents.value,
        images: images
    })
    
    form.post("/products", {
        onSuccess: () => {
            // Refresh the page to show updated data
            router.reload()
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors)
        }
    })
}

function resetForm() {
    form.reset()
    form.clearErrors()
    nutritionalImageUrl.value = ''
    ingredientsImageUrl.value = ''
    preparationImageUrl.value = ''
    yieldsImageUrl.value = ''
    selectedRecipes.value = []
    selectedContents.value = []
    form.recipe_ids = []
    form.content_ids = []
}

function editProduct(product) {
    // Basic product data
    form.id = product.id
    form.descricao = product.descricao
    form.codigo_padrao = product.codigo_padrao || ''
    form.sku = product.sku || ''
    form.group_product_id = product.group_product_id || ''
    form.marca = product.marca || ''
    form.status = product.status
    
    // Product details
    if (product.detail) {
        form.escolha_embalagem = product.detail.escolha_embalagem || ''
        form.prompt_especificacao_embalagens = product.detail.prompt_especificacao_embalagens || ''
        form.prompt_uso_informacoes_produto = product.detail.prompt_uso_informacoes_produto || ''
        form.especificacao_produto = product.detail.especificacao_produto || ''
        form.perfil_sabor = product.detail.perfil_sabor || ''
        form.descricao_tabela_nutricional = product.detail.descricao_tabela_nutricional || ''
        form.descricao_lista_ingredientes = product.detail.descricao_lista_ingredientes || ''
        form.descricao_modos_preparo = product.detail.descricao_modos_preparo || ''
        form.descricao_rendimentos = product.detail.descricao_rendimentos || ''
        form.embalagem_tipo = product.detail.embalagem_tipo || ''
        form.embalagem_descricao = product.detail.embalagem_descricao || ''
        form.quantidade_caixa = product.detail.quantidade_caixa || ''
        form.peso_liquido = product.detail.peso_liquido
        form.peso_bruto = product.detail.peso_bruto
        form.validade = product.detail.validade || ''
        form.valor = product.detail.valor
        form.desconto = product.detail.desconto
        form.informacao_adicional = product.detail.informacao_adicional || ''
        form.ean = product.detail.ean || ''
        form.qr_code = product.detail.qr_code || ''
        form.url_rede_social = product.detail.url_rede_social || ''
        form.catalogo = product.detail.catalogo
        form.lancamento = product.detail.lancamento
    }
    
    // Load images
    if (product.images) {
        product.images.forEach(image => {
            switch(image.image_type) {
                case 'tabela_nutricional':
                    nutritionalImageUrl.value = image.url
                    break
                case 'lista_ingredientes':
                    ingredientsImageUrl.value = image.url
                    break
                case 'modos_preparo':
                    preparationImageUrl.value = image.url
                    break
                case 'rendimentos':
                    yieldsImageUrl.value = image.url
                    break
            }
        })
    }
    
    // Load relationships
    selectedRecipes.value = product.recipes ? product.recipes.map(r => r.id) : []
    selectedContents.value = product.contents ? product.contents.map(c => c.id) : []
    form.recipe_ids = selectedRecipes.value
    form.content_ids = selectedContents.value
    
    // Scroll to form
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function deleteProduct(product) {
    if (confirm(`Tem certeza que deseja excluir "${product.descricao}"?`)) {
        router.delete(`/products/${product.id}`)
    }
}
</script>