<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md">
      <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Create an Account</h1>

      <form @submit.prevent="registerUser">
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-indigo-300"
            required
          />
        </div>

        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-indigo-300"
            required
          />
        </div>

        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-indigo-300"
            required
          />
        </div>

        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-indigo-300"
            required
          />
        </div>

        <button
          type="submit"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg focus:outline-none transition"
          :disabled="loading"
        >
          {{ loading ? "Creating Account..." : "Register" }}
        </button>

        <p v-if="message" class="mt-4 text-center text-sm" :class="messageColor">
          {{ message }}
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../services/api';

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const message = ref('');
const messageColor = ref('');
const loading = ref(false);

const registerUser = async () => {
  loading.value = true;
  message.value = '';
  messageColor.value = '';

  try {
    const response = await api.post('/register', form.value);
    message.value = response.data.message || 'Registration successful!';
    messageColor.value = 'text-green-600';
    form.value = { name: '', email: '', password: '', password_confirmation: '' };
  } catch (error) {
    message.value =
      error.response?.data?.message || 'Registration failed. Please try again.';
    messageColor.value = 'text-red-600';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
body {
  font-family: 'Inter', sans-serif;
}
</style>
