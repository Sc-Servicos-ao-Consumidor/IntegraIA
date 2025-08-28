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
                                <option value="website">Website</option>
                                <option value="youtube">YouTube</option>
                                <option value="instagram">Instagram</option>
                                <option value="blog">Blog</option>
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
import {ref, nextTick} from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'



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
        form.id = null
        showToast('Edição cancelada', 'info')
    } else {
        // If creating new, ask for confirmation
        if (confirm('Tem certeza que deseja limpar todos os dados do formulário?')) {
            form.reset()
            form.selected_products = []
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
    general_images_link: null,
    product_code: null,
    content_code: null,
    // Product associations
    selected_products: []
})

const resetForm = () => {
    form.reset()
    form.selected_products = []
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
    form.post("/recipes", {
        onSuccess: () => {
            const isUpdate = form.id !== null
            const itemName = form.recipe_name || 'Receita'
            showFormSuccessMessage(isUpdate, itemName)
            
            // Reset form after successful submission
            form.reset()
            form.selected_products = []
            
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