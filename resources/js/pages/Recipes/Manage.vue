<template>
    <Head title="Receitas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-2xl mx-auto p-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-6 text-white-800">{{ header }}</h1>
        <!-- Semantic Search -->
        <div class="mb-10">
            <h2 class="text-xl font-bold mb-4">🔍 Busca Semântica</h2>
            
            <div class="flex gap-2 mb-4">
                <input
                v-model="query"
                @keyup.enter="search"
                type="text"
                placeholder="ex: massa cremosa vegana"
                class="flex-1 px-4 py-2 border rounded border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                />
                <button @click="search" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg disabled:opacity-50">
                    Buscar
                </button>
            </div>
            
            <div v-if="loading">Buscando...</div>
            
            <div v-if="assistantResponse" class="mt-10 rounded">
                <h2 class="text-xl font-bold mb-2">Sugestão do Assistente</h2>
                <p class="w-full border border-gray-500 text-white-700 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    {{ assistantResponse || JSON.stringify(assistantResponse, null, 2) }}
                </p>
            </div>

            <div v-if="results.length" class="mt-10">
                <h2 class="text-xl font-bold mb-4">Resultados da Busca</h2>
                
                <ul class="space-y-4">
                    <li
                    v-for="recipe in results"
                    :key="recipe.id"
                    class="border rounded p-4 shadow"
                    >
                    <h3 class="text-lg font-semibold">{{ recipe.title || 'Sem título' }}</h3>
                    <p class="text-sm text-gray-500 italic mt-1">
                        Tags: 
                        <span v-if="recipe.tags?.length">{{ recipe.tags.join(', ') }}</span>
                        <span v-else class="text-gray-400">Nenhuma</span>
                    </p>
                    
                    <p class="text-sm text-white-600 line-clamp-2">
                        {{ recipe.raw_text.slice(0, 150) }}...
                    </p>
                    
                    
                </li>
            </ul>
        </div>

        <div v-else-if="!loading && hasSearched" class="text-gray-600 italic mt-10">
            Nenhuma receita encontrada.
        </div>

    </div>
    
    <form @submit.prevent="submit" class="space-y-8">
        <!-- Basic Information Section -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📝 Informações Básicas</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Recipe Code -->
                <div>
                    <label for="recipe_code" class="block text-sm font-medium text-gray-700 mb-1">Código da Receita</label>
                    <input
                        v-model="form.recipe_code"
                        id="recipe_code"
                        type="text"
                        placeholder="ex: REC001"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.recipe_code" class="text-red-500 text-sm mt-1">{{ form.errors.recipe_code }}</p>
                </div>

                <!-- Recipe Name -->
                <div>
                    <label for="recipe_name" class="block text-sm font-medium text-gray-700 mb-1">Nome da Receita</label>
                    <input
                        v-model="form.recipe_name"
                        id="recipe_name"
                        type="text"
                        placeholder="ex: Risotto Cremoso de Cogumelos"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.recipe_name" class="text-red-500 text-sm mt-1">{{ form.errors.recipe_name }}</p>
                </div>

                <!-- Title (Legacy) -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Título (Legado)</label>
                    <input
                        v-model="form.title"
                        id="title"
                        type="text"
                        placeholder="Título da sua receita"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
                </div>

                <!-- Metadata -->
                <div class="md:col-span-2">
                    <label for="metadata" class="block text-sm font-medium text-gray-700 mb-1">Metadados (JSON)</label>
                    <textarea
                        v-model="metadataString"
                        @input="updateMetadata"
                        id="metadata"
                        rows="2"
                        placeholder='{"chave": "valor", "nutricao": {...}}'
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 font-mono text-sm"
                    ></textarea>
                    <p v-if="form.errors.metadata" class="text-red-500 text-sm mt-1">{{ form.errors.metadata }}</p>
                    <p class="text-gray-500 text-xs mt-1">Metadados JSON opcionais para informações adicionais da receita</p>
                </div>

                <!-- Cuisine -->
                <div>
                    <label for="cuisine" class="block text-sm font-medium text-gray-700 mb-1">Culinária</label>
                    <input
                        v-model="form.cuisine"
                        id="cuisine"
                        type="text"
                        placeholder="ex: Italiana, Mexicana, Asiática"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.cuisine" class="text-red-500 text-sm mt-1">{{ form.errors.cuisine }}</p>
                </div>

                <!-- Recipe Type -->
                <div>
                    <label for="recipe_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Receita</label>
                    <select
                        v-model="form.recipe_type"
                        id="recipe_type"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    >
                        <option value="">Selecione o tipo</option>
                        <option value="appetizer">Aperitivo</option>
                        <option value="main_course">Prato Principal</option>
                        <option value="dessert">Sobremesa</option>
                        <option value="beverage">Bebida</option>
                        <option value="side_dish">Acompanhamento</option>
                        <option value="salad">Salada</option>
                        <option value="soup">Sopa</option>
                        <option value="snack">Lanche</option>
                    </select>
                    <p v-if="form.errors.recipe_type" class="text-red-500 text-sm mt-1">{{ form.errors.recipe_type }}</p>
                </div>

                <!-- Channel -->
                <div>
                    <label for="channel" class="block text-sm font-medium text-gray-700 mb-1">Canal</label>
                    <input
                        v-model="form.channel"
                        id="channel"
                        type="text"
                        placeholder="ex: YouTube, Site, Blog"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.channel" class="text-red-500 text-sm mt-1">{{ form.errors.channel }}</p>
                </div>
            </div>
        </div>

        <!-- Recipe Details Section -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">⏱️ Detalhes da Receita</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Preparation Time -->
                <div>
                    <label for="preparation_time" class="block text-sm font-medium text-gray-700 mb-1">Tempo de Preparo (minutos)</label>
                    <input
                        v-model.number="form.preparation_time"
                        id="preparation_time"
                        type="number"
                        min="1"
                        placeholder="ex: 30"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.preparation_time" class="text-red-500 text-sm mt-1">{{ form.errors.preparation_time }}</p>
                </div>

                <!-- Difficulty Level -->
                <div>
                    <label for="difficulty_level" class="block text-sm font-medium text-gray-700 mb-1">Nível de Dificuldade</label>
                    <select
                        v-model="form.difficulty_level"
                        id="difficulty_level"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    >
                        <option value="">Selecione a dificuldade</option>
                        <option value="easy">Fácil</option>
                        <option value="medium">Médio</option>
                        <option value="hard">Difícil</option>
                        <option value="expert">Especialista</option>
                    </select>
                    <p v-if="form.errors.difficulty_level" class="text-red-500 text-sm mt-1">{{ form.errors.difficulty_level }}</p>
                </div>

                <!-- Yield/Servings -->
                <div>
                    <label for="yield" class="block text-sm font-medium text-gray-700 mb-1">Rendimento/Porções</label>
                    <input
                        v-model="form.yield"
                        id="yield"
                        type="text"
                        placeholder="ex: 4 porções"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.yield" class="text-red-500 text-sm mt-1">{{ form.errors.yield }}</p>
                </div>
            </div>

            <div class="mt-4">
                <!-- Service Order -->
                <div>
                    <label for="service_order" class="block text-sm font-medium text-gray-700 mb-1">Ordem de Serviço</label>
                    <input
                        v-model="form.service_order"
                        id="service_order"
                        type="text"
                        placeholder="ex: Primeiro prato, Prato principal"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.service_order" class="text-red-500 text-sm mt-1">{{ form.errors.service_order }}</p>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📖 Conteúdo</h3>
            
            <!-- Recipe Description -->
            <div class="mb-4">
                <label for="recipe_description" class="block text-sm font-medium text-gray-700 mb-1">Descrição da Receita</label>
                <textarea
                    v-model="form.recipe_description"
                    id="recipe_description"
                    rows="3"
                    placeholder="Breve descrição da receita..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                ></textarea>
                <p v-if="form.errors.recipe_description" class="text-red-500 text-sm mt-1">{{ form.errors.recipe_description }}</p>
            </div>

            <!-- Ingredients Description -->
            <div class="mb-4">
                <label for="ingredients_description" class="block text-sm font-medium text-gray-700 mb-1">Descrição dos Ingredientes</label>
                <textarea
                    v-model="form.ingredients_description"
                    id="ingredients_description"
                    rows="3"
                    placeholder="Descrição dos ingredientes utilizados..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                ></textarea>
                <p v-if="form.errors.ingredients_description" class="text-red-500 text-sm mt-1">{{ form.errors.ingredients_description }}</p>
            </div>

            <!-- Preparation Method -->
            <div class="mb-4">
                <label for="preparation_method" class="block text-sm font-medium text-gray-700 mb-1">Método de Preparo</label>
                <textarea
                    v-model="form.preparation_method"
                    id="preparation_method"
                    rows="6"
                    placeholder="Instruções passo a passo do preparo..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                ></textarea>
                <p v-if="form.errors.preparation_method" class="text-red-500 text-sm mt-1">{{ form.errors.preparation_method }}</p>
            </div>

            <!-- Raw Text (Legacy) -->
            <div>
                <label for="raw_text" class="block text-sm font-medium text-gray-700 mb-1">Texto da Receita (Legado)</label>
                <textarea
                    v-model="form.raw_text"
                    id="raw_text"
                    rows="4"
                    placeholder="Cole o texto completo da receita aqui..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                ></textarea>
                <p v-if="form.errors.raw_text" class="text-red-500 text-sm mt-1">{{ form.errors.raw_text }}</p>
            </div>
        </div>

        <!-- Ingredients Section -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🥘 Ingredientes</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Main Ingredients -->
                <div>
                    <label for="main_ingredients_input" class="block text-sm font-medium text-gray-700 mb-1">Ingredientes Principais</label>
                    <div class="flex flex-wrap gap-2 mb-2">
                        <span
                            v-for="(ingredient, index) in form.main_ingredients"
                            :key="index"
                            class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm flex items-center"
                        >
                            {{ ingredient }}
                            <button
                                type="button"
                                @click="removeMainIngredient(index)"
                                class="ml-2 text-green-600 hover:text-red-600 focus:outline-none"
                            >
                                &times;
                            </button>
                        </span>
                    </div>
                    <input
                        v-model="newMainIngredient"
                        @keydown.enter.prevent="addMainIngredient"
                        id="main_ingredients_input"
                        type="text"
                        placeholder="Adicione ingrediente principal e pressione Enter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.main_ingredients" class="text-red-500 text-sm mt-1">{{ form.errors.main_ingredients }}</p>
                </div>

                <!-- Supporting Ingredients -->
                <div>
                    <label for="supporting_ingredients_input" class="block text-sm font-medium text-gray-700 mb-1">Ingredientes de Apoio</label>
                    <div class="flex flex-wrap gap-2 mb-2">
                        <span
                            v-for="(ingredient, index) in form.supporting_ingredients"
                            :key="index"
                            class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm flex items-center"
                        >
                            {{ ingredient }}
                            <button
                                type="button"
                                @click="removeSupportingIngredient(index)"
                                class="ml-2 text-yellow-600 hover:text-red-600 focus:outline-none"
                            >
                                &times;
                            </button>
                        </span>
                    </div>
                    <input
                        v-model="newSupportingIngredient"
                        @keydown.enter.prevent="addSupportingIngredient"
                        id="supporting_ingredients_input"
                        type="text"
                        placeholder="Adicione ingrediente de apoio e pressione Enter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.supporting_ingredients" class="text-red-500 text-sm mt-1">{{ form.errors.supporting_ingredients }}</p>
                </div>
            </div>
        </div>

        <!-- Categories Section -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🏷️ Categorias e Classificações</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Usage Groups -->
                <div>
                    <label for="usage_groups_input" class="block text-sm font-medium text-gray-700 mb-1">Grupos de Uso</label>
                    <div class="flex flex-wrap gap-2 mb-2">
                        <span
                            v-for="(group, index) in form.usage_groups"
                            :key="index"
                            class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm flex items-center"
                        >
                            {{ group }}
                            <button
                                type="button"
                                @click="removeUsageGroup(index)"
                                class="ml-2 text-purple-600 hover:text-red-600 focus:outline-none"
                            >
                                &times;
                            </button>
                        </span>
                    </div>
                    <input
                        v-model="newUsageGroup"
                        @keydown.enter.prevent="addUsageGroup"
                        id="usage_groups_input"
                        type="text"
                        placeholder="Adicione grupo de uso e pressione Enter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.usage_groups" class="text-red-500 text-sm mt-1">{{ form.errors.usage_groups }}</p>
                </div>

                <!-- Preparation Techniques -->
                <div>
                    <label for="preparation_techniques_input" class="block text-sm font-medium text-gray-700 mb-1">Técnicas de Preparo</label>
                    <div class="flex flex-wrap gap-2 mb-2">
                        <span
                            v-for="(technique, index) in form.preparation_techniques"
                            :key="index"
                            class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm flex items-center"
                        >
                            {{ technique }}
                            <button
                                type="button"
                                @click="removePreparationTechnique(index)"
                                class="ml-2 text-orange-600 hover:text-red-600 focus:outline-none"
                            >
                                &times;
                            </button>
                        </span>
                    </div>
                    <input
                        v-model="newPreparationTechnique"
                        @keydown.enter.prevent="addPreparationTechnique"
                        id="preparation_techniques_input"
                        type="text"
                        placeholder="Adicione técnica e pressione Enter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.preparation_techniques" class="text-red-500 text-sm mt-1">{{ form.errors.preparation_techniques }}</p>
                </div>

                <!-- Consumption Occasion -->
                <div>
                    <label for="consumption_occasion_input" class="block text-sm font-medium text-gray-700 mb-1">Ocasião de Consumo</label>
                    <div class="flex flex-wrap gap-2 mb-2">
                        <span
                            v-for="(occasion, index) in form.consumption_occasion"
                            :key="index"
                            class="bg-pink-100 text-pink-800 px-3 py-1 rounded-full text-sm flex items-center"
                        >
                            {{ occasion }}
                            <button
                                type="button"
                                @click="removeConsumptionOccasion(index)"
                                class="ml-2 text-pink-600 hover:text-red-600 focus:outline-none"
                            >
                                &times;
                            </button>
                        </span>
                    </div>
                    <input
                        v-model="newConsumptionOccasion"
                        @keydown.enter.prevent="addConsumptionOccasion"
                        id="consumption_occasion_input"
                        type="text"
                        placeholder="Adicione ocasião e pressione Enter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.consumption_occasion" class="text-red-500 text-sm mt-1">{{ form.errors.consumption_occasion }}</p>
                </div>

                <!-- Tags (Legacy) -->
                <div>
                    <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">Tags (Legado)</label>
                    <div class="flex flex-wrap gap-2 mb-2">
                        <span
                            v-for="(tag, index) in form.tags"
                            :key="index"
                            class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm flex items-center"
                        >
                            {{ tag }}
                            <button
                                type="button"
                                @click="removeTag(index)"
                                class="ml-2 text-blue-600 hover:text-red-600 focus:outline-none"
                            >
                                &times;
                            </button>
                        </span>
                    </div>
                    <input
                        v-model="newTag"
                        @keydown.enter.prevent="addTag"
                        id="tags"
                        type="text"
                        placeholder="Digite uma tag e pressione Enter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.tags" class="text-red-500 text-sm mt-1">{{ form.errors.tags }}</p>
                </div>
            </div>
        </div>

        <!-- Media & References Section -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📸 Mídia e Referências</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- General Images Link -->
                <div>
                    <label for="general_images_link" class="block text-sm font-medium text-gray-700 mb-1">Link de Imagens Gerais</label>
                    <input
                        v-model="form.general_images_link"
                        id="general_images_link"
                        type="url"
                        placeholder="https://exemplo.com/imagens"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.general_images_link" class="text-red-500 text-sm mt-1">{{ form.errors.general_images_link }}</p>
                </div>

                <!-- Product Code -->
                <div>
                    <label for="product_code" class="block text-sm font-medium text-gray-700 mb-1">Código do Produto</label>
                    <input
                        v-model="form.product_code"
                        id="product_code"
                        type="text"
                        placeholder="ex: PROD001"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.product_code" class="text-red-500 text-sm mt-1">{{ form.errors.product_code }}</p>
                </div>

                <!-- Content Code -->
                <div>
                    <label for="content_code" class="block text-sm font-medium text-gray-700 mb-1">Código do Conteúdo</label>
                    <input
                        v-model="form.content_code"
                        id="content_code"
                        type="text"
                        placeholder="ex: CONT001"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.content_code" class="text-red-500 text-sm mt-1">{{ form.errors.content_code }}</p>
                </div>
            </div>
        </div>

<div class="flex justify-end gap-3 mt-6">
    <!-- Reset Button -->
    <button
    type="reset"
    @click="form.reset()"
    class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-2 rounded-lg"
    >
    Limpar
</button>

<!-- Submit Button -->
<button
type="submit"
:disabled="form.processing"
class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg disabled:opacity-50"
>
{{ form.processing ? 'Salvando...' : 'Salvar Receita' }}
</button>
</div>

</form>
<!-- Saved Recipes -->
<div class="mt-10">
    <h2 class="text-xl font-bold mb-4">📋 Receitas Salvas</h2>
    
    <ul class="space-y-4">
        <li class="border rounded p-4 shadow" v-for="recipe in props.recipes" :key="recipe.id">
            <h3 class="text-lg font-semibold">{{ recipe.title || 'Sem título' }}</h3>
            <p class="text-sm text-white-600 line-clamp-2">
                {{ recipe.raw_text.slice(0, 150) }}...
            </p>
            
            <div class="mt-2 flex gap-2">
                <button @click="editRecipe  (recipe)" class="text-blue-600 hover:underline">Editar</button>
                <button @click="deleteRecipe(recipe)" class="text-red-600 hover:underline">Excluir</button>
            </div>
        </li>
    </ul>
</div>
        </div>
    </AppLayout>
</template>


<script setup>
import { router, useForm, Head } from '@inertiajs/vue3'
import {ref} from 'vue'
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'

const query = ref('')
const results = ref([])
const loading = ref(false)
const hasSearched = ref(false)
const header = ref('SmartChef IA')
const assistantResponse = ref(null)

const breadcrumbs = [
    {
        title: 'Receitas',
        href: '/recipes',
    },
]

const props = defineProps({
    recipes: Array
})
const form = useForm({
    id: null,
    title: null,
    raw_text: null,
    metadata: null,
    tags: [],
    // New fields
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
    general_images_link: null,
    product_code: null,
    content_code: null
})
const search = async () => {
    if (!query.value.trim()) return
    
    loading.value = true
    hasSearched.value = true
    
    try {
        const searchResponse = await axios.get('/recipes/search', {
            params: { query: query.value }
        })
        results.value = searchResponse.data

        const assistantRes = await axios.post('/recipes/assistant', {
            text: query.value,
            context: results.value,
        })
        assistantResponse.value = assistantRes.data

    } catch (error) {
        console.error('Search error:', error)
        results.value = []
    } finally {
        loading.value = false
    }
}

const newTag = ref('')
const newMainIngredient = ref('')
const newSupportingIngredient = ref('')
const newUsageGroup = ref('')
const newPreparationTechnique = ref('')
const newConsumptionOccasion = ref('')
const metadataString = ref('')

function addTag() {
    const tag = newTag.value.trim()

    if (tag && !form.tags.includes(tag)) {
        form.tags.push(tag)
    }

    newTag.value = ''
}

function removeTag(index) {
    form.tags.splice(index, 1)
}

function addMainIngredient() {
    const ingredient = newMainIngredient.value.trim()

    if (ingredient && !form.main_ingredients.includes(ingredient)) {
        form.main_ingredients.push(ingredient)
    }

    newMainIngredient.value = ''
}

function removeMainIngredient(index) {
    form.main_ingredients.splice(index, 1)
}

function addSupportingIngredient() {
    const ingredient = newSupportingIngredient.value.trim()

    if (ingredient && !form.supporting_ingredients.includes(ingredient)) {
        form.supporting_ingredients.push(ingredient)
    }

    newSupportingIngredient.value = ''
}

function removeSupportingIngredient(index) {
    form.supporting_ingredients.splice(index, 1)
}

function addUsageGroup() {
    const group = newUsageGroup.value.trim()

    if (group && !form.usage_groups.includes(group)) {
        form.usage_groups.push(group)
    }

    newUsageGroup.value = ''
}

function removeUsageGroup(index) {
    form.usage_groups.splice(index, 1)
}

function addPreparationTechnique() {
    const technique = newPreparationTechnique.value.trim()

    if (technique && !form.preparation_techniques.includes(technique)) {
        form.preparation_techniques.push(technique)
    }

    newPreparationTechnique.value = ''
}

function removePreparationTechnique(index) {
    form.preparation_techniques.splice(index, 1)
}

function addConsumptionOccasion() {
    const occasion = newConsumptionOccasion.value.trim()

    if (occasion && !form.consumption_occasion.includes(occasion)) {
        form.consumption_occasion.push(occasion)
    }

    newConsumptionOccasion.value = ''
}

function removeConsumptionOccasion(index) {
    form.consumption_occasion.splice(index, 1)
}

function updateMetadata() {
    try {
        if (metadataString.value.trim()) {
            form.metadata = JSON.parse(metadataString.value)
        } else {
            form.metadata = null
        }
    } catch (error) {
        // Keep the string value if JSON is invalid, validation will handle it
        console.warn('Invalid JSON in metadata field')
    }
}

function editRecipe(recipe) {
    form.id = recipe.id
    form.title = recipe.title
    form.raw_text = recipe.raw_text
    form.metadata = recipe.metadata
    metadataString.value = recipe.metadata ? JSON.stringify(recipe.metadata, null, 2) : ''
    form.tags = recipe.tags || []
    // New fields
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
    form.general_images_link = recipe.general_images_link
    form.product_code = recipe.product_code
    form.content_code = recipe.content_code
}

function deleteRecipe(recipe) {
    router.delete(`recipes/${recipe.id}`)
}

const submit = () => {
    form.post("/recipes", {
        onSuccess: () => {
            form.reset()
        }
    })
}

</script>