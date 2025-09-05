<template>
    <Head title="Cadastros de Receitas" />

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
        <div class="max-w-7xl mx-auto">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">⚡ Ações Rápidas</h2>
                
                <div class="flex gap-3">
                    <a 
                        href="/recipes/semantic-search"
                        class="bg-orange-600 hover:bg-orange-700 text-white font-medium px-6 py-2 rounded-md text-sm transition-colors inline-flex items-center gap-2"
                    >
                        🔍 Busca Semântica
                    </a>
                    <button 
                        @click="resetForm"
                        class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-6 py-2 rounded-md text-sm transition-colors"
                    >
                        ✨ Nova Receita
                    </button>
                </div>
            </div>
    
            <!-- Recipe Form -->
            <form @submit.prevent="submit" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column - Select/Smaller Fields -->
                    <div class="space-y-4">
                        <!-- Nome da Receita -->
                        <div>
                            <label for="recipe_name" class="block text-sm font-medium text-gray-700 mb-1">Nome da Receita</label>
                            <input
                                v-model="form.recipe_name"
                                id="recipe_name"
                                type="text"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            />
                            <p v-if="form.errors.recipe_name" class="text-red-500 text-xs mt-1">{{ form.errors.recipe_name }}</p>
                        </div>

                        <!-- Culinária -->
                        <div>
                            <label for="cuisine" class="block text-sm font-medium text-gray-700 mb-1">Culinária</label>
                            <select
                                v-model="form.cuisine"
                                id="cuisine"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option value="">Selecione</option>
                                <option value="italiana">Italiana</option>
                                <option value="mexicana">Mexicana</option>
                                <option value="asiatica">Asiática</option>
                                <option value="brasileira">Brasileira</option>
                                <option value="francesa">Francesa</option>
                            </select>
                            <p v-if="form.errors.cuisine" class="text-red-500 text-xs mt-1">{{ form.errors.cuisine }}</p>
                        </div>

                        <!-- Tipo de Receita -->
                        <div>
                            <label for="recipe_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Receita</label>
                            <select
                                v-model="form.recipe_type"
                                id="recipe_type"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option value="">Doce ou Salgada</option>
                                <option value="doce">Doce</option>
                                <option value="salgada">Salgada</option>
                            </select>
                            <p v-if="form.errors.recipe_type" class="text-red-500 text-xs mt-1">{{ form.errors.recipe_type }}</p>
                        </div>

                        <!-- Ordem de Serviço -->
                        <div>
                            <label for="service_order" class="block text-sm font-medium text-gray-700 mb-1">Ordem de Serviço</label>
                            <select
                                v-model="form.service_order"
                                id="service_order"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option value="">Selecione</option>
                                <option value="entrada">Entrada</option>
                                <option value="prato_principal">Prato Principal</option>
                                <option value="sobremesa">Sobremesa</option>
                                <option value="bebida">Bebida</option>
                                <option value="acompanhamento">Acompanhamento</option>
                            </select>
                            <p v-if="form.errors.service_order" class="text-red-500 text-xs mt-1">{{ form.errors.service_order }}</p>
                        </div>

                        <!-- Tempo de Preparo -->
                        <div>
                            <label for="preparation_time" class="block text-sm font-medium text-gray-700 mb-1">Tempo de Preparo (minutos)</label>
                            <input
                                type="number"
                                v-model="form.preparation_time"
                                id="preparation_time"
                                min="1"
                                placeholder="Ex: 45"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            />
                            <p v-if="form.errors.preparation_time" class="text-red-500 text-xs mt-1">{{ form.errors.preparation_time }}</p>
                        </div>

                        <!-- Grau de Dificuldade -->
                        <div>
                            <label for="difficulty_level" class="block text-sm font-medium text-gray-700 mb-1">Grau de Dificuldade</label>
                            <select
                                v-model="form.difficulty_level"
                                id="difficulty_level"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option value="">Selecione</option>
                                <option value="facil">Fácil</option>
                                <option value="medio">Médio</option>
                                <option value="dificil">Difícil</option>
                            </select>
                            <p v-if="form.errors.difficulty_level" class="text-red-500 text-xs mt-1">{{ form.errors.difficulty_level }}</p>
                        </div>

                        <!-- Rendimento -->
                        <div>
                            <label for="yield" class="block text-sm font-medium text-gray-700 mb-1">Rendimento</label>
                            <select
                                v-model="form.yield"
                                id="yield"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option value="">Selecione</option>
                                <option value="1">1 porção</option>
                                <option value="2">2 porções</option>
                                <option value="4">4 porções</option>
                                <option value="6">6 porções</option>
                                <option value="8">8 porções</option>
                                <option value="10">10 porções</option>
                            </select>
                            <p v-if="form.errors.yield" class="text-red-500 text-xs mt-1">{{ form.errors.yield }}</p>
                        </div>

                        <!-- Canal -->
                        <div>
                            <label for="channel" class="block text-sm font-medium text-gray-700 mb-1">Canal</label>
                            <select
                                v-model="form.channel"
                                id="channel"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option value="">Selecione</option>
                                <option value="padaria">Padaria</option>
                                <option value="lanchonete">Lanchonete</option>
                                <option value="buffet">Buffet</option>
                                <option value="ala-carte">A la Carte</option>
                                <option value="industrial">Industrial</option>
                            </select>
                            <p v-if="form.errors.channel" class="text-red-500 text-xs mt-1">{{ form.errors.channel }}</p>
                        </div>
                    </div>

                    <!-- Right Column - Larger Text Fields -->
                    <div class="space-y-4">
                        <!-- Descrição da Receita -->
                        <div>
                            <label for="recipe_description" class="block text-sm font-medium text-gray-700 mb-1">Descrição da Receita</label>
                            <textarea
                                v-model="form.recipe_description"
                                id="recipe_description"
                                rows="7"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                            ></textarea>
                            <p v-if="form.errors.recipe_description" class="text-red-500 text-xs mt-1">{{ form.errors.recipe_description }}</p>
                        </div>

                        <!-- Descrição dos Ingredientes -->
                        <div>
                            <label for="ingredients_description" class="block text-sm font-medium text-gray-700 mb-1">Descrição dos Ingredientes</label>
                            <textarea
                                v-model="form.ingredients_description"
                                id="ingredients_description"
                                rows="7"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                            ></textarea>
                            <p v-if="form.errors.ingredients_description" class="text-red-500 text-xs mt-1">{{ form.errors.ingredients_description }}</p>
                        </div>

                        <!-- Modo de Preparo -->
                        <div>
                            <label for="preparation_method" class="block text-sm font-medium text-gray-700 mb-1">Modo de Preparo</label>
                            <textarea
                                v-model="form.preparation_method"
                                id="preparation_method"
                                rows="7"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                            ></textarea>
                            <p v-if="form.errors.preparation_method" class="text-red-500 text-xs mt-1">{{ form.errors.preparation_method }}</p>
                        </div>
                    </div>
                </div>

                <!-- Connections Section -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🔗 Conexões e Associações</h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Product Associations -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="bg-orange-100 px-4 py-2 rounded-md mb-4">
                                <h4 class="text-sm font-semibold text-orange-900">🛒 Produtos (N para N)</h4>
                            </div>
                            
                            <div class="space-y-3">
                                <p class="text-sm font-medium text-gray-700">Produtos Vinculados</p>
                                <div class="space-y-2">
                                    <div v-for="(product, index) in form.selected_products" :key="index" class="flex items-center gap-3 p-3 border border-gray-200 rounded bg-white">
                                        <div class="w-8 h-8 bg-orange-100 rounded flex items-center justify-center">
                                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                        </div>
                                        
                                        <select
                                            v-model="product.product_id"
                                            class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                        >
                                            <option value="">Selecione um produto...</option>
                                            <option v-for="availableProduct in props.products" :key="availableProduct.id" :value="availableProduct.id">
                                                {{ availableProduct.descricao || 'Sem descrição' }}
                                            </option>
                                        </select>
                                        
                                        <select
                                            v-model="product.ingredient_type"
                                            class="w-32 border border-gray-300 rounded-md px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                        >
                                            <option value="main">Principal</option>
                                            <option value="supporting">Secundário</option>
                                        </select>

                                        <button 
                                            type="button"
                                            @click="removeProduct(index)"
                                            class="text-red-600 hover:text-red-800 text-sm font-medium px-2 py-1 rounded hover:bg-red-50"
                                        >
                                            Remover
                                        </button>
                                    </div>
                                    
                                    <button 
                                        type="button"
                                        @click="addProduct"
                                        class="text-orange-600 hover:text-orange-800 text-sm font-medium px-3 py-2 border border-orange-300 rounded-md hover:bg-orange-50 transition-colors"
                                    >
                                        + Adicionar Produto
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Content Associations -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="bg-blue-100 px-4 py-2 rounded-md mb-4">
                                <h4 class="text-sm font-semibold text-blue-900">📄 Conteúdos (N para N)</h4>
                            </div>

                            <div class="space-y-3">
                                <p class="text-sm font-medium text-gray-700">Conteúdos Vinculados</p>
                                <div class="space-y-2">
                                    <div v-for="(content, index) in form.selected_contents" :key="index" class="flex items-center gap-3 p-3 border border-gray-200 rounded bg-white">
                                        <div class="w-8 h-8 bg-blue-100 rounded flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        
                                        <select
                                            v-model="content.content_id"
                                            class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        >
                                            <option value="">Selecione um conteúdo...</option>
                                            <option v-for="availableContent in props.contents" :key="availableContent.id" :value="availableContent.id">
                                                {{ availableContent.nome_conteudo || 'Sem nome' }}
                                            </option>
                                        </select>
                                        
                                        <label class="flex items-center gap-2 text-sm">
                                            <input
                                                type="checkbox"
                                                v-model="content.top_dish"
                                                :true-value="'sim'"
                                                :false-value="'nao'"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                            />
                                            <span class="text-gray-700">Top Dish</span>
                                        </label>

                                        <button 
                                            type="button"
                                            @click="removeContent(index)"
                                            class="text-red-600 hover:text-red-800 text-sm font-medium px-2 py-1 rounded hover:bg-red-50"
                                        >
                                            Remover
                                        </button>
                                    </div>
                                    
                                    <button 
                                        type="button"
                                        @click="addContent"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium px-3 py-2 border border-blue-300 rounded-md hover:bg-blue-50 transition-colors"
                                    >
                                        + Adicionar Conteúdo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ingredient Associations -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="bg-green-100 px-4 py-2 rounded-md mb-4">
                            <h4 class="text-sm font-semibold text-green-900">🥕 Ingredientes (N para N)</h4>
                        </div>

                        <div class="space-y-3">
                            <p class="text-sm font-medium text-gray-700">Ingredientes da Receita</p>
                            <div class="space-y-2">
                                <div v-for="(ingredient, index) in selectedIngredients" :key="index" class="flex items-center gap-3 p-3 border border-gray-200 rounded bg-white">
                                    <div class="w-8 h-8 bg-green-100 rounded flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                    </div>
                                    
                                    <!-- Ingredient Search Input -->
                                    <div class="flex-1 relative">
                                        <input
                                            :value="ingredient.search_term"
                                            @input="updateIngredientSearchTerm(index, $event.target.value)"
                                            @focus="showIngredientDropdown(index)"
                                            @blur="hideIngredientDropdown(index)"
                                            type="text"
                                            placeholder="Digite o nome do ingrediente..."
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                        />
                                        
                                        <!-- Dropdown Results -->
                                        <div 
                                            v-if="ingredient.show_dropdown && ingredient.search_results.length > 0"
                                            class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-y-auto"
                                        >
                                            <div 
                                                v-for="result in ingredient.search_results" 
                                                :key="result.id"
                                                @mousedown="selectIngredient(index, result)"
                                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm"
                                            >
                                                {{ result.name }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <label class="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            :checked="ingredient.primary_ingredient"
                                            @change="updateIngredientPrimary(index, $event.target.checked)"
                                            class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 focus:ring-2"
                                        />
                                        <span class="text-gray-700">Principal</span>
                                    </label>

                                    <button 
                                        type="button"
                                        @click="removeIngredient(index)"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium px-2 py-1 rounded hover:bg-red-50"
                                    >
                                        Remover
                                    </button>
                                </div>
                                
                                <button 
                                    type="button"
                                    @click="addIngredient"
                                    class="text-green-600 hover:text-green-800 text-sm font-medium px-3 py-2 border border-green-300 rounded-md hover:bg-green-50 transition-colors"
                                >
                                    + Adicionar Ingrediente
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
                        <span v-else>{{ form.id ? 'Atualizar Receita' : 'Salvar Receita' }}</span>
                    </button>
                </div>
            </form>

            <!-- Saved Recipes Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">📋 Receitas Salvas</h2>
                
                <div v-if="props.recipes?.length" class="space-y-3">
                    <div 
                        v-for="recipe in props.recipes" 
                        :key="recipe.id"
                        class="border border-gray-200 rounded-md p-4 bg-gray-50"
                    >
                        <h3 class="font-medium text-gray-900">{{ recipe.recipe_name || 'Sem título' }}</h3>
                        <p class="text-sm text-gray-600 mt-2 line-clamp-2">
                            {{ recipe.recipe_description || 'Sem descrição' }}...
                        </p>
                        
                        <!-- Associations Info -->
                        <div class="mt-3 space-y-2">
                            <!-- Products -->
                            <div v-if="recipe.products?.length" class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="font-medium">🛒 Produtos:</span>
                                <span>{{ recipe.products.length }} vinculado(s)</span>
                            </div>
                            
                            <!-- Contents -->
                            <div v-if="recipe.contents?.length" class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="font-medium">📄 Conteúdos:</span>
                                <span>{{ recipe.contents.length }} vinculado(s)</span>
                            </div>
                            
                            <!-- Ingredients -->
                            <div v-if="recipe.ingredients?.length" class="flex items-center gap-2 text-sm text-gray-600">
                                <span class="font-medium">🥕 Ingredientes:</span>
                                <span>{{ recipe.ingredients.length }} vinculado(s)</span>
                            </div>
                        </div>
                        
                        <div class="mt-3 flex gap-2">
                            <button 
                                @click="editRecipe(recipe)" 
                                class="text-sm text-orange-600 hover:text-orange-800 font-medium"
                            >
                                Editar
                            </button>
                            <button 
                                @click="deleteRecipe(recipe)" 
                                class="text-sm text-red-600 hover:text-red-800 font-medium"
                            >
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-8 text-gray-500">
                    <p>Nenhuma receita cadastrada</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { router, useForm, Head } from '@inertiajs/vue3'
import {ref, nextTick, onMounted, reactive} from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'



const breadcrumbs = [
    {
        title: 'Receitas',
        href: '/recipes',
    },
]

const props = defineProps({
    recipes: Array,
    products: Array,
    contents: Array,
    ingredients: Array
})

// Toast notification system
const toasts = ref([])
let toastIdCounter = 0

// Reactive ingredients state
const selectedIngredients = ref([])

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

// Content management functions
function addContent() {
    form.selected_contents.push({ content_id: '', top_dish: 'nao' })
}

function removeContent(index) {
    form.selected_contents.splice(index, 1)
}

// Product management functions
function addProduct() {
    form.selected_products.push({ 
        product_id: '', 
        ingredient_type: 'main'
    })
}

function removeProduct(index) {
    form.selected_products.splice(index, 1)
}

// Ingredient management functions
function addIngredient() {
    const newIngredient = { 
        ingredient_id: null,
        ingredient_name: '',
        search_term: '',
        search_results: [],
        show_dropdown: false,
        primary_ingredient: true
    }
    
    selectedIngredients.value.push(newIngredient)
}

function removeIngredient(index) {
    if (selectedIngredients.value[index]) {
        selectedIngredients.value.splice(index, 1)
    }
}

function showIngredientDropdown(index) {
    if (selectedIngredients.value[index]) {
        selectedIngredients.value[index].show_dropdown = true
    }
}

function hideIngredientDropdown(index) {
    setTimeout(() => {
        if (selectedIngredients.value[index]) {
            selectedIngredients.value[index].show_dropdown = false
        }
    }, 200)
}

function searchIngredients(index) {
    const ingredient = selectedIngredients.value[index]
    if (!ingredient) return
    
    const query = ingredient.search_term.trim()
    
    if (query.length < 2) {
        ingredient.search_results = []
        return
    }
    
    // Search in existing ingredients first
    const existingIngredients = props.ingredients.filter(ing => 
        ing.name.toLowerCase().includes(query.toLowerCase())
    )
    
    ingredient.search_results = existingIngredients.slice(0, 5)
}

function selectIngredient(index, selectedIngredient) {
    const ingredient = selectedIngredients.value[index]
    if (!ingredient) return
    
    ingredient.ingredient_id = selectedIngredient.id
    ingredient.ingredient_name = selectedIngredient.name
    ingredient.search_term = selectedIngredient.name
    ingredient.show_dropdown = false
    ingredient.search_results = []
}

function updateIngredientSearchTerm(index, value) {
    if (selectedIngredients.value[index]) {
        selectedIngredients.value[index].search_term = value
        // Keep ingredient_name mirrored so backend can create when id is missing
        selectedIngredients.value[index].ingredient_name = value
        searchIngredients(index)
    }
}

function updateIngredientPrimary(index, value) {
    if (selectedIngredients.value[index]) {
        selectedIngredients.value[index].primary_ingredient = value
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

function showFormSuccessMessage(isUpdate, itemName) {
    const message = isUpdate 
        ? `${itemName} atualizado com sucesso!` 
        : `${itemName} criado com sucesso!`
    showToast(message, 'success')
}

function confirmResetForm() {
    if (form.id) {
        // If editing, just cancel without confirmation
        form.reset()
        form.selected_products = []
        form.selected_contents = []
        selectedIngredients.value = []
        form.id = null
        showToast('Edição cancelada', 'info')
    } else {
        // If creating new, ask for confirmation
        if (confirm('Tem certeza que deseja limpar todos os dados do formulário?')) {
            form.reset()
            form.selected_products = []
            form.selected_contents = []
            selectedIngredients.value = []
            showToast('Formulário limpo', 'info')
        }
    }
}

const form = useForm({
    id: null,
    recipe_code: null,
    recipe_name: null,
    cuisine: null,
    recipe_type: null,
    service_order: null,
    preparation_time: null,
    difficulty_level: null,
    yield: null,
    channel: null,
    recipe_description: null,
    ingredients_description: null,
    preparation_method: null,
    main_ingredients: [],
    supporting_ingredients: [],
    usage_groups: [],
    preparation_techniques: [],
    consumption_occasion: [],
    // Product associations
    selected_products: [],
    // Content associations
    selected_contents: [],
    // Ingredient associations
    selected_ingredients: []
})

const resetForm = () => {
    form.reset()
    form.selected_products = []
    form.selected_contents = []
    selectedIngredients.value = []
    form.id = null
    showToast('Formulário limpo para nova receita', 'info')
}

function editRecipe(recipe) {
    form.id = recipe.id
    form.recipe_code = recipe.recipe_code
    form.recipe_name = recipe.recipe_name
    form.cuisine = recipe.cuisine
    form.recipe_type = recipe.recipe_type
    form.service_order = recipe.service_order
    form.preparation_time = recipe.preparation_time
    form.difficulty_level = recipe.difficulty_level
    form.yield = recipe.yield
    form.channel = recipe.channel
    form.recipe_description = recipe.recipe_description
    form.ingredients_description = recipe.ingredients_description
    form.preparation_method = recipe.preparation_method
    form.main_ingredients = recipe.main_ingredients || []
    form.supporting_ingredients = recipe.supporting_ingredients || []
    form.usage_groups = recipe.usage_groups || []
    form.preparation_techniques = recipe.preparation_techniques || []
    form.consumption_occasion = recipe.consumption_occasion || []
    
    // Load associated products
    form.selected_products = recipe.products ? recipe.products.map(product => ({
        product_id: product.id,
        ingredient_type: product.pivot.ingredient_type
    })) : []
    
    // Load associated contents
    form.selected_contents = recipe.contents ? recipe.contents.map(content => ({
        content_id: content.id,
        top_dish: content.pivot.top_dish || 'nao'
    })) : []
    
    // Load associated ingredients
    selectedIngredients.value = recipe.ingredients ? recipe.ingredients.map(ingredient => ({
        ingredient_id: ingredient.id,
        ingredient_name: ingredient.name,
        search_term: ingredient.name,
        search_results: [],
        show_dropdown: false,
        primary_ingredient: ingredient.pivot.primary_ingredient
    })) : []
}

function deleteRecipe(recipe) {
    if (confirm(`Tem certeza que deseja excluir "${recipe.recipe_name || 'esta receita'}"?`)) {
        router.delete(`recipes/${recipe.id}`, {
            onSuccess: () => {
                const message = `Receita "${recipe.recipe_name || 'sem título'}" excluída com sucesso!`
                showToast(message, 'success')
            },
            onError: (errors) => {
                console.error('Recipe deletion errors:', errors)
                const errorMessage = 'Erro ao excluir a receita. Tente novamente.'
                showToast(errorMessage, 'error')
            }
        })
    }
}

const submit = () => {
    // Add selected ingredients to form data before submission
    form.selected_ingredients = selectedIngredients.value
    
    form.post("/recipes", {
        onSuccess: () => {
            const isUpdate = form.id !== null
            const itemName = form.recipe_name || 'Receita'
            showFormSuccessMessage(isUpdate, itemName)
            
            // Reset form after successful submission
            form.reset()
            form.selected_products = []
            form.selected_contents = []
            selectedIngredients.value = []
            
            // Clear form ID to indicate new recipe creation
            form.id = null
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors)
            
            if (Object.keys(errors).length > 0) {
                showValidationErrors(errors)
            } else {
                showToast('Erro ao salvar a receita. Verifique os dados e tente novamente.', 'error')
            }
        }
    })
}
</script>