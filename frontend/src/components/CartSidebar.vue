<template>
  <Teleport to="body">
    <!-- Overlay -->
    <Transition name="overlay">
      <div v-if="cartStore.isOpen" class="cart-overlay" @click="cartStore.closeCart"></div>
    </Transition>

    <!-- Sidebar -->
    <Transition name="slide">
      <div v-if="cartStore.isOpen" class="cart-sidebar">
        <div class="cart-header">
          <h3>🛒 Carrinho <span class="cart-count" v-if="cartStore.totalItems">({{ cartStore.totalItems }})</span></h3>
          <button class="close-btn" @click="cartStore.closeCart">✕</button>
        </div>

        <div v-if="cartStore.isEmpty" class="cart-empty">
          <span class="empty-icon">🛍️</span>
          <p>Seu carrinho está vazio</p>
          <router-link to="/loja" class="btn-primary" @click="cartStore.closeCart" style="text-decoration:none; text-align:center;">
            Ver Produtos
          </router-link>
        </div>

        <div v-else class="cart-content">
          <div class="cart-items">
            <div v-for="item in cartStore.items" :key="item.id" class="cart-item">
              <img :src="item.image_url || 'https://via.placeholder.com/60'" :alt="item.name" class="item-img" />
              <div class="item-details">
                <h4>{{ item.name }}</h4>
                <p class="item-price">R$ {{ (item.price * item.quantity).toFixed(2).replace('.', ',') }}</p>
                <div class="qty-controls">
                  <button @click="cartStore.updateQuantity(item.id, item.quantity - 1)">−</button>
                  <span>{{ item.quantity }}</span>
                  <button @click="cartStore.updateQuantity(item.id, item.quantity + 1)">+</button>
                </div>
              </div>
              <button class="remove-btn" @click="cartStore.removeItem(item.id)">🗑️</button>
            </div>
          </div>

          <div class="cart-footer">
            <div class="cart-total">
              <span>Total</span>
              <span class="total-price text-gradient">R$ {{ cartStore.totalPrice.toFixed(2).replace('.', ',') }}</span>
            </div>
            <button 
              class="btn-primary checkout-btn" 
              @click="handleCheckout"
              :disabled="cartStore.loading"
            >
              {{ cartStore.loading ? 'Processando...' : '💳 Finalizar Compra' }}
            </button>
            <button class="btn-outline clear-btn" @click="cartStore.clearCart">Limpar Carrinho</button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { useCartStore } from '../stores/cartStore'
import { useAuthStore } from '../stores/authStore'
import { useNotificationStore } from '../stores/notificationStore'
import { useRouter } from 'vue-router'

const cartStore = useCartStore()
const authStore = useAuthStore()
const notify = useNotificationStore()
const router = useRouter()

async function handleCheckout() {
  if (!authStore.isAuthenticated) {
    notify.warning('Faça login para finalizar a compra.')
    cartStore.closeCart()
    router.push('/login')
    return
  }
  await cartStore.checkout()
}
</script>

<style scoped>
.cart-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 998;
}

.cart-sidebar {
  position: fixed;
  top: 0;
  right: 0;
  width: 400px;
  max-width: 90vw;
  height: 100vh;
  background: rgba(15, 15, 20, 0.95);
  backdrop-filter: blur(20px);
  border-left: 1px solid rgba(255, 255, 255, 0.1);
  z-index: 999;
  display: flex;
  flex-direction: column;
  box-shadow: -10px 0 40px rgba(0, 0, 0, 0.5);
}

.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.cart-header h3 { font-size: 1.2rem; font-weight: 700; }
.cart-count { color: var(--accent-primary); }

.close-btn {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.3s;
}

.close-btn:hover {
  background: rgba(255, 80, 80, 0.2);
  border-color: #ff5050;
}

.cart-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 2rem;
  color: var(--text-muted);
}

.empty-icon { font-size: 3rem; }

.cart-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.cart-items {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.cart-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  transition: background 0.2s;
}

.cart-item:hover { background: rgba(255, 255, 255, 0.03); }

.item-img {
  width: 60px;
  height: 60px;
  border-radius: 10px;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.item-details { flex: 1; }
.item-details h4 { font-size: 0.9rem; font-weight: 600; margin-bottom: 0.3rem; }
.item-price { font-size: 0.85rem; color: var(--accent-secondary); font-weight: 700; }

.qty-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.qty-controls button {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: rgba(255, 255, 255, 0.05);
  color: white;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
}

.qty-controls button:hover {
  background: rgba(0, 242, 255, 0.15);
  border-color: var(--accent-primary);
}

.qty-controls span {
  font-weight: 700;
  font-size: 0.9rem;
  min-width: 20px;
  text-align: center;
}

.remove-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.1rem;
  opacity: 0.5;
  transition: opacity 0.2s;
}

.remove-btn:hover { opacity: 1; }

.cart-footer {
  padding: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.cart-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.1rem;
  font-weight: 700;
}

.total-price { font-size: 1.4rem; }

.checkout-btn { width: 100%; text-align: center; }

.clear-btn {
  width: 100%;
  text-align: center;
  font-size: 0.85rem;
  padding: 0.6rem;
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: var(--text-muted);
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s;
}

.clear-btn:hover {
  border-color: #ff5050;
  color: #ff5050;
}

/* Transitions */
.slide-enter-active, .slide-leave-active {
  transition: transform 0.35s ease;
}
.slide-enter-from, .slide-leave-to {
  transform: translateX(100%);
}

.overlay-enter-active, .overlay-leave-active {
  transition: opacity 0.35s ease;
}
.overlay-enter-from, .overlay-leave-to {
  opacity: 0;
}
</style>
