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
\                    class="ml-2 text-white hover:text-slate-200 focus:outline-none"
                >
                    x
                </button>
            </div>
        </div>
    </div>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div>
            <!-- Recipe Form -->
            <FormCard title="Cadastro de Receitas" icon="🍳" class="mt-8">
                <form @submit.prevent="submit">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column - Select/Smaller Fields -->
                    <div class="space-y-4">
                        <!-- Nome da Receita -->
                        <div>
                            <label for="recipe_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nome da Receita</label>
                            <input
                                v-model="form.recipe_name"
                                id="recipe_name"
                                type="text"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                required
                                placeholder="Digite o nome da receita"
                            />
                            <p v-if="form.errors.recipe_name" class="text-red-500 text-xs mt-1">{{ form.errors.recipe_name }}</p>
                        </div>

                        <!-- Culinária -->
                        <div>
                            <label for="cuisine" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Culinária(s)</label>
                            <!-- Selected cuisines chips -->
                            <div v-if="form.selected_cuisines.length" class="flex flex-wrap gap-2 mb-2">
                                <span
                                    v-for="(c, idx) in form.selected_cuisines" 
                                    :key="`${c.cuisine_id || c.name}-${idx}`"
                                    class="inline-flex items-center gap-2 bg-orange-50 dark:bg-orange-900 text-orange-800 dark:text-orange-200 border border-orange-200 dark:border-orange-700 px-2 py-1 rounded text-xs"
                                >
                                    {{ c.name }}
                                    <button type="button" class="text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300" @click="removeCuisine(idx)">x</button>
                                </span>
                            </div>
                            <div class="relative">
                                <input
                                    :value="cuisineSearchTerm"
                                    @input="updateCuisineSearchTerm($event.target.value)"
                                    @keydown.enter.prevent="addCuisineFromInput"
                                    @focus="showCuisineDropdown"
                                    @blur="hideCuisineDropdown"
                                    type="text"
                                    id="cuisine"
                                    placeholder="Digite e selecione ou pressione Enter..."
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                />
                                
                                <!-- Dropdown Results -->
                                <div 
                                    v-if="cuisineShowDropdown && cuisineSearchResults.length > 0"
                                    class="absolute z-10 w-full mt-1 bg-white dark:bg-card border border-gray-300 dark:border-gray-600 dark:border-gray-600 rounded-md shadow-lg max-h-48 overflow-y-auto"
                                >
                                    <div 
                                        v-for="result in cuisineSearchResults" 
                                        :key="result.id"
                                        @mousedown="addCuisine(result)"
                                        class="px-3 py-2 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 cursor-pointer text-sm"
                                    >
                                        {{ result.name }}
                                    </div>
                                </div>
                            </div>
                            <p v-if="form.errors.selected_cuisines" class="text-red-500 text-xs mt-1">{{ form.errors.selected_cuisines }}</p>
                        </div>

                        <!-- Tipo de Receita -->
                        <div>
                            <label for="recipe_type" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tipo de Receita</label>
                            <select
                                v-model="form.recipe_type"
                                id="recipe_type"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option selected disabled value="">Selecione</option>
                                <option value="doce">Doce</option>
                                <option value="salgada">Salgada</option>
                            </select>
                            <p v-if="form.errors.recipe_type" class="text-red-500 text-xs mt-1">{{ form.errors.recipe_type }}</p>
                        </div>

                        <!-- Ordem de Serviço -->
                        <div>
                            <label for="service_order" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Ordem de Serviço</label>
                            <select
                                v-model="form.service_order"
                                id="service_order"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option selected disabled value="">Selecione</option>
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
                            <label for="preparation_time" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tempo de Preparo</label>
                            <select
                                v-model="form.preparation_time"
                                id="preparation_time"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option selected disabled value="">Selecione</option>
                                <option value="15">15 minutos</option>
                                <option value="30">30 minutos</option>
                                <option value="45">45 minutos</option>
                                <option value="60">1 hora</option>
                                <option value="75">1h 15min</option>
                                <option value="90">1h 30min</option>
                                <option value="105">1h 45min</option>
                                <option value="120">2 horas</option>
                                <option value="135">2h 15min</option>
                                <option value="150">2h 30min</option>
                                <option value="165">2h 45min</option>
                                <option value="180">3 horas</option>
                                <option value="195">3h 15min</option>
                                <option value="210">3h 30min</option>
                                <option value="225">3h 45min</option>
                                <option value="240">4 horas</option>
                                <option value="255">4h 15min</option>
                                <option value="270">4h 30min</option>
                                <option value="285">4h 45min</option>
                                <option value="300">5 horas</option>
                            </select>
                            <p v-if="form.errors.preparation_time" class="text-red-500 text-xs mt-1">{{ form.errors.preparation_time }}</p>
                        </div>

                        <!-- Grau de Dificuldade -->
                        <div>
                            <label for="difficulty_level" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Grau de Dificuldade</label>
                            <select
                                v-model="form.difficulty_level"
                                id="difficulty_level"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option selected disabled value="">Selecione</option>
                                <option value="muito_facil">Muito fácil</option>
                                <option value="facil">Fácil</option>
                                <option value="elaborada">Elaborada</option>
                                <option value="muito_elaborada">Muito elaborada</option>
                            </select>
                            <p v-if="form.errors.difficulty_level" class="text-red-500 text-xs mt-1">{{ form.errors.difficulty_level }}</p>
                        </div>

                        <!-- Rendimento -->
                        <div>
                            <label for="yield" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Rendimento</label>
                            <select
                                v-model="form.yield"
                                id="yield"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option selected disabled value="">Selecione</option>
                                <option value="1-2">1 a 2 porções</option>
                                <option value="2-4">2 a 4 porções</option>
                                <option value="4-6">4 a 6 porções</option>
                                <option value="6-8">6 a 8 porções</option>
                                <option value="8-10">8 a 10 porções</option>
                                <option value="10-12">10 a 12 porções</option>
                                <option value="12-14">12 a 14 porções</option>
                                <option value="14-16">14 a 16 porções</option>
                                <option value="16-18">16 a 18 porções</option>
                                <option value="18-20">18 a 20 porções</option>
                            </select>
                            <p v-if="form.errors.yield" class="text-red-500 text-xs mt-1">{{ form.errors.yield }}</p>
                        </div>

                        <!-- Canal -->
                        <div>
                            <label for="channel" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Canal</label>
                            <select
                                v-model="form.channel"
                                id="channel"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option selected disabled value="">Selecione</option>
                                <option value="padaria">Padaria</option>
                                <option value="lanchonete">Lanchonete</option>
                                <option value="restaurante">Restaurante</option>
                                <option value="confeitaria">Confeitaria</option>
                            </select>
                            <p v-if="form.errors.channel" class="text-red-500 text-xs mt-1">{{ form.errors.channel }}</p>
                        </div>

                        <!-- Grupo de Uso -->
                        <div>
                            <label for="usage_groups" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Grupo de Uso</label>
                            <select
                                v-model="form.usage_groups"
                                id="usage_groups"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option selected disabled value="">Selecione</option>
                                <option value="bolos">Bolos</option>
                                <option value="tortas">Tortas</option>
                                <option value="paves">Pavês</option>
                                <option value="sorvetes">Sorvetes</option>
                                <option value="paes">Pães</option>
                                <option value="docinhos_de_festa">Docinhos de Festa</option>
                                <option value="bombons">Bombons</option>
                                <option value="paes_de_mel">Pães de Mel</option>
                                <option value="massas">Massas</option>
                                <option value="molhos">Molhos</option>
                                <option value="carnes">Carnes</option>
                                <option value="saladas">Saladas</option>
                                <option value="sopas">Sopas</option>
                                <option value="cozidos">Cozidos</option>
                                <option value="sem_acucar">Sem açúcar</option>
                                <option value="sem_gluten">Sem gluten</option>
                                <option value="sem_lactose">Sem lactose</option>
                                <option value="vegetarianas">Vegetarianas</option>
                                <option value="veganas">Veganas</option>
                                <option value="sem_alcool">Sem alcool</option>
                            </select>
                            <p v-if="form.errors.usage_groups" class="text-red-500 text-xs mt-1">{{ form.errors.usage_groups }}</p>
                        </div>

                        <!-- Técnica -->
                        <div>
                            <label for="preparation_techniques" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Técnica</label>
                            <select
                                v-model="form.preparation_techniques"
                                id="preparation_techniques"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option selected disabled value="">Selecione</option>
                                <option value="fritura_por_imersao">Fritura por imersão</option>
                                <option value="fritura_rasa">Fritura rasa</option>
                                <option value="grelhados">Grelhados</option>
                                <option value="refogados">Refogados</option>
                                <option value="ensopados_e_molhos">Ensopados e Molhos</option>
                                <option value="assados">Assados</option>
                                <option value="cozimento_por_absorcao">Cozimento por absorção</option>
                                <option value="cozimento_em_agua_quente">Cozimento em água quente</option>

                            </select>
                            <p v-if="form.errors.preparation_techniques" class="text-red-500 text-xs mt-1">{{ form.errors.preparation_techniques }}</p>
                        </div>

                        <!-- Ocasião de Consumo -->
                        <div>
                            <label for="consumption_occasion" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Ocasião de Consumo</label>
                            <select
                                v-model="form.consumption_occasion"
                                id="consumption_occasion"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option selected disabled value="">Selecione</option>
                                <option value="datas_comemorativas">Datas Comemorativas</option>
                                <option value="dia_a_dia">Dia a Dia</option>
                                <option value="para_dias_quentes">Para dias quentes</option>
                                <option value="para_dias_frios">Para dias frios</option>
                            </select>
                            <p v-if="form.errors.consumption_occasion" class="text-red-500 text-xs mt-1">{{ form.errors.consumption_occasion }}</p>
                        </div>
                    </div>

                    <!-- Right Column - Larger Text Fields -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Descrição da Receita -->
                        <div class="lg:col-span-2">
                            <label for="recipe_description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Descrição da Receita</label>
                            <textarea
                                v-model="form.recipe_description"
                                id="recipe_description"
                                 rows="10"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                                placeholder="Descreva a receita em detalhe"
                            ></textarea>
                            <p v-if="form.errors.recipe_description" class="text-red-500 text-xs mt-1">{{ form.errors.recipe_description }}</p>
                        </div>

                        <!-- Prompt da Receita -->
                        <div class="lg:col-span-2">
                            <label for="recipe_prompt" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Prompt da Receita</label>
                            <textarea
                                v-model="form.recipe_prompt"
                                id="recipe_prompt"
                                rows="11"
                                placeholder="Instruções/briefing sobre a receita para geração de conteúdo por IA..."
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                            ></textarea>
                            <p v-if="form.errors.recipe_prompt" class="text-red-500 text-xs mt-1">{{ form.errors.recipe_prompt }}</p>
                        </div>

                        <!-- Descrição dos Ingredientes -->
                        <div>
                            <label for="ingredients_description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Descrição dos Ingredientes</label>
                            <textarea
                                v-model="form.ingredients_description"
                                id="ingredients_description"
                                rows="11"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                                placeholder="Descreva os ingredientes em detalhe"
                            ></textarea>
                            <p v-if="form.errors.ingredients_description" class="text-red-500 text-xs mt-1">{{ form.errors.ingredients_description }}</p>
                        </div>

                        <!-- Modo de Preparo -->
                        <div>
                            <label for="preparation_method" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Modo de Preparo</label>
                            <textarea
                                v-model="form.preparation_method"
                                id="preparation_method"
                                rows="11"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-vertical"
                                placeholder="Descreva o modo de preparo em detalhe"
                            ></textarea>
                            <p v-if="form.errors.preparation_method" class="text-red-500 text-xs mt-1">{{ form.errors.preparation_method }}</p>
                        </div>
                    </div>
                </div>

                <!-- Connections Section -->
                <div class="mt-8 pt-6">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">🔗 Conexões e Associações</h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Product Associations -->
                        <ConnectionCard title="Produtos" icon="🛒" type="product" color="yellow">
                            <ConnectionItem
                                v-for="(product, index) in form.selected_products"
                                :key="index"
                                color="yellow"
                            >
                                <select
                                    v-model="product.product_id"
                                    class="flex-1 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                >
                                    <option value="">Selecione um produto...</option>
                                    <option v-for="availableProduct in props.products" :key="availableProduct.id" :value="availableProduct.id">
                                        {{ availableProduct.descricao || 'Sem descrição' }}
                                    </option>
                                </select>
                                
                                <template #actions>
                                    <div class="flex items-center gap-3">
                                        <select
                                            v-model="product.ingredient_type"
                                            class="w-32 border border-gray-300 dark:border-gray-600 rounded-md px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                        >
                                            <option value="main">Principal</option>
                                            <option value="supporting">Secundário</option>
                                        </select>
                                        <label class="flex items-center gap-2 text-sm">
                                            <input
                                                type="checkbox"
                                                v-model="product.top_dish"
                                                :true-value="true"
                                                :false-value="false"
                                                class="w-4 h-4 text-yellow-600 bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded focus:ring-yellow-500 focus:ring-2"
                                            />
                                            <span class="text-slate-700 dark:text-slate-300">Top Dish</span>
                                        </label>
                                    </div>
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
                                class="text-yellow-600 hover:text-yellow-800 text-sm font-medium px-3 py-2 border border-yellow-300 rounded-md hover:bg-yellow-50 transition-colors"
                            >
                                + Adicionar Produto
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
                                    class="flex-1 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Selecione um conteúdo...</option>
                                    <option v-for="availableContent in props.contents" :key="availableContent.id" :value="availableContent.id">
                                        {{ availableContent.nome_conteudo || 'Sem nome' }}
                                    </option>
                                </select>
                                
                                <template #actions>
                                    <label class="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            v-model="content.top_dish"
                                            :true-value="true"
                                            :false-value="false"
                                            class="w-4 h-4 text-orange-600 dark:text-orange-400 bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded focus:ring-orange-500 focus:ring-2"
                                        />
                                        <span class="text-slate-700 dark:text-slate-300">Top Dish</span>
                                    </label>
                                </template>
                                
                                <template #remove>
                                    <button 
                                        type="button"
                                        @click="removeContent(index)"
                                        class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium px-3 py-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900 transition-colors"
                                    >
                                        Remover
                                    </button>
                                </template>
                            </ConnectionItem>
                            
                            <button 
                                type="button"
                                @click="addContent"
                                class="text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 text-sm font-medium px-3 py-2 border border-orange-300 dark:border-orange-600 rounded-md hover:bg-orange-50 dark:hover:bg-orange-900 transition-colors"
                            >
                                + Adicionar Conteúdo
                            </button>
                        </ConnectionCard>
                    </div>

                    <!-- Ingredient Associations -->
                    <ConnectionCard title="Ingredientes" icon="🥕" type="ingredient" color="green" class="mt-8">
                        <ConnectionItem
                            v-for="(ingredient, index) in selectedIngredients"
                            :key="index"
                            color="green"
                        >
                            <!-- Ingredient Search Input -->
                            <div class="flex-1 relative">
                                <input
                                    :value="ingredient.search_term"
                                    @input="updateIngredientSearchTerm(index, $event.target.value)"
                                    @focus="showIngredientDropdown(index)"
                                    @blur="hideIngredientDropdown(index)"
                                    type="text"
                                    placeholder="Digite o nome do ingrediente..."
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                />
                                
                                <!-- Dropdown Results -->
                                <div 
                                    v-if="ingredient.show_dropdown && ingredient.search_results.length > 0"
                                    class="absolute z-10 w-full mt-1 bg-white dark:bg-card border border-gray-300 dark:border-gray-600 dark:border-gray-600 rounded-md shadow-lg max-h-48 overflow-y-auto"
                                >
                                    <div 
                                        v-for="result in ingredient.search_results" 
                                        :key="result.id"
                                        @mousedown="selectIngredient(index, result)"
                                        class="px-3 py-2 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 cursor-pointer text-sm"
                                    >
                                        {{ result.name }}
                                    </div>
                                </div>
                            </div>
                            
                            <template #actions>
                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        :checked="ingredient.primary_ingredient"
                                        @change="updateIngredientPrimary(index, $event.target.checked)"
                                        class="w-4 h-4 text-green-600 bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded focus:ring-green-500 focus:ring-2"
                                    />
                                    <span class="text-slate-700 dark:text-slate-300">Principal</span>
                                </label>
                            </template>
                            
                            <template #remove>
                                <button 
                                    type="button"
                                    @click="removeIngredient(index)"
                                    class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium px-3 py-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900 transition-colors"

                                >
                                    Remover
                                </button>
                            </template>
                        </ConnectionItem>
                        
                        <button 
                            type="button"
                            @click="addIngredient"
                            class="text-green-600 hover:text-green-800 text-sm font-medium px-3 py-2 border border-green-300 rounded-md hover:bg-green-50 transition-colors"
                        >
                            + Adicionar Ingrediente
                        </button>
                    </ConnectionCard>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 mt-8 pt-6">
                    <button
                        type="button"
                        @click="confirmResetForm"
                        class="px-6 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-secondary border border-gray-300 dark:border-gray-600 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-accent focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
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
            </FormCard>

            <!-- Saved Recipes Section -->
            <ListCard title="Receitas Salvas" icon="📋" empty-message="Nenhuma receita cadastrada" empty-icon="🍳" class="mt-8">
                <ListItem
                    v-for="recipe in props.recipes"
                    :key="recipe.id"
                    :title="recipe.recipe_name || 'Sem título'"
                    :description="recipe.recipe_description || 'Sem descrição'"
                >
                    <template #associations>
                        <!-- Products -->
                        <div v-if="recipe.products?.length" class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                            <span class="font-medium">🛒 Produtos:</span>
                            <span>{{ recipe.products.length }} vinculado(s)</span>
                        </div>
                        
                        <!-- Contents -->
                        <div v-if="recipe.contents?.length" class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                            <span class="font-medium">📄 Conteúdos:</span>
                            <span>{{ recipe.contents.length }} vinculado(s)</span>
                        </div>
                        
                        <!-- Ingredients -->
                        <div v-if="recipe.ingredients?.length" class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                            <span class="font-medium">🥕 Ingredientes:</span>
                            <span>{{ recipe.ingredients.length }} vinculado(s)</span>
                        </div>
                    </template>
                    
                    <template #actions>
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
                    </template>
                </ListItem>
            </ListCard>
        </div>
    </AppLayout>
</template>

<script setup>
import { router, useForm, Head } from '@inertiajs/vue3'
import {ref, nextTick, onMounted, reactive} from 'vue'
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
        title: 'Receitas',
        href: '/recipes',
    },
]

const props = defineProps({
    recipes: Array,
    products: Array,
    contents: Array,
    ingredients: Array,
    cuisines: Array
})

// Toast notification system
const toasts = ref([])
let toastIdCounter = 0

// Reactive ingredients state
const selectedIngredients = ref([])

// Reactive cuisine state (multi)
const cuisineSearchTerm = ref('')
const cuisineSearchResults = ref([])
const cuisineShowDropdown = ref(false)

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
    form.selected_contents.push({ content_id: '', top_dish: false })
}

function removeContent(index) {
    form.selected_contents.splice(index, 1)
}

// Product management functions
function addProduct() {
    form.selected_products.push({ 
        product_id: '', 
        ingredient_type: 'main',
        top_dish: false
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

// Cuisine management functions
function showCuisineDropdown() {
    cuisineShowDropdown.value = true
}

function hideCuisineDropdown() {
    setTimeout(() => {
        cuisineShowDropdown.value = false
    }, 200)
}

async function searchCuisines() {
    const query = cuisineSearchTerm.value.trim()
    
    if (query.length < 2) {
        cuisineSearchResults.value = []
        return
    }
    
    try {
        const response = await fetch(`/recipes/search-cuisines?query=${encodeURIComponent(query)}&limit=5`)
        const data = await response.json()
        cuisineSearchResults.value = data
    } catch (error) {
        console.error('Error searching cuisines:', error)
        // Fallback to local search
        const existingCuisines = props.cuisines ? props.cuisines.filter(cuisine => 
            cuisine.name.toLowerCase().includes(query.toLowerCase())
        ) : []
        cuisineSearchResults.value = existingCuisines.slice(0, 5)
    }
}

function addCuisine(selectedCuisine) {
    const exists = form.selected_cuisines.some(c => (c.cuisine_id && c.cuisine_id === selectedCuisine.id) || (c.name && c.name.toLowerCase() === selectedCuisine.name.toLowerCase()))
    if (!exists) {
        form.selected_cuisines.push({ cuisine_id: selectedCuisine.id, name: selectedCuisine.name })
    }
    cuisineSearchTerm.value = ''
    cuisineShowDropdown.value = false
    cuisineSearchResults.value = []
}

function addCuisineFromInput() {
    const name = cuisineSearchTerm.value.trim()
    if (!name) return
    const exists = form.selected_cuisines.some(c => c.name && c.name.toLowerCase() === name.toLowerCase())
    if (!exists) {
        form.selected_cuisines.push({ cuisine_id: null, name })
    }
    cuisineSearchTerm.value = ''
    // keep dropdown logic to reopen on next input
    cuisineShowDropdown.value = false
    cuisineSearchResults.value = []
}

function removeCuisine(index) {
    form.selected_cuisines.splice(index, 1)
}

function updateCuisineSearchTerm(value) {
    cuisineSearchTerm.value = value
    const query = value.trim()
    if (query.length >= 2) {
        cuisineShowDropdown.value = true
        searchCuisines()
    } else {
        cuisineSearchResults.value = []
        cuisineShowDropdown.value = false
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
        cuisineSearchTerm.value = ''
        cuisineSearchResults.value = []
        cuisineShowDropdown.value = false
        form.id = null
        showToast('Edição cancelada', 'info')
    } else {
        // If creating new, ask for confirmation
        if (confirm('Tem certeza que deseja limpar todos os dados do formulário?')) {
            form.reset()
            form.selected_products = []
            form.selected_contents = []
            selectedIngredients.value = []
            form.selected_cuisines = []
            cuisineSearchTerm.value = ''
            cuisineSearchResults.value = []
            cuisineShowDropdown.value = false
            showToast('Formulário limpo', 'info')
        }
    }
}

const form = useForm({
    id: null,
    recipe_code: null,
    recipe_name: '',
    // cuisines are selected via selected_cuisines
    recipe_type: '',
    service_order: '',
    preparation_time: '',
    difficulty_level: '',
    yield: '',
    channel: '',
    recipe_description: null,
    recipe_prompt: null,
    ingredients_description: null,
    preparation_method: null,
    usage_groups: '',
    preparation_techniques: '',
    consumption_occasion: '',
    // Product associations
    selected_products: [],
    // Content associations
    selected_contents: [],
    // Ingredient associations
    selected_ingredients: [],
    // Cuisine associations
    selected_cuisines: []
})

const resetForm = () => {
    form.reset()
    form.selected_products = []
    form.selected_contents = []
    selectedIngredients.value = []
    form.selected_cuisines = []
    cuisineSearchTerm.value = ''
    cuisineSearchResults.value = []
    cuisineShowDropdown.value = false
    form.id = null
    showToast('Formulário limpo para nova receita', 'info')
}

function editRecipe(recipe) {
    form.id = recipe.id
    form.recipe_code = recipe.recipe_code
    form.recipe_name = recipe.recipe_name || ''
    // Load cuisines relation into chips
    form.selected_cuisines = recipe.cuisines ? recipe.cuisines.map(c => ({ cuisine_id: c.id, name: c.name })) : []
    cuisineSearchTerm.value = ''
    form.recipe_type = recipe.recipe_type || ''
    form.service_order = recipe.service_order || ''
    form.preparation_time = recipe.preparation_time || ''
    form.difficulty_level = recipe.difficulty_level || ''
    form.yield = recipe.yield || ''
    form.channel = recipe.channel || ''
    form.recipe_description = recipe.recipe_description || ''
    form.recipe_prompt = recipe.recipe_prompt || ''
    form.ingredients_description = recipe.ingredients_description
    form.preparation_method = recipe.preparation_method
    form.usage_groups = recipe.usage_groups || ''
    form.preparation_techniques = recipe.preparation_techniques || ''
    form.consumption_occasion = recipe.consumption_occasion || ''
    
    // Load associated products
    form.selected_products = recipe.products ? recipe.products.map(product => ({
        product_id: product.id,
        ingredient_type: product.pivot.ingredient_type,
        top_dish: !!product.pivot.top_dish
    })) : []
    
    // Load associated contents
    form.selected_contents = recipe.contents ? recipe.contents.map(content => ({
        content_id: content.id,
        top_dish: !!content.pivot.top_dish
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
    // Add selected associations to form data before submission
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
            form.selected_cuisines = []
            form.reset()
            form.selected_products = []
            form.selected_contents = []
            selectedIngredients.value = []
            form.selected_cuisines = []
            cuisineSearchTerm.value = ''
            cuisineSearchResults.value = []
            cuisineShowDropdown.value = false
            
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