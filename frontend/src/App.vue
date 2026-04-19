<template>
  <nav class="nav-bar">
    <div class="logo">TECH<span class="text-gradient">FIT</span></div>
    <div class="nav-links">
      <router-link to="/" exact-active-class="active">Home</router-link>
      <router-link to="/loja" exact-active-class="active">Loja</router-link>
      <router-link to="/ai" exact-active-class="active">AI Assistant</router-link>
      
      <!-- Se não estiver logado -->
      <router-link v-if="!authStore.isAuthenticated" to="/login" class="btn-primary login-nav-btn">Entrar</router-link>
      
      <!-- Se estiver logado -->
      <template v-else>
        <router-link v-if="authStore.isAdmin" to="/admin" exact-active-class="active">Admin</router-link>
        <router-link v-if="authStore.isEmployee" to="/recepcao" exact-active-class="active">Recepção</router-link>
        <router-link v-if="authStore.isMember" to="/painel" exact-active-class="active">Meu Painel</router-link>
        
        <button @click="logout" class="btn-outline logout-btn">Sair</button>
      </template>
    </div>
  </nav>

  <router-view></router-view>
</template>

<script setup>
import { useAuthStore } from './stores/authStore'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const logout = async () => {
  await authStore.logout()
  router.push('/')
}
</script>

<style>
/* Global navigation styles are in style.css */
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
  margin-left: 1rem;
}
.logout-btn:hover {
  background: rgba(239, 68, 68, 0.1);
}
</style>
