<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  recipes: { type: Array, required: true },
  pagination: { type: Object, required: true },
  loading: { type: Boolean, default: false },
  onImageError: { type: Function, default: null }
});

const emits = defineEmits(['delete', 'page-change']);

const FALLBACK_PLACEHOLDER =
  'https://placehold.co/48x48/dddddd/999999?text=No+Img';

const recipePictureUrl = (path) => {
  if (!path) return FALLBACK_PLACEHOLDER;
  const repo = import.meta.env.VITE_IMG_REPO_URL || 'http://localhost:9020';
  return `${repo}/${path}`;
};

const handleDelete = (recipe) => {
  emits('delete', recipe);
};
</script>

<template>
  <div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div>
      <p class="mt-2 text-gray-600">Loading recipes...</p>
    </div>

    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cuisine</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="recipe in recipes" :key="recipe.id" class="hover:bg-gray-50 transition">
            <td class="px-6 py-4 whitespace-nowrap">
              <img
                :src="recipePictureUrl(recipe.picture)"
                alt="Recipe"
                class="h-12 w-12 rounded-full object-cover border"
                @error="onImageError || ((e) => e.target.src = FALLBACK_PLACEHOLDER)"
              />
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ recipe.name }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-600">{{ recipe.cuisine_type || '—' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <router-link :to="{ name: 'recipe.show', params: { id: recipe.id } }"
                class="text-indigo-600 hover:text-indigo-900 mr-3">View</router-link>
              <router-link :to="{ name: 'recipe.edit', params: { id: recipe.id } }"
                class="text-emerald-600 hover:text-emerald-900 mr-3">Edit</router-link>
              <button @click="handleDelete(recipe)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex justify-center items-center space-x-2 mt-6 mb-8">
        <button
          @click="$emit('page-change', pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
        >
          Prev
        </button>

        <div class="flex space-x-1">
          <button
            v-for="page in pagination.last_page"
            :key="page"
            @click="$emit('page-change', page)"
            class="px-3 py-1 rounded"
            :class="page === pagination.current_page ? 'bg-emerald-600 text-white' : 'bg-gray-200'"
          >
            {{ page }}
          </button>
        </div>

        <button
          @click="$emit('page-change', pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>
