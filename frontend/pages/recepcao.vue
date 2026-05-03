<template>
  <div class="page-container">
    <header class="admin-header" v-reveal>
      <div class="header-main">
        <h1 class="section-title">Recepção <span class="text-gradient">TechFit</span></h1>
        <p class="subtitle">Gestão de acesso e atendimento em tempo real.</p>
      </div>
      <div class="status-live">
        <span class="pulse"></span>
        LIVE DASHBOARD
      </div>
    </header>

    <div v-if="loading" class="loading-state">
      <SkeletonLoader variant="stat" :count="3" />
    </div>

    <div v-else class="recepcao-content">
      <!-- Quick Stats -->
      <section class="stats-row" v-reveal>
        <div class="mini-card glass-card">
          <span class="icon">🚪</span>
          <div class="data">
            <span class="val">{{ stats.total_entries || 0 }}</span>
            <span class="lab">Entradas Hoje</span>
          </div>
        </div>
        <div class="mini-card glass-card">
          <span class="icon">💰</span>
          <div class="data">
            <span class="val">R$ {{ formatMoney(stats.today_sales) }}</span>
            <span class="lab">Vendas Hoje</span>
          </div>
        </div>
        <div class="mini-card glass-card warning">
          <span class="icon">⚠️</span>
          <div class="data">
            <span class="val">{{ stats.expired_members?.length || 0 }}</span>
            <span class="lab">Vencidos</span>
          </div>
        </div>
      </section>

      <div class="recepcao-grid">
        <!-- Check-in Action -->
        <section class="glass-card action-section" v-reveal>
          <div class="section-head">
            <span class="s-icon">🔑</span>
            <h3>Registrar Acesso</h3>
          </div>
          <div class="checkin-box">
            <input 
              v-model="cpf" 
              placeholder="CPF do Membro" 
              class="checkin-input"
              @keyup.enter="doCheckin"
            >
            <button class="btn-primary" @click="doCheckin" :disabled="checkinLoading">
              {{ checkinLoading ? 'Validando...' : 'Confirmar' }}
            </button>
          </div>

          <Transition name="fade-up">
            <div v-if="checkinResult" class="feedback-card" :class="checkinResult.type">
              <div class="f-info">
                <strong>{{ checkinResult.member }}</strong>
                <span>{{ checkinResult.message }}</span>
              </div>
              <div v-if="checkinResult.planWarning" class="alert-pill">DÉBITO PENDENTE</div>
            </div>
          </Transition>
        </section>

        <!-- Fast Registration -->
        <section class="glass-card action-section" v-reveal>
          <div class="section-head">
            <span class="s-icon">📝</span>
            <h3>Cadastro Rápido</h3>
          </div>
          <form @submit.prevent="registerMember" class="fast-form">
            <input v-model="newMember.name" placeholder="Nome Completo" required>
            <input v-model="newMember.email" type="email" placeholder="E-mail" required>
            <input v-model="newMember.cpf" placeholder="CPF">
            <button type="submit" class="btn-outline" :disabled="registerLoading">
              {{ registerLoading ? 'Cadastrando...' : 'Finalizar Cadastro' }}
            </button>
          </form>
        </section>

        <!-- Live Feed -->
        <section class="glass-card list-section wide" v-reveal>
          <div class="section-head">
            <span class="s-icon">📋</span>
            <h3>Entradas Recentes</h3>
          </div>
          <div class="feed-list">
            <div v-for="c in todayCheckins" :key="c.id" class="feed-item">
              <div class="f-user">
                <strong>{{ c.user?.name }}</strong>
                <span>{{ c.user?.cpf || 'Visitante' }}</span>
              </div>
              <span class="f-time">{{ formatTime(c.checked_in_at) }}</span>
            </div>
          </div>
          <div v-if="todayCheckins.length === 0" class="empty-feed">
            Aguardando primeiras entradas...
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
const authStore = useAuthStore()
const config = useRuntimeConfig()
const loading = ref(true)

const stats = ref({})
const todayCheckins = ref([])
const cpf = ref('')
const checkinLoading = ref(false)
const checkinResult = ref(null)

const registerLoading = ref(false)
const newMember = ref({ name: '', email: '', cpf: '', password: 'techfit123' })

function formatMoney(v) { return parseFloat(v || 0).toFixed(2).replace('.', ',') }
function formatTime(dt) { return new Date(dt).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) }

async function loadData() {
  try {
    const dash = await $fetch(`${config.public.apiBase}/employee/dashboard`, { headers: authStore.authHeaders })
    const checks = await $fetch(`${config.public.apiBase}/checkins/today`, { headers: authStore.authHeaders })
    stats.value = dash
    todayCheckins.value = checks
  } catch (e) { }
  finally { loading.value = false }
}

async function doCheckin() {
  if (!cpf.value) return
  checkinLoading.value = true
  checkinResult.value = null
  try {
    const res = await $fetch(`${config.public.apiBase}/checkins`, {
      method: 'POST',
      body: { cpf: cpf.value },
      headers: authStore.authHeaders
    })
    checkinResult.value = {
      type: 'success',
      member: res.member?.name,
      message: res.message,
      planWarning: !res.has_active_plan
    }
    cpf.value = ''
    loadData()
  } catch (e) {
    checkinResult.value = {
      type: 'error',
      member: e.data?.member?.name || 'Erro',
      message: e.data?.message || 'Falha ao registrar.'
    }
  } finally { checkinLoading.value = false }
}

async function registerMember() {
  registerLoading.value = true
  try {
    await $fetch(`${config.public.apiBase}/admin/members`, {
      method: 'POST',
      body: newMember.value,
      headers: authStore.authHeaders
    })
    alert('Membro cadastrado!')
    newMember.value = { name: '', email: '', cpf: '', password: 'techfit123' }
  } catch (e) { alert('Erro no cadastro.') }
  finally { registerLoading.value = false }
}

onMounted(loadData)
</script>

<style scoped>
.admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4rem; }

.status-live {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  font-size: 0.75rem;
  font-weight: 800;
  color: var(--accent-primary);
  letter-spacing: 1px;
}

.pulse {
  width: 10px;
  height: 10px;
  background: var(--accent-primary);
  border-radius: 50%;
  animation: pulse-ring 1.5s infinite;
}

@keyframes pulse-ring {
  0% { transform: scale(0.5); opacity: 0.8; }
  100% { transform: scale(2.5); opacity: 0; }
}

.stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2.5rem; }

.mini-card { padding: 1.5rem; display: flex; align-items: center; gap: 1.5rem; }
.mini-card .icon { font-size: 1.8rem; background: rgba(255,255,255,0.03); width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
.mini-card .val { display: block; font-size: 1.4rem; font-weight: 900; }
.mini-card .lab { font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; }
.mini-card.warning .val { color: var(--warning); }

.recepcao-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
@media (max-width: 992px) { .recepcao-grid { grid-template-columns: 1fr; } }

.action-section { padding: 2.5rem; }
.section-head { display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; }
.s-icon { font-size: 1.4rem; }
.section-head h3 { font-size: 1.1rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; }

.checkin-box { display: flex; gap: 1rem; }
.checkin-input { flex: 1; padding: 1rem 1.5rem; background: rgba(0,0,0,0.2); }

.feedback-card {
  margin-top: 2rem;
  padding: 1.5rem;
  border-radius: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.feedback-card.success { background: rgba(0, 255, 170, 0.1); border: 1px solid var(--success); color: var(--success); }
.feedback-card.error { background: rgba(255, 51, 102, 0.1); border: 1px solid var(--danger); color: #ff3366; }

.f-info { display: flex; flex-direction: column; }
.f-info strong { font-size: 1.1rem; }
.f-info span { font-size: 0.85rem; opacity: 0.8; }

.alert-pill { background: var(--danger); color: #fff; padding: 0.3rem 0.8rem; border-radius: 4px; font-size: 0.7rem; font-weight: 900; }

.fast-form { display: flex; flex-direction: column; gap: 1.2rem; }
.fast-form input { background: rgba(255,255,255,0.03); }

.list-section { padding: 2.5rem; }
.wide { grid-column: span 2; }
@media (max-width: 992px) { .wide { grid-column: span 1; } }

.feed-list { display: flex; flex-direction: column; gap: 1rem; max-height: 400px; overflow-y: auto; }
.feed-item { display: flex; justify-content: space-between; align-items: center; padding: 1.2rem; background: rgba(255,255,255,0.02); border-radius: 14px; border: 1px solid var(--glass-border); }
.f-user { display: flex; flex-direction: column; }
.f-user strong { font-size: 1rem; }
.f-user span { font-size: 0.8rem; color: var(--text-dim); }
.f-time { font-weight: 800; color: var(--accent-primary); font-size: 0.9rem; }

.empty-feed { text-align: center; padding: 4rem; color: var(--text-dim); font-style: italic; }
</style>
