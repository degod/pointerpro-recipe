<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import api from '../services/api';
import { useRouter } from 'vue-router';
import RecipeTable from '../Components/RecipeTable.vue';

const auth = useAuthStore();
const router = useRouter();

const recipes = ref([]);
const loading = ref(true);
const error = ref(null);

const showDeleteModal = ref(false);
const recipeToDelete = ref(null);
const deleting = ref(false);

// Pagination state
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 3,
  total: 0
});

const fetchRecipes = async (page = 1) => {
  try {
    loading.value = true;
    const response = await api.get(`/recipes?page=${page}`);
    recipes.value = response.data.data;
    const meta = response.data.extra?.meta;

    pagination.value = {
      current_page: meta?.current_page || 1,
      last_page: meta?.last_page || 1,
      per_page: meta?.per_page || 3,
      total: meta?.total || 0
    };

  } catch (err) {
    if (err.response?.status === 401) {
      auth.logout();
    } else {
      error.value = err.response?.data?.message || 'Failed to load recipes.';
    }
  } finally {
    loading.value = false;
  }
};

const confirmDelete = (recipe) => {
  recipeToDelete.value = recipe;
  showDeleteModal.value = true;
};

const deleteRecipe = async () => {
  if (!recipeToDelete.value) return;

  try {
    deleting.value = true;
    await api.delete(`/recipes/${recipeToDelete.value.id}`);
    recipes.value = recipes.value.filter(r => r.id !== recipeToDelete.value.id);
    showDeleteModal.value = false;
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete recipe.');
  } finally {
    deleting.value = false;
    recipeToDelete.value = null;
  }
};

onMounted(() => {
  if (!auth.isAuthenticated) {
    router.push({ name: 'login' });
  } else {
    fetchRecipes();
  }
});
</script>

<template>
  <div class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">My Recipes</h1>
      <router-link
        to="/recipes/create"
        class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-lg transition"
      >
        Add New Recipe
      </router-link>
    </div>

    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div>
      <p class="mt-2 text-gray-600">Loading your recipes...</p>
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
      {{ error }}
    </div>

    <RecipeTable
      :recipes="recipes"
      :pagination="pagination"
      :loading="loading"
      @delete="confirmDelete"
      @page-change="fetchRecipes"/>

    <div v-if="!recipes.length" class="text-center py-12 bg-gray-50 rounded-lg">
      <p class="text-gray-600 mb-4">You haven't created any recipes yet.</p>
    </div>

    <teleport to="body">
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click="showDeleteModal = false"
      >
        <div
          class="bg-white rounded-lg p-6 max-w-sm w-full mx-4"
          @click.stop
        >
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Recipe?</h3>
          <p class="text-sm text-gray-600 mb-6">
            Are you sure you want to delete "<strong>{{ recipeToDelete?.name }}</strong>"?
            This action cannot be undone.
          </p>
          <div class="flex justify-end space-x-3">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded hover:bg-gray-200"
            >
              Cancel
            </button>
            <button
              @click="deleteRecipe"
              :disabled="deleting"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded hover:bg-red-700 disabled:opacity-50"
            >
              <span v-if="deleting">Deleting...</span>
              <span v-else>Delete</span>
            </button>
          </div>
        </div>
      </div>
    </teleport>
  </div>
</template>

<style scoped>
tr:hover td {
  background-color: #f9fafb;
}
</style>
