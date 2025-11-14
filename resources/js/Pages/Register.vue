<script setup>
import { ref } from 'vue';
import api from '../services/api';
import Input from '../Components/Input.vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const auth = useAuthStore();

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

let message = ref('');
const messageColor = ref('');
const loading = ref(false);

const registerUser = async () => {
  loading.value = true;
  message = null;
  messageColor.value = '';

  try {
    const response = await api.post('/register', form.value);

    const user = response.data.data;
    const token = user.token;
    auth.setAuth({ user, token });

    form.value = { name: '', email: '', password: '', password_confirmation: '' };
    
    router.push({ name: 'home' });
  } catch (error) {
    message = error?.response?.data?.errors;
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md">
      <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Create an Account</h1>

      <form @submit.prevent="registerUser">
        <Input
          v-model="form.name"
          label="Name"
          type="text"
          :message="message?.name || ''"
        />
        <Input
          v-model="form.email"
          label="Email"
          type="email"
          :message="message?.email || ''"
        />
        <Input
          v-model="form.password"
          label="Password"
          type="password"
          :message="message?.password || ''"
        />
        <Input
          v-model="form.password_confirmation"
          label="Confirm Password"
          type="password"
          :message="message?.password_confirmation || ''"
        />

        <button
          type="submit"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg focus:outline-none transition"
          :disabled="loading"
        >
          {{ loading ? "Creating Account..." : "Register" }}
        </button>
        <p class="p-2">
          Already have an account? <router-link to="/login" class="blue-400">Login now</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<style scoped>
body {
  font-family: 'Inter', sans-serif;
}
</style>
