import { defineStore } from 'pinia'
import api from '../services/api'
import router from '../router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,          // the logged-in user object
    token: null,         // JWT / Sanctum token
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    // Call this after register / login success
    setAuth({ user, token }) {
      this.user = user
      this.token = token

      // Persist token in localStorage (optional, survives page reload)
      localStorage.setItem('auth_token', token)

      // Add token to every future request
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`
    },

    // Load token on app boot (optional)
    loadToken() {
      const token = localStorage.getItem('auth_token')
      if (token) {
        this.token = token
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`
      }
    },

    logout() {
      this.user = null
      this.token = null
      localStorage.removeItem('auth_token')
      delete api.defaults.headers.common['Authorization']
      router.push({ name: 'login' })
    },
  },
})