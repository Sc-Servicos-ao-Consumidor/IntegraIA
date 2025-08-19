<template>
    <Head title="Gerenciamento de Produtos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto p-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-6 text-white-800">{{ header }}</h1>
        
        <!-- Product Group Management -->
        <div class="mb-10 bg-gray-50 p-6 rounded-lg">
            <h2 class="text-xl font-bold mb-4">📦 Grupos de Produtos</h2>
            
            <form @submit.prevent="submitGroup" class="mb-4">
                <div class="flex gap-2">
                    <input
                        v-model="groupForm.descricao"
                        type="text"
                        placeholder="Descrição do grupo (ex: Bebidas, Laticínios)"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                        required
                    />
                    <input
                        v-model="groupForm.codigo_padrao"
                        type="text"
                        placeholder="Código (opcional)"
                        class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Adicionar Grupo
                    </button>
                </div>
                <p v-if="groupForm.errors.descricao" class="text-red-500 text-sm mt-1">{{ groupForm.errors.descricao }}</p>
            </form>
            
            <div class="flex flex-wrap gap-2">
                <span
                    v-for="group in props.groupProducts"
                    :key="group.id"
                    class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm"
                >
                    {{ group.descricao }} ({{ group.codigo_padrao || 'Sem código' }})
                </span>
            </div>
        </div>
        
        <!-- Product Form -->
        <form @submit.prevent="submit" class="space-y-6 bg-white p-6 rounded-lg border">
            <h2 class="text-xl font-bold mb-4">{{ form.id ? '✏️ Editar Produto' : '➕ Adicionar Novo Produto' }}</h2>
            
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição *</label>
                    <input
                        v-model="form.descricao"
                        id="descricao"
                        type="text"
                        placeholder="Descrição do produto"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        required
                    />
                    <p v-if="form.errors.descricao" class="text-red-500 text-sm mt-1">{{ form.errors.descricao }}</p>
                </div>
                
                <div>
                    <label for="group_product_id" class="block text-sm font-medium text-gray-700 mb-1">Grupo do Produto</label>
                    <select
                        v-model="form.group_product_id"
                        id="group_product_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    >
                        <option value="">Selecione um grupo (opcional)</option>
                        <option v-for="group in props.groupProducts" :key="group.id" :value="group.id">
                            {{ group.descricao }}
                        </option>
                    </select>
                </div>
            </div>
            
            <div>
                <label for="descricao_breve" class="block text-sm font-medium text-gray-700 mb-1">Descrição Resumida</label>
                <input
                    v-model="form.descricao_breve"
                    id="descricao_breve"
                    type="text"
                    placeholder="Descrição curta"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                />
            </div>
            
            <div>
                <label for="informacao_adicional" class="block text-sm font-medium text-gray-700 mb-1">Informações Adicionais</label>
                <textarea
                    v-model="form.informacao_adicional"
                    id="informacao_adicional"
                    rows="3"
                    placeholder="Detalhes adicionais do produto..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                ></textarea>
            </div>
            
            <!-- Product Codes -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="codigo_padrao" class="block text-sm font-medium text-gray-700 mb-1">Código do Produto</label>
                    <input
                        v-model="form.codigo_padrao"
                        id="codigo_padrao"
                        type="text"
                        placeholder="Código do produto"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                </div>
                
                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                    <input
                        v-model="form.sku"
                        id="sku"
                        type="text"
                        placeholder="Unidade de Manutenção de Estoque"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                </div>
                
                <div>
                    <label for="ean" class="block text-sm font-medium text-gray-700 mb-1">EAN</label>
                    <input
                        v-model="form.ean"
                        id="ean"
                        type="text"
                        placeholder="Código de Barras Europeu"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                </div>
            </div>
            
            <!-- Package Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="quantidade_caixa" class="block text-sm font-medium text-gray-700 mb-1">Quantidade por Caixa</label>
                    <input
                        v-model="form.quantidade_caixa"
                        id="quantidade_caixa"
                        type="text"
                        placeholder="ex: 12 unidades"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                </div>
                
                <div>
                    <label for="embalagem_tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Embalagem</label>
                    <select
                        v-model="form.embalagem_tipo"
                        id="embalagem_tipo"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    >
                        <option value="">Selecione o tipo</option>
                        <option value="kg">Quilograma (kg)</option>
                        <option value="c">Contagem (c)</option>
                        <option value="cx">Caixa (cx)</option>
                        <option value="l">Litro (l)</option>
                        <option value="ml">Mililitro (ml)</option>
                    </select>
                </div>
                
                <div>
                    <label for="peso_liquido" class="block text-sm font-medium text-gray-700 mb-1">Peso Líquido</label>
                    <input
                        v-model="form.peso_liquido"
                        id="peso_liquido"
                        type="number"
                        step="0.01"
                        placeholder="0.00"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                </div>
                
                <div>
                    <label for="peso_bruto" class="block text-sm font-medium text-gray-700 mb-1">Peso Bruto</label>
                    <input
                        v-model="form.peso_bruto"
                        id="peso_bruto"
                        type="number"
                        step="0.01"
                        placeholder="0.00"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                </div>
            </div>
            
            <!-- Pricing and URLs -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="valor" class="block text-sm font-medium text-gray-700 mb-1">Preço</label>
                    <input
                        v-model="form.valor"
                        id="valor"
                        type="number"
                        step="0.01"
                        placeholder="0.00"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                </div>
                
                <div>
                    <label for="validade" class="block text-sm font-medium text-gray-700 mb-1">Validade</label>
                    <input
                        v-model="form.validade"
                        id="validade"
                        type="text"
                        placeholder="ex: 12 meses"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                </div>
            </div>
            
            <div>
                <label for="url_imagem_principal" class="block text-sm font-medium text-gray-700 mb-1">URL da Imagem Principal</label>
                <input
                    v-model="form.url_imagem_principal"
                    id="url_imagem_principal"
                    type="url"
                    placeholder="https://example.com/image.jpg"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                />
            </div>
            
            <!-- Status Checkboxes -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center">
                    <input
                        v-model="form.catalogo"
                        id="catalogo"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label for="catalogo" class="ml-2 text-sm text-gray-700">Mostrar no Catálogo</label>
                </div>
                
                <div class="flex items-center">
                    <input
                        v-model="form.lancamento"
                        id="lancamento"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label for="lancamento" class="ml-2 text-sm text-gray-700">Lançamento</label>
                </div>
                
                <div class="flex items-center">
                    <input
                        v-model="form.status"
                        id="status"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label for="status" class="ml-2 text-sm text-gray-700">Ativo</label>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
                <button
                    type="button"
                    @click="resetForm"
                    class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-2 rounded-lg"
                >
                    {{ form.id ? 'Cancelar' : 'Limpar' }}
                </button>
                
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg disabled:opacity-50"
                >
                    {{ form.processing ? 'Salvando...' : (form.id ? 'Atualizar Produto' : 'Salvar Produto') }}
                </button>
            </div>
        </form>
        
        <!-- Saved Products -->
        <div class="mt-10">
            <h2 class="text-xl font-bold mb-4">📋 Produtos Salvos</h2>
            
            <div v-if="props.products.length === 0" class="text-gray-600 italic">
                Nenhum produto encontrado. Crie seu primeiro produto acima!
            </div>
            
            <div v-else class="grid gap-4">
                <div
                    v-for="product in props.products"
                    :key="product.id"
                    class="border rounded-lg p-4 shadow hover:shadow-md transition-shadow"
                >
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold">{{ product.descricao }}</h3>
                                <span v-if="product.group_product" class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                    {{ product.group_product.descricao }}
                                </span>
                                <span v-if="product.lancamento" class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                    NOVO
                                </span>
                                <span v-if="!product.status" class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">
                                    INATIVO
                                </span>
                            </div>
                            
                            <p v-if="product.descricao_breve" class="text-sm text-gray-600 mb-2">
                                {{ product.descricao_breve }}
                            </p>
                            
                            <div class="text-sm text-gray-500 space-y-1">
                                <div v-if="product.codigo_padrao || product.sku">
                                    <span v-if="product.codigo_padrao">Código: {{ product.codigo_padrao }}</span>
                                    <span v-if="product.codigo_padrao && product.sku"> • </span>
                                    <span v-if="product.sku">SKU: {{ product.sku }}</span>
                                </div>
                                <div v-if="product.valor">
                                    Preço: R$ {{ parseFloat(product.valor).toFixed(2) }}
                                </div>
                                <div v-if="product.peso_liquido || product.embalagem_tipo">
                                    <span v-if="product.peso_liquido">{{ product.peso_liquido }}{{ product.embalagem_tipo || 'kg' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex gap-2 ml-4">
                            <button 
                                @click="editProduct(product)" 
                                class="text-blue-600 hover:underline text-sm"
                            >
                                Editar
                            </button>
                            <button 
                                @click="deleteProduct(product)" 
                                class="text-red-600 hover:underline text-sm"
                            >
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { router, useForm, Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

const header = ref('Gerenciamento de Produtos')

const breadcrumbs = [
    {
        title: 'Produtos',
        href: '/products',
    },
]

const props = defineProps({
    products: Array,
    groupProducts: Array
})

const form = useForm({
    id: null,
    descricao: '',
    descricao_breve: '',
    informacao_adicional: '',
    codigo_padrao: '',
    sku: '',
    group_product_id: '',
    url_imagem_principal: '',
    ean: '',
    qr_code: '',
    url_rede_social: '',
    quantidade_caixa: '',
    embalagem_tipo: '',
    embalagem_descricao: '',
    peso_liquido: null,
    peso_bruto: null,
    validade: '',
    valor: null,
    desconto: null,
    catalogo: true,
    lancamento: false,
    status: true,
})

const groupForm = useForm({
    descricao: '',
    codigo_padrao: '',
    observacao: '',
    status: true,
})

function submit() {
    form.post("/products", {
        onSuccess: () => {
            if (!form.id) {
                resetForm()
            }
        }
    })
}

function submitGroup() {
    groupForm.post("/products/groups", {
        onSuccess: () => {
            groupForm.reset()
        }
    })
}

function resetForm() {
    form.reset()
    form.clearErrors()
}

function editProduct(product) {
    form.id = product.id
    form.descricao = product.descricao
    form.descricao_breve = product.descricao_breve || ''
    form.informacao_adicional = product.informacao_adicional || ''
    form.codigo_padrao = product.codigo_padrao || ''
    form.sku = product.sku || ''
    form.group_product_id = product.group_product_id || ''
    form.url_imagem_principal = product.url_imagem_principal || ''
    form.ean = product.ean || ''
    form.qr_code = product.qr_code || ''
    form.url_rede_social = product.url_rede_social || ''
    form.quantidade_caixa = product.quantidade_caixa || ''
    form.embalagem_tipo = product.embalagem_tipo || ''
    form.embalagem_descricao = product.embalagem_descricao || ''
    form.peso_liquido = product.peso_liquido
    form.peso_bruto = product.peso_bruto
    form.validade = product.validade || ''
    form.valor = product.valor
    form.desconto = product.desconto
    form.catalogo = product.catalogo
    form.lancamento = product.lancamento
    form.status = product.status
    
    // Scroll to form
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function deleteProduct(product) {
    if (confirm(`Tem certeza que deseja excluir "${product.descricao}"?`)) {
        router.delete(`/products/${product.id}`)
    }
}
</script>
