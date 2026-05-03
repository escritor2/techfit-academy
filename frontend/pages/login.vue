<template>
  <div class="login-page">
    <!-- Visual Side (Animated AI Image) -->
    <div class="visual-side hide-mobile">
      <div class="image-overlay"></div>
      <div class="animated-bg-image"></div>
      
      <div class="visual-content">
        <h1 class="logo-large">TECH<span class="text-gradient">FIT</span></h1>
        <p class="visual-tagline">O Futuro da Performance Humana</p>
        <div class="visual-divider"></div>
        <p class="visual-desc">Sua evolução potencializada por Inteligência Artificial e Biometria de ponta.</p>
      </div>
    </div>

    <!-- Form Side -->
    <div class="form-side">
      <div class="glass-card login-box" v-reveal>
        <header class="login-header">
          <h2>Bem-vindo <span class="text-gradient">Techfit</span></h2>
          <p>Acesse seu portal de alta performance</p>
        </header>

        <form @submit.prevent="handleLogin" class="login-form">
          <div class="form-group">
            <label>E-mail ou Usuário</label>
            <div class="input-container">
              <span class="icon">👤</span>
              <input type="email" v-model="email" required placeholder="seu@email.com">
            </div>
          </div>

          <div class="form-group">
            <label>Senha Segura</label>
            <div class="input-container">
              <span class="icon">🔒</span>
              <input type="password" v-model="password" required placeholder="••••••••">
            </div>
          </div>

          <div v-if="error" class="error-bubble">
            <span>⚠️</span> {{ error }}
          </div>

          <button type="submit" class="btn-primary login-btn" :disabled="loading">
            <span v-if="!loading">Entrar no Sistema</span>
            <span v-else class="loading-spinner"></span>
          </button>

          <footer class="login-footer">
            <a href="#" class="forgot-link">Esqueceu a senha?</a>
            <p class="register-text">Novo por aqui? <NuxtLink to="/planos">Ver Planos</NuxtLink></p>
          </footer>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
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
    error.value = 'Falha na conexão com o servidor.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  display: flex;
  min-height: 100vh;
  background: var(--bg-dark);
}

.visual-side {
  flex: 1.5;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background: #000;
}

.animated-bg-image {
  position: absolute;
  inset: -5%; /* slightly larger for animation */
  background-image: url('/login-bg.png');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  animation: slowPan 30s infinite alternate ease-in-out;
  z-index: 1;
}

.image-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    90deg, 
    rgba(5, 5, 7, 0.4) 0%, 
    rgba(5, 5, 7, 0.95) 100%
  ),
  radial-gradient(circle at center, transparent 0%, rgba(0,0,0,0.8) 100%);
  z-index: 2;
}

@keyframes slowPan {
  0% { transform: scale(1) translate(0, 0); }
  50% { transform: scale(1.05) translate(-1%, 1%); }
  100% { transform: scale(1.1) translate(1%, -1%); }
}

.visual-content {
  position: relative;
  z-index: 10;
  text-align: center;
  max-width: 500px;
}

.logo-large {
  font-size: 5rem;
  font-weight: 900;
  letter-spacing: -3px;
  margin-bottom: 0.5rem;
}

.visual-tagline {
  font-size: 1.2rem;
  letter-spacing: 4px;
  text-transform: uppercase;
  color: var(--text-muted);
  font-weight: 600;
}

.visual-divider {
  width: 60px;
  height: 4px;
  background: var(--accent-primary);
  margin: 2rem auto;
  border-radius: 2px;
}

.visual-desc {
  color: var(--text-dim);
  line-height: 1.8;
  font-size: 1.1rem;
}

.form-side {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: radial-gradient(circle at center, #0c0c0e 0%, #050507 100%);
}

.login-box {
  width: 100%;
  max-width: 480px;
  padding: 3.5rem;
}

.login-header {
  margin-bottom: 3rem;
  text-align: center;
}

.login-header h2 {
  font-size: 2.2rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
}

.login-header p {
  color: var(--text-muted);
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 0.8rem;
}

.input-container {
  position: relative;
  display: flex;
  align-items: center;
}

.input-container .icon {
  position: absolute;
  left: 1.2rem;
  font-size: 1.2rem;
  opacity: 0.5;
}

.input-container input {
  width: 100%;
  padding: 1.2rem 1.2rem 1.2rem 3.5rem;
  background: rgba(255, 255, 255, 0.02);
}

.error-bubble {
  background: rgba(255, 51, 102, 0.1);
  border: 1px solid rgba(255, 51, 102, 0.2);
  color: #ff3366;
  padding: 1rem;
  border-radius: 12px;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.login-btn {
  height: 60px;
  font-size: 1.1rem;
}

.login-footer {
  margin-top: 2rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.forgot-link {
  font-size: 0.9rem;
  color: var(--text-dim);
  transition: color 0.3s;
}

.forgot-link:hover { color: var(--accent-primary); }

.register-text {
  font-size: 0.95rem;
  color: var(--text-muted);
}

.register-text a {
  color: var(--accent-primary);
  font-weight: 700;
}

@media (max-width: 992px) {
  .login-box { padding: 2.5rem; }
}
</style>
