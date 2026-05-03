import { defineStore } from 'pinia'
import axios from 'axios'

// Configuração padrão do Axios para o backend Laravel
// Configuração do Axios será feita dinamicamente
axios.defaults.headers.common['Accept'] = 'application/json'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    authHeaders: (state) => ({
      Authorization: `Bearer ${state.token}`,
      Accept: 'application/json',
    }),
    isAdmin: (state) => state.user?.role === 'admin',
    isEmployee: (state) => state.user?.role === 'employee',
    isMember: (state) => state.user?.role === 'member',
  },

  actions: {
    async login(email, password) {
      try {
        const response = await axios.post('/login', { email, password })
        const { access_token, user } = response.data.data
        
        this.token = access_token
        this.user = user

        if (process.client) {
          localStorage.setItem('token', this.token)
          localStorage.setItem('user', JSON.stringify(this.user))
        }

        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

        return { success: true, role: this.user.role }
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Credenciais inválidas ou erro no servidor'
        }
      }
    },

    async fetchUser() {
      try {
        const response = await axios.get('/user')
        this.user = response.data.data
        if (process.client) {
          localStorage.setItem('user', JSON.stringify(this.user))
        }
      } catch (error) {
        console.error('Erro ao buscar dados do usuário', error)
        this.clearAuth()
      }
    },

    async logout() {
      try {
        if (this.token) {
          await axios.post('/logout')
        }
      } catch (error) {
        console.error('Erro ao encerrar sessão na API', error)
      } finally {
        this.clearAuth()
      }
    },

    clearAuth() {
      this.user = null
      this.token = null
      if (process.client) {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
      }
      delete axios.defaults.headers.common['Authorization']
    },

    initializeAuth() {
      if (process.client) {
        this.token = localStorage.getItem('token') || null
        const userJson = localStorage.getItem('user')
        this.user = userJson ? JSON.parse(userJson) : null
      }
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
      }
    }
  }
})
