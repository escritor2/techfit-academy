<template>
  <div class="app-wrapper">
    <!-- Background Blobs for depth -->
    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-2"></div>
    
    <!-- Navigation Bar -->
    <nav class="nav-bar" :class="{ 'scrolled': isScrolled }" v-if="!isLoginPage">
      <NuxtLink to="/" class="logo-link">
        <div class="logo">TECH<span class="text-gradient">FIT</span></div>
      </NuxtLink>

      <!-- Desktop & Mobile Navigation -->
      <div class="nav-links">
        <template v-if="!authStore.isAuthenticated">
          <NuxtLink to="/" exact-active-class="active">Home</NuxtLink>
          <NuxtLink to="/loja" exact-active-class="active">Loja</NuxtLink>
          <NuxtLink to="/login" class="btn-primary login-nav-btn">Entrar</NuxtLink>
        </template>
        
        <template v-else>
          <div class="user-dropdown-container" @click="toggleUserMenu" ref="userMenuRef">
            <div class="user-avatar-small">
              <img :src="avatarSrc" alt="Avatar">
            </div>
            
            <Transition name="dropdown-fade">
              <div v-if="userMenuOpen" class="user-dropdown-menu">
                <div class="dropdown-header">
                  <strong>{{ authStore.user?.name || 'Usuário' }}</strong>
                  <span>{{ authStore.user?.email }}</span>
                </div>
                <div class="dropdown-divider"></div>
                
                <NuxtLink to="/" class="dropdown-item">🏠 Home</NuxtLink>
                <NuxtLink v-if="authStore.isAdmin" to="/admin" class="dropdown-item">⚙️ Painel Admin</NuxtLink>
                <NuxtLink v-if="authStore.isEmployee" to="/recepcao" class="dropdown-item">📋 Recepção</NuxtLink>
                <NuxtLink v-if="authStore.isMember" to="/painel" class="dropdown-item">🎯 Meu Painel</NuxtLink>
                
                <div class="dropdown-divider"></div>
                <NuxtLink to="/loja" class="dropdown-item">🛒 Loja Supplements</NuxtLink>
                <NuxtLink to="/ai" class="dropdown-item">🤖 AI Assistant</NuxtLink>
                <NuxtLink to="/nutricao" class="dropdown-item">🥗 Minha Dieta</NuxtLink>
                
                <div class="dropdown-divider"></div>
                <a href="#" @click.prevent="cartStore.toggleCart" class="dropdown-item">🛍️ Carrinho <span v-if="cartStore.totalItems" class="badge">{{ cartStore.totalItems }}</span></a>
                <NuxtLink to="/historico-compras" class="dropdown-item">🧾 Histórico de Compras</NuxtLink>
                <NuxtLink to="/perfil" class="dropdown-item">👤 Configurações</NuxtLink>
                
                <div class="dropdown-divider"></div>
                <a href="#" @click.prevent="logout" class="dropdown-item logout-item">🚪 Sair do Sistema</a>
              </div>
            </Transition>
          </div>
        </template>
      </div>
    </nav>

    <!-- Page Content -->
    <main class="main-content">
      <NuxtPage />
    </main>

    <!-- Global Overlays -->
    <CartSidebar />
    <ToastNotification />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from './stores/authStore'
import { useCartStore } from './stores/cartStore'

const authStore = useAuthStore()
const cartStore = useCartStore()
const route = useRoute()
const router = useRouter()

const isScrolled = ref(false)
const userMenuOpen = ref(false)
const userMenuRef = ref(null)

const isLoginPage = computed(() => route.path === '/login')

const avatarSrc = computed(() => {
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(authStore.user?.name || 'User')}&background=00f2ff&color=000&bold=true`
})

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value
}

const handleClickOutside = (event) => {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    userMenuOpen.value = false
  }
}

const handleScroll = () => {
  isScrolled.value = window.scrollY > 50
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  window.addEventListener('click', handleClickOutside)
  authStore.initializeAuth()
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('click', handleClickOutside)
})

const logout = async () => {
  await authStore.logout()
  userMenuOpen.value = false
  router.push('/')
}
</script>

<style>
/* ... existing styles ... */
.app-wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
}

.logo-link {
  text-decoration: none;
  color: inherit;
  transition: transform 0.3s var(--transition-bounce);
}

.logo-link:hover {
  transform: scale(1.05);
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.user-dropdown-container {
  position: relative;
  cursor: pointer;
}

.user-avatar-small {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  border: 2px solid var(--accent-primary);
  overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
}

.user-avatar-small:hover {
  transform: scale(1.1);
  box-shadow: 0 0 15px rgba(var(--accent-primary-rgb), 0.5);
}

.user-avatar-small img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-dropdown-menu {
  position: absolute;
  top: 60px;
  right: 0;
  width: 250px;
  background: rgba(10, 10, 15, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: 16px;
  padding: 1rem 0;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  z-index: 2000;
  display: flex;
  flex-direction: column;
}

.dropdown-header {
  padding: 0.5rem 1.5rem 1rem;
  display: flex;
  flex-direction: column;
}

.dropdown-header strong {
  font-size: 1rem;
  color: #fff;
}

.dropdown-header span {
  font-size: 0.75rem;
  color: var(--text-dim);
}

.dropdown-divider {
  height: 1px;
  background: var(--glass-border);
  margin: 0.5rem 0;
}

.dropdown-item {
  padding: 0.8rem 1.5rem;
  color: var(--text-muted);
  text-decoration: none;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.dropdown-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #fff;
  padding-left: 1.8rem;
}

.dropdown-item.logout-item {
  color: #ff3366;
}

.dropdown-item.logout-item:hover {
  background: rgba(255, 51, 102, 0.1);
}

.badge {
  background: var(--accent-primary);
  color: #000;
  font-size: 0.7rem;
  font-weight: 800;
  padding: 0.1rem 0.5rem;
  border-radius: 20px;
}

.dropdown-fade-enter-active, .dropdown-fade-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}
.dropdown-fade-enter-from, .dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

</style>
