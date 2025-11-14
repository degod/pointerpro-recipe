import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

import Register from '../Pages/Register.vue';
import Home from '../Pages/Home.vue';
import Layout from '../Layouts/Layout.vue';
import Login from '../Pages/Login.vue';
import Recipe from '../Pages/Recipe.vue';

const routes = [
  {
    path: '/',
    component: Layout,
    children: [
      { path: '', name: 'home', component: Home, meta: { requiresAuth: true } },
      { path: 'register', name: 'register', component: Register },
      { path: 'login', name: 'login', component: Login },
      { path: 'recipes', name: 'recipes', component: Recipe, meta: { requiresAuth: true } },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation Guard
router.beforeEach((to, from, next) => {
  const auth = useAuthStore();
  const publicPages = ['login', 'register'];
  const isPublic = publicPages.includes(to.name);

  if (!isPublic && !auth.isAuthenticated) {
    return next({
      name: 'login',
      query: { redirect: to.fullPath },
    });
  }

  next();
});

export default router