import { mount, flushPromises } from '@vue/test-utils'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia, setActivePinia } from 'pinia'
import RecipeShow from '@/Pages/RecipeShow.vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const routes = [
  { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
  { path: '/login', name: 'login', component: { template: '<div>Login</div>' } },
  { path: '/recipes', name: 'recipes', component: { template: '<div>Recipes</div>' } },
  { path: '/recipes/:id/edit', name: 'recipe.edit', component: { template: '<div>Edit Recipe</div>' } },
]

vi.mock('@/services/api', () => ({
  default: {
    defaults: { headers: { common: {} } },
    get: vi.fn(),
  },
}))

setActivePinia(createPinia())

describe('RecipeShow.vue', () => {
  let wrapper
  let router
  let authStore

  beforeEach(async () => {
    vi.clearAllMocks()

    router = createRouter({ history: createWebHistory(), routes })

    authStore = useAuthStore()
    authStore.isAuthenticated = true

    wrapper = mount(RecipeShow, {
      global: {
        plugins: [router, createPinia()],
        mocks: { $route: { params: { id: '1' } } },
      },
    })

    await router.isReady()
  })

  it('redirects to login if not authenticated', async () => {
    authStore.isAuthenticated = false

    wrapper = mount(RecipeShow, {
      global: {
        plugins: [router, createPinia()],
        mocks: { $route: { params: { id: '1' } } },
      },
    })
    await router.isReady()
    await wrapper.vm.fetchRecipe()
    await flushPromises()

    expect(router.currentRoute.value.name).toBe('login')
  })

  it('fetches recipe successfully', async () => {
    const mock = {
      data: {
        data: {
          id: 1,
          name: 'Pizza',
          cuisine_type: 'Italian',
          ingredients: 'Dough\nCheese\nTomato',
          steps: 'Mix\nBake',
          picture: 'pizza.jpg',
          created_at: '2025-01-01T00:00:00.000Z',
        },
      },
    }
    api.get.mockResolvedValueOnce(mock)

    await wrapper.vm.fetchRecipe()
    await flushPromises()

    expect(wrapper.vm.recipe.name).toBe('Pizza')
    expect(wrapper.vm.loading).toBe(false)
    expect(wrapper.find('h1').text()).toBe('Pizza')
  })

  it('handles 404 error', async () => {
    api.get.mockRejectedValueOnce({ response: { status: 404 } })
    await wrapper.vm.fetchRecipe()
    await flushPromises()
    expect(wrapper.vm.error).toBe('Recipe not found.')
    expect(wrapper.vm.loading).toBe(false)
  })

  it('handles other API errors', async () => {
    api.get.mockRejectedValueOnce({ response: { data: { message: 'Server Error' } } })
    await wrapper.vm.fetchRecipe()
    await flushPromises()
    await wrapper.vm.$nextTick()
    expect(wrapper.vm.error).toBe('Server Error')
  })

  const fullRecipe = () => ({
    data: {
      data: {
        id: 1,
        name: 'Pizza',
        cuisine_type: 'Italian',
        ingredients: 'Dough\nCheese\nTomato',
        steps: 'Mix\nBake',
        picture: 'pizza.jpg',
        created_at: '2025-01-01T00:00:00.000Z',
      },
    },
  })

  it('navigates back when clicking back button', async () => {
    api.get.mockResolvedValueOnce(fullRecipe())
    await wrapper.vm.fetchRecipe()
    await flushPromises()
    await wrapper.vm.$nextTick()

    const backBtn = wrapper.find('button')
    expect(backBtn.exists()).toBe(true)

    await backBtn.trigger('click')
    await flushPromises()

    expect(router.currentRoute.value.name).toBe('recipes')
  })

  it('navigates to edit page', async () => {
    api.get.mockResolvedValueOnce(fullRecipe())
    await wrapper.vm.fetchRecipe()
    await flushPromises()
    await wrapper.vm.$nextTick()

    const buttons = wrapper.findAll('button')
    expect(buttons).toHaveLength(2)

    const editBtn = buttons.at(1)
    await editBtn.trigger('click')
    await flushPromises()

    expect(router.currentRoute.value.name).toBe('recipe.edit')
    expect(router.currentRoute.value.params.id).toBe('1')
  })
})