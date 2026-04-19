<template>
  <div class="profile-container animate-fade-in">
    <header class="profile-header">
      <button class="back-btn" @click="$router.back()">← Voltar</button>
      <h2>Meu <span class="text-gradient">Perfil</span></h2>
      <p>Gerencie suas informações pessoais e segurança</p>
    </header>

    <div class="profile-grid">

      <!-- Card: Foto de Perfil -->
      <div class="glass-card photo-card">
        <div class="avatar-wrapper">
          <img :src="avatarSrc" alt="Foto de perfil" class="avatar" />
          <label class="avatar-overlay" for="photo-upload">
            <span>📷</span>
          </label>
          <input id="photo-upload" type="file" accept="image/*" @change="onPhotoChange" hidden />
        </div>
        <h3>{{ authStore.user?.name }}</h3>
        <p class="role-badge">{{ roleName }}</p>
      </div>

      <!-- Card: Dados Pessoais -->
      <div class="glass-card info-card">
        <h3 class="card-title">📋 Dados Pessoais</h3>

        <form @submit.prevent="saveProfile">
          <div class="form-group">
            <label>Nome Completo</label>
            <input v-model="form.name" type="text" class="input-field" placeholder="Seu nome" required />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input :value="authStore.user?.email" type="email" class="input-field" disabled />
            <small>O email não pode ser alterado.</small>
          </div>
          <div v-if="profileMsg" class="msg" :class="profileSuccess ? 'success' : 'error'">
            {{ profileMsg }}
          </div>
          <button type="submit" class="btn-primary w-full" :disabled="savingProfile">
            {{ savingProfile ? 'Salvando...' : '💾 Salvar Alterações' }}
          </button>
        </form>
      </div>

      <!-- Card: Alterar Senha -->
      <div class="glass-card password-card">
        <h3 class="card-title">🔒 Alterar Senha</h3>

        <form @submit.prevent="changePassword">
          <div class="form-group">
            <label>Senha Atual</label>
            <input v-model="pwd.current" type="password" class="input-field" placeholder="••••••••" required />
          </div>
          <div class="form-group">
            <label>Nova Senha</label>
            <input v-model="pwd.new" type="password" class="input-field" placeholder="Mínimo 6 caracteres" required />
          </div>
          <div class="form-group">
            <label>Confirmar Nova Senha</label>
            <input v-model="pwd.confirm" type="password" class="input-field" placeholder="Repita a nova senha" required />
          </div>
          <div v-if="pwdMsg" class="msg" :class="pwdSuccess ? 'success' : 'error'">
            {{ pwdMsg }}
          </div>
          <button type="submit" class="btn-primary w-full" :disabled="savingPwd">
            {{ savingPwd ? 'Alterando...' : '🔑 Alterar Senha' }}
          </button>
        </form>
      </div>

      <!-- Card: Recuperação de Senha -->
      <div class="glass-card forgot-card">
        <h3 class="card-title">📧 Recuperação de Senha</h3>
        <p class="text-muted">Esqueceu sua senha? Informe seu email e enviaremos um link de redefinição.</p>

        <form @submit.prevent="sendForgotEmail">
          <div class="form-group">
            <label>Email Cadastrado</label>
            <input v-model="forgotEmail" type="email" class="input-field" placeholder="seu@email.com" required />
          </div>
          <div v-if="forgotMsg" class="msg" :class="forgotSuccess ? 'success' : 'error'">
            {{ forgotMsg }}
          </div>
          <button type="submit" class="btn-outline w-full" :disabled="sendingForgot">
            {{ sendingForgot ? 'Enviando...' : '📨 Enviar Link de Recuperação' }}
          </button>
        </form>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/authStore'
import axios from 'axios'

const authStore = useAuthStore()

// Foto de perfil
const AVATAR_KEY = `techfit_avatar_${authStore.user?.id}`
const avatarSrc = ref(localStorage.getItem(AVATAR_KEY) || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(authStore.user?.name || 'User') + '&background=00f2ff&color=000&bold=true&size=200')

function onPhotoChange(e) {
  const file = e.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = (ev) => {
    avatarSrc.value = ev.target.result
    localStorage.setItem(AVATAR_KEY, ev.target.result)
  }
  reader.readAsDataURL(file)
}

// Perfil
const form = ref({ name: authStore.user?.name || '' })
const profileMsg = ref('')
const profileSuccess = ref(false)
const savingProfile = ref(false)

async function saveProfile() {
  savingProfile.value = true
  profileMsg.value = ''
  try {
    const res = await axios.put('/profile', { name: form.value.name })
    authStore.user.name = form.value.name
    localStorage.setItem('user', JSON.stringify(authStore.user))
    profileMsg.value = res.data.message
    profileSuccess.value = true
  } catch (e) {
    profileMsg.value = e.response?.data?.message || 'Erro ao salvar.'
    profileSuccess.value = false
  } finally {
    savingProfile.value = false
  }
}

// Senha
const pwd = ref({ current: '', new: '', confirm: '' })
const pwdMsg = ref('')
const pwdSuccess = ref(false)
const savingPwd = ref(false)

async function changePassword() {
  pwdMsg.value = ''
  if (pwd.value.new !== pwd.value.confirm) {
    pwdMsg.value = 'As senhas não coincidem.'
    pwdSuccess.value = false
    return
  }
  savingPwd.value = true
  try {
    const res = await axios.put('/profile/password', {
      current_password: pwd.value.current,
      new_password: pwd.value.new,
      new_password_confirmation: pwd.value.confirm
    })
    pwdMsg.value = res.data.message
    pwdSuccess.value = true
    pwd.value = { current: '', new: '', confirm: '' }
  } catch (e) {
    pwdMsg.value = e.response?.data?.message || 'Erro ao alterar senha.'
    pwdSuccess.value = false
  } finally {
    savingPwd.value = false
  }
}

// Recuperação de senha
const forgotEmail = ref(authStore.user?.email || '')
const forgotMsg = ref('')
const forgotSuccess = ref(false)
const sendingForgot = ref(false)

async function sendForgotEmail() {
  forgotMsg.value = ''
  sendingForgot.value = true
  try {
    const res = await axios.post('/forgot-password', { email: forgotEmail.value })
    forgotMsg.value = res.data.message
    forgotSuccess.value = true
  } catch (e) {
    forgotMsg.value = e.response?.data?.message || 'Erro ao enviar email.'
    forgotSuccess.value = false
  } finally {
    sendingForgot.value = false
  }
}

const roleName = computed(() => {
  const roles = { admin: '👑 Administrador', employee: '🛎️ Recepcionista', member: '💪 Membro' }
  return roles[authStore.user?.role] || 'Usuário'
})
</script>

<style scoped>
.profile-container {
  padding: 2rem 5%;
  max-width: 1200px;
  margin: 0 auto;
}

.profile-header {
  margin-bottom: 2.5rem;
}

.profile-header h2 {
  font-size: 2.5rem;
  margin: 0.5rem 0 0.3rem;
}

.profile-header p {
  color: var(--text-muted);
}

.back-btn {
  background: none;
  border: none;
  color: var(--accent-primary);
  cursor: pointer;
  font-size: 0.9rem;
  padding: 0;
  margin-bottom: 0.5rem;
}

.back-btn:hover { text-decoration: underline; }

.profile-grid {
  display: grid;
  grid-template-columns: 260px 1fr;
  grid-template-rows: auto auto;
  gap: 1.5rem;
}

/* Foto ocupa as 2 linhas à esquerda */
.photo-card {
  grid-row: 1 / 3;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 2rem 1.5rem;
}

.avatar-wrapper {
  position: relative;
  width: 130px;
  height: 130px;
  margin-bottom: 1.5rem;
}

.avatar {
  width: 130px;
  height: 130px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--accent-primary);
  box-shadow: 0 0 20px rgba(0, 242, 255, 0.3);
}

.avatar-overlay {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.3s;
}

.avatar-wrapper:hover .avatar-overlay { opacity: 1; }

.photo-card h3 {
  font-size: 1.2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.role-badge {
  background: rgba(0, 242, 255, 0.1);
  border: 1px solid var(--accent-primary);
  color: var(--accent-primary);
  padding: 4px 14px;
  border-radius: 50px;
  font-size: 0.8rem;
  font-weight: 600;
}

.card-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(255,255,255,0.07);
}

.form-group {
  margin-bottom: 1.2rem;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  color: var(--text-muted);
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.form-group small {
  color: var(--text-muted);
  font-size: 0.75rem;
  margin-top: 0.3rem;
  display: block;
}

.input-field {
  width: 100%;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 0.75rem 1rem;
  color: white;
  font-size: 0.95rem;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

.input-field:focus {
  outline: none;
  border-color: var(--accent-primary);
  box-shadow: 0 0 0 3px rgba(0, 242, 255, 0.1);
}

.input-field:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.w-full { width: 100%; margin-top: 0.5rem; }

.msg {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.85rem;
  margin-bottom: 1rem;
  font-weight: 500;
}

.msg.success {
  background: rgba(0, 255, 136, 0.1);
  border: 1px solid #00ff88;
  color: #00ff88;
}

.msg.error {
  background: rgba(255, 80, 80, 0.1);
  border: 1px solid #ff5050;
  color: #ff5050;
}

.forgot-card p { margin-bottom: 1.5rem; font-size: 0.9rem; }

@media (max-width: 768px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }
  .photo-card { grid-row: auto; }
}
</style>
