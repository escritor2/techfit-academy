import axios from 'axios'

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()
  const cartStore = useCartStore()

  // Configura a URL base da API dinamicamente
  axios.defaults.baseURL = config.public.apiBase

  // Initialize stores only on client side
  authStore.initializeAuth()
  cartStore.initializeCart()
})
