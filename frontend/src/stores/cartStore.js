import { defineStore } from 'pinia'
import axios from 'axios'
import { useNotificationStore } from './notificationStore'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: JSON.parse(localStorage.getItem('techfit_cart') || '[]'),
    isOpen: false,
    loading: false,
  }),

  getters: {
    totalItems: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
    totalPrice: (state) => state.items.reduce((sum, item) => sum + (item.price * item.quantity), 0),
    isEmpty: (state) => state.items.length === 0,
  },

  actions: {
    _persist() {
      localStorage.setItem('techfit_cart', JSON.stringify(this.items))
    },

    addItem(product) {
      const existing = this.items.find(i => i.id === product.id)
      if (existing) {
        existing.quantity++
      } else {
        this.items.push({
          id: product.id,
          name: product.name,
          price: parseFloat(product.price),
          image_url: product.image_url,
          quantity: 1,
          stock: product.stock_quantity,
        })
      }
      this._persist()
      const notify = useNotificationStore()
      notify.success(`${product.name} adicionado ao carrinho!`)
    },

    removeItem(productId) {
      this.items = this.items.filter(i => i.id !== productId)
      this._persist()
    },

    updateQuantity(productId, quantity) {
      const item = this.items.find(i => i.id === productId)
      if (item) {
        if (quantity <= 0) {
          this.removeItem(productId)
        } else {
          item.quantity = Math.min(quantity, item.stock || 99)
          this._persist()
        }
      }
    },

    clearCart() {
      this.items = []
      this._persist()
    },

    toggleCart() {
      this.isOpen = !this.isOpen
    },

    openCart() {
      this.isOpen = true
    },

    closeCart() {
      this.isOpen = false
    },

    async checkout() {
      const notify = useNotificationStore()

      if (this.isEmpty) {
        notify.error('Seu carrinho está vazio!')
        return false
      }

      this.loading = true
      try {
        const payload = {
          items: this.items.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
          }))
        }

        const response = await axios.post('/sales', payload)
        this.clearCart()
        this.closeCart()
        notify.success('Compra realizada com sucesso! 🎉')
        return response.data
      } catch (error) {
        const msg = error.response?.data?.message || 'Erro ao finalizar compra.'
        notify.error(msg)
        return false
      } finally {
        this.loading = false
      }
    }
  }
})
