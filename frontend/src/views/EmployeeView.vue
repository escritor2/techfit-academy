<template>
  <div class="dashboard-container animate-fade-in">
    <header class="dashboard-header">
      <h2>Painel da <span class="text-gradient">Recepção</span></h2>
      <p>Gerenciamento de entrada e atendimento aos membros.</p>
    </header>

    <div v-if="loading" class="loading-state text-muted">Carregando painel...</div>

    <div v-else>
      <!-- Stats rápidas -->
      <div class="stats-bar">
        <div class="glass-card mini-stat">
          <span class="mini-icon">🚪</span>
          <div><p class="mini-num text-gradient">{{ stats.total_entries || 0 }}</p><p class="mini-label">Entradas Hoje</p></div>
        </div>
        <div class="glass-card mini-stat">
          <span class="mini-icon">💰</span>
          <div><p class="mini-num text-gradient">R$ {{ parseFloat(stats.today_sales || 0).toFixed(2).replace('.', ',') }}</p><p class="mini-label">Vendas Hoje</p></div>
        </div>
        <div class="glass-card mini-stat">
          <span class="mini-icon">⚠️</span>
          <div><p class="mini-num" style="color: #fbbf24;">{{ (stats.expired_members || []).length }}</p><p class="mini-label">Inadimplentes</p></div>
        </div>
      </div>

      <div class="main-grid">
        <!-- Check-in -->
        <div class="glass-card section-card">
          <h3>🚪 Registrar Entrada</h3>
          <div class="checkin-container">
            <input 
              type="text" 
              v-model="cpf" 
              placeholder="CPF do Membro (ex: 222.222.222-22)" 
              class="checkin-input"
              @keyup.enter="doCheckin"
            />
            <button class="btn-primary" @click="doCheckin" :disabled="checkinLoading">
              {{ checkinLoading ? 'Buscando...' : '✅ Registrar' }}
            </button>
          </div>

          <!-- Feedback do check-in -->
          <div v-if="checkinResult" class="checkin-feedback animate-fade-in" :class="checkinResult.type">
            <p><strong>{{ checkinResult.member }}</strong></p>
            <p>{{ checkinResult.message }}</p>
            <p v-if="checkinResult.planWarning" class="plan-warning">⚠️ Membro sem plano ativo!</p>
          </div>
        </div>

        <!-- Cadastro rápido -->
        <div class="glass-card section-card">
          <h3>👤 Cadastro Rápido</h3>
          <form @submit.prevent="registerMember" class="register-form">
            <input v-model="newMember.name" placeholder="Nome completo" class="checkin-input" required />
            <input v-model="newMember.email" type="email" placeholder="Email" class="checkin-input" required />
            <input v-model="newMember.cpf" placeholder="CPF" class="checkin-input" />
            <input v-model="newMember.age" type="number" placeholder="Idade" class="checkin-input" />
            <input v-model="newMember.password" type="password" placeholder="Senha (min. 6)" class="checkin-input" required />
            <button type="submit" class="btn-primary" :disabled="registerLoading" style="width:100%;">
              {{ registerLoading ? 'Cadastrando...' : '📝 Cadastrar Membro' }}
            </button>
          </form>
        </div>

        <!-- Check-ins do dia -->
        <div class="glass-card section-card checkins-today">
          <h3>📋 Entradas de Hoje ({{ todayCheckins.length }})</h3>
          <div v-if="todayCheckins.length > 0" class="checkin-list">
            <div v-for="c in todayCheckins" :key="c.id" class="checkin-row">
              <div>
                <strong>{{ c.user?.name }}</strong>
                <span class="text-muted"> — {{ c.user?.cpf || 'Sem CPF' }}</span>
              </div>
              <span class="checkin-time">{{ formatTime(c.checked_in_at) }}</span>
            </div>
          </div>
          <p v-else class="text-muted" style="margin-top: 1rem;">Nenhuma entrada registrada hoje.</p>
        </div>

        <!-- Venda rápida -->
        <div class="glass-card section-card">
          <h3>🛒 Venda Rápida</h3>
          <p class="text-muted" style="margin-bottom: 1rem;">Venda de balcão para membros.</p>
          <router-link to="/loja" class="btn-primary" style="text-decoration:none; text-align:center; display:block;">
            Abrir Loja →
          </router-link>
        </div>

        <!-- Membros inadimplentes -->
        <div class="glass-card section-card" v-if="stats.expired_members && stats.expired_members.length > 0">
          <h3>⚠️ Membros Inadimplentes</h3>
          <div class="expired-list">
            <div v-for="m in stats.expired_members" :key="m.id" class="expired-row">
              <div>
                <strong>{{ m.name }}</strong>
                <span class="text-muted"> — {{ m.email }}</span>
              </div>
              <span class="badge expired">Sem Plano</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '../stores/notificationStore'

const notify = useNotificationStore()
const loading = ref(true)
const stats = ref({})
const todayCheckins = ref([])

// Check-in
const cpf = ref('')
const checkinLoading = ref(false)
const checkinResult = ref(null)

// Cadastro
const registerLoading = ref(false)
const newMember = ref({ name: '', email: '', cpf: '', age: '', password: '' })

function formatTime(dt) {
  if (!dt) return ''
  return new Date(dt).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

async function doCheckin() {
  if (!cpf.value.trim()) return
  checkinLoading.value = true
  checkinResult.value = null

  try {
    const res = await axios.post('/checkins', { cpf: cpf.value.trim() })
    checkinResult.value = {
      type: 'success',
      member: res.data.member?.name,
      message: res.data.message,
      planWarning: !res.data.has_active_plan,
    }
    cpf.value = ''
    // Refresh checkins
    const checkRes = await axios.get('/checkins/today')
    todayCheckins.value = checkRes.data
    // Update stats
    stats.value.total_entries = todayCheckins.value.length
    notify.success(`${res.data.member?.name} — Entrada registrada!`)
  } catch (e) {
    const msg = e.response?.data?.message || 'Erro ao registrar entrada.'
    checkinResult.value = {
      type: e.response?.status === 409 ? 'warning' : 'error',
      member: e.response?.data?.member?.name || '',
      message: msg,
    }
    if (e.response?.status === 409) {
      notify.warning(msg)
    } else {
      notify.error(msg)
    }
  } finally {
    checkinLoading.value = false
  }
}

async function registerMember() {
  registerLoading.value = true
  try {
    await axios.post('/admin/members', newMember.value)
    notify.success('Membro cadastrado com sucesso! 🎉')
    newMember.value = { name: '', email: '', cpf: '', age: '', password: '' }
  } catch (e) {
    const errors = e.response?.data?.errors
    if (errors) {
      const firstError = Object.values(errors)[0]?.[0]
      notify.error(firstError || 'Erro ao cadastrar.')
    } else {
      notify.error(e.response?.data?.message || 'Erro ao cadastrar membro.')
    }
  } finally {
    registerLoading.value = false
  }
}

onMounted(async () => {
  try {
    const [dashRes, checkRes] = await Promise.all([
      axios.get('/employee/dashboard'),
      axios.get('/checkins/today'),
    ])
    stats.value = dashRes.data
    todayCheckins.value = checkRes.data
  } catch (e) {
    console.error('Erro ao carregar painel da recepção', e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.dashboard-container { padding: 2rem 5%; max-width: 1300px; margin: 0 auto; }
.dashboard-header { margin-bottom: 2rem; }
.dashboard-header h2 { font-size: 2.5rem; }
.dashboard-header p { color: var(--text-muted); margin-top: 0.5rem; }
.loading-state { text-align: center; padding: 4rem 0; font-size: 1.1rem; }

/* Stats bar */
.stats-bar { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.mini-stat { display: flex; align-items: center; gap: 1rem; padding: 1.2rem !important; }
.mini-icon { font-size: 2rem; }
.mini-num { font-size: 1.4rem; font-weight: 900; }
.mini-label { font-size: 0.8rem; color: var(--text-muted); }

.main-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 1.5rem; }

/* Check-in */
.checkin-container { display: flex; gap: 1rem; margin-top: 1.5rem; }
.checkin-input {
  flex: 1; padding: 0.8rem 1rem;
  background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px; color: white; font-size: 1rem;
  transition: border-color 0.3s;
}
.checkin-input:focus { outline: none; border-color: var(--accent-primary); }

.checkin-feedback {
  margin-top: 1.5rem; padding: 1rem; border-radius: 12px;
}
.checkin-feedback.success { background: rgba(16,185,129,0.1); border: 1px solid #10b981; color: #34d399; }
.checkin-feedback.error { background: rgba(239,68,68,0.1); border: 1px solid #ef4444; color: #f87171; }
.checkin-feedback.warning { background: rgba(245,158,11,0.1); border: 1px solid #f59e0b; color: #fbbf24; }
.plan-warning { margin-top: 0.5rem; font-weight: 700; }

/* Register form */
.register-form { display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem; }

/* Checkin list */
.checkins-today { grid-column: span 2; }
.checkin-list { margin-top: 1rem; max-height: 400px; overflow-y: auto; }
.checkin-row { display: flex; justify-content: space-between; align-items: center; padding: 0.8rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; }
.checkin-time { color: var(--accent-primary); font-weight: 600; font-size: 0.85rem; }

/* Expired members */
.expired-list { margin-top: 1rem; }
.expired-row { display: flex; justify-content: space-between; align-items: center; padding: 0.8rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; }
.badge { padding: 0.3rem 0.6rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
.badge.expired { background: rgba(239,68,68,0.15); color: #f87171; }

@media (max-width: 900px) {
  .main-grid { grid-template-columns: 1fr; }
  .checkins-today { grid-column: auto; }
  .checkin-container { flex-direction: column; }
}
</style>
