import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia, setActivePinia } from 'pinia'

import RecipeCreate from '@/Pages/RecipeCreate.vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

// Mock routes
const routes = [
  { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
  { path: '/login', name: 'login', component: { template: '<div>Login</div>' } },
  { path: '/recipes', name: 'recipes', component: { template: '<div>Recipes</div>' } },
]
const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Mock API service
vi.mock('@/services/api', () => ({
  default: {
    defaults: { headers: { common: {} } },
    post: vi.fn(),
  },
}))

// Activate Pinia
setActivePinia(createPinia())

describe('RecipeCreate.vue', () => {
  let wrapper
  let authStore

  beforeEach(async () => {
    authStore = useAuthStore()
    authStore.isAuthenticated = true

    wrapper = mount(RecipeCreate, {
      global: {
        plugins: [router],
        stubs: ['Input', 'Textarea', 'ImageUpload'],
      },
    })
    await router.isReady()
    vi.clearAllMocks()
  })

  it('redirects to login if not authenticated', async () => {
    authStore.isAuthenticated = false

    mount(RecipeCreate, {
      global: {
        plugins: [router],
        stubs: ['Input', 'Textarea', 'ImageUpload'],
      },
    })

    await router.isReady()
    expect(router.currentRoute.value.name).toBe('login')
  })

  it('shows error if recipe name is empty', async () => {
    await wrapper.vm.submitRecipe()
    expect(wrapper.vm.error).toBe('Recipe name is required.')
  })

  it('submits form successfully and sets success', async () => {
    wrapper.vm.form.name = 'Pizza'
    wrapper.vm.form.cuisine_type = 'Italian'
    wrapper.vm.form.ingredients = 'Flour\nTomato'
    wrapper.vm.form.steps = 'Mix\nBake'

    api.post.mockResolvedValue({ data: { id: 1 } })

    await wrapper.vm.submitRecipe()
    expect(api.post).toHaveBeenCalled()
    expect(wrapper.vm.success).toBe(true)
    expect(wrapper.vm.error).toBe(null)
  })

  it('sets loading state correctly', async () => {
    wrapper.vm.form.name = 'Pizza'

    api.post.mockImplementation(
      () => new Promise(resolve => setTimeout(() => resolve({ data: { id: 1 } }), 50))
    )

    const promise = wrapper.vm.submitRecipe()
    expect(wrapper.vm.loading).toBe(true)
    await promise
    expect(wrapper.vm.loading).toBe(false)
  })
})
