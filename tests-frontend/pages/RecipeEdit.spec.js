import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia, setActivePinia } from 'pinia'
import RecipeEdit from '@/Pages/RecipeEdit.vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const routes = [
  { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
  { path: '/login', name: 'login', component: { template: '<div>Login</div>' } },
  { path: '/recipes', name: 'recipes', component: { template: '<div>Recipes</div>' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

vi.mock('@/services/api', () => ({
  default: {
    defaults: { headers: { common: {} } },
    get: vi.fn(),
    post: vi.fn(),
  },
}))

setActivePinia(createPinia())

describe('RecipeEdit.vue', () => {
  let wrapper
  let authStore

  beforeEach(async () => {
    vi.clearAllMocks()

    authStore = useAuthStore()
    authStore.isAuthenticated = true

    wrapper = mount(RecipeEdit, {
      global: {
        plugins: [router, createPinia()],
        stubs: ['Input', 'Textarea', 'ImageUpload'],
        mocks: {
          $route: { params: { id: 1 } },
        },
      },
    })

    await router.isReady()
  })

  it('loads recipe on mount', async () => {
    const mockRecipe = {
      data: {
        data: {
          name: 'Pizza',
          cuisine_type: 'Italian',
          ingredients: 'Dough\nCheese\nTomato',
          steps: 'Mix\nBake',
          picture: 'pizza.jpg',
        },
      },
    }
    api.get.mockResolvedValueOnce(mockRecipe)

    await wrapper.vm.loadRecipe()
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.form.name).toBe('Pizza')
    expect(wrapper.vm.form.cuisine_type).toBe('Italian')
    expect(wrapper.vm.form.ingredients).toBe('Dough\nCheese\nTomato')
    expect(wrapper.vm.form.steps).toBe('Mix\nBake')
    expect(wrapper.vm.form.current_picture).toBe('pizza.jpg')
    expect(wrapper.vm.loadingRecipe).toBe(false)
  })

  it('redirects to login if not authenticated', async () => {
    authStore.isAuthenticated = false
    wrapper = mount(RecipeEdit, {
      global: {
        plugins: [router, createPinia()],
        stubs: ['Input', 'Textarea', 'ImageUpload'],
        mocks: {
          $route: { params: { id: 1 } },
        },
      },
    })
    await router.isReady()
    expect(router.currentRoute.value.name).toBe('login')
  })

  it('submits recipe successfully', async () => {
    api.post.mockResolvedValueOnce({})

    wrapper.vm.form.name = 'New Pizza'
    wrapper.vm.form.cuisine_type = 'Italian'
    wrapper.vm.form.ingredients = 'Dough\nCheese\nTomato'
    wrapper.vm.form.steps = 'Mix\nBake'

    await wrapper.vm.submitRecipe()
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.success).toBe(true)
    expect(wrapper.vm.error).toBeNull()
    expect(api.post).toHaveBeenCalled()
  })

  it('handles API error gracefully', async () => {
    api.post.mockRejectedValueOnce({
      response: {
        data: {
          message: 'Failed to update',
          errors: { name: 'Invalid' },
        },
      },
    })

    wrapper.vm.form.name = ''
    await wrapper.vm.submitRecipe()
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.error).toBe('Recipe name is required.')

    wrapper.vm.form.name = 'Some Name'
    await wrapper.vm.submitRecipe()
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.error).toBe('Failed to update')
    expect(wrapper.vm.message).toEqual({ name: 'Invalid' })
  })
})
