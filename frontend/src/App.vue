<template>
  <nav class="nav-bar">
    <router-link to="/" class="logo-link">
      <div class="logo">TECH<span class="text-gradient">FIT</span></div>
    </router-link>

    <!-- Mobile hamburger -->
    <button class="hamburger" @click="menuOpen = !menuOpen" :class="{ open: menuOpen }">
      <span></span><span></span><span></span>
    </button>

    <div class="nav-links" :class="{ 'mobile-open': menuOpen }">
      <router-link to="/" exact-active-class="active" @click="menuOpen = false">Home</router-link>
      <router-link to="/loja" exact-active-class="active" @click="menuOpen = false">Loja</router-link>
      <router-link to="/ai" exact-active-class="active" @click="menuOpen = false">AI Assistant</router-link>
      
      <!-- Carrinho -->
      <button class="cart-nav-btn" @click="cartStore.toggleCart">
        🛒
        <span v-if="cartStore.totalItems" class="cart-badge">{{ cartStore.totalItems }}</span>
      </button>

      <!-- Se não estiver logado -->
      <router-link v-if="!authStore.isAuthenticated" to="/login" class="btn-primary login-nav-btn" @click="menuOpen = false">Entrar</router-link>
      
      <!-- Se estiver logado -->
      <template v-else>
        <router-link v-if="authStore.isAdmin" to="/admin" exact-active-class="active" @click="menuOpen = false">Admin</router-link>
        <router-link v-if="authStore.isEmployee" to="/recepcao" exact-active-class="active" @click="menuOpen = false">Recepção</router-link>
        <router-link v-if="authStore.isMember" to="/painel" exact-active-class="active" @click="menuOpen = false">Meu Painel</router-link>
        
        <button @click="logout" class="btn-outline logout-btn">Sair</button>
      </template>
    </div>
  </nav>

  <router-view></router-view>

  <!-- Global components -->
  <CartSidebar />
  <ToastNotification />
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from './stores/authStore'
import { useCartStore } from './stores/cartStore'
import { useRouter } from 'vue-router'
import CartSidebar from './components/CartSidebar.vue'
import ToastNotification from './components/ToastNotification.vue'

const authStore = useAuthStore()
const cartStore = useCartStore()
const router = useRouter()
const menuOpen = ref(false)

const logout = async () => {
  await authStore.logout()
  menuOpen.value = false
  router.push('/')
}
</script>

<style>
/* Global navigation styles are in style.css */
.logo-link {
  text-decoration: none;
  color: inherit;
}

.login-nav-btn {
  padding: 0.4rem 1.2rem;
  font-size: 0.9rem;
  text-decoration: none;
  border-radius: 8px;
}

.logout-btn {
  padding: 0.4rem 1.2rem;
  font-size: 0.9rem;
  cursor: pointer;
  background: transparent;
  color: #ef4444;
  border: 1px solid #ef4444;
  border-radius: 8px;
  margin-left: 0.5rem;
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.1);
}

/* Cart button */
.cart-nav-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  position: relative;
  padding: 0.3rem;
  transition: transform 0.2s;
}

.cart-nav-btn:hover {
  transform: scale(1.15);
}

.cart-badge {
  position: absolute;
  top: -6px;
  right: -8px;
  background: linear-gradient(135deg, var(--accent-primary), #0072ff);
  color: #000;
  font-size: 0.65rem;
  font-weight: 900;
  min-width: 18px;
  height: 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}

/* Hamburger */
.hamburger {
  display: none;
  flex-direction: column;
  gap: 5px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  z-index: 101;
}

.hamburger span {
  display: block;
  width: 24px;
  height: 2px;
  background: white;
  border-radius: 2px;
  transition: all 0.3s;
}

.hamburger.open span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
.hamburger.open span:nth-child(2) { opacity: 0; }
.hamburger.open span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }

@media (max-width: 768px) {
  .hamburger { display: flex; }

  .nav-links {
    position: fixed;
    top: 0;
    right: -100%;
    width: 280px;
    height: 100vh;
    flex-direction: column;
    background: rgba(10, 10, 12, 0.98);
    backdrop-filter: blur(20px);
    padding: 5rem 2rem 2rem;
    gap: 1.5rem;
    transition: right 0.35s ease;
    z-index: 100;
    border-left: 1px solid rgba(255,255,255,0.1);
  }

  .nav-links.mobile-open {
    right: 0;
  }

  .nav-links a, .nav-links button {
    font-size: 1.1rem;
  }
}
</style>
