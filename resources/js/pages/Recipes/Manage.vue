<template>
    <div class="max-w-2xl mx-auto p-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-6 text-white-800">{{ header }}</h1>
        <!-- Semantic Search -->
        <div class="mb-10">
        <h2 class="text-xl font-bold mb-4">🔍 Semantic Search</h2>

        <div class="flex gap-2 mb-4">
            <input
            v-model="query"
            @keyup.enter="search"
            type="text"
            placeholder="e.g. creamy vegan pasta"
            class="flex-1 px-4 py-2 border rounded border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
            />
            <button @click="search" class="px-4 py-2 bg-blue-600 text-blue rounded hover:bg-blue-700">
                Search
            </button>
        </div>

        <div v-if="loading">Searching...</div>

        <div v-if="results.length" class="mt-10">
        <h2 class="text-xl font-bold mb-4">Search Results</h2>

        <ul class="space-y-4">
            <li
            v-for="recipe in results"
            :key="recipe.id"
            class="border rounded p-4 shadow"
            >
            <h3 class="text-lg font-semibold">{{ recipe.title || 'Untitled' }}</h3>
            <p class="text-sm text-gray-500 italic mt-1">
                Tags: 
                <span v-if="recipe.tags?.length">{{ recipe.tags.join(', ') }}</span>
                <span v-else class="text-gray-400">None</span>
            </p>

            <p class="text-sm text-white-600 line-clamp-2">
                {{ recipe.raw_text.slice(0, 150) }}...
            </p>


            </li>
        </ul>
        </div>

        <div v-else-if="!loading && hasSearched" class="text-gray-600 italic mt-10">
        No matching recipes found.
        </div>

        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Title Field -->
            <div>
                <label for="title" class="block text-sm font-medium text-white-700 mb-1">Title</label>
                <input
                v-model="form.title"
                id="title"
                type="text"
                name="title"
                placeholder="Your recipe title"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                />
                <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
            </div>
            
            <!-- Raw Text Field -->
            <div>
                <label for="raw_text" class="block text-sm font-medium text-white-700 mb-1">Recipe Text</label>
                <textarea
                v-model="form.raw_text"
                id="raw_text"
                name="raw_text"
                rows="8"
                placeholder="Paste full recipe text here..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                ></textarea>
                <p v-if="form.errors.raw_text" class="text-red-500 text-sm mt-1">{{ form.errors.raw_text }}</p>
            </div>

            <!-- Tags Input -->
            <div class="mt-4">
                <label for="tags" class="block text-sm font-medium text-white-700 mb-1">Tags</label>

                <!-- Display Chips -->
                <div class="flex flex-wrap gap-2 mb-2">
                    <span
                        v-for="(tag, index) in form.tags"
                        :key="index"
                        class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm flex items-center"
                    >
                        {{ tag }}
                        <button
                            type="button"
                            @click="removeTag(index)"
                            class="ml-2 text-white hover:text-red-300 focus:outline-none"
                        >
                            &times;
                        </button>
                    </span>
                </div>

                <!-- Tag Input -->
                <input
                    v-model="newTag"
                    @keydown.enter.prevent="addTag"
                    id="tags"
                    name="tags"
                    type="text"
                    placeholder="Type a tag and press Enter"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white-900 text-gray focus:outline-none focus:ring-2 focus:ring-gray-500"
                />
                <p v-if="form.errors.tags" class="text-red-500 text-sm mt-1">{{ form.errors.tags }}</p>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <!-- Reset Button -->
                <button
                    type="reset"
                    @click="form.reset()"
                    class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-2 rounded-lg"
                >
                    Reset
                </button>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg disabled:opacity-50"
                >
                    {{ form.processing ? 'Saving...' : 'Save Recipe' }}
                </button>
            </div>

        </form>
        <!-- Saved Recipes -->
        <div class="mt-10">
            <h2 class="text-xl font-bold mb-4">📋 Saved Recipes</h2>
            
            <ul class="space-y-4">
                <li class="border rounded p-4 shadow" v-for="recipe in props.recipes" :key="recipe.id">
                    <h3 class="text-lg font-semibold">{{ recipe.title || 'Untitled' }}</h3>
                    <p class="text-sm text-white-600 line-clamp-2">
                        {{ recipe.raw_text.slice(0, 150) }}...
                    </p>
                    
                    <div class="mt-2 flex gap-2">
                        <button @click="editRecipe  (recipe)" class="text-blue-600 hover:underline">Edit</button>
                        <button @click="deleteRecipe(recipe)" class="text-red-600 hover:underline">Delete</button>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>


<script setup>
import { router, useForm } from '@inertiajs/vue3'
import {ref} from 'vue'
import axios from 'axios'

const query = ref('')
const results = ref([])
const loading = ref(false)
const hasSearched = ref(false)
const header = ref('SmartChef AI')
const props = defineProps({
    recipes: Array
})
const form = useForm({
    id:null,
    title: null,
    raw_text:null,
    tags: [],
})
const search = async () => {
  if (!query.value.trim()) return

  loading.value = true
  hasSearched.value = true

  try {
    const response = await axios.get('/recipes/search', {
      params: { query: query.value }
    })
    results.value = response.data
  } catch (error) {
        console.error('Search error:', error)
        results.value = []
  } finally {
        loading.value = false
  }
}
const newTag = ref('')

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

function editRecipe(recipe) {
    form.id = recipe.id,
    form.title = recipe.title,
    form.raw_text = recipe.raw_text,
    form.tags = recipe.tags
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