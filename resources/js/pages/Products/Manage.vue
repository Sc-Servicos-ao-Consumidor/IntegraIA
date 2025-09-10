<template>
    <Head title="Cadastro de Produtos" />

    <!-- Toast Notifications -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
        <div
            v-for="toast in toasts"
            :key="toast.id"
            :class="[
                'px-4 py-3 rounded-lg shadow-lg max-w-sm transform transition-all duration-300',
                toast.type === 'success' ? 'bg-green-500 text-white' : '',
                toast.type === 'error' ? 'bg-red-500 text-white' : '',
                toast.type === 'warning' ? 'bg-yellow-500 text-white' : '',
                toast.type === 'info' ? 'bg-blue-500 text-white' : '',
                toast.visible ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'
            ]"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span v-if="toast.type === 'success'" class="text-lg">✅</span>
                    <span v-else-if="toast.type === 'error'" class="text-lg">❌</span>
                    <span v-else-if="toast.type === 'warning'" class="text-lg">⚠️</span>
                    <span v-else-if="toast.type === 'info'" class="text-lg">ℹ️</span>
                    <span class="font-medium">{{ toast.message }}</span>
                </div>
                <button
                    @click="removeToast(toast.id)"
                    class="ml-2 text-white hover:text-gray-200 focus:outline-none"
                >
                    ×
                </button>
            </div>
        </div>
    </div>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div>
            <!-- Product Form -->
            <FormCard 
                title="Cadastro de Produtos" 
                icon="🛒"
                class="mt-8"
            >
                <form @submit.prevent="submit">
                <div class="space-y-8">
                    <!-- Informações do Produto -->
                    <div>
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
                    </div>

                    <!-- Embalagens -->
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Informações do Produto</h3>
                        
                        <div class="space-y-4">
                            <!-- Prompt para uso das informações do Produto -->
                            <div>
                                <label for="prompt_uso_informacoes_produto" class="block text-sm font-medium text-gray-700 mb-1">Prompt para uso das informações do Produto</label>
                                <textarea
                                    v-model="form.prompt_uso_informacoes_produto"
                                    id="prompt_uso_informacoes_produto"
                                    rows="4"
                                    placeholder="Descreva como usar as informações do produto..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Product Specifications -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Especificação do Produto -->
                            <div>
                                <label for="especificacao_produto" class="block text-sm font-medium text-gray-700 mb-1">Especificação do Produto</label>
                                <textarea
                                    v-model="form.especificacao_produto"
                                    id="especificacao_produto"
                                    rows="6"
                                    placeholder="Especificações técnicas do produto..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                                ></textarea>
                            </div>

                            <!-- Descrição Tabela da Nutricional -->
                            <div>
                                <label for="descricao_tabela_nutricional" class="block text-sm font-medium text-gray-700 mb-1">Descrição Tabela da Nutricional</label>
                                <textarea
                                    v-model="form.descricao_tabela_nutricional"
                                    id="descricao_tabela_nutricional"
                                    rows="6"
                                    placeholder="Informações nutricionais do produto..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                                ></textarea>
                            </div>

                            <!-- Imagem Tabela Nutricional -->
                            <!-- <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Imagem Tabela Nutricional</label>
                                <div class="border border-dashed border-gray-300 rounded-md p-4 text-center">
                                    <input
                                        v-model="nutritionalImageUrl"
                                        type="url"
                                        placeholder="Inserir Imagem..."
                                        class="w-full border-0 text-center text-sm focus:outline-none cursor-not-allowed"
                                        disabled
                                    />
                                </div> -->
                                
                                <!-- Imagens Cadastradas -->
                                <!-- <div class="mt-3">
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
                            </div> -->

                            <!-- Descrição dos Modos de Preparo -->
                            <div>
                                <label for="descricao_modos_preparo" class="block text-sm font-medium text-gray-700 mb-1">Descrição dos Modos de Preparo</label>
                                <textarea
                                    v-model="form.descricao_modos_preparo"
                                    id="descricao_modos_preparo"
                                    rows="6"
                                    placeholder="Instruções de preparo..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                                ></textarea>
                            </div>

                            
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Perfil de Sabor -->
                            <div>
                                <label for="perfil_sabor" class="block text-sm font-medium text-gray-700 mb-1">Perfil de Sabor</label>
                                <textarea
                                    v-model="form.perfil_sabor"
                                    id="perfil_sabor"
                                    rows="6"
                                    placeholder="Características de sabor do produto..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                                ></textarea>
                            </div>

                            <!-- Descrição dos Rendimentos -->
                            <div>
                                <label for="descricao_rendimentos" class="block text-sm font-medium text-gray-700 mb-1">Descrição dos Rendimentos</label>
                                <textarea
                                    v-model="form.descricao_rendimentos"
                                    id="descricao_rendimentos"
                                    rows="6"
                                    placeholder="Informações sobre rendimento..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                                ></textarea>
                            </div>

                            <!-- Descrição Lista de Ingredientes -->
                            <div>
                                <label for="descricao_lista_ingredientes" class="block text-sm font-medium text-gray-700 mb-1">Descrição Lista de Ingredientes</label>
                                <textarea
                                    v-model="form.descricao_lista_ingredientes"
                                    id="descricao_lista_ingredientes"
                                    rows="6"
                                    placeholder="Lista de ingredientes do produto..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                                ></textarea>
                            </div>

                            <!-- Imagem Lista de Ingredientes -->
                            <!-- <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Imagem Lista de Ingredientes</label>
                                <div class="border border-dashed border-gray-300 rounded-md p-4 text-center">
                                    <input
                                        v-model="ingredientsImageUrl"
                                        type="url"
                                        placeholder="Inserir Imagem..."
                                        class="w-full border-0 text-center text-sm focus:outline-none cursor-not-allowed"
                                        disabled
                                    />
                                </div> -->
                                
                                <!-- Imagens Cadastradas -->
                                <!-- <div class="mt-3">
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
                            </div> -->

                            <!-- Imagem dos Modos de Preparo -->
                            <!-- <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Imagem dos Modos de Preparo</label>
                                <div class="border border-dashed border-gray-300 rounded-md p-4 text-center">
                                    <input
                                        v-model="preparationImageUrl"
                                        type="url"
                                        placeholder="Inserir Imagem..."
                                        class="w-full border-0 text-center text-sm focus:outline-none cursor-not-allowed"
                                        disabled
                                    />
                                </div> -->
                                
                                <!-- Imagens Cadastradas -->
                                <!-- <div class="mt-3">
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
                            </div> -->

                            <!-- Imagem dos Rendimentos -->
                            <!-- <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Imagem dos Rendimentos</label>
                                <div class="border border-dashed border-gray-300 rounded-md p-4 text-center">
                                    <input
                                        v-model="yieldsImageUrl"
                                        type="url"
                                        placeholder="Inserir Imagem..."
                                        class="w-full border-0 text-center text-sm focus:outline-none cursor-not-allowed"
                                        disabled
                                    />
                                </div> -->
                                
                                <!-- Imagens Cadastradas -->
                                <!-- <div class="mt-3">
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
                            </div> -->
                        </div>
                    </div>

                    <!-- Connections Section -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">🔗 Conexões e Associações</h3>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Recipe Associations -->
                            <ConnectionCard title="Receitas" icon="🍳" type="recipe" color="orange">
                                <ConnectionItem
                                    v-for="(recipe, index) in form.selected_recipes"
                                    :key="index"
                                    color="orange"
                                >
                                    <select
                                        v-model="recipe.recipe_id"
                                        class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    >
                                        <option value="">Selecione uma receita...</option>
                                        <option v-for="availableRecipe in props.recipes" :key="availableRecipe.id" :value="availableRecipe.id">
                                            {{ availableRecipe.recipe_name || availableRecipe.descricao || 'Sem nome' }}
                                        </option>
                                    </select>
                                    
                                    <template #actions>
                                        <label class="flex items-center gap-2 text-sm">
                                            <input
                                                type="checkbox"
                                                v-model="recipe.top_dish"
                                                :true-value="true"
                                                :false-value="false"
                                                class="w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 focus:ring-2"
                                            />
                                            <span class="text-gray-700">Top Dish</span>
                                        </label>
                                    </template>
                                    
                                    <template #remove>
                                        <button 
                                            type="button"
                                            @click="removeRecipe(index)"
                                        >
                                            Remover
                                        </button>
                                    </template>
                                </ConnectionItem>
                                
                                <button 
                                    type="button"
                                    @click="addRecipe"
                                    class="text-orange-600 hover:text-orange-800 text-sm font-medium px-3 py-2 border border-orange-300 rounded-md hover:bg-orange-50 transition-colors"
                                >
                                    + Adicionar Receita
                                </button>
                            </ConnectionCard>

                            <!-- Content Associations -->
                            <ConnectionCard title="Conteúdos" icon="📄" type="content" color="blue">
                                <ConnectionItem
                                    v-for="(content, index) in form.selected_contents"
                                    :key="index"
                                    color="blue"
                                >
                                    <select
                                        v-model="content.content_id"
                                        class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Selecione um conteúdo...</option>
                                        <option v-for="availableContent in props.contents" :key="availableContent.id" :value="availableContent.id">
                                            {{ availableContent.nome_conteudo || availableContent.descricao || 'Sem nome' }}
                                        </option>
                                    </select>
                                    
                                    <template #remove>
                                        <button 
                                            type="button"
                                            @click="removeContent(index)"
                                        >
                                            Remover
                                        </button>
                                    </template>
                                </ConnectionItem>
                                
                                <button 
                                    type="button"
                                    @click="addContent"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium px-3 py-2 border border-blue-300 rounded-md hover:bg-blue-50 transition-colors"
                                >
                                    + Adicionar Conteúdo
                                </button>
                            </ConnectionCard>
                        </div>
                    </div>

                    <!-- Additional Product Information -->
                    <div>
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


                        </div>

                        <!-- Status Checkboxes -->
                        <div class="mt-6 space-y-3">
                            <h4 class="text-sm font-medium text-gray-700">Status do Produto</h4>
                            <div class="flex flex-wrap gap-6">
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
                    </div>

                    <!-- Packaging Information Section -->
                    <div class="mt-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">📦 Informações de Embalagem</h3>
                        
                        <!-- Packaging List -->
                        <div v-if="form.packagings && form.packagings.length > 0" class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Embalagens Cadastradas</h4>
                            <div class="space-y-3">
                                <div
                                    v-for="(packaging, index) in form.packagings"
                                    :key="index"
                                    class="border border-gray-200 rounded-lg p-4 bg-white"
                                >
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                                <span class="text-orange-600 text-sm">📦</span>
                                            </div>
                                            <div>
                                                <h5 class="font-medium text-gray-900">{{ packaging.descricao || 'Embalagem sem descrição' }}</h5>
                                                <p class="text-sm text-gray-600">
                                                    {{ packaging.codigo_padrao ? `Código: ${packaging.codigo_padrao}` : '' }}
                                                    {{ packaging.sku ? `SKU: ${packaging.sku}` : '' }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2">
                                            <span v-if="!packaging.status" class="bg-red-50 text-red-700 px-2 py-1 rounded text-xs border border-red-200">
                                                INATIVA
                                            </span>
                                            <span v-if="packaging.descontinuado" class="bg-yellow-50 text-yellow-700 px-2 py-1 rounded text-xs border border-yellow-200">
                                                DESCONTINUADA
                                            </span>
                                            <button
                                                type="button"
                                                @click="editPackaging(packaging, index)"
                                                class="text-orange-600 hover:text-orange-800 text-sm font-medium"
                                            >
                                                Editar
                                            </button>
                                            <button
                                                type="button"
                                                @click="removePackaging(index)"
                                                class="text-gray-600 hover:text-gray-800 text-sm font-medium"
                                            >
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                        <div>
                                            <span class="text-gray-500">Tipo:</span>
                                            <span class="ml-1 font-medium">{{ packaging.embalagem_tipo || 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Qtd/Caixa:</span>
                                            <span class="ml-1 font-medium">{{ packaging.quantidade_caixa || 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Peso Líquido:</span>
                                            <span class="ml-1 font-medium">{{ packaging.peso_liquido ? `${packaging.peso_liquido}kg` : 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Peso Bruto:</span>
                                            <span class="ml-1 font-medium">{{ packaging.peso_bruto ? `${packaging.peso_bruto}kg` : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Note about packaging management -->
                        <!-- <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800">
                                💡 <strong>Nota:</strong> As embalagens são criadas automaticamente quando você salva um produto pela primeira vez. 
                                Use o botão "Editar" para atualizar as informações da embalagem.
                            </p>
                        </div> -->

                        <!-- Packaging Form -->
                        <div v-if="showPackagingForm" class="border border-gray-200 rounded-lg p-4 bg-white">
                            <h4 class="text-sm font-medium text-gray-700 mb-4">
                                Editar Embalagem
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <!-- Basic Information -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição*</label>
                                    <input
                                        v-model="packagingForm.descricao"
                                        type="text"
                                        placeholder="Descrição da embalagem"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Código Padrão</label>
                                    <input
                                        v-model="packagingForm.codigo_padrao"
                                        type="text"
                                        placeholder="Código interno"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                    <input
                                        v-model="packagingForm.sku"
                                        type="text"
                                        placeholder="Stock Keeping Unit"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">EAN</label>
                                    <input
                                        v-model="packagingForm.ean"
                                        type="text"
                                        placeholder="Código de Barras"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Embalagem</label>
                                    <select
                                        v-model="packagingForm.embalagem_tipo"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    >
                                        <option value="">Selecione...</option>
                                        <option value="kg">Quilogramas (kg)</option>
                                        <option value="g">Gramas (g)</option>
                                        <option value="l">Litros (L)</option>
                                        <option value="ml">Mililitros (ml)</option>
                                        <option value="unidade">Unidade</option>
                                        <option value="caixa">Caixa</option>
                                        <option value="saco">Saco</option>
                                        <option value="garrafa">Garrafa</option>
                                        <option value="lata">Lata</option>
                                        <option value="pote">Pote</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade por Caixa</label>
                                    <input
                                        v-model="packagingForm.quantidade_caixa"
                                        type="text"
                                        placeholder="Ex: 12 unidades"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Peso Líquido (kg)</label>
                                    <input
                                        v-model="packagingForm.peso_liquido"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Peso Bruto (kg)</label>
                                    <input
                                        v-model="packagingForm.peso_bruto"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Data de Validade</label>
                                    <input
                                        v-model="packagingForm.validade"
                                        type="date"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    />
                                </div>
                            </div>

                            <!-- AI Specifications -->
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">🤖 Especificações para IA</label>
                                <textarea
                                    v-model="packagingForm.prompt_especificacao_embalagens"
                                    rows="4"
                                    placeholder="Descreva detalhadamente as especificações da embalagem para geração de conteúdo por IA..."
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                                ></textarea>
                                <p class="text-xs text-gray-500 mt-1">
                                    Este texto será usado para gerar descrições automáticas da embalagem
                                </p>
                            </div>

                            <!-- Status -->
                            <div class="mt-4 space-y-3">
                                <h5 class="text-sm font-medium text-gray-700">Status da Embalagem</h5>
                                <div class="flex flex-wrap gap-6">
                                    <div class="flex items-center">
                                        <input
                                            v-model="packagingForm.status"
                                            type="checkbox"
                                            class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                                        />
                                        <label class="ml-2 text-sm text-gray-700">Embalagem Ativa</label>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input
                                            v-model="packagingForm.descontinuado"
                                            type="checkbox"
                                            class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                                        />
                                        <label class="ml-2 text-sm text-gray-700">Embalagem Descontinuada</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Packaging Form Actions -->
                            <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200">
                                <button
                                    type="button"
                                    @click="cancelPackagingForm"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="button"
                                    @click="savePackaging"
                                    class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                                >
                                    Atualizar Embalagem
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
                    <button
                        type="button"
                        @click="confirmResetForm"
                        class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                    >
                        {{ form.id ? 'Cancelar' : 'Limpar' }}
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Salvando...
                        </span>
                        <span v-else>{{ form.id ? 'Atualizar Produto' : 'Salvar Produto' }}</span>
                    </button>
                </div>
                </form>
            </FormCard>

            <!-- Saved Products Section -->
            <ListCard title="Produtos Cadastrados" icon="📋" empty-message="Nenhum produto cadastrado" empty-icon="🛒" class="mt-8">
                <ListItem
                    v-for="product in props.products"
                    :key="product.id"
                    :title="product.descricao"
                    :subtitle="product.codigo_padrao || product.sku ? `${product.codigo_padrao ? 'Código: ' + product.codigo_padrao : ''}${product.codigo_padrao && product.sku ? ' • ' : ''}${product.sku ? 'SKU: ' + product.sku : ''}` : ''"
                    :status="product.status ? 'active' : 'inactive'"
                >
                    <template #associations>
                        <div v-if="product.recipes?.length" class="flex items-center gap-2 text-sm text-gray-600">
                            <span class="font-medium">🍳 Receitas:</span>
                            <span>{{ product.recipes.length }} vinculado(s)</span>
                        </div>
                        <div v-if="product.contents?.length" class="flex items-center gap-2 text-sm text-gray-600">
                            <span class="font-medium">📄 Conteúdos:</span>
                            <span>{{ product.contents.length }} vinculado(s)</span>
                        </div>
                    </template>
                    <template #actions>
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
                    </template>
                </ListItem>
            </ListCard>
        </div>
    </AppLayout>
</template>

<script setup>
import { router, useForm, Head, usePage } from '@inertiajs/vue3'
import { ref, nextTick } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { 
    FormCard, 
    ListCard, 
    SectionCard, 
    ConnectionCard, 
    ListItem, 
    ConnectionItem 
} from '@/components/ui/patterns'

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

// Toast notification system
const toasts = ref([])
let toastIdCounter = 0

function showToast(message, type = 'info', duration = 5000) {
    const toast = {
        id: ++toastIdCounter,
        message,
        type,
        visible: false
    }
    
    toasts.value.push(toast)
    
    // Show toast with animation
    nextTick(() => {
        toast.visible = true
    })
    
    // Auto-remove toast after duration
    setTimeout(() => {
        removeToast(toast.id)
    }, duration)
}

function removeToast(id) {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index > -1) {
        toasts.value[index].visible = false
        setTimeout(() => {
            toasts.value.splice(index, 1)
        }, 300)
    }
}

function showValidationErrors(errors) {
    // Show first error as a toast
    const firstError = Object.values(errors)[0]
    if (firstError) {
        const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError
        showToast(`Erro de validação: ${errorMessage}`, 'error')
    }
    
    // Show additional errors if there are more
    const errorCount = Object.keys(errors).length
    if (errorCount > 1) {
        setTimeout(() => {
            showToast(`Mais ${errorCount - 1} erro(s) de validação encontrado(s)`, 'warning')
        }, 1000)
    }
}

function showFormProcessingMessage() {
    showToast('Processando formulário...', 'info', 2000)
}

function showFormSuccessMessage(isUpdate, itemName) {
    const message = isUpdate 
        ? `${itemName} atualizado com sucesso!` 
        : `${itemName} criado com sucesso!`
    showToast(message, 'success')
}

function confirmResetForm() {
    if (form.id) {
        // If editing, just cancel without confirmation
        resetForm()
        showToast('Edição cancelada', 'info')
    } else {
        // If creating new, ask for confirmation
        if (confirm('Tem certeza que deseja limpar todos os dados do formulário?')) {
            resetForm()
            showToast('Formulário limpo', 'info')
        }
    }
}

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
    ean: '',
    
    // Images will be handled separately
    images: [],
    
    // Relationships
    recipe_ids: [],
    content_ids: [],
    selected_recipes: [],
    selected_contents: [],
    
    // Packaging
    packagings: [],
    packaging: null,
})

// Separate refs for image URLs
const nutritionalImageUrl = ref('')
const ingredientsImageUrl = ref('')
const preparationImageUrl = ref('')
const yieldsImageUrl = ref('')

// Selected relationships
const selectedRecipes = ref([])
const selectedContents = ref([])

// Packaging management
const showPackagingForm = ref(false)
const packagingForm = ref({
    descricao: '',
    codigo_padrao: '',
    sku: '',
    ean: '',
    quantidade_caixa: '',
    embalagem_tipo: '',
    peso_liquido: '',
    peso_bruto: '',
    validade: '',
    descontinuado: false,
    status: true,
    prompt_especificacao_embalagens: '',
})

// Recipe management functions
const addRecipe = () => {
    form.selected_recipes.push({ recipe_id: '', top_dish: false })
}

const removeRecipe = (index) => {
    form.selected_recipes.splice(index, 1)
}

// Content management functions
const addContent = () => {
    form.selected_contents.push({ content_id: '' })
}

const removeContent = (index) => {
    form.selected_contents.splice(index, 1)
}

// Packaging management functions
const addPackaging = () => {
    // This function is no longer needed - packaging is created automatically
    showToast('As embalagens são criadas automaticamente. Use "Editar" para atualizar.', 'info')
}

const savePackaging = () => {
    if (!packagingForm.value.descricao) {
        showToast('Descrição da embalagem é obrigatória', 'error')
        return
    }
    
    const packagingData = { ...packagingForm.value }
    delete packagingData.editIndex // Remove the edit index from data
    
    // Update existing packaging
    form.packagings[packagingForm.value.editIndex] = {
        ...packagingData,
        id: form.packagings[packagingForm.value.editIndex].id // Preserve original ID
    }
    showToast('Embalagem atualizada com sucesso!', 'success')
    
    // Reset packaging form
    resetPackagingForm()
    showPackagingForm.value = false
}

const removePackaging = (index) => {
    showToast('As embalagens não podem ser removidas. Use "Editar" para atualizar as informações.', 'info')
}

const editPackaging = (packaging, index) => {
    // Reset form first
    resetPackagingForm()
    
    // Load packaging data into form
    packagingForm.value = {
        descricao: packaging.descricao || '',
        codigo_padrao: packaging.codigo_padrao || '',
        sku: packaging.sku || '',
        ean: packaging.ean || '',
        quantidade_caixa: packaging.quantidade_caixa || '',
        embalagem_tipo: packaging.embalagem_tipo || '',
        peso_liquido: packaging.peso_liquido || '',
        peso_bruto: packaging.peso_bruto || '',
        validade: packaging.validade || '',
        descontinuado: packaging.descontinuado || false,
        status: packaging.status,
        prompt_especificacao_embalagens: packaging.prompt_especificacao_embalagens || '',
        editIndex: index // Store the index for updating
    }
    
    showPackagingForm.value = true
}

const cancelPackagingForm = () => {
    resetPackagingForm()
    showPackagingForm.value = false
}

const resetPackagingForm = () => {
    packagingForm.value = {
        descricao: '',
        codigo_padrao: '',
        sku: '',
        ean: '',
        quantidade_caixa: '',
        embalagem_tipo: '',
        peso_liquido: '',
        peso_bruto: '',
        validade: '',
        descontinuado: false,
        status: true,
        prompt_especificacao_embalagens: '',
        editIndex: undefined
    }
}

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
    
    // Prepare packaging data
    const packagingData = {
        prompt_especificacao_embalagens: form.prompt_especificacao_embalagens,
        packagings: form.packagings
    }
    
    // Add images, relationships, and packaging to form data
    form.images = images
    form.recipe_ids = form.selected_recipes.map(r => r.recipe_id).filter(id => id)
    form.content_ids = form.selected_contents.map(c => c.content_id).filter(id => id)
    
    // Ensure packaging data is properly structured
    form.packaging = packagingData
    
    // Prepare final form data for submission
    const formData = form.data()
    
    form.post("/products", {
        onSuccess: () => {
            const isUpdate = form.id !== null
            showFormSuccessMessage(isUpdate, `Produto "${form.descricao}"`)
            
            // Reset form after successful submission
            resetForm()
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors)
            
            if (Object.keys(errors).length > 0) {
                showValidationErrors(errors)
            } else {
                showToast('Erro ao salvar o produto. Verifique os dados e tente novamente.', 'error')
            }
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
    form.selected_recipes = []
    form.selected_contents = []
    form.packagings = []
    showPackagingForm.value = false
    resetPackagingForm()
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
    
    // Product details - now directly on product model
    form.escolha_embalagem = product.escolha_embalagem || ''
    form.prompt_uso_informacoes_produto = product.prompt_uso_informacoes_produto || ''
    
    // Load packaging data
    form.packagings = product.packagings ? product.packagings.map(p => ({
        id: p.id,
        descricao: p.descricao || '',
        codigo_padrao: p.codigo_padrao || '',
        sku: p.sku || '',
        ean: p.ean || '',
        quantidade_caixa: p.quantidade_caixa || '',
        embalagem_tipo: p.embalagem_tipo || '',
        peso_liquido: p.peso_liquido || '',
        peso_bruto: p.peso_bruto || '',
        validade: p.validade || '',
        descontinuado: p.descontinuado || false,
        status: p.status,
        prompt_especificacao_embalagens: p.prompt_especificacao_embalagens || ''
    })) : []
    
    // Load packaging-specific fields
    if (product.packagings && product.packagings.length > 0) {
        form.prompt_especificacao_embalagens = product.packagings[0].prompt_especificacao_embalagens || ''
    }
    form.especificacao_produto = product.especificacao_produto || ''
    form.perfil_sabor = product.perfil_sabor || ''
    form.descricao_tabela_nutricional = product.descricao_tabela_nutricional || ''
    form.descricao_lista_ingredientes = product.descricao_lista_ingredientes || ''
    form.descricao_modos_preparo = product.descricao_modos_preparo || ''
    form.descricao_rendimentos = product.descricao_rendimentos || ''
    form.ean = product.ean || ''
    
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
    
    // Load selected recipes and contents for the new pattern
    form.selected_recipes = product.recipes ? product.recipes.map(recipe => ({
        recipe_id: recipe.id,
        top_dish: !!(recipe.pivot && recipe.pivot.top_dish)
    })) : []
    
    form.selected_contents = product.contents ? product.contents.map(content => ({
        content_id: content.id
    })) : []
    
    // Scroll to form
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function deleteProduct(product) {
    if (confirm(`Tem certeza que deseja excluir "${product.descricao}"?`)) {
        router.delete(`/products/${product.id}`, {
            onSuccess: () => {
                const message = `Produto "${product.descricao}" excluído com sucesso!`
                showToast(message, 'success')
            },
            onError: (errors) => {
                console.error('Product deletion errors:', errors)
                const errorMessage = 'Erro ao excluir o produto. Tente novamente.'
                showToast(errorMessage, 'error')
            }
        })
    }
}

// Group Form data
const groupForm = useForm({
    id: null,
    descricao: '',
    codigo_padrao: '',
    observacao: '',
    status: true,
})

function submitGroup() {
    // Always ensure status is true
    groupForm.status = true
    
    if (groupForm.id) {
        // Update existing group
        groupForm.put(`/group-products/${groupForm.id}`, {
            onSuccess: () => {
                showFormSuccessMessage(true, `Grupo "${groupForm.descricao}"`)
                
                // Reset form after successful submission
                resetGroupForm()
                
                // Refresh the page to show updated data
                router.reload()
            },
            onError: (errors) => {
                console.error('Group update errors:', errors)
                
                if (Object.keys(errors).length > 0) {
                    showValidationErrors(errors)
                } else {
                    showToast('Erro ao atualizar o grupo. Verifique os dados e tente novamente.', 'error')
                }
            }
        })
    } else {
        // Create new group
        groupForm.post('/group-products', {
            onSuccess: () => {
                showFormSuccessMessage(false, `Grupo "${groupForm.descricao}"`)
                
                // Reset form after successful submission
                resetGroupForm()
                
                // Refresh the page to show updated data
                router.reload()
            },
            onError: (errors) => {
                console.error('Group creation errors:', errors)
                
                if (Object.keys(errors).length > 0) {
                    showValidationErrors(errors)
                } else {
                    showToast('Erro ao criar o grupo. Verifique os dados e tente novamente.', 'error')
                }
            }
        })
    }
}

function editGroup(group) {
    groupForm.id = group.id
    groupForm.descricao = group.descricao
    groupForm.codigo_padrao = group.codigo_padrao || ''
    groupForm.observacao = group.observacao || ''
    groupForm.status = true // Always set to true
}

function resetGroupForm() {
    groupForm.reset()
    groupForm.clearErrors()
    groupForm.id = null
    groupForm.descricao = ''
    groupForm.codigo_padrao = ''
    groupForm.observacao = ''
    groupForm.status = true // Always set to true
}

function deleteGroup(group) {
    if (confirm(`Tem certeza que deseja excluir o grupo "${group.descricao}"?`)) {
        router.delete(`/group-products/${group.id}`, {
            onSuccess: () => {
                const message = `Grupo "${group.descricao}" excluído com sucesso!`
                showToast(message, 'success')
            },
            onError: (errors) => {
                console.error('Group deletion errors:', errors)
                const errorMessage = 'Erro ao excluir o grupo. Tente novamente.'
                showToast(errorMessage, 'error')
            }
        })
    }
}
</script>