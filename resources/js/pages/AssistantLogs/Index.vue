<template>
    <Head title="Histórico de Interações" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div>
            <ListCard
                title="Registros do Assistente"
                subtitle="Pesquise e visualize avaliações, respostas e sugestões"
                icon="🧠"
                badge="BETA"
                class="mt-8"
            >
                <template #toolbar>
                    <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center">
                        <div class="w-full md:w-80">
                            <input
                                v-model="localSearch"
                                type="text"
                                placeholder="Buscar em consulta ou resposta..."
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500">Por página:</span>
                            <select
                                v-model.number="localPerPage"
                                @change="updatePerPage"
                                class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 cursor-pointer hover:shadow-sm transition"
                            >
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="localAssistantId"
                                class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option value="">Todos assistentes</option>
                                <option
                                    v-for="a in uniqueAssistants"
                                    :key="a.id"
                                    :value="a.id"
                                >
                                    {{ a.name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                class="px-3 py-2 text-xs rounded-md border transition-colors duration-150 cursor-pointer hover:shadow-sm hover:opacity-90"
                                :class="localRating === '' ? 'bg-orange-600 text-white border-orange-600' : 'bg-white dark:bg-secondary text-slate-700 dark:text-slate-300 border-gray-300 dark:border-gray-600'"
                                @click="localRating=''"
                            >
                                Todos
                            </button>
                            <button
                                type="button"
                                class="px-3 py-2 text-xs rounded-md border transition-colors duration-150 cursor-pointer hover:shadow-sm hover:opacity-90"
                                :class="localRating === 'up' ? 'bg-green-600 text-white border-green-600' : 'bg-white dark:bg-secondary text-slate-700 dark:text-slate-300 border-gray-300 dark:border-gray-600'"
                                @click="localRating='up'"
                            >
                                👍 Positiva
                            </button>
                            <button
                                type="button"
                                class="px-3 py-2 text-xs rounded-md border transition-colors duration-150 cursor-pointer hover:shadow-sm hover:opacity-90"
                                :class="localRating === 'down' ? 'bg-red-600 text-white border-red-600' : 'bg-white dark:bg-secondary text-slate-700 dark:text-slate-300 border-gray-300 dark:border-gray-600'"
                                @click="localRating='down'"
                            >
                                👎 Negativa
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-colors duration-150 cursor-pointer hover:shadow-sm"
                                @click="applyLocalFilters"
                            >
                                Aplicar
                            </button>
                            <button
                                type="button"
                                @click="clearFilters"
                                class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-secondary border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-accent transition-colors duration-150 cursor-pointer hover:shadow-sm"
                            >
                                Limpar
                            </button>
                        </div>

                    </div>
                </template>

                <div class="overflow-x-auto">
                    <div v-if="filteredLogs.length" class="min-w-[800px]">
                        <div class="grid grid-cols-12 px-3 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                            <div class="col-span-2">Data</div>
                            <div class="col-span-2">Avaliação</div>
                            <div class="col-span-7">Consulta</div>
                            <div class="col-span-1 text-right">Ações</div>
                        </div>
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div
                                v-for="log in filteredLogs"
                                :key="log.id"
                                class="grid grid-cols-12 items-center px-3 py-3 text-sm"
                            >
                                <div class="col-span-2 text-slate-700 dark:text-slate-300">
                                    {{ formatDate(log.created_at) }}
                                </div>
                                <div class="col-span-2">
                                    <span
                                        v-if="ratingIcon(log.rating)"
                                        :class="[
                                            'inline-flex items-center px-2 py-0.5 text-xs rounded border',
                                            ratingClass(log.rating)
                                        ]"
                                    >
                                        <span class="text-base mr-1">{{ ratingIcon(log.rating) }}</span>
                                        <span>{{ ratingLabel(log.rating) }}</span>
                                    </span>
                                    <span v-else class="text-slate-500">—</span>
                                </div>
                                <div class="col-span-7 text-slate-700 dark:text-slate-300 line-clamp-2">
                                    {{ log.query || 'Sem consulta registrada' }}
                                </div>
                                <div class="col-span-1 text-right">
                                    <button
                                        type="button"
                                        class="text-orange-600 hover:text-orange-800 text-xs font-medium px-2 py-1 rounded-md hover:bg-orange-50 dark:hover:bg-secondary transition-colors duration-150 cursor-pointer"
                                        @click="openDetails(log)"
                                    >
                                        Detalhes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="p-12 my-4 text-center border-2 border-dashed rounded-lg border-gray-200 dark:border-gray-700">
                        <div class="text-5xl mb-3">🗒️</div>
                        <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200">Nenhum log encontrado</h3>
                        <p v-if="isFiltered" class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            Tente ajustar ou limpar os filtros para ver outros resultados.
                        </p>
                        <p v-else class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            Ainda não há registros de interações para exibir.
                        </p>
                        <div v-if="isFiltered" class="mt-4">
                            <button
                                type="button"
                                @click="clearFilters"
                                class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-secondary border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-accent transition-colors duration-150 cursor-pointer hover:shadow-sm"
                            >
                                Limpar filtros
                            </button>
                        </div>
                    </div>
                </div>
            </ListCard>

            <div v-if="props.logs?.links?.length" class="mt-4 flex flex-wrap items-center gap-2">
                <button
                    v-for="(link, idx) in props.logs.links"
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

        <!-- Details Dialog -->
        <Dialog v-model:open="detailsOpen">
            <DialogOverlay />
            <DialogContent class="w-[95vw] md:w-[90vw] sm:max-w-5xl md:max-w-6xl lg:max-w-7xl max-h-[80vh] overflow-hidden !px-8 !pt-3 !pb-4">
                <DialogHeader>
                    <DialogTitle>Detalhes do Log</DialogTitle>
                    <DialogDescription v-if="selectedLog">
                        {{ formatDate(selectedLog.created_at) }} •
                        {{ currentTenant?.name || '—' }}
                    </DialogDescription>
                </DialogHeader>
                <div v-if="selectedLog" class="space-y-3 max-h-[62vh] overflow-y-auto pr-1">
                    <div class="flex items-center gap-2 sticky top-0 bg-background py-0.5">
                        <span class="text-sm font-medium">Avaliação:</span>
                        <span
                            v-if="ratingIcon(selectedLog.rating)"
                            :class="[
                                'inline-flex items-center px-2 py-0.5 text-xs rounded border',
                                ratingClass(selectedLog.rating)
                            ]"
                        >
                            <span class="text-base mr-1">{{ ratingIcon(selectedLog.rating) }}</span>
                            <span>{{ ratingLabel(selectedLog.rating) }}</span>
                        </span>
                        <span v-else class="text-slate-500 text-sm">—</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-7 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-card px-5 py-2 max-h-[56vh] overflow-auto">
                            <div class="font-medium mb-2">Resposta</div>
                            <div class="whitespace-pre-wrap text-sm">
                                {{ selectedLog.response || 'Sem resposta registrada' }}
                            </div>
                            <div class="mt-2">
                                <button
                                    v-if="selectedLog.response"
                                    type="button"
                                    class="text-slate-700 dark:text-slate-300 hover:underline text-xs px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-accent transition-colors duration-150 cursor-pointer"
                                    @click="copyText(selectedLog.response)"
                                >
                                    Copiar
                                </button>
                            </div>
                        </div>
                        <div class="md:col-span-5 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-card px-5 py-2 max-h-[56vh] overflow-auto">
                            <div class="font-medium mb-2">Resposta sugerida</div>
                            <div class="whitespace-pre-wrap text-sm">
                                {{ selectedLog.comment || '—' }}
                            </div>
                            <div class="mt-2">
                                <button
                                    v-if="selectedLog?.comment"
                                    type="button"
                                    class="text-slate-700 dark:text-slate-300 hover:underline text-xs px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-accent transition-colors duration-150 cursor-pointer"
                                    @click="copyText(selectedLog.comment)"
                                >
                                    Copiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <DialogFooter class="mt-4">
                    <DialogClose class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-secondary border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-accent">
                        Fechar
                    </DialogClose>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
    </template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { ListCard } from '@/components/ui/patterns'
import { Dialog, DialogOverlay, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose } from '@/components/ui/dialog'

const props = defineProps({
    logs: Object,
})

const breadcrumbs = [
    {
        title: 'Histórico de Interações',
        href: '/assistant-logs',
    },
]

const localSearch = ref('')
const localRating = ref('')
const localAssistantId = ref('')
const applied = ref({ search: '', rating: '', assistantId: '' })
const localPerPage = ref(Number((props.logs && props.logs.per_page) ? props.logs.per_page : 10))

const page = usePage()
const tenants = computed(() => page.props.auth?.tenant?.list ?? [])
const currentTenantId = computed(() => page.props.auth?.tenant?.current_id ?? null)
const currentTenant = computed(() => tenants.value.find((t) => t.id === currentTenantId.value))

function copyToClipboard(text) {
    try {
        navigator.clipboard?.writeText?.(String(text || ''))
    } catch (_) {
        // no-op
    }
}

function formatSubtitle(log) {
    const parts = []
    if (log?.tenant?.name) parts.push(`Tenant: ${log.tenant.name}`)
    if (log?.created_at) {
        const d = new Date(log.created_at)
        parts.push(d.toLocaleString())
    }
    return parts.join(' • ')
}

function formatDate(value) {
    const d = new Date(value)
    return d.toLocaleString()
}

function ratingIcon(value) {
    const v = normalizeRating(value)
    if (v === 'up') return '👍'
    if (v === 'down') return '👎'
    return ''
}

function ratingLabel(value) {
    const v = normalizeRating(value)
    if (v === 'up') return 'Positiva'
    if (v === 'down') return 'Negativa'
    return ''
}

function ratingClass(value) {
    const v = normalizeRating(value)
    if (v === 'up') return 'border-green-300 text-green-700 bg-green-50 dark:border-green-700 dark:text-green-300 dark:bg-slate-800'
    if (v === 'down') return 'border-red-300 text-red-700 bg-red-50 dark:border-red-700 dark:text-red-300 dark:bg-slate-800'
    return 'border-gray-300 text-slate-700 bg-slate-50 dark:border-gray-700 dark:text-slate-300 dark:bg-slate-800'
}

function normalizeRating(value) {
    if (value === null || value === undefined) return ''
    if (typeof value === 'boolean') return value ? 'up' : 'down'
    if (typeof value === 'number') return value > 0 ? 'up' : value < 0 ? 'down' : ''
    const s = String(value).toLowerCase().trim()
    if (['up', 'positive', 'like', 'thumbs_up', 'upvote', '1', '+1', 'true', 'positivo'].includes(s)) return 'up'
    if (['down', 'negative', 'dislike', 'thumbs_down', 'downvote', '-1', '0', 'false', 'negativo'].includes(s)) return 'down'
    return ''
}

function copyText(text) {
    try {
        navigator.clipboard?.writeText?.(String(text || ''))
    } catch (_) {
        // no-op
    }
}

const detailsOpen = ref(false)
const selectedLog = ref(null)
function openDetails(log) {
    selectedLog.value = log
    detailsOpen.value = true
}

const uniqueAssistants = computed(() => {
    const map = new Map()
    for (const l of (props.logs?.data || [])) {
        if (l?.assistant?.id && !map.has(l.assistant.id)) {
            map.set(l.assistant.id, { id: l.assistant.id, name: l.assistant.name })
        }
    }
    return Array.from(map.values())
})

const filteredLogs = computed(() => {
    const data = props.logs?.data || []
    const s = (applied.value.search || '').toLowerCase().trim()
    const r = applied.value.rating
    const a = applied.value.assistantId
    return data.filter(l => {
        // rating
        if (r) {
            const norm = normalizeRating(l.rating)
            if (norm !== r) return false
        }
        // assistant
        if (a) {
            if (!l?.assistant?.id || String(l.assistant.id) !== String(a)) return false
        }
        // search
        if (s) {
            const hay = [
                l.query || '',
                l.response || '',
                l.comment || '',
                l.assistant?.name || '',
                l.tenant?.name || ''
            ].join(' ').toLowerCase()
            if (!hay.includes(s)) return false
        }
        return true
    })
})

const isFiltered = computed(() => {
    return Boolean(
        (applied.value.search || '').trim() ||
        applied.value.rating ||
        applied.value.assistantId
    )
})

function applyLocalFilters() {
    applied.value = {
        search: localSearch.value,
        rating: localRating.value,
        assistantId: localAssistantId.value
    }
}

function clearFilters() {
    localSearch.value = ''
    localRating.value = ''
    localAssistantId.value = ''
    applyLocalFilters()
}

function updatePerPage() {
    router.get('/assistant-logs', { per_page: localPerPage.value }, { preserveState: true, preserveScroll: true, replace: true })
}
</script>

<style scoped>
.max-h-24 {
    max-height: 6rem;
}
.line-clamp-6 {
    display: -webkit-box;
    -webkit-line-clamp: 6;
    line-clamp: 6;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

