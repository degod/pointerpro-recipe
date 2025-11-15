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
      { path: '', name: 'home', component: Home, meta: { requiresAuth: true } },
      { path: 'login', name: 'login', component: Login },
      { path: 'register', name: 'register', component: Register },

      {
        path: 'recipes',
        meta: { requiresAuth: true },
        children: [
          { path: '', name: 'recipes', component: Recipe },
          { path: 'create', name: 'recipe.create', component: RecipeCreate },
          { path: ':id', name: 'recipe.show', component: RecipeShow, props: true },
          { path: ':id/edit', name: 'recipe.edit', component: RecipeEdit, props: true },
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

export default router;