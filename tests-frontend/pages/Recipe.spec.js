import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia, setActivePinia } from 'pinia'

import Recipe from '@/Pages/Recipe.vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const routes = [
  { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
  { path: '/login', name: 'login', component: { template: '<div>Login</div>' } },
  { path: '/recipes/:id', name: 'recipe.show', component: { template: '<div>Recipe Show</div>' } },
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
    delete: vi.fn(),
  },
}))

setActivePinia(createPinia())

describe('Recipe.vue', () => {
  let auth

  beforeEach(() => {
    auth = useAuthStore()
    auth.token = 'fake-token'
    auth.isAuthenticated = true

    api.get.mockReset()
    api.delete.mockReset()
  })

  it('loads recipes on mount', async () => {
    const mockData = {
      data: {
        data: [{ id: 1, name: 'Pizza' }],
        meta: { current_page: 1, last_page: 1, per_page: 3, total: 1 },
      },
    }
    api.get.mockResolvedValueOnce(mockData)

    const wrapper = mount(Recipe, {
      global: {
        plugins: [router],
        stubs: ['RouterLink', 'RecipeTable'],
      },
    })

    await wrapper.vm.$nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.recipes).toHaveLength(1)
    expect(wrapper.vm.recipes[0].name).toBe('Pizza')
  })

  it('shows loading state', async () => {
    api.get.mockImplementationOnce(() => new Promise(() => {}))
    const wrapper = mount(Recipe, {
      global: {
        plugins: [router],
        stubs: ['RouterLink', 'RecipeTable'],
      },
    })
    expect(wrapper.vm.loading).toBe(true)
  })

it('deletes a recipe', async () => {
    const wrapper = mount(Recipe, {
        global: {
            plugins: [router],
            stubs: ['RouterLink', 'RecipeTable'],
        },
    })

    wrapper.vm.recipes = [{ id: 1, name: 'Pizza' }]
    wrapper.vm.showDeleteModal = true
    api.delete.mockResolvedValueOnce({ data: {} })
    await wrapper.vm.deleteRecipe(1)
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.showDeleteModal).toBe(true)
})

  it('logs out on 401 error', async () => {
    api.get.mockRejectedValueOnce({ response: { status: 401 } })
    const logoutSpy = vi.spyOn(auth, 'logout')

    const wrapper = mount(Recipe, {
      global: {
        plugins: [router],
        stubs: ['RouterLink', 'RecipeTable'],
      },
    })

    await wrapper.vm.fetchRecipes()

    expect(logoutSpy).toHaveBeenCalled()
  })

  it('handles API error gracefully', async () => {
    const error = { response: { data: { message: 'Failed!' } } }
    api.get.mockRejectedValueOnce(error)

    const wrapper = mount(Recipe, {
      global: {
        plugins: [router],
        stubs: ['RouterLink', 'RecipeTable'],
      },
    })

    await wrapper.vm.fetchRecipes()

    expect(wrapper.vm.loading).toBe(false)
    expect(wrapper.vm.recipes).toHaveLength(0)
  })
})
