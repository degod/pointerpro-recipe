<script setup></script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
    <h1 class="text-3xl font-bold mb-6">Welcome to the App</h1>

    <router-link
      to="/register"
      class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
    >
      Go to Register
    </router-link>
  </div>
</template>
