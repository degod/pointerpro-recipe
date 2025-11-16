<script setup>
const props = defineProps({
  recipes: {
    type: Array,
    required: true,
    default: () => [],
  },
});

const FALLBACK_PLACEHOLDER =
  'https://placehold.co/120x120/dddddd/999999?text=No+Img';

const recipeImageUrl = (path) => {
  if (!path) return FALLBACK_PLACEHOLDER;
  const repo = import.meta.env.VITE_IMG_REPO_URL || 'http://localhost:9020';
  return `${repo}/${path}`;
};

const onImageError = (event) => {
  event.target.src = FALLBACK_PLACEHOLDER;
  event.target.onerror = null;
};
</script>

<template>
  <div
    v-for="recipe in recipes"
    :key="recipe.id"
    class="bg-white p-4 rounded-lg shadow"
  >
    <img
      :src="recipeImageUrl(recipe.picture)"
      alt="Recipe Image"
      class="w-full h-40 object-cover rounded mb-3"
      @error="onImageError"
    />
    <h2 class="font-semibold text-lg">{{ recipe.name }}</h2>
    <p class="text-sm text-gray-500">{{ recipe.cuisine_type }}</p>
  </div>
</template>
