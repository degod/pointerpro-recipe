<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';
import ImageUpload from '../Components/ImageUpload.vue';
import Input from '../Components/Input.vue';
import Textarea from '../Components/Textarea.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const recipeId = route.params.id;

const form = ref({
  name: '',
  cuisine_type: '',
  ingredients: '',
  steps: '',
  picture: null,
  current_picture: '',
});

let message = ref('');
const loading = ref(false);
const error = ref(null);
const success = ref(false);
const loadingRecipe = ref(true);

const FALLBACK_PLACEHOLDER =
  'https://placehold.co/120x120/dddddd/999999?text=No+Img';

const recipePictureUrl = (path) => {
  if (!path) return '';
  const repo = import.meta.env.VITE_IMG_REPO_URL || 'http://localhost:9020';
  return `${repo}/${path}`;
};

const onImageError = (event) => {
  event.target.src = FALLBACK_PLACEHOLDER;
  event.target.onerror = null;
};

const loadRecipe = async () => {
  try {
    const res = await api.get(`/recipes/${recipeId}`);
    const recipe = res.data.data;

    form.value.name = recipe.name;
    form.value.cuisine_type = recipe.cuisine_type ?? '';
    form.value.ingredients = recipe.ingredients ?? '';
    form.value.steps = recipe.steps ?? '';
    form.value.current_picture = recipe.picture ?? null;
  } catch (err) {
    error.value = 'Failed to load recipe.';
    console.error(err);
  } finally {
    loadingRecipe.value = false;
  }
};

const submitRecipe = async () => {
  if (!form.value.name.trim()) {
    error.value = 'Recipe name is required.';
    return;
  }

  const formData = new FormData();
  formData.append('name', form.value.name);
  formData.append('cuisine_type', form.value.cuisine_type);
  formData.append('ingredients', form.value.ingredients);
  formData.append('steps', form.value.steps);
  if (form.value.picture) {
    formData.append('picture', form.value.picture);
  }
  formData.append('_method', 'PUT');

  try {
    loading.value = true;
    error.value = null;
    message = null;

    await api.post(`/recipes/${recipeId}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    success.value = true;

    setTimeout(() => {
      router.push({ name: 'recipes' });
    }, 1500);

  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update recipe.';
    message = err?.response?.data?.errors;
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  if (!authStore.isAuthenticated) {
    router.push({ name: 'login' });
  } else {
    loadRecipe();
  }
});
</script>

<template>
  <div class="max-w-screen-xl mx-auto px-4 py-6 md:px-6 md:py-8">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Edit Recipe</h1>
    </div>

    <div v-if="loadingRecipe" class="py-12 text-center">
      <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-emerald-600"></div>
      <p class="mt-3 text-gray-600">Loading recipe...</p>
    </div>

    <template v-else>
      <div v-if="success" class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
        Recipe updated successfully! Redirecting...
      </div>

      <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
        {{ error }}
      </div>

      <form @submit.prevent="submitRecipe" class="space-y-6">
        <Input
          v-model="form.name"
          label="Recipe Name"
          type="text"
          placeholder="e.g., Spaghetti Carbonara"
          :message="message?.name || ''"
        />
        <Input
          v-model="form.cuisine_type"
          label="Cuisine Type"
          type="text"
          placeholder="e.g., Italian, Thai, Mexican"
          :message="message?.cuisine_type || ''"
        />
        <div class="mb-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">Existing Image</label>

          <div v-if="form.current_picture" class="flex items-center space-x-4">
            <img
              :src="recipePictureUrl(form.current_picture)"
              class="h-20 w-20 rounded-lg object-cover border"
              alt="Current Recipe Image"
              @error="onImageError"
            />
            <p class="text-gray-600 text-sm">You can upload a new image to replace it.</p>
          </div>

          <div v-else class="flex items-center space-x-3">
            <img :src="FALLBACK_PLACEHOLDER" class="h-20 w-20 rounded-lg border" />
            <p class="text-gray-500">No image found.</p>
          </div>
        </div>
        <ImageUpload
          v-model="form.picture"
          label="Upload New Image (optional)"
        />
        <Textarea
          v-model="form.ingredients"
          label="Ingredients (one per line)"
          placeholder="2 cups flour&#10;1 tsp salt&#10;..."
          :message="message?.ingredients || ''"
        />
        <Textarea
          v-model="form.steps"
          label="Instructions (one per line)"
          placeholder="Mix dry ingredients...&#10;Add water...&#10;..."
          :message="message?.steps || ''"
        />

        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="router.push({ name: 'recipes' })"
            class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
          >
            Cancel
          </button>

          <button
            type="submit"
            :disabled="loading"
            class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition disabled:opacity-50"
          >
            <span v-if="loading">Updating...</span>
            <span v-else>Update Recipe</span>
          </button>
        </div>
      </form>
    </template>
  </div>
</template>

<style scoped>
input,
textarea,
button {
  transition: all 0.2s ease;
}
input:focus,
textarea:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}
</style>
