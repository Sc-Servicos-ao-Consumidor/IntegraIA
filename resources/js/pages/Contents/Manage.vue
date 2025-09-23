<template>
    <Head title="Cadastros de Conteúdos" />

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
                    class="ml-2 text-white hover:text-slate-200 focus:outline-none"
                >
                    ×
                </button>
            </div>
        </div>
    </div>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div>
            <!-- Content Form -->
            <FormCard title="Cadastro de Conteúdos" icon="📄" class="mt-8">
                <form @submit.prevent="submit">
                <!-- Header Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Nome do Conteúdo -->
                    <div>
                        <label for="nome_conteudo" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nome do Conteúdo</label>
                        <input
                            v-model="form.nome_conteudo"
                            id="nome_conteudo"
                            type="text"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            placeholder="Digite o nome do conteúdo"
                        />
                        <p v-if="form.errors.nome_conteudo" class="text-red-500 text-xs mt-1">{{ form.errors.nome_conteudo }}</p>
                    </div>

                    <!-- Tipo de Conteúdo -->
                    <div>
                        <label for="tipo_conteudo" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tipo de Conteúdo</label>
                        <select
                            v-model="form.tipo_conteudo"
                            id="tipo_conteudo"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                            <option selected disabled value="">Selecione</option>
                            <option value="ebook">E-book</option>
                            <option value="ufs-academy">UFS Academy</option>
                            <option value="artigos">Artigos</option>
                            <option value="menu-futuro">Menu do Futuro</option>
                            <option value="dicas">Dicas</option>
                        </select>
                    </div>

                    <!-- Pilares -->
                    <div>
                        <label for="pilares" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pilares</label>
                        <select
                            v-model="form.pilares"
                            id="pilares"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                            <option selected disabled value="">Selecione</option>
                            <option value="treinamento">Treinamento</option>
                            <option value="inspiracao">Inspiração</option>
                            <option value="comprar">Comprar</option>
                            <option value="sac">SAC</option>
                        </select>
                    </div>

                    <!-- Cozinheiro -->
                    <div>
                        <label for="cozinheiro" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Cozinheiro</label>
                        <select
                            v-model="form.cozinheiro"
                            id="cozinheiro"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                            <option selected disabled value="">Selecione</option>
                            <option :value="true">Sim</option>
                            <option :value="false">Não</option>
                        </select>
                    </div>

                    <!-- Comprador -->
                    <div>
                        <label for="comprador" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Comprador</label>
                        <select
                            v-model="form.comprador"
                            id="comprador"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                            <option selected disabled value="">Selecione</option>
                            <option :value="true">Sim</option>
                            <option :value="false">Não</option>
                        </select>
                    </div>

                    <!-- Administrador -->
                    <div>
                        <label for="administrador" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Administrador</label>
                        <select
                            v-model="form.administrador"
                            id="administrador"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                            <option selected disabled value="">Selecione</option>
                            <option :value="true">Sim</option>
                            <option :value="false">Não</option>
                        </select>
                    </div>

                    <!-- Canal -->
                    <div>
                        <label for="canal" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Canal</label>
                        <select
                            v-model="form.canal"
                            id="canal"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                            <option selected disabled value="">Selecione</option>
                            <option value="padaria">Padaria</option>
                            <option value="lanchonete">Lanchonete</option>
                            <option value="restaurante">Restaurante</option>
                            <option value="confeitaria">Confeitaria</option>
                        </select>
                    </div>
                </div>

                <!-- Links do Conteúdo -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100 mb-4">Links do Conteúdo</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div v-for="(link, index) in form.links_conteudo" :key="index" class="flex gap-3">
                            <input
                                v-model="link.nome"
                                type="text"
                                placeholder="Descrição do Link"
                                class="flex-1 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            />
                            <input
                                v-model="link.url"
                                type="url"
                                placeholder="Link"
                                class="flex-1 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            />
                            <button 
                                type="button"
                                @click="removeLink(index)"
                                class="text-red-600 hover:text-red-800 px-2"
                            >
                                ×
                            </button>
                        </div>
                        <button 
                            type="button"
                            @click="addLink"
                            class="text-orange-600 hover:text-orange-800 text-sm font-medium"
                        >
                            + Adicionar Link
                        </button>
                    </div>
                </div>

                <!-- Descrição do Conteúdo -->
                <div class="mt-8">
                    <label for="descricao_conteudo" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Descrição do Conteúdo</label>
                    <textarea
                        v-model="form.descricao_conteudo"
                        id="descricao_conteudo"
                        rows="4"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                        placeholder="Descreva o conteúdo em detalhe"
                    ></textarea>
                </div>

                <!-- Prompt do Conteúdo -->
                <div class="mt-4">
                    <label for="content_prompt" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Prompt do Conteúdo</label>
                    <textarea
                        v-model="form.content_prompt"
                        id="content_prompt"
                        rows="4"
                        placeholder="Instruções/briefing sobre o conteúdo para geração de conteúdo por IA..."
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                    ></textarea>
                    <p v-if="form.errors.content_prompt" class="text-red-500 text-xs mt-1">{{ form.errors.content_prompt }}</p>
                </div>

                <!-- Connections Section -->
                <div class="mt-8 pt-8">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">🔗 Conexões e Associações</h3>
                    
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
                                    class="flex-1 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                >
                                    <option disabled selected value="">Selecione uma receita...</option>
                                    <option v-for="availableRecipe in props.recipes" :key="availableRecipe.id" :value="availableRecipe.id">
                                        {{ availableRecipe.recipe_name }}
                                    </option>
                                </select>
                                
                                <template #actions>
                                    <label class="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            v-model="recipe.top_dish"
                                            :true-value="true"
                                            :false-value="false"
                                            class="w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 dark:border-gray-600 rounded focus:ring-orange-500 focus:ring-2"
                                        />
                                        <span class="text-slate-700 dark:text-slate-300">Top Dish</span>
                                    </label>
                                </template>
                                
                                <template #remove>
                                    <button 
                                        type="button"
                                        @click="removeRecipe(index)"
                                        class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium px-3 py-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900 transition-colors"
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

                        <!-- Product Associations -->
                        <ConnectionCard title="Produtos" icon="🛒" type="product" color="blue">
                            <ConnectionItem
                                v-for="(product, index) in form.selected_products"
                                :key="index"
                                color="blue"
                            >
                                <select
                                    v-model="product.product_id"
                                    class="flex-1 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                >
                                    <option disabled selected value="">Selecione um produto...</option>
                                    <option v-for="availableProduct in props.products" :key="availableProduct.id" :value="availableProduct.id">
                                        {{ availableProduct.descricao }}
                                    </option>
                                </select>
                                
                                <template #actions>
                                    <label class="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            v-model="product.featured"
                                            :true-value="'sim'"
                                            :false-value="'nao'"
                                            class="w-4 h-4 text-orange-600 dark:text-orange-400 bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 dark:border-gray-600 rounded focus:ring-orange-500 focus:ring-2"
                                        />
                                        <span class="text-slate-700 dark:text-slate-300">Principal</span>
                                    </label>
                                </template>
                                
                                <template #remove>
                                    <button 
                                        type="button"
                                        @click="removeProduct(index)"
                                        class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium px-3 py-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900 transition-colors"
                                    >
                                        Remover
                                    </button>
                                </template>
                            </ConnectionItem>
                            
                            <button 
                                type="button"
                                @click="addProduct"
                                class="text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 text-sm font-medium px-3 py-2 border border-orange-300 dark:border-orange-600 rounded-md hover:bg-orange-50 dark:hover:bg-orange-900 transition-colors"
                            >
                                + Adicionar Produto
                            </button>
                        </ConnectionCard>
                    </div>
                </div>


                <!-- Action Buttons -->
                <div class="flex gap-3 mt-8 pt-6">
                    <button
                        type="button"
                        @click="confirmResetForm"
                        class="px-6 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 dark:text-gray-300 bg-white dark:bg-secondary border border-gray-300 dark:border-gray-600 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-accent focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
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
                        <span v-else>{{ form.id ? 'Atualizar Conteúdo' : 'Salvar Conteúdo' }}</span>
                    </button>
                </div>
                </form>
            </FormCard>

            <!-- Saved Contents Section -->
            <ListCard
                title="Conteúdos Salvos"
                icon="📋"
                empty-message="Nenhum conteúdo cadastrado"
                empty-icon="📄"
                class="mt-8"
                searchable
                :search="localSearch"
                :per-page="localPerPage"
                search-placeholder="Buscar por nome ou descrição do conteúdo..."
                @update:search="value => localSearch = value"
                @update:perPage="value => localPerPage = value"
                @submit="router.get('/contents', { search: localSearch || undefined, per_page: localPerPage || undefined }, { preserveState: true, preserveScroll: true, replace: true })"
                @clear="() => { localSearch=''; localPerPage=10; router.get('/contents', {}, { preserveState: true, preserveScroll: true, replace: true }) }"
            >
                <ListItem
                    v-for="content in (props.contents?.data || [])"
                    :key="content.id"
                    :title="content.nome_conteudo || 'Sem título'"
                    :description="content.descricao_conteudo || 'Sem descrição'"
                >
                    <template #actions>
                        <button 
                            @click="editContent(content)" 
                            class="text-sm text-orange-600 hover:text-orange-800 font-medium"
                        >
                            Editar
                        </button>
                        <button 
                            @click="deleteContent(content)" 
                            class="text-sm text-red-600 hover:text-red-800 font-medium"
                        >
                            Excluir
                        </button>
                    </template>
                </ListItem>
            </ListCard>

            <!-- Pagination -->
            <div v-if="props.contents?.links?.length" class="mt-4 flex flex-wrap items-center gap-2">
                <button
                    v-for="(link, idx) in props.contents.links"
                    :key="idx"
                    :disabled="!link.url"
                    @click.prevent="router.get(link.url, {}, { preserveState: true, preserveScroll: true, replace: true })"
                    class="px-3 py-1 text-sm rounded border"
                    :class="[
                        'border-gray-300 dark:border-gray-600',
                        link.active ? 'bg-orange-600 text-white border-orange-600' : 'bg-white dark:bg-secondary hover:bg-gray-50 dark:hover:bg-accent',
                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { router, useForm, Head } from '@inertiajs/vue3'
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
        title: 'Conteúdos',
        href: '/contents',
    },
]

const props = defineProps({
    contents: Object,
    recipes: Array,
    products: Array,
    filters: Object
})

// Toast notification system
// List search/pagination state
const localSearch = ref((props.filters && props.filters.search) ? props.filters.search : '')
const localPerPage = ref(Number((props.filters && props.filters.per_page) ? props.filters.per_page : 10))
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
        form.links_conteudo = [{ nome: '', url: '' }]
        form.imagens_nutricionais_cadastradas = []
        form.imagens_ingredientes_cadastradas = []
        form.imagens_preparo_cadastradas = []
        form.imagens_rendimentos_cadastradas = []
        form.selected_recipes = []
        form.selected_products = []
        form.id = null
        showToast('Edição cancelada', 'info')
    } else {
        // If creating new, ask for confirmation
        if (confirm('Tem certeza que deseja limpar todos os dados do formulário?')) {
            form.reset()
            form.links_conteudo = [{ nome: '', url: '' }]
            form.imagens_nutricionais_cadastradas = []
            form.imagens_ingredientes_cadastradas = []
            form.imagens_preparo_cadastradas = []
            form.imagens_rendimentos_cadastradas = []
            form.selected_recipes = []
            form.selected_products = []
            showToast('Formulário limpo', 'info')
        }
    }
}

const form = useForm({
    id: null,
    nome_conteudo: null,
    content_code: null,
    descricao_tabela_nutricional: null,
    descricao_lista_ingredientes: null,
    descricao_modos_preparo: null,
    descricao_rendimentos: null,
    imagem_tabela_nutricional: null,
    imagem_lista_ingredientes: null,
    imagem_modos_preparo: null,
    imagem_rendimentos: null,
    imagens_nutricionais_cadastradas: [],
    imagens_ingredientes_cadastradas: [],
    imagens_preparo_cadastradas: [],
    imagens_rendimentos_cadastradas: [],
    tipo_conteudo: '',
    pilares: '',
    canal: '',
    links_conteudo: [{ nome: '', url: '' }],
    cozinheiro: '',
    comprador: '',
    administrador: '',
    status: true,
    descricao_conteudo: null,
    content_prompt: null,
    selected_recipes: [],
    selected_products: []
})

// Link management functions
const addLink = () => {
    form.links_conteudo.push({ nome: '', url: '' })
}

const removeLink = (index) => {
    form.links_conteudo.splice(index, 1)
}

// Image management functions
const addImageNutricional = () => {
    form.imagens_nutricionais_cadastradas.push({ nome: '', url: '' })
}

const removeImageNutricional = (index) => {
    form.imagens_nutricionais_cadastradas.splice(index, 1)
}

const addImageIngredientes = () => {
    form.imagens_ingredientes_cadastradas.push({ nome: '', url: '' })
}

const removeImageIngredientes = (index) => {
    form.imagens_ingredientes_cadastradas.splice(index, 1)
}

const addImagePreparo = () => {
    form.imagens_preparo_cadastradas.push({ nome: '', url: '' })
}

const removeImagePreparo = (index) => {
    form.imagens_preparo_cadastradas.splice(index, 1)
}

const addImageRendimentos = () => {
    form.imagens_rendimentos_cadastradas.push({ nome: '', url: '' })
}

const removeImageRendimentos = (index) => {
    form.imagens_rendimentos_cadastradas.splice(index, 1)
}

// Recipe management functions
const addRecipe = () => {
    form.selected_recipes.push({ recipe_id: '', top_dish: false })
}

const removeRecipe = (index) => {
    form.selected_recipes.splice(index, 1)
}

// Product management functions
const addProduct = () => {
    form.selected_products.push({ product_id: '', featured: 'nao', notes: '' })
}

const removeProduct = (index) => {
    form.selected_products.splice(index, 1)
}

function editContent(content) {
    form.id = content.id
    form.nome_conteudo = content.nome_conteudo
    form.content_code = content.content_code
    form.descricao_tabela_nutricional = content.descricao_tabela_nutricional
    form.descricao_lista_ingredientes = content.descricao_lista_ingredientes
    form.descricao_modos_preparo = content.descricao_modos_preparo
    form.descricao_rendimentos = content.descricao_rendimentos
    form.imagem_tabela_nutricional = content.imagem_tabela_nutricional
    form.imagem_lista_ingredientes = content.imagem_lista_ingredientes
    form.imagem_modos_preparo = content.imagem_modos_preparo
    form.imagem_rendimentos = content.imagem_rendimentos
    form.imagens_nutricionais_cadastradas = content.imagens_nutricionais_cadastradas || []
    form.imagens_ingredientes_cadastradas = content.imagens_ingredientes_cadastradas || []
    form.imagens_preparo_cadastradas = content.imagens_preparo_cadastradas || []
    form.imagens_rendimentos_cadastradas = content.imagens_rendimentos_cadastradas || []
    form.tipo_conteudo = content.tipo_conteudo
    form.pilares = content.pilares
    form.canal = content.canal
    form.links_conteudo = content.links_conteudo || [{ nome: '', url: '' }]
    form.cozinheiro = content.cozinheiro
    form.comprador = content.comprador
    form.administrador = content.administrador
    form.status = content.status
    form.descricao_conteudo = content.descricao_conteudo
    form.content_prompt = content.content_prompt
    
    // Load associated recipes
    form.selected_recipes = content.recipes ? content.recipes.map(recipe => ({
        recipe_id: recipe.id,
        top_dish: !!recipe.pivot.top_dish
    })) : []
    
    // Load associated products
    form.selected_products = content.products ? content.products.map(product => ({
        product_id: product.id,
        featured: product.pivot.featured,
        notes: product.pivot.notes
    })) : []
}

function deleteContent(content) {
    if (confirm(`Tem certeza que deseja excluir "${content.nome_conteudo || 'este conteúdo'}"?`)) {
        router.delete(`contents/${content.id}`, {
            onSuccess: () => {
                const message = `Conteúdo "${content.nome_conteudo || 'sem título'}" excluído com sucesso!`
                showToast(message, 'success')
            },
            onError: (errors) => {
                console.error('Content deletion errors:', errors)
                const errorMessage = 'Erro ao excluir o conteúdo. Tente novamente.'
                showToast(errorMessage, 'error')
            }
        })
    }
}

const submit = () => {
    form.post("/contents", {
        onSuccess: () => {
            const isUpdate = form.id !== null
            const itemName = form.nome_conteudo || 'Conteúdo'
            showFormSuccessMessage(isUpdate, itemName)
            
            // Reset form after successful submission
            form.reset()
            form.links_conteudo = [{ nome: '', url: '' }]
            form.imagens_nutricionais_cadastradas = []
            form.imagens_ingredientes_cadastradas = []
            form.imagens_preparo_cadastradas = []
            form.imagens_rendimentos_cadastradas = []
            form.selected_recipes = []
            form.selected_products = []
            
            // Clear form ID to indicate new content creation
            form.id = null
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors)
            
            if (Object.keys(errors).length > 0) {
                showValidationErrors(errors)
            } else {
                showToast('Erro ao salvar o conteúdo. Verifique os dados e tente novamente.', 'error')
            }
        }
    })
}
</script>
