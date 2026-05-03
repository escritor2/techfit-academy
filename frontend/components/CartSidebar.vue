<template>
  <Teleport to="body">
    <!-- Overlay -->
    <Transition name="overlay">
      <div v-if="cartStore.isOpen" class="cart-overlay" @click="cartStore.closeCart"></div>
    </Transition>

    <!-- Sidebar -->
    <Transition name="slide">
      <div v-if="cartStore.isOpen" class="cart-sidebar glass-card">
        <div class="cart-header">
          <h3 class="text-glow">🛒 Carrinho <span class="cart-count" v-if="cartStore.totalItems">({{ cartStore.totalItems }})</span></h3>
          <button class="close-btn" @click="cartStore.closeCart">✕</button>
        </div>

        <div v-if="cartStore.isEmpty" class="cart-empty">
          <div class="empty-animation">🛍️</div>
          <p>Seu carrinho está vazio</p>
          <NuxtLink to="/loja" class="btn-primary" @click="cartStore.closeCart">
            Ver Produtos
          </NuxtLink>
        </div>

        <div v-else class="cart-content">
          <div class="cart-items">
            <div v-for="item in cartStore.items" :key="item.id" class="cart-item">
              <div class="img-wrapper">
                <img :src="item.image_url || 'https://via.placeholder.com/80'" :alt="item.name" class="item-img" />
              </div>
              <div class="item-details">
                <h4>{{ item.name }}</h4>
                <p class="item-price">R$ {{ (item.price * item.quantity).toFixed(2).replace('.', ',') }}</p>
                <div class="qty-controls">
                  <button @click="cartStore.updateQuantity(item.id, item.quantity - 1)">−</button>
                  <span class="qty-num">{{ item.quantity }}</span>
                  <button @click="cartStore.updateQuantity(item.id, item.quantity + 1)">+</button>
                </div>
              </div>
              <button class="remove-btn" @click="cartStore.removeItem(item.id)" title="Remover">🗑️</button>
            </div>
          </div>

          <div class="cart-footer">
            <div class="cart-total">
              <span class="total-label">Total do Pedido</span>
              <span class="total-price text-gradient text-glow">R$ {{ cartStore.totalPrice.toFixed(2).replace('.', ',') }}</span>
            </div>
            <button 
              class="btn-primary checkout-btn" 
              @click="handleCheckout"
              :disabled="cartStore.loading"
            >
              <span v-if="!cartStore.loading">💳 Finalizar Compra</span>
              <div v-else class="loader-dots"><span></span><span></span><span></span></div>
            </button>
            <button class="clear-all-btn" @click="cartStore.clearCart">Esvaziar Carrinho</button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
const cartStore = useCartStore()
const authStore = useAuthStore()
const router = useRouter()

async function handleCheckout() {
  if (!authStore.isAuthenticated) {
    // In a real app we'd use a notification store here
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
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(8px);
  z-index: 1500;
}

.cart-sidebar {
  position: fixed;
  top: 0;
  right: 0;
  width: 450px;
  max-width: 100vw;
  height: 100vh;
  z-index: 1600;
  display: flex;
  flex-direction: column;
  border-radius: 0;
  border-top: none;
  border-bottom: none;
  border-right: none;
  background: rgba(10, 10, 12, 0.95);
  box-shadow: -20px 0 60px rgba(0,0,0,0.8);
}

.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 2rem;
  border-bottom: 1px solid var(--glass-border);
}

.cart-header h3 { font-size: 1.5rem; font-weight: 800; letter-spacing: -1px; }
.cart-count { color: var(--accent-primary); font-size: 1rem; margin-left: 0.5rem; }

.close-btn {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--glass-border);
  color: white;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1.2rem;
  transition: all 0.3s var(--transition-bounce);
}

.close-btn:hover {
  background: var(--danger);
  transform: rotate(90deg);
  border-color: transparent;
}

.cart-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2rem;
  padding: 3rem;
  text-align: center;
}

.empty-animation {
  font-size: 5rem;
  animation: float 3s infinite ease-in-out;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}

.cart-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.cart-items {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.cart-item {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.02);
  border-radius: var(--radius-md);
  margin-bottom: 1rem;
  border: 1px solid var(--glass-border);
  transition: all 0.3s;
}

.cart-item:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: var(--glass-border-bright);
  transform: translateX(-5px);
}

.img-wrapper {
  width: 80px;
  height: 80px;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid var(--glass-border);
}

.item-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-details { flex: 1; }
.item-details h4 { font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem; }
.item-price { font-size: 1rem; color: var(--accent-primary); font-weight: 800; }

.qty-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 1rem;
}

.qty-controls button {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid var(--glass-border);
  background: rgba(255, 255, 255, 0.05);
  color: white;
  cursor: pointer;
  transition: all 0.2s;
}

.qty-controls button:hover {
  background: var(--accent-primary);
  color: #000;
}

.qty-num { font-weight: 800; font-size: 1rem; }

.remove-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  opacity: 0.3;
  transition: all 0.3s;
}

.remove-btn:hover { opacity: 1; transform: scale(1.2); }

.cart-footer {
  padding: 2rem;
  background: rgba(255, 255, 255, 0.02);
  border-top: 1px solid var(--glass-border);
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.cart-total {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 1rem;
}

.total-label { color: var(--text-muted); font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
.total-price { font-size: 2rem; font-weight: 900; }

.checkout-btn { width: 100%; height: 60px; font-size: 1.1rem; }

.clear-all-btn {
  background: transparent;
  border: none;
  color: var(--text-muted);
  font-size: 0.9rem;
  cursor: pointer;
  text-decoration: underline;
  transition: color 0.3s;
}

.clear-all-btn:hover { color: var(--danger); }

/* Transitions */
.slide-enter-active, .slide-leave-active { transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-enter-from, .slide-leave-to { transform: translateX(100%); }

.overlay-enter-active, .overlay-leave-active { transition: opacity 0.5s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }

.loader-dots { display: flex; gap: 4px; }
.loader-dots span { width: 8px; height: 8px; background: #000; border-radius: 50%; animation: pulse 0.6s infinite alternate; }
.loader-dots span:nth-child(2) { animation-delay: 0.2s; }
.loader-dots span:nth-child(3) { animation-delay: 0.4s; }

@keyframes pulse { to { opacity: 0.3; transform: scale(0.8); } }
</style>

