<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import api from '../services/api';
import HomeRecipeCard from '../Components/HomeRecipeCard.vue';
import { watch } from 'vue';
import _ from 'lodash';

const auth = useAuthStore();
const router = useRouter();

const recipes = ref([]);
const page = ref(1);
const loading = ref(false);
const error = ref(null);
const hasMore = ref(true);

const filters = ref({
  name: '',
  cuisine_type: ''
});

const getApiUrl = () => {
  const params = new URLSearchParams({
    ...filters.value,
    page: page.value
  }).toString();
  return `/recipes/filtered?${params}`;
};

const loadRecipes = async () => {
  if (loading.value || !hasMore.value) return;

  loading.value = true;
  error.value = null;

  try {
    const res = await api.get(getApiUrl());
    const data = res.data.data;

    if (data.length === 0) {
      hasMore.value = false;
    } else {
      recipes.value.push(...data);
      page.value++;
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load recipes.';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const handleScroll = () => {
  if (
    window.innerHeight + window.scrollY >=
    document.documentElement.scrollHeight - 300
  ) {
    loadRecipes();
  }
};

const applyFilters = () => {
  recipes.value = [];
  page.value = 1;
  hasMore.value = true;
  loadRecipes();
};

watch(filters, _.debounce(() => {
  applyFilters();
}, 500), { deep: true });

onMounted(() => {
  loadRecipes();
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
  <div class="min-h-screen bg-gray-100 px-4 py-6 md:px-8">
    <div class="max-w-screen-xl mx-auto">
      <div class="flex gap-3 mb-6">
        <input
          v-model="filters.name"
          placeholder="Search by name..."
          class="flex-1 p-2 border rounded"
        />
        <input
          v-model="filters.cuisine_type"
          placeholder="Filter by cuisine..."
          class="flex-1 p-2 border rounded"
        />
      </div>

      <!-- Recipe list -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <HomeRecipeCard :recipes="recipes" />
      </div>

      <div v-if="loading" class="text-center py-4 text-gray-600">
        Loading more recipes...
      </div>

      <div v-if="error" class="text-center py-4 text-red-600">
        {{ error }}
      </div>

      <div v-if="!hasMore && recipes.length" class="text-center py-4 text-gray-500">
        No more recipes to load.
      </div>
    </div>
  </div>
</template>

<style scoped>
input {
  transition: all 0.2s ease;
}
input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}
</style>
