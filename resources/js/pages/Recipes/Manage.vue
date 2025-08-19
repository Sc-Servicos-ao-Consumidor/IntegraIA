<template>
    <Head title="Cadastros de Receitas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Cadastros de Receitas</h1>
            </div>

            <!-- Semantic Search Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">🔍 Busca Semântica</h2>
                
                <div class="flex gap-3 mb-4">
                    <input
                        v-model="query"
                        @keyup.enter="search"
                        type="text"
                        placeholder="ex: massa cremosa vegana"
                        class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    />
                    <button 
                        @click="search" 
                        class="bg-orange-600 hover:bg-orange-700 text-white font-medium px-6 py-2 rounded-md text-sm transition-colors"
                    >
                        Buscar
                    </button>
                </div>
                
                <div v-if="loading" class="text-gray-600">Buscando...</div>
                
                <div v-if="assistantResponse" class="mt-6">
                    <h3 class="font-medium text-gray-900 mb-2">Sugestão do Assistente</h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-md p-3 text-sm text-gray-700">
                        {{ assistantResponse }}
                    </div>
                </div>

                <div v-if="results.length" class="mt-6">
                    <h3 class="font-medium text-gray-900 mb-3">Resultados da Busca</h3>
                    <div class="space-y-3">
                        <div
                            v-for="recipe in results"
                            :key="recipe.id"
                            class="border border-gray-200 rounded-md p-4 bg-gray-50"
                        >
                            <h4 class="font-medium text-gray-900">{{ recipe.title || 'Sem título' }}</h4>
                            <p class="text-xs text-gray-500 mt-1">
                                Tags: 
                                <span v-if="recipe.tags?.length">{{ recipe.tags.join(', ') }}</span>
                                <span v-else>Nenhuma</span>
                            </p>
                            <p class="text-sm text-gray-600 mt-2 line-clamp-2">
                                {{ recipe.raw_text.slice(0, 150) }}...
                            </p>
                        </div>
                    </div>
                </div>

                <div v-else-if="!loading && hasSearched" class="text-gray-500 text-sm mt-6">
                    Nenhuma receita encontrada.
                </div>
            </div>
    
            <!-- Recipe Form -->
            <form @submit.prevent="submit" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column -->
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

                        <!-- Prompt da Receita -->
                        <div>
                            <label for="recipe_description" class="block text-sm font-medium text-gray-700 mb-1">Prompt da Receita</label>
                            <textarea
                                v-model="form.recipe_description"
                                id="recipe_description"
                                rows="4"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
                            ></textarea>
                            <p v-if="form.errors.recipe_description" class="text-red-500 text-xs mt-1">{{ form.errors.recipe_description }}</p>
                        </div>

                        <!-- Descrição dos Ingredientes -->
                        <div>
                            <label for="ingredients_description" class="block text-sm font-medium text-gray-700 mb-1">Descrição dos Ingredientes</label>
                            <textarea
                                v-model="form.ingredients_description"
                                id="ingredients_description"
                                rows="8"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
                            ></textarea>
                            <p v-if="form.errors.ingredients_description" class="text-red-500 text-xs mt-1">{{ form.errors.ingredients_description }}</p>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
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
                            <label for="preparation_time" class="block text-sm font-medium text-gray-700 mb-1">Tempo de Preparo</label>
                            <select
                                v-model="form.preparation_time"
                                id="preparation_time"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option value="">Selecione</option>
                                <option value="15">15 minutos</option>
                                <option value="30">30 minutos</option>
                                <option value="45">45 minutos</option>
                                <option value="60">1 hora</option>
                                <option value="90">1h 30min</option>
                                <option value="120">2 horas</option>
                                <option value="180">3 horas</option>
                            </select>
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
                                <option value="website">Website</option>
                                <option value="youtube">YouTube</option>
                                <option value="instagram">Instagram</option>
                                <option value="blog">Blog</option>
                            </select>
                            <p v-if="form.errors.channel" class="text-red-500 text-xs mt-1">{{ form.errors.channel }}</p>
                        </div>

                        <!-- Modo de Preparo -->
                        <div>
                            <label for="preparation_method" class="block text-sm font-medium text-gray-700 mb-1">Modo de Preparo</label>
                            <textarea
                                v-model="form.preparation_method"
                                id="preparation_method"
                                rows="8"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
                            ></textarea>
                            <p v-if="form.errors.preparation_method" class="text-red-500 text-xs mt-1">{{ form.errors.preparation_method }}</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
                    <button
                        type="reset"
                        @click="form.reset()"
                        class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                    >
                        Limpar
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Salvando...' : 'Salvar Receita' }}
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
                        <h3 class="font-medium text-gray-900">{{ recipe.title || recipe.recipe_name || 'Sem título' }}</h3>
                        <p class="text-sm text-gray-600 mt-2 line-clamp-2">
                            {{ recipe.recipe_description || recipe.raw_text?.slice(0, 150) || 'Sem descrição' }}...
                        </p>
                        
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
    recipes: Array,
    products: Array
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
    content_code: null,
    // Product associations
    selected_products: []
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

function editRecipe(recipe) {
    form.id = recipe.id
    form.title = recipe.title
    form.raw_text = recipe.raw_text
    form.metadata = recipe.metadata
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
    
    // Load associated products
    form.selected_products = recipe.products ? recipe.products.map(product => ({
        product_id: product.id,
        quantity: product.pivot.quantity,
        unit: product.pivot.unit,
        ingredient_type: product.pivot.ingredient_type,
        preparation_notes: product.pivot.preparation_notes,
        optional: product.pivot.optional
    })) : []
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