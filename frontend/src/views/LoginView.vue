<template>
  <div class="login-page">
    <!-- Visual Side -->
    <div class="visual-side">
      <div class="animated-mesh"></div>
      <div class="overlay"></div>
      <img src="../assets/login_bg.png" alt="Futuristic Gym" class="bg-image">
      <div class="visual-content">
        <h1 class="logo">TECH<span class="text-gradient">FIT</span></h1>
        <p class="tagline">O futuro da sua performance começa aqui.</p>
      </div>
    </div>

    <!-- Form Side -->
    <div class="form-side">
      <div class="glass-card login-card animate-fade-in">
        <header class="card-header">
          <h2>Bem-vindo de volta</h2>
          <p>Acesse seu portal de alta performance.</p>
        </header>
        
        <form @submit.prevent="handleLogin" class="login-form">
          <div class="input-group">
            <label>Email Corporativo</label>
            <div class="input-wrapper">
              <input type="email" v-model="email" required placeholder="exemplo@techfit.com">
              <span class="input-focus"></span>
            </div>
          </div>
          
          <div class="input-group">
            <label>Senha Segura</label>
            <div class="input-wrapper">
              <input type="password" v-model="password" required placeholder="••••••••">
              <span class="input-focus"></span>
            </div>
          </div>

          <div v-if="error" class="error-msg">
            <span class="error-icon">⚠️</span> {{ error }}
          </div>

          <button type="submit" :disabled="loading" class="btn-primary login-btn">
            {{ loading ? 'Autenticando...' : 'Entrar no Sistema' }}
            <span v-if="!loading" class="btn-arrow">→</span>
          </button>

          <footer class="form-footer">
            <p>Esqueceu sua senha? <a href="#">Recuperar acesso</a></p>
          </footer>
        </form>
      </div>
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
  
  try {
    const result = await authStore.login(email.value, password.value)
    
    if (result.success) {
      if (result.role === 'admin') router.push('/admin')
      else if (result.role === 'employee') router.push('/recepcao')
      else router.push('/painel')
    } else {
      error.value = result.message
    }
  } catch (e) {
    error.value = 'Erro de conexão com o servidor.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  display: flex;
  min-height: 100vh;
  background-color: var(--bg-dark);
}

/* Visual Side */
.visual-side {
  flex: 1.2;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

@media (max-width: 900px) {
  .visual-side { display: none; }
}

.bg-image {
  position: absolute;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 10s ease;
}

.visual-side:hover .bg-image {
  transform: scale(1.1);
}

/* Animated Mesh Background */
.animated-mesh {
  position: absolute;
  inset: 0;
  background: 
    radial-gradient(at 0% 0%, rgba(0, 242, 255, 0.15) 0, transparent 50%), 
    radial-gradient(at 100% 100%, rgba(188, 255, 0, 0.1) 0, transparent 50%),
    radial-gradient(at 50% 50%, rgba(112, 0, 255, 0.05) 0, transparent 50%);
  background-size: 200% 200%;
  animation: meshMove 15s ease infinite alternate;
  z-index: 0;
}

@keyframes meshMove {
  0% { background-position: 0% 0%; }
  100% { background-position: 100% 100%; }
}

.overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, rgba(5,5,5,0.9) 0%, rgba(5,5,5,0.2) 100%);
  z-index: 1;
}

.visual-content {
  position: relative;
  z-index: 2;
  text-align: center;
  padding: 2rem;
}

.visual-content .logo {
  font-size: 5rem;
  margin-bottom: 0.5rem;
}

.tagline {
  font-size: 1.2rem;
  color: var(--text-muted);
  letter-spacing: 2px;
  text-transform: uppercase;
}

/* Form Side */
.form-side {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: radial-gradient(circle at center, var(--bg-surface) 0%, var(--bg-dark) 100%);
}

.login-card {
  width: 100%;
  max-width: 450px;
  border-radius: 30px;
  padding: 3.5rem 3rem;
}

.card-header {
  margin-bottom: 2.5rem;
  text-align: center;
}

.card-header h2 {
  font-size: 2rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
}

.card-header p {
  color: var(--text-muted);
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.8rem;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.input-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
}

.input-wrapper {
  position: relative;
}

.input-wrapper input {
  width: 100%;
  padding: 1rem 1.2rem;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--glass-border);
  border-radius: 14px;
  color: #fff;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.input-wrapper input:focus {
  outline: none;
  background: rgba(255,255,255,0.06);
  border-color: var(--accent-primary);
}

.input-focus {
  position: absolute;
  bottom: 0;
  left: 50%;
  width: 0;
  height: 2px;
  background: var(--accent-primary);
  transition: all 0.3s ease;
  transform: translateX(-50%);
}

.input-wrapper input:focus ~ .input-focus {
  width: 80%;
}

.login-btn {
  width: 100%;
  margin-top: 1rem;
  font-size: 1.1rem;
}

.btn-arrow {
  transition: transform 0.3s ease;
}

.login-btn:hover .btn-arrow {
  transform: translateX(5px);
}

.error-msg {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #f87171;
  padding: 0.8rem;
  border-radius: 10px;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.form-footer {
  margin-top: 2rem;
  text-align: center;
  font-size: 0.9rem;
  color: var(--text-muted);
}

.form-footer a {
  color: var(--accent-primary);
  text-decoration: none;
  font-weight: 600;
}

/* ── Mobile Responsiveness ── */
@media (max-width: 900px) {
  .login-page {
    flex-direction: column;
  }

  .form-side {
    padding: 1.5rem;
    min-height: 100vh;
  }

  .login-card {
    padding: 2.5rem 1.8rem;
    border-radius: 20px;
  }

  .card-header h2 {
    font-size: 1.6rem;
  }
}

@media (max-width: 480px) {
  .form-side {
    padding: 1rem;
  }

  .login-card {
    padding: 2rem 1.2rem;
  }

  .card-header h2 {
    font-size: 1.4rem;
  }

  .login-form {
    gap: 1.4rem;
  }

  .input-wrapper input {
    padding: 0.85rem 1rem;
    font-size: 0.95rem;
  }
}
</style>
