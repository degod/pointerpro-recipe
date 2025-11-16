import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

import Register from '../Pages/Register.vue';
import Home from '../Pages/Home.vue';
import Layout from '../Layouts/Layout.vue';
import Login from '../Pages/Login.vue';
import Recipe from '../Pages/Recipe.vue';
import RecipeCreate from '../Pages/RecipeCreate.vue';
import RecipeShow from '../Pages/RecipeShow.vue';
import RecipeEdit from '../Pages/RecipeEdit.vue';

const routes = [
  {
    path: '/',
    component: Layout,
    children: [
      { path: '', name: 'home', component: Home, meta: { requiresAuth: false } },
      { path: 'login', name: 'login', component: Login, meta: { requiresAuth: false } },
      { path: 'register', name: 'register', component: Register, meta: { requiresAuth: false } },

      {
        path: 'recipes',
        children: [
          { path: '', name: 'recipes', component: Recipe, meta: { requiresAuth: true } },
          { path: 'create', name: 'recipe.create', component: RecipeCreate, meta: { requiresAuth: true } },
          { path: ':id', name: 'recipe.show', component: RecipeShow, props: true, meta: { requiresAuth: false } },
          { path: ':id/edit', name: 'recipe.edit', component: RecipeEdit, props: true, meta: { requiresAuth: true } },
        ],
      },
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
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

  if (requiresAuth && !auth.isAuthenticated) {
    return next({ 
      name: 'login', 
      query: { redirect: to.fullPath } 
    });
  }

  if (auth.isAuthenticated && (to.name === 'login' || to.name === 'register')) {
    return next({ name: 'home' });
  }

  next();
});

export default router;