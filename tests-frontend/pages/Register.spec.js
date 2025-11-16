// tests-frontend/pages/Register.spec.js
import { mount, flushPromises } from '@vue/test-utils'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia, setActivePinia } from 'pinia'

import Register from '@/Pages/Register.vue'
import Input from '@/Components/Input.vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'

// ---------------------------------------------------------------------------
// Router
// ---------------------------------------------------------------------------
const routes = [
  { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
  { path: '/login', name: 'login', component: { template: '<div>Login</div>' } },
]

let router
beforeEach(() => {
  router = createRouter({ history: createWebHistory(), routes })
})

// ---------------------------------------------------------------------------
// Mock API
// ---------------------------------------------------------------------------
vi.mock('@/services/api', () => ({
  default: {
    post: vi.fn(),
  },
}))

// ---------------------------------------------------------------------------
// Pinia
// ---------------------------------------------------------------------------
setActivePinia(createPinia())

describe('Register.vue', () => {
  let wrapper
  let authStore

  beforeEach(async () => {
    vi.clearAllMocks()
    authStore = useAuthStore()
    vi.spyOn(authStore, 'setAuth')

    wrapper = mount(Register, {
      global: {
        plugins: [router, createPinia()],
        stubs: {
          RouterLink: true,
        },
      },
    })

    await router.isReady()
  })

  it('renders the register form', () => {
    expect(wrapper.find('h1').text()).toBe('Create an Account')
    expect(wrapper.findAll('input').length).toBeGreaterThanOrEqual(4)
    expect(wrapper.find('button[type="submit"]').text()).toBe('Register')
  })

  it('submits the form and registers successfully', async () => {
    const inputs = wrapper.findAll('input')
    await inputs[0].setValue('John Doe')
    await inputs[1].setValue('john@example.com')
    await inputs[2].setValue('secret123')
    await inputs[3].setValue('secret123')

    const mockResp = {
      data: {
        data: {
          id: 1,
          name: 'John Doe',
          email: 'john@example.com',
          token: 'jwt-abc123',
        },
      },
    }
    api.post.mockResolvedValueOnce(mockResp)

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()
    await wrapper.vm.$nextTick()

    expect(api.post).toHaveBeenCalledWith('/register', {
      name: 'John Doe',
      email: 'john@example.com',
      password: 'secret123',
      password_confirmation: 'secret123',
    })
    expect(router.currentRoute.value.name).toBe('home')
  })

  it('shows validation errors from API', async () => {
    const inputs = wrapper.findAll('input')
    await inputs[0].setValue('')
    await inputs[1].setValue('bad')
    await inputs[2].setValue('short')
    await inputs[3].setValue('diff')

    api.post.mockRejectedValueOnce({
      response: {
        data: {
          errors: {
            name: ['The name field is required.'],
            email: ['The email must be a valid email address.'],
            password: ['The password must be at least 8 characters.'],
            password_confirmation: ['The password confirmation does not match.'],
          },
        },
      },
    })

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()
    await wrapper.vm.$nextTick() // message set in catch

    const inputComponents = wrapper.findAllComponents(Input)
    expect(inputComponents[0].props('message')).toEqual(['The name field is required.'])
    expect(inputComponents[1].props('message')).toEqual(['The email must be a valid email address.'])
    expect(inputComponents[2].props('message')).toEqual(['The password must be at least 8 characters.'])
    expect(inputComponents[3].props('message')).toEqual(['The password confirmation does not match.'])
  })

  it('disables the button and shows loading text while submitting', async () => {
    const button = wrapper.find('button[type="submit"]')

    api.post.mockImplementation(() => new Promise(() => {}))

    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    expect(button.attributes('disabled')).toBeDefined()
    expect(button.text()).toBe('Creating Account...')
  })

  it('has a link to the login page', () => {
    const link = wrapper.findComponent({ name: 'RouterLink' })
    expect(link.exists()).toBe(true)
    expect(link.props('to')).toBe('/login')
  })
})