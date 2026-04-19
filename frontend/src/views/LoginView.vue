<template>
  <div class="login-container">
    <div class="login-card">
      <h2>Bem-vindo(a)</h2>
      <p>Faça login para acessar o sistema.</p>
      
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="input-group">
          <label>Email</label>
          <input type="email" v-model="email" required placeholder="seu@email.com">
        </div>
        
        <div class="input-group">
          <label>Senha</label>
          <input type="password" v-model="password" required placeholder="••••••••">
        </div>

        <div v-if="error" class="error-msg">
          {{ error }}
        </div>

        <button type="submit" :disabled="loading" class="login-btn">
          {{ loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

const router = useRouter()
const authStore = useAuthStore()

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  
  const result = await authStore.login(email.value, password.value)
  
  if (result.success) {
    if (result.role === 'admin') router.push('/admin')
    else if (result.role === 'employee') router.push('/recepcao')
    else router.push('/painel')
  } else {
    error.value = result.message
  }
  
  loading.value = false
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--background-dark);
}

.login-card {
  background: var(--surface-dark);
  padding: 3rem;
  border-radius: 12px;
  width: 100%;
  max-width: 400px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
  text-align: center;
}

h2 {
  color: var(--accent-magenta);
  margin-bottom: 0.5rem;
}

p {
  color: var(--text-muted);
  margin-bottom: 2rem;
}

.input-group {
  margin-bottom: 1.5rem;
  text-align: left;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: #fff;
  font-size: 0.9rem;
}

input {
  width: 100%;
  padding: 0.8rem;
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  color: #fff;
  font-size: 1rem;
}

input:focus {
  outline: none;
  border-color: var(--accent-magenta);
}

.error-msg {
  color: #ef4444;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.login-btn {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, var(--accent-magenta), var(--accent-purple));
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  font-size: 1rem;
  cursor: pointer;
  transition: opacity 0.2s;
}

.login-btn:hover {
  opacity: 0.9;
}

.login-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
