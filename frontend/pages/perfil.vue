<template>
  <div class="page-container">
    <header class="page-header" v-reveal>
      <NuxtLink to="/painel" class="btn-text-dim">← Voltar ao Painel</NuxtLink>
      <h1 class="section-title">Meu <span class="text-gradient">Perfil</span></h1>
      <p class="subtitle">Personalize sua experiência TechFit e gerencie sua segurança.</p>
    </header>

    <div class="profile-layout">
      <!-- Sidebar / Identity -->
      <aside class="profile-sidebar glass-card" v-reveal>
        <div class="avatar-section">
          <div class="avatar-container">
            <img :src="avatarSrc" alt="Avatar" class="profile-avatar">
            <label for="avatar-upload" class="avatar-edit-btn">
              <span>📷</span>
            </label>
            <input id="avatar-upload" type="file" hidden accept="image/*" @change="onPhotoChange">
          </div>
          <div class="user-id">
            <h3>{{ authStore.user?.name }}</h3>
            <span class="role-badge">{{ roleName }}</span>
          </div>
        </div>

        <div class="sidebar-stats">
          <div class="sidebar-stat">
            <span class="stat-lab">Membro desde</span>
            <span class="stat-val">{{ formatDate(authStore.user?.created_at) }}</span>
          </div>
          <div class="sidebar-stat">
            <span class="stat-lab">Status</span>
            <span class="stat-val text-success">Ativo</span>
          </div>
        </div>
      </aside>

      <!-- Main Settings -->
      <main class="settings-main">
        <!-- Personal Data -->
        <section class="glass-card settings-section" v-reveal>
          <h3 class="section-heading">📋 Informações Pessoais</h3>
          <form @submit.prevent="saveProfile" class="settings-form">
            <div class="form-grid">
              <div class="form-group">
                <label>Nome Completo</label>
                <input v-model="form.name" type="text" placeholder="Como quer ser chamado?" required>
              </div>
              <div class="form-group">
                <label>E-mail Corporativo</label>
                <input :value="authStore.user?.email" type="email" disabled title="O e-mail não pode ser alterado">
              </div>
            </div>
            <div v-if="profileMsg" class="feedback-msg" :class="profileSuccess ? 'success' : 'error'">
              {{ profileMsg }}
            </div>
            <button type="submit" class="btn-primary" :disabled="savingProfile">
              {{ savingProfile ? 'Salvando...' : 'Salvar Alterações' }}
            </button>
          </form>
        </section>

        <!-- Password Change -->
        <section class="glass-card settings-section" v-reveal>
          <h3 class="section-heading">🔒 Segurança</h3>
          <form @submit.prevent="changePassword" class="settings-form">
            <div class="form-group">
              <label>Senha Atual</label>
              <input v-model="pwd.current" type="password" placeholder="••••••••" required>
            </div>
            <div class="form-grid">
              <div class="form-group">
                <label>Nova Senha</label>
                <input v-model="pwd.new" type="password" placeholder="Mín. 6 caracteres" required>
              </div>
              <div class="form-group">
                <label>Confirmar Senha</label>
                <input v-model="pwd.confirm" type="password" placeholder="Repita a nova senha" required>
              </div>
            </div>
            <div v-if="pwdMsg" class="feedback-msg" :class="pwdSuccess ? 'success' : 'error'">
              {{ pwdMsg }}
            </div>
            <button type="submit" class="btn-outline" :disabled="savingPwd">
              {{ savingPwd ? 'Processando...' : 'Alterar Senha' }}
            </button>
          </form>
        </section>

        <!-- Password Recovery -->
        <section class="glass-card settings-section" v-reveal>
          <h3 class="section-heading">📧 Recuperação de Acesso</h3>
          <p class="section-desc">Enviaremos um link de redefinição para o seu e-mail cadastrado.</p>
          <form @submit.prevent="sendForgotEmail" class="settings-form horizontal">
            <input v-model="forgotEmail" type="email" placeholder="seu@email.com" required>
            <button type="submit" class="btn-outline" :disabled="sendingForgot">
              {{ sendingForgot ? 'Enviando...' : 'Enviar Link' }}
            </button>
          </form>
          <div v-if="forgotMsg" class="feedback-msg" :class="forgotSuccess ? 'success' : 'error'">
            {{ forgotMsg }}
          </div>
        </section>
      </main>
    </div>
  </div>
</template>

<script setup>
const authStore = useAuthStore()
const config = useRuntimeConfig()

// Avatar handling
const avatarSrc = ref(`https://ui-avatars.com/api/?name=${encodeURIComponent(authStore.user?.name || 'User')}&background=00f2ff&color=000&bold=true&size=200`)

function onPhotoChange(e) {
  const file = e.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = (ev) => {
    avatarSrc.value = ev.target.result
    // Real implementation would upload to server
  }
  reader.readAsDataURL(file)
}

// Profile Data
const form = ref({ name: authStore.user?.name || '' })
const profileMsg = ref('')
const profileSuccess = ref(false)
const savingProfile = ref(false)

async function saveProfile() {
  savingProfile.value = true
  profileMsg.value = ''
  try {
    const response = await $fetch(`${config.public.apiBase}/profile`, {
      method: 'PUT',
      body: { name: form.value.name },
      headers: authStore.authHeaders
    })
    if (response.success && authStore.user) {
      authStore.user.name = form.value.name
      profileMsg.value = response.message || 'Perfil atualizado com sucesso!'
      profileSuccess.value = true
    }
  } catch (e) {
    profileMsg.value = e.response?._data?.message || 'Erro ao atualizar perfil.'
    profileSuccess.value = false
  } finally {
    savingProfile.value = false
  }
}

// Password
const pwd = ref({ current: '', new: '', confirm: '' })
const pwdMsg = ref('')
const pwdSuccess = ref(false)
const savingPwd = ref(false)

async function changePassword() {
  if (pwd.value.new !== pwd.value.confirm) {
    pwdMsg.value = 'As senhas não coincidem.'
    pwdSuccess.value = false
    return
  }
  savingPwd.value = true
  try {
    await $fetch(`${config.public.apiBase}/profile/password`, {
      method: 'PUT',
      body: {
        current_password: pwd.value.current,
        new_password: pwd.value.new,
        new_password_confirmation: pwd.value.confirm
      },
      headers: authStore.authHeaders
    })
    pwdMsg.value = 'Senha alterada com sucesso!'
    pwdSuccess.value = true
    pwd.value = { current: '', new: '', confirm: '' }
  } catch (e) {
    pwdMsg.value = 'Erro ao alterar senha. Verifique a senha atual.'
    pwdSuccess.value = false
  } finally {
    savingPwd.value = false
  }
}

// Recovery
const forgotEmail = ref(authStore.user?.email || '')
const forgotMsg = ref('')
const forgotSuccess = ref(false)
const sendingForgot = ref(false)

async function sendForgotEmail() {
  sendingForgot.value = true
  try {
    await $fetch(`${config.public.apiBase}/forgot-password`, {
      method: 'POST',
      body: { email: forgotEmail.value }
    })
    forgotMsg.value = 'Link de recuperação enviado!'
    forgotSuccess.value = true
  } catch (e) {
    forgotMsg.value = 'Erro ao enviar e-mail.'
    forgotSuccess.value = false
  } finally {
    sendingForgot.value = false
  }
}

const roleName = computed(() => {
  const roles = { admin: '👑 Administrador', employee: '🛎️ Recepcionista', member: '💪 Membro' }
  return roles[authStore.user?.role] || 'Usuário'
})

function formatDate(d) {
  if (!d) return '--'
  return new Date(d).toLocaleDateString('pt-BR', { year: 'numeric', month: 'long' })
}
</script>

<style scoped>
.page-header { margin-bottom: 4rem; }
.btn-text-dim { color: var(--text-dim); font-size: 0.9rem; font-weight: 600; margin-bottom: 1rem; display: inline-block; }
.btn-text-dim:hover { color: var(--accent-primary); }

.profile-layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 3rem;
  align-items: start;
}

@media (max-width: 992px) {
  .profile-layout { grid-template-columns: 1fr; }
}

.profile-sidebar {
  padding: 3rem 2rem;
  text-align: center;
  position: sticky;
  top: 100px;
}

.avatar-container {
  position: relative;
  width: 150px;
  height: 150px;
  margin: 0 auto 2rem;
}

.profile-avatar {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--accent-primary);
  box-shadow: 0 0 30px rgba(var(--accent-primary-rgb), 0.3);
}

.avatar-edit-btn {
  position: absolute;
  bottom: 5px;
  right: 5px;
  width: 44px;
  height: 44px;
  background: var(--bg-surface);
  border: 2px solid var(--accent-primary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s;
}

.avatar-edit-btn:hover { background: var(--accent-primary); transform: scale(1.1); }

.user-id h3 { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem; }
.role-badge { font-size: 0.8rem; font-weight: 700; color: var(--accent-primary); background: rgba(var(--accent-primary-rgb), 0.1); padding: 0.4rem 1rem; border-radius: 50px; }

.sidebar-stats {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid var(--glass-border);
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.sidebar-stat { display: flex; flex-direction: column; gap: 0.3rem; }
.stat-lab { font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; }
.stat-val { font-weight: 600; font-size: 1rem; }

.settings-main { display: flex; flex-direction: column; gap: 2.5rem; }

.settings-section { padding: 3rem; }
.section-heading { font-size: 1.4rem; font-weight: 800; margin-bottom: 2rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 1rem; }
.section-desc { color: var(--text-muted); margin-bottom: 2rem; font-size: 0.95rem; }

.settings-form { display: flex; flex-direction: column; gap: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
@media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }

.feedback-msg { padding: 1rem; border-radius: 12px; font-size: 0.9rem; font-weight: 600; }
.feedback-msg.success { background: rgba(0, 255, 170, 0.1); border: 1px solid var(--success); color: var(--success); }
.feedback-msg.error { background: rgba(255, 51, 102, 0.1); border: 1px solid var(--danger); color: var(--danger); }

.horizontal { display: flex; gap: 1rem; align-items: flex-end; }
.horizontal input { flex: 1; }
</style>
