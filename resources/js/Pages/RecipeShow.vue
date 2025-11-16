<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import api from '../services/api';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const recipe = ref(null);
const loading = ref(true);
const error = ref(null);

const FALLBACK_PLACEHOLDER = 'https://placehold.co/300x250/e5e7eb/9ca3af?text=No+Image';

const onImageError = (event) => {
  event.target.src = FALLBACK_PLACEHOLDER;
  event.target.onerror = null;
};

const recipeImageUrl = (path) => {
  if (!path) return '';
  const repo = import.meta.env.VITE_IMG_REPO_URL || 'http://localhost:9020';
  return `${repo}/${path}`;
};

const fetchRecipe = async () => {
  try {
    loading.value = true;
    const response = await api.get(`/recipes/${route.params.id}`);
    recipe.value = response.data.data || response.data;
  } catch (err) {
    if (err.response?.status === 401) {
      authStore.logout();
    } else if (err.response?.status === 404) {
      error.value = 'Recipe not found.';
    } else {
      error.value = err.response?.data?.message || 'Failed to load recipe.';
    }
  } finally {
    loading.value = false;
  }
};

const goBack = () => {
  router.push({ name: 'recipes' });
};

const editRecipe = () => {
  router.push({ name: 'recipe.edit', params: { id: recipe.value.id } });
};

const showButtons = computed(() => {
  return route.meta.fromRecipes === true;
});

onMounted(() => {
  fetchRecipe();
});
</script>

<template>
  <div class="max-w-screen-xl mx-auto px-4 py-6 md:px-6 md:py-8">
    <div v-if="loading" class="flex flex-col items-center justify-center py-16">
      <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-emerald-600"></div>
      <p class="mt-3 text-gray-600">Loading recipe...</p>
    </div>

    <div v-else-if="error" class="max-w-4xl mx-auto bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-lg mb-6">
      <p class="font-medium">Error</p>
      <p class="text-sm mt-1">{{ error }}</p>
      <button @click="goBack" class="mt-4 text-sm underline hover:text-red-900">
        Back to All Recipes
      </button>
    </div>

    <div v-else-if="recipe" class="w-full bg-white shadow-lg rounded-xl overflow-hidden">
      <div v-if="showButtons"
        class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
        <button @click="goBack" class="text-gray-600 hover:text-gray-900 flex items-center text-sm font-medium">
          Back to All Recipes
        </button>
        <button @click="editRecipe"
          class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
          Edit Recipe
        </button>
      </div>

      <div class="flex flex-col md:flex-row">
        <div class="md:w-1/2">
          <div class="relative aspect-w-16 aspect-h-9 md:aspect-auto">
            <img
              :src="recipeImageUrl(recipe.picture)"
              alt="Recipe"
              class="w-full h-64 md:h-full object-cover"
              @error="onImageError"
            />
          </div>
        </div>

        <div class="md:w-1/2 p-6 md:p-8">
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">{{ recipe.name }}</h1>

          <div class="flex flex-wrap items-center text-sm text-gray-600 mb-6 gap-2">
            <span class="capitalize">{{ recipe.cuisine_type || 'Uncategorized' }}</span>
            <span class="hidden md:inline">•</span>
            <span class="block md:inline">
              <span>Created {{ new Date(recipe.created_at).toLocaleDateString() }}</span>
            </span>
          </div>

          <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-3">Ingredients</h2>
            <ul class="list-disc list-inside space-y-1 text-gray-700">
              <li v-for="(ingredient, index) in recipe.ingredients.split('\n')" :key="index">
                {{ ingredient.trim() || '—' }}
              </li>
            </ul>
          </div>

          <div>
            <h2 class="text-xl font-semibold text-gray-800 mb-3">Instructions</h2>
            <ol class="list-decimal list-inside space-y-3 text-gray-700">
              <li v-for="(step, index) in recipe.steps.split('\n')" :key="index">
                {{ step.trim() || '—' }}
              </li>
            </ol>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
button {
  transition: all 0.2s ease;
}
img {
  display: block;
  width: 100%;
  height: auto;
}
</style>