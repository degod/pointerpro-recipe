import { mount, flushPromises } from '@vue/test-utils'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia, setActivePinia } from 'pinia'
import Login from '@/Pages/Login.vue'
import Input from '@/Components/Input.vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'

const routes = [
  { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
  { path: '/register', path: '/register', component: { template: '<div>Register</div>' } },
  { path: '/dashboard', name: 'dashboard', component: { template: '<div>Dashboard</div>' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

vi.mock('@/services/api', () => ({
  default: {
    post: vi.fn(),
  },
}))

setActivePinia(createPinia())

describe('Login.vue', () => {
  let wrapper
  let authStore

  beforeEach(async () => {
    vi.clearAllMocks()
    authStore = useAuthStore()
    vi.spyOn(authStore, 'setAuth')

    wrapper = mount(Login, {
      global: {
        plugins: [router, createPinia()],
        stubs: {
        },
      },
    })

    await router.isReady()
  })

  it('renders the login form', () => {
    expect(wrapper.find('h1').text()).toContain('Login to your account')
    expect(wrapper.findAll('input').length).toBeGreaterThanOrEqual(2)
    expect(wrapper.find('button[type="submit"]').text()).toBe('Login')
  })

  it('submits the form and logs in successfully', async () => {
    const emailInput = wrapper.find('input[type="email"]')
    const passwordInput = wrapper.find('input[type="password"]')

    await emailInput.setValue('user@example.com')
    await passwordInput.setValue('secret123')

    const mockResp = {
      data: {
        data: {
          id: 1,
          name: 'John',
          email: 'user@example.com',
          token: 'abc123',
        },
      },
    }
    api.post.mockResolvedValueOnce(mockResp)
    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(api.post).toHaveBeenCalledWith('/login', {
      email: 'user@example.com',
      password: 'secret123',
    })
    expect(router.currentRoute.value.name).toBe('home')
  })

  it('redirects to the "redirect" query param after login', async () => {
    await router.replace({ path: '/login', query: { redirect: '/dashboard' } })

    const email = wrapper.find('input[type="email"]')
    const password = wrapper.find('input[type="password"]')

    await email.setValue('a@b.c')
    await password.setValue('pwd')

    api.post.mockResolvedValueOnce({
      data: { data: { token: 'x', email: 'a@b.c' } },
    })

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(router.currentRoute.value.path).toBe('/login')
  })

  it('shows validation errors from API', async () => {
    const email = wrapper.find('input[type="email"]')
    const password = wrapper.find('input[type="password"]')

    await email.setValue('invalid')
    await password.setValue('')

    api.post.mockRejectedValueOnce({
      response: {
        data: {
          errors: {
            email: ['The email must be a valid email address.'],
            password: ['The password field is required.'],
          },
        },
      },
    })

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()
    await wrapper.vm.$nextTick()

    const inputs = wrapper.findAllComponents(Input)
    expect(inputs[0].props('message')).toEqual(['The email must be a valid email address.'])
    expect(inputs[1].props('message')).toEqual(['The password field is required.'])
  })

  it('disables the button and shows loading text while submitting', async () => {
    const button = wrapper.find('button[type="submit"]')

    api.post.mockImplementation(() => new Promise(() => {}))

    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    expect(button.attributes('disabled')).toBeDefined()
    expect(button.text()).toBe('Logging into your account...')
  })

  it('has a link to the register page', () => {
    const link = wrapper.findComponent({ name: 'RouterLink' })
    const registerLink = wrapper.find('a[href="/register"]')

    if (link.exists()) {
      expect(link.props('to')).toBe('/register')
      expect(link.text()).toBe('Register now')
    } else {
      expect(registerLink.exists()).toBe(true)
      expect(registerLink.text()).toBe('Register now')
    }
  })
})