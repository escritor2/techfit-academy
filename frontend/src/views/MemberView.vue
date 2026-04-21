<template>
  <div class="dashboard-container animate-fade-in">
    <header class="dashboard-header">
      <div class="header-left">
        <h2>Bem-vindo, <span class="text-gradient">{{ authStore.user?.name || 'Membro' }}</span></h2>
        <p>Seu painel pessoal de evolução e treinos.</p>
      </div>
      <router-link to="/perfil" class="profile-link">
        <img :src="avatarSrc" alt="Foto" class="header-avatar" />
        <span>Meu Perfil</span>
      </router-link>
    </header>

    <div v-if="loading" style="padding: 2rem 0;">
      <SkeletonLoader variant="stat" :count="4" />
    </div>

    <div v-else class="dashboard-grid">

      <!-- Treino Atual -->
      <div class="glass-card">
        <h3>🏋️ Treino Atual</h3>
        <div v-if="latestWorkout" class="workout-display">
          <p class="workout-name">{{ latestWorkout.goal }} — {{ latestWorkout.level }}</p>
          <p class="text-muted workout-preview">{{ workoutPreview }}</p>
        </div>
        <p v-else class="text-muted" style="margin-top: 1rem;">Nenhuma ficha gerada ainda.</p>
        <div class="workout-actions">
          <router-link to="/ai" class="btn-primary" style="text-decoration: none; text-align: center;">
            {{ latestWorkout ? '🔄 Gerar Novo Treino' : '✨ Gerar Ficha com IA' }}
          </router-link>
        </div>
      </div>

      <!-- Meu Plano -->
      <div class="glass-card">
        <h3>💳 Meu Plano</h3>
        <div v-if="subscription" class="plan-display">
          <div class="plan-badge active">✅ {{ subscription.plan?.name || 'Plano Ativo' }}</div>
          <div class="glass-card stat-card">
            <span class="stat-icon">⚖️</span>
            <div>
              <p class="stat-label">Peso Atual</p>
              <p class="stat-value">{{ authStore.user?.body_metrics?.[0]?.weight || '--' }} kg</p>
              <router-link to="/metricas" class="stat-link">Ver medidas →</router-link>
            </div>
          </div>
          <div class="plan-details">
            <div class="plan-item">
              <span class="text-muted">Tipo</span>
              <span>{{ subscription.plan?.name }}</span>
            </div>
            <div class="plan-item">
              <span class="text-muted">Início</span>
              <span>{{ formatDate(subscription.starts_at) }}</span>
            </div>
            <div class="plan-item">
              <span class="text-muted">Vencimento</span>
              <span class="highlight">{{ formatDate(subscription.expires_at) }}</span>
            </div>
          </div>
        </div>
        <div v-else class="no-plan">
          <p class="text-muted">⚠️ Você não possui um plano ativo.</p>
          <p class="text-muted small">Procure a recepção para ativar seu plano.</p>
        </div>
      </div>

      <!-- Seu Progresso -->
      <div class="glass-card">
        <h3>📈 Seu Progresso</h3>
        <div class="progress-bar">
          <div class="progress" :style="{ width: progressPercent + '%' }"></div>
        </div>
        <p class="text-muted">{{ monthlyCheckins }}/20 treinos este mês</p>
        <div class="stat-row">
          <div class="stat"><span class="stat-num text-gradient">{{ monthlyCheckins }}</span><span class="stat-label">Check-ins</span></div>
          <div class="stat"><span class="stat-num text-gradient">{{ Math.ceil(monthlyCheckins / 4) }}</span><span class="stat-label">Semanas</span></div>
          <div class="stat"><span class="stat-num text-gradient">{{ progressPercent }}%</span><span class="stat-label">Assiduidade</span></div>
        </div>
      </div>

      <!-- Próximas Aulas -->
      <div class="glass-card">
        <h3>📅 Minhas Aulas</h3>
        <ul v-if="bookings.length > 0" class="booking-list">
          <li v-for="b in bookings" :key="b.id">
            <div>
              <strong>{{ b.gym_class?.name }}</strong>
              <span class="text-muted"> — {{ b.gym_class?.day_of_week }} {{ b.gym_class?.start_time?.substring(0,5) }}</span>
            </div>
            <button class="btn-cancel" @click="cancelBooking(b.id)">Cancelar</button>
          </li>
        </ul>
        <p v-else class="text-muted" style="margin-top: 1rem;">Nenhuma aula reservada.</p>
        <button class="btn-outline" style="width:100%; margin-top: 1.5rem;" @click="showClasses = !showClasses">
          {{ showClasses ? 'Fechar' : '📋 Ver Aulas Disponíveis' }}
        </button>

        <!-- Aulas disponíveis -->
        <div v-if="showClasses" class="available-classes animate-fade-in">
          <div v-for="c in availableClasses" :key="c.id" class="class-item">
            <div>
              <strong>{{ c.name }}</strong>
              <span class="text-muted"> — {{ c.day_of_week }} {{ c.start_time?.substring(0,5) }}-{{ c.end_time?.substring(0,5) }}</span>
              <span class="spots-badge">{{ c.spots_available }} vagas</span>
            </div>
            <button 
              class="btn-primary" style="padding: 0.4rem 1rem; font-size: 0.8rem;"
              @click="bookClass(c.id)"
              :disabled="c.spots_available <= 0"
            >
              Reservar
            </button>
          </div>
        </div>
      </div>

      <!-- Compras Recentes -->
      <div class="glass-card">
        <h3>🛍️ Compras Recentes</h3>
        <ul v-if="purchases.length > 0" class="purchase-list">
          <li v-for="sale in purchases" :key="sale.id">
            <span>Pedido #{{ sale.id }}</span>
            <span class="text-gradient">R$ {{ parseFloat(sale.total_price).toFixed(2).replace('.', ',') }}</span>
          </li>
        </ul>
        <p v-else class="text-muted" style="margin-top: 1rem;">Nenhuma compra recente encontrada.</p>
        <router-link to="/loja" class="btn-outline" style="text-decoration:none; text-align:center; display:block; margin-top: 1.5rem;">
          Ir para a Loja →
        </router-link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/authStore'
import { useNotificationStore } from '../stores/notificationStore'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import axios from 'axios'

const authStore = useAuthStore()
const notify = useNotificationStore()
const loading = ref(true)

const purchases = ref([])
const subscription = ref(null)
const latestWorkout = ref(null)
const monthlyCheckins = ref(0)
const bookings = ref([])
const availableClasses = ref([])
const showClasses = ref(false)

// Avatar
const AVATAR_KEY = computed(() => `techfit_avatar_${authStore.user?.id}`)
const avatarSrc = ref('')

const progressPercent = computed(() => Math.min(100, Math.round((monthlyCheckins.value / 20) * 100)))

const workoutPreview = computed(() => {
  if (!latestWorkout.value?.content) return ''
  return latestWorkout.value.content
    .replace(/[#*|>\-]/g, '')
    .substring(0, 120)
    .trim() + '...'
})

function formatDate(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('pt-BR')
}

async function cancelBooking(id) {
  try {
    await axios.delete(`/class-bookings/${id}`)
    bookings.value = bookings.value.filter(b => b.id !== id)
    notify.success('Reserva cancelada.')
  } catch (e) {
    notify.error('Erro ao cancelar reserva.')
  }
}

async function bookClass(classId) {
  try {
    await axios.post('/class-bookings', { gym_class_id: classId })
    notify.success('Aula reservada com sucesso! 🎉')
    // Refresh
    const [bookRes, classRes] = await Promise.all([
      axios.get('/my/bookings'),
      axios.get('/gym-classes'),
    ])
    bookings.value = bookRes.data
    availableClasses.value = classRes.data
  } catch (e) {
    notify.error(e.response?.data?.message || 'Erro ao reservar aula.')
  }
}

onMounted(async () => {
  avatarSrc.value = localStorage.getItem(AVATAR_KEY.value)
    || `https://ui-avatars.com/api/?name=${encodeURIComponent(authStore.user?.name || 'User')}&background=00f2ff&color=000&bold=true&size=200`

  try {
    const [dashRes, bookRes, classRes] = await Promise.all([
      axios.get('/member/dashboard'),
      axios.get('/my/bookings'),
      axios.get('/gym-classes'),
    ])

    purchases.value = dashRes.data.recent_purchases || []
    subscription.value = dashRes.data.subscription
    latestWorkout.value = dashRes.data.latest_workout
    monthlyCheckins.value = dashRes.data.monthly_checkins || 0
    bookings.value = bookRes.data || []
    availableClasses.value = classRes.data || []
  } catch (error) {
    console.error('Erro ao buscar dados do membro', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.dashboard-container { padding: 2rem 5%; max-width: 1200px; margin: 0 auto; }
.dashboard-header { margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; }
.dashboard-header h2 { font-size: 2.5rem; }
.header-left p { color: var(--text-muted); margin-top: 0.5rem; }
.loading-state { text-align: center; padding: 4rem 0; font-size: 1.1rem; }

.profile-link {
  display: flex; align-items: center; gap: 0.7rem;
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 50px; padding: 0.5rem 1rem 0.5rem 0.5rem;
  text-decoration: none; color: white; font-size: 0.9rem; font-weight: 600;
  transition: border-color 0.3s, background 0.3s;
}
.profile-link:hover { border-color: var(--accent-primary); background: rgba(0,242,255,0.05); }

.header-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent-primary); }

.dashboard-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }

.workout-name { font-weight: 700; font-size: 1rem; margin: 1rem 0 0.4rem; color: var(--accent-primary); }
.workout-preview { font-size: 0.85rem; line-height: 1.5; }
.workout-actions { display: flex; flex-direction: column; gap: 1rem; margin-top: 1.5rem; }

.plan-display { margin-top: 1rem; }
.plan-badge { display: inline-block; padding: 5px 14px; border-radius: 50px; font-size: 0.85rem; font-weight: 600; margin-bottom: 1.2rem; background: rgba(0,255,136,0.1); border: 1px solid #00ff88; color: #00ff88; }
.plan-details { display: flex; flex-direction: column; gap: 0.8rem; }
.plan-item { display: flex; justify-content: space-between; font-size: 0.9rem; padding-bottom: 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
.highlight { color: var(--accent-secondary); font-weight: 700; }
.no-plan { margin-top: 1rem; }
.no-plan .small { font-size: 0.8rem; margin-top: 0.5rem; }

.progress-bar { background: rgba(255,255,255,0.1); height: 10px; border-radius: 5px; margin: 1.5rem 0 0.5rem; overflow: hidden; }
.progress { background: var(--accent-primary); height: 100%; box-shadow: 0 0 10px var(--accent-primary); transition: width 0.6s ease; }

.stat-row { display: flex; justify-content: space-around; margin-top: 1.5rem; }
.stat { display: flex; flex-direction: column; align-items: center; gap: 0.3rem; }
.stat-num { font-size: 1.6rem; font-weight: 900; }
.stat-label { font-size: 0.75rem; color: var(--text-muted); }

.booking-list, .purchase-list { list-style: none; padding: 0; margin-top: 1rem; }
.booking-list li, .purchase-list li { display: flex; justify-content: space-between; align-items: center; padding: 0.8rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; }

.btn-cancel { background: none; border: 1px solid #ef4444; color: #ef4444; padding: 0.3rem 0.8rem; border-radius: 8px; font-size: 0.75rem; cursor: pointer; transition: all 0.3s; }
.btn-cancel:hover { background: rgba(239,68,68,0.1); }

.available-classes { margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.8rem; }
.class-item { display: flex; justify-content: space-between; align-items: center; padding: 0.8rem; background: rgba(255,255,255,0.03); border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); }
.spots-badge { margin-left: 0.5rem; background: rgba(0,242,255,0.1); color: var(--accent-primary); padding: 2px 8px; border-radius: 50px; font-size: 0.7rem; font-weight: 600; }

/* ── Mobile Responsiveness ── */
@media (max-width: 768px) {
  .dashboard-container { padding: 1.5rem 4%; }
  .dashboard-header { flex-direction: column; align-items: flex-start; margin-bottom: 2rem; }
  .dashboard-header h2 { font-size: 1.6rem; }
  .dashboard-grid { grid-template-columns: 1fr; gap: 1rem; }
  .stat-row { flex-wrap: wrap; gap: 1rem; }
  .stat-num { font-size: 1.3rem; }
  .class-item { flex-direction: column; align-items: flex-start; gap: 0.8rem; }
  .class-item .btn-primary { width: 100%; }
  .booking-list li { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
  .purchase-list li { font-size: 0.85rem; }
  .plan-item { font-size: 0.85rem; }
  .stat-card { flex-direction: column; text-align: center; }
}

@media (max-width: 480px) {
  .dashboard-header h2 { font-size: 1.3rem; }
  .profile-link span { display: none; }
}
</style>
