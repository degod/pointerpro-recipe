<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';
import ImageUpload from '../Components/ImageUpload.vue';
import Input from '../Components/Input.vue';
import Textarea from '../Components/Textarea.vue';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  name: '',
  cuisine_type: '',
  ingredients: '',
  steps: '',
  picture: null,
});

let message = ref('');
const loading = ref(false);
const error = ref(null);
const success = ref(false);

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

  try {
    loading.value = true;
    error.value = null;
    message = null;

    await api.post('/recipes', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    success.value = true;
    setTimeout(() => {
      router.push({ name: 'recipes' });
    }, 1500);
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create recipe.';
    message = error?.response?.data?.errors;
    console.error(error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  if (!authStore.isAuthenticated) {
    router.push({ name: 'login' });
  }
});
</script>

<template>
  <div class="max-w-screen-xl mx-auto px-4 py-6 md:px-6 md:py-8">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Create New Recipe</h1>
    </div>

    <div v-if="success" class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
      Recipe created successfully! Redirecting...
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

      <ImageUpload v-model="form.picture" />

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
        <button type="button"
          @click="router.push({ name: 'recipes' })"
          class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
          Cancel
        </button>
        <button type="submit" :disabled="loading"
          class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition disabled:opacity-50">
          <span v-if="loading">Creating...</span>
          <span v-else>Create Recipe</span>
        </button>
      </div>
    </form>
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