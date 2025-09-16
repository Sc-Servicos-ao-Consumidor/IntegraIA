<template>
    <Head title="Busca Semântica" />

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
            <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">🔍 Busca Semântica</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Encontre receitas, produtos e conteúdos usando inteligência artificial</p>
            </div>

            <!-- Search Interface -->
            <div class="dark:bg-card rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 mb-8">
                <div class="max-w-4xl mx-auto">
                    <!-- Search Input -->
                    <div class="mb-6">
                        <label for="search-query" class="block text-sm font-medium text-slate-900 dark:text-slate-100 mb-2">
                            O que você está procurando?
                        </label>
                        <div class="flex gap-3">
                            <input
                                v-model="query"
                                @keyup.enter="search"
                                id="search-query"
                                type="text"
                                placeholder="ex: massa cremosa vegana, prato italiano tradicional, ingredientes para sobremesa..."
                                class="flex-1 border border-gray-300 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            />
                            <button 
                                @click="search" 
                                :disabled="loading || !query.trim()"
                                class="bg-orange-600 hover:bg-orange-700 disabled:bg-orange-300 dark:disabled:bg-orange-800 text-white font-medium px-8 py-3 rounded-lg text-base transition-colors disabled:cursor-not-allowed shadow-sm hover:shadow-md"
                            >
                                <span v-if="loading" class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Buscando...
                                </span>
                                <span v-else>Buscar</span>
                            </button>
                        </div>
                    </div>

                    <!-- AI Assistant Toggle -->
                    <div class="text-center mb-6">
                        <label class="flex items-center justify-center gap-2 cursor-pointer">
                            <input
                                v-model="showAIAssistant"
                                type="checkbox"
                                class="rounded border-orange-300 text-orange-600 focus:ring-orange-500 hidden"
                            />
                            <!-- <span class="text-sm font-medium text-gray-700">🤖 Ativar Assistente IA</span> -->
                        </label>
                    </div>
                    
                    <!-- Example Queries -->
                    <div class="mt-6 text-center">
                        <h4 class="text-sm font-medium text-slate-900 dark:text-slate-100 mb-3">💡 Exemplos de busca:</h4>
                        <div class="flex flex-wrap justify-center gap-2">
                            <button 
                                @click="query = 'massa cremosa vegana'; search()"
                                class="px-4 py-2 bg-orange-50 hover:bg-orange-100 dark:bg-orange-900/30 dark:hover:bg-orange-900/50 text-orange-700 dark:text-orange-300 text-sm rounded-lg border border-orange-200 dark:border-orange-800 transition-all duration-200 hover:shadow-sm"
                            >
                                massa cremosa vegana
                            </button>
                            <button 
                                @click="query = 'prato italiano tradicional'; search()"
                                class="px-4 py-2 bg-orange-50 hover:bg-orange-100 dark:bg-orange-900/30 dark:hover:bg-orange-900/50 text-orange-700 dark:text-orange-300 text-sm rounded-lg border border-orange-200 dark:border-orange-800 transition-all duration-200 hover:shadow-sm"
                            >
                                prato italiano tradicional
                            </button>
                            <button 
                                @click="query = 'receita de empanada de carne'; search()"
                                class="px-4 py-2 bg-orange-50 hover:bg-orange-100 dark:bg-orange-900/30 dark:hover:bg-orange-900/50 text-orange-700 dark:text-orange-300 text-sm rounded-lg border border-orange-200 dark:border-orange-800 transition-all duration-200 hover:shadow-sm"
                            >
                                receita de empanada de carne
                            </button>
                            <button 
                                @click="query = 'receita fácil para iniciantes'; search()"
                                class="px-4 py-2 bg-orange-50 hover:bg-orange-100 dark:bg-orange-900/30 dark:hover:bg-orange-900/50 text-orange-700 dark:text-orange-300 text-sm rounded-lg border border-orange-200 dark:border-orange-800 transition-all duration-200 hover:shadow-sm"
                            >
                                receita fácil para iniciantes
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-12">
                <div class="inline-flex items-center gap-3 text-gray-600">
                    <svg class="animate-spin h-8 w-8 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-lg">Buscando resultados...</span>
                </div>
            </div>

            <!-- AI Assistant Response -->
            <div v-if="assistantResponse && showAIAssistant" class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800 p-6 mb-8">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                            <span class="text-white text-lg">🤖</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold text-orange-900 dark:text-orange-100">Assistente IA</h3>
                            <button
                                v-if="containsMarkdown(assistantResponse)"
                                @click="showRawMarkdown = !showRawMarkdown"
                                class="text-xs px-2 py-1 bg-orange-100 dark:bg-orange-900 hover:bg-orange-200 dark:hover:bg-orange-800 text-orange-700 dark:text-orange-300 rounded transition-colors"
                                :title="showRawMarkdown ? 'Ver renderizado' : 'Ver código markdown'"
                            >
                                {{ showRawMarkdown ? '👁️ Renderizado' : '📝 Código' }}
                            </button>
                        </div>
                        <div v-if="showRawMarkdown" class="bg-orange-900 dark:bg-orange-800 text-orange-100 dark:text-orange-200 p-4 rounded font-mono text-sm overflow-x-auto">
                            <pre>{{ assistantResponse }}</pre>
                        </div>
                        <div v-else class="prose prose-orange max-w-none prose-headings:text-orange-900 dark:prose-headings:text-orange-100 prose-p:text-orange-800 dark:prose-p:text-orange-200 prose-strong:text-orange-900 dark:prose-strong:text-orange-100 prose-code:text-orange-900 dark:prose-code:text-orange-100 prose-code:bg-orange-100 dark:prose-code:bg-orange-900 prose-code:px-1 prose-code:py-0.5 prose-code:rounded">
                            <div v-if="markdownError" class="text-orange-800 dark:text-orange-200 whitespace-pre-wrap">{{ assistantResponse }}</div>
                            <div v-else v-html="renderedMarkdown" class="text-orange-800 dark:text-orange-200"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Results -->
            <div v-if="hasSearched && !loading" class="space-y-8">
                <!-- Recipes Results -->
                <!-- <div v-if="results.recipes && results.recipes.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        📖 Receitas Encontradas
                        <span class="text-sm font-normal text-gray-500">({{ results.recipes.length }})</span>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div 
                            v-for="recipe in results.recipes" 
                            :key="recipe.id"
                            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                        >
                            <h3 class="font-medium text-gray-900 mb-2">{{ recipe.recipe_name || 'Sem título' }}</h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-3">
                                {{ recipe.recipe_description || 'Sem descrição' }}
                            </p>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span v-if="recipe.cuisine" class="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded-full">
                                    {{ recipe.cuisine }}
                                </span>
                                <span v-if="recipe.recipe_type" class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                    {{ recipe.recipe_type }}
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <button 
                                    @click="editRecipe(recipe)" 
                                    class="text-sm text-orange-600 hover:text-orange-800 font-medium"
                                >
                                    Editar
                                </button>
                                <button 
                                    @click="viewRecipe(recipe)" 
                                    class="text-sm text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 font-medium"
                                >
                                    Ver Detalhes
                                </button>
                            </div>
                        </div>
                    </div>
                </div> -->

                <!-- Products Results -->
                <!-- <div v-if="results.products && results.products.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        🛍️ Produtos Encontrados
                        <span class="text-sm font-normal text-gray-500">({{ results.products.length }})</span>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div 
                            v-for="product in results.products" 
                            :key="product.id"
                            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                        >
                            <h3 class="font-medium text-gray-900 mb-2">{{ product.descricao || 'Sem descrição' }}</h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                {{ product.detalhes || 'Sem detalhes' }}
                            </p>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span v-if="product.group_product" class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full">
                                    {{ product.group_product.nome }}
                                </span>
                                <span v-if="product.status" class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                    Ativo
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <button 
                                    @click="editProduct(product)" 
                                    class="text-sm text-orange-600 hover:text-orange-800 font-medium"
                                >
                                    Editar
                                </button>
                                <button 
                                    @click="viewProduct(product)" 
                                    class="text-sm text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 font-medium"
                                >
                                    Ver Detalhes
                                </button>
                            </div>
                        </div>
                    </div>
                </div> -->

                <!-- Content Results -->
                <!-- <div v-if="results.contents && results.contents.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        📰 Conteúdos Encontrados
                        <span class="text-sm font-normal text-gray-500">({{ results.contents.length }})</span>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div 
                            v-for="content in results.contents" 
                            :key="content.id"
                            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                        >
                            <h3 class="font-medium text-gray-900 mb-2">{{ content.titulo || 'Sem título' }}</h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-3">
                                {{ content.conteudo || 'Sem conteúdo' }}
                            </p>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span v-if="content.tipo" class="px-2 py-1 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 text-xs rounded-full">
                                    {{ content.tipo }}
                                </span>
                                <span v-if="content.status" class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                    Ativo
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <button 
                                    @click="editContent(content)" 
                                    class="text-sm text-orange-600 hover:text-orange-800 font-medium"
                                >
                                    Editar
                                </button>
                                <button 
                                    @click="viewContent(content)" 
                                    class="text-sm text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 font-medium"
                                >
                                    Ver Detalhes
                                </button>
                            </div>
                        </div>
                    </div>
                </div> -->

                <!-- No Results -->
                <!-- <div v-if="hasSearched && !loading && (!results.recipes || results.recipes.length === 0) && (!results.products || results.products.length === 0) && (!results.contents || results.contents.length === 0)" class="text-center py-12">
                    <div class="text-gray-500">
                        <div class="text-6xl mb-4">🔍</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum resultado encontrado</h3>
                        <p class="text-gray-600">Tente usar termos diferentes ou mais específicos para sua busca.</p>
                    </div>
                </div> -->
            </div>

            <!-- Feature Highlights -->
            <div v-if="!hasSearched" class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200 dark:border-green-800 p-8 mb-8">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-green-900 dark:text-green-100 mb-2">🚀 Recursos da Busca Semântica</h3>
                    <p class="text-green-700 dark:text-green-300">Descubra o poder da inteligência artificial para encontrar o que você precisa</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="text-center">
                        <div class="text-5xl mb-3">🧠</div>
                        <h4 class="font-bold text-green-900 dark:text-green-100 mb-2">IA Inteligente</h4>
                        <p class="text-green-700 dark:text-green-300 text-sm">Entende o contexto e significado das suas consultas</p>
                    </div>
                    <div class="text-center">
                        <div class="text-5xl mb-3">🔍</div>
                        <h4 class="font-bold text-green-900 dark:text-green-100 mb-2">Busca Precisa</h4>
                        <p class="text-green-700 dark:text-green-300 text-sm">Encontra resultados relevantes mesmo com termos similares</p>
                    </div>
                    <div class="text-center">
                        <div class="text-5xl mb-3">⚡</div>
                        <h4 class="font-bold text-green-900 dark:text-green-100 mb-2">Rápido e Eficiente</h4>
                        <p class="text-green-700 dark:text-green-300 text-sm">Resultados instantâneos com tecnologia de ponta</p>
                    </div>
                </div>
            </div>

            <!-- Search Tips -->
            <div v-if="!hasSearched" class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-8">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-orange-900 dark:text-orange-100 mb-2">💡 Dicas para uma Busca Eficiente</h3>
                    <p class="text-orange-700 dark:text-orange-300">Aprenda a usar nossa busca semântica para obter os melhores resultados</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="dark:bg-card rounded-lg p-6 border border-orange-200 dark:border-orange-800 shadow-sm">
                        <div class="text-4xl mb-3">📖</div>
                        <h4 class="font-bold text-orange-900 dark:text-orange-100 mb-3 text-lg">Para Receitas</h4>
                        <ul class="text-sm text-orange-800 dark:text-orange-200 space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600 dark:text-orange-400 mt-1">•</span>
                                <span>Descreva o prato desejado</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600 dark:text-orange-400 mt-1">•</span>
                                <span>Mencione ingredientes principais</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600 dark:text-orange-400 mt-1">•</span>
                                <span>Especifique o tipo de culinária</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600 dark:text-orange-400 mt-1">•</span>
                                <span>Indique o nível de dificuldade</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="dark:bg-card rounded-lg p-6 border border-orange-200 dark:border-orange-800 shadow-sm">
                        <div class="text-4xl mb-3">🛍️</div>
                        <h4 class="font-bold text-orange-900 dark:text-orange-100 mb-3 text-lg">Para Produtos</h4>
                        <ul class="text-sm text-orange-800 dark:text-orange-200 space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600 dark:text-orange-400 mt-1">•</span>
                                <span>Use nomes específicos</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600 dark:text-orange-400 mt-1">•</span>
                                <span>Mencione características</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600 dark:text-orange-400 mt-1">•</span>
                                <span>Descreva o uso pretendido</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600 dark:text-orange-400 mt-1">•</span>
                                <span>Inclua categorias</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="dark:bg-card rounded-lg p-6 border border-orange-200 dark:border-orange-800 shadow-sm">
                        <div class="text-4xl mb-3">📰</div>
                        <h4 class="font-bold text-orange-900 dark:text-orange-100 mb-3 text-lg">Para Conteúdos</h4>
                        <ul class="text-sm text-orange-800 dark:text-orange-200 space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600 dark:text-orange-400 mt-1">•</span>
                                <span>Descreva o tema</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600 dark:text-orange-400 mt-1">•</span>
                                <span>Mencione palavras-chave</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600 dark:text-orange-400 mt-1">•</span>
                                <span>Especifique o formato</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600 dark:text-orange-400 mt-1">•</span>
                                <span>Indique o público-alvo</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-8 text-center">
                    <div class="bg-blue-100 dark:bg-blue-900/30 rounded-lg p-4 inline-block">
                        <p class="text-orange-800 dark:text-orange-200 font-medium">
                            💡 <strong>Pro tip:</strong> Quanto mais específica for sua busca, melhores serão os resultados!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { router, Head } from '@inertiajs/vue3'
import { ref, nextTick, computed } from 'vue'
import axios from 'axios'
import { marked } from 'marked'
import AppLayout from '@/layouts/AppLayout.vue'

// Configure marked for security and better rendering
marked.setOptions({
    breaks: true,
    gfm: true,
    sanitize: false,
    headerIds: false,
    mangle: false,
    pedantic: false,
    smartLists: true,
    smartypants: true
})

const query = ref('')
const results = ref({})
const loading = ref(false)
const hasSearched = ref(false)
const showAIAssistant = ref(true)
const assistantResponse = ref(null)
const markdownError = ref(false)
const showRawMarkdown = ref(false)

// Function to detect if content contains markdown
function containsMarkdown(text) {
    if (!text) return false
    const markdownPatterns = [
        /^#+\s+/m,           // Headers
        /\*\*.*\*\*/,        // Bold
        /\*.*\*/,            // Italic
        /`.*`/,              // Inline code
        /^```[\s\S]*```$/m,  // Code blocks
        /^-\s+/m,            // Unordered lists
        /^\d+\.\s+/m,        // Ordered lists
        /^>\s+/m,            // Blockquotes
        /\[.*\]\(.*\)/,      // Links
        /!\[.*\]\(.*\)/,     // Images
        /^\|.*\|$/m,         // Tables
    ]
    return markdownPatterns.some(pattern => pattern.test(text))
}

// Computed property to render markdown
const renderedMarkdown = computed(() => {
    if (!assistantResponse.value) return ''
    
    // Check if content contains markdown
    if (!containsMarkdown(assistantResponse.value)) {
        markdownError.value = false
        return assistantResponse.value // Return as plain text if no markdown
    }
    
    try {
        markdownError.value = false
        return marked(assistantResponse.value)
    } catch (error) {
        console.error('Markdown rendering error:', error)
        markdownError.value = true
        return '' // Return empty string when error occurs
    }
})

const breadcrumbs = [
    {
        title: 'Busca Semântica',
        href: '/recipes/semantic-search',
    },
]

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

const search = async () => {
    if (!query.value.trim()) return
    
    loading.value = true
    hasSearched.value = true
    assistantResponse.value = null
    markdownError.value = false
    showRawMarkdown.value = false
    
    try {
        // Perform semantic search
        const searchResponse = await axios.get('/recipes/search', {
            params: { 
                query: query.value,
                type: 'all',
                limit: 2
            }
        })
        results.value = searchResponse.data

        // Get AI assistant response if enabled
        if (showAIAssistant.value) {
            try {
                const assistantRes = await axios.post('/recipes/assistant', {
                    text: query.value,
                    context: results.value,
                })
                assistantResponse.value = assistantRes.data.response
            } catch (assistantError) {
                console.error('Assistant error:', assistantError)
                showToast('Erro ao obter resposta do assistente IA', 'warning')
            }
        }

        showToast(`Busca concluída! Encontrados ${getTotalResults()} resultados`, 'success')

    } catch (error) {
        console.error('Search error:', error)
        results.value = {}
        showToast('Erro ao realizar a busca. Tente novamente.', 'error')
    } finally {
        loading.value = false
    }
}

function getTotalResults() {
    let total = 0
    if (results.value.recipes) total += results.value.recipes.length
    if (results.value.products) total += results.value.products.length
    if (results.value.contents) total += results.value.contents.length
    return total
}

// Navigation functions
function editRecipe(recipe) {
    router.visit('/recipes', { 
        data: { editRecipe: recipe.id },
        preserveState: false 
    })
}

function viewRecipe(recipe) {
    // Navigate to recipe view page (implement when available)
    showToast('Funcionalidade de visualização em desenvolvimento', 'info')
}

function editProduct(product) {
    router.visit('/products', { 
        data: { editProduct: product.id },
        preserveState: false 
    })
}

function viewProduct(product) {
    // Navigate to product view page (implement when available)
    showToast('Funcionalidade de visualização em desenvolvimento', 'info')
}

function editContent(content) {
    router.visit('/contents', { 
        data: { editContent: content.id },
        preserveState: false 
    })
}

function viewContent(content) {
    // Navigate to content view page (implement when available)
    showToast('Funcionalidade de visualização em desenvolvimento', 'info')
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Markdown content styling */
:deep(.prose) {
    line-height: 1.6;
}

:deep(.prose h1) {
    font-size: 1.5rem;
    font-weight: 700;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

:deep(.prose h2) {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 1.25rem;
    margin-bottom: 0.75rem;
}

:deep(.prose h3) {
    font-size: 1.125rem;
    font-weight: 600;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

:deep(.prose p) {
    margin-bottom: 1rem;
}

:deep(.prose ul) {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

:deep(.prose ol) {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

:deep(.prose li) {
    margin-bottom: 0.25rem;
}

:deep(.prose code) {
    background-color: rgba(59, 130, 246, 0.1);
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.875rem;
}

:deep(.prose pre) {
    background-color: rgba(59, 130, 246, 0.1);
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1rem 0;
}

:deep(.prose pre code) {
    background: none;
    padding: 0;
}

:deep(.prose blockquote) {
    border-left: 4px solid rgba(59, 130, 246, 0.3);
    padding-left: 1rem;
    margin: 1rem 0;
    font-style: italic;
    color: rgba(30, 64, 175, 0.8);
}

:deep(.prose strong) {
    font-weight: 600;
    color: rgba(30, 64, 175, 0.9);
}

:deep(.prose em) {
    font-style: italic;
}

:deep(.prose a) {
    color: rgba(59, 130, 246, 0.8);
    text-decoration: underline;
}

:deep(.prose a:hover) {
    color: rgba(59, 130, 246, 1);
}

:deep(.prose table) {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

:deep(.prose th) {
    background-color: rgba(59, 130, 246, 0.1);
    padding: 0.5rem;
    text-align: left;
    font-weight: 600;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

:deep(.prose td) {
    padding: 0.5rem;
    border: 1px solid rgba(59, 130, 246, 0.2);
}
</style>
