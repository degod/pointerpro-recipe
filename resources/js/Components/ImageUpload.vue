<script setup>
import { ref, defineEmits } from 'vue';

const props = defineProps({
  modelValue: File,
});

const emit = defineEmits(['update:modelValue']);

const previewUrl = ref(props.modelValue ? URL.createObjectURL(props.modelValue) : null);
const isDragging = ref(false);
const fileInput = ref(null);

const handleDragOver = (e) => {
  e.preventDefault();
  isDragging.value = true;
};

const handleDragLeave = (e) => {
  e.preventDefault();
  isDragging.value = false;
};

const handleDrop = (e) => {
  e.preventDefault();
  isDragging.value = false;
  const files = e.dataTransfer.files;
  if (files.length) handleFile(files[0]);
};

const handleFileSelect = (e) => {
  const files = e.target.files;
  if (files.length) handleFile(files[0]);
};

const handleFile = (file) => {
  if (!file.type.startsWith('image/')) {
    alert('Please upload an image file.');
    return;
  }
  emit('update:modelValue', file);
  previewUrl.value = URL.createObjectURL(file);
};

const removeImage = () => {
  emit('update:modelValue', null);
  previewUrl.value = null;
  if (fileInput.value) fileInput.value.value = '';
};
</script>

<template>
  <div
    @dragover="handleDragOver"
    @dragleave="handleDragLeave"
    @drop="handleDrop"
    :class="[
      'relative border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition',
      isDragging ? 'border-emerald-500 bg-emerald-50' : 'border-gray-300 hover:border-gray-400'
    ]"
    @click="fileInput.click()"
  >
    <input
      ref="fileInput"
      type="file"
      accept="image/*"
      class="hidden"
      @change="handleFileSelect"
    />

    <!-- Preview -->
    <div v-if="previewUrl" class="space-y-3">
      <img :src="previewUrl" alt="Preview" class="mx-auto h-32 w-32 object-cover rounded-lg shadow" />
      <p class="text-sm text-gray-600">{{ modelValue?.name }}</p>
      <button
        type="button"
        @click.stop="removeImage"
        class="text-xs text-red-600 hover:text-red-700 underline"
      >
        Remove
      </button>
    </div>

    <!-- Placeholder -->
    <div v-else>
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
      </svg>
      <p class="mt-2 text-sm text-gray-600">
        <span class="font-medium">Click to upload</span> or drag and drop
      </p>
      <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
    </div>
  </div>
</template>