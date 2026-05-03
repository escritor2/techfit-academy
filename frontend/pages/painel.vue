<template>
  <div class="page-container">
    <header class="dashboard-header" v-reveal>
      <div class="user-intro">
        <div class="avatar-wrapper">
          <img :src="avatarSrc" alt="Avatar" class="user-avatar">
          <div class="status-indicator"></div>
        </div>
        <div class="user-text">
          <h1>Bem-vindo, <span class="text-gradient">{{ authStore.user?.name }}</span></h1>
          <p>Seu centro de comando para performance extrema.</p>
        </div>
      </div>
      <NuxtLink to="/perfil" class="btn-outline btn-small">Configurações de Perfil</NuxtLink>
    </header>

    <div v-if="loading" class="loading-grid">
      <SkeletonLoader variant="stat" :count="4" />
    </div>

    <div v-else class="dashboard-grid">
      <!-- Status Card -->
      <section v-reveal class="glass-card info-card">
        <div class="card-icon">⚡</div>
        <div class="card-content">
          <h3>Treino do Dia</h3>
          <div v-if="latestWorkout" class="workout-info">
            <p class="workout-title">{{ latestWorkout.goal }}</p>
            <p class="workout-level">{{ latestWorkout.level }}</p>
            <div class="workout-excerpt">{{ workoutPreview }}</div>
          </div>
          <p v-else class="text-dim">Nenhuma ficha ativa no momento.</p>
          <NuxtLink to="/ai" class="btn-primary btn-full">{{ latestWorkout ? 'Atualizar Treino' : 'Gerar Ficha IA' }}</NuxtLink>
        </div>
      </section>

      <!-- Plan Card -->
      <section v-reveal class="glass-card info-card">
        <div class="card-icon">💎</div>
        <div class="card-content">
          <h3>Assinatura Ativa</h3>
          <div v-if="subscription" class="plan-info">
            <div class="plan-tag">{{ subscription.plan?.name }}</div>
            <div class="plan-expiry">Vence em: <span class="text-gradient">{{ formatDate(subscription.expires_at) }}</span></div>
            <div class="quick-metric">
              <span class="m-label">Peso Atual</span>
              <span class="m-value">{{ authStore.user?.body_metrics?.[0]?.weight || '--' }} kg</span>
            </div>
          </div>
          <div v-else class="no-plan-msg">
            <p>Nenhum plano ativo encontrado.</p>
            <NuxtLink to="/planos" class="btn-link">Ver Planos →</NuxtLink>
          </div>
        </div>
      </section>

      <!-- Progress Card -->
      <section v-reveal class="glass-card info-card wide-card">
        <div class="card-icon">🚀</div>
        <div class="card-content">
          <h3>Sua Jornada</h3>
          <div class="assiduity-box">
            <div class="assiduity-header">
              <span>Assiduidade Mensal</span>
              <span class="text-glow">{{ progressPercent }}%</span>
            </div>
            <div class="assiduity-track">
              <div class="assiduity-fill" :style="{ width: progressPercent + '%' }"></div>
            </div>
            <div class="assiduity-footer">
              <span>{{ monthlyCheckins }} de 20 treinos completados</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Bookings Card -->
      <section v-reveal class="glass-card list-card">
        <div class="card-header">
          <h3>📅 Minhas Aulas</h3>
          <button @click="showClasses = !showClasses" class="btn-icon-small">➕</button>
        </div>
        
        <div v-if="bookings.length > 0" class="scroll-list">
          <div v-for="b in bookings" :key="b.id" class="list-item">
            <div class="item-main">
              <strong>{{ b.gym_class?.name }}</strong>
              <span>{{ b.gym_class?.day_of_week }} às {{ b.gym_class?.start_time?.substring(0,5) }}</span>
            </div>
            <button class="btn-cancel" @click="cancelBooking(b.id)">✕</button>
          </div>
        </div>
        <p v-else class="text-dim empty-msg">Você não tem reservas.</p>

        <!-- Booking Modal-like Area -->
        <Transition name="fade-up">
          <div v-if="showClasses" class="available-overlay">
            <h4>Aulas Disponíveis</h4>
            <div class="available-list">
              <div v-for="c in availableClasses" :key="c.id" class="available-item">
                <div class="c-info">
                  <strong>{{ c.name }}</strong>
                  <span>{{ c.day_of_week }} • {{ c.start_time?.substring(0,5) }}</span>
                </div>
                <button class="btn-primary btn-xs" @click="bookClass(c.id)" :disabled="c.spots_available <= 0">Reservar</button>
              </div>
            </div>
            <button class="btn-text-dim" @click="showClasses = false">Fechar</button>
          </div>
        </Transition>
      </section>

    </div>

    <!-- Booking Premium Modal -->
    <Transition name="fade-up">
      <div v-if="bookingModalOpen" class="premium-modal-overlay" @click="closeBookingModal">
        <div class="premium-modal" @click.stop>
          <div class="modal-icon" :class="bookingModalType">
            {{ bookingModalType === 'success' ? '🎉' : '⚠️' }}
          </div>
          <h3>{{ bookingModalTitle }}</h3>
          <p>{{ bookingModalMessage }}</p>
          <button class="btn-primary" @click="closeBookingModal">Entendido</button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
const authStore = useAuthStore()
const config = useRuntimeConfig()
const loading = ref(true)

const purchases = ref([])
const subscription = ref(null)
const latestWorkout = ref(null)
const monthlyCheckins = ref(0)
const bookings = ref([])
const availableClasses = ref([])
const showClasses = ref(false)

// Modal State
const bookingModalOpen = ref(false)
const bookingModalType = ref('success')
const bookingModalTitle = ref('')
const bookingModalMessage = ref('')

const avatarSrc = computed(() => {
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(authStore.user?.name || 'User')}&background=00f2ff&color=000&bold=true&size=200`
})

const progressPercent = computed(() => Math.min(100, Math.round((monthlyCheckins.value / 20) * 100)))

const workoutPreview = computed(() => {
  if (!latestWorkout.value?.content) return ''
  return latestWorkout.value.content.replace(/[#*|>\-]/g, '').substring(0, 100) + '...'
})

function formatDate(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('pt-BR')
}

const openModal = (type, title, message) => {
  bookingModalType.value = type
  bookingModalTitle.value = title
  bookingModalMessage.value = message
  bookingModalOpen.value = true
}

const closeBookingModal = () => {
  bookingModalOpen.value = false
}

const bookClass = async (classId) => {
  try {
    await $fetch(`${config.public.apiBase}/class-bookings`, {
      method: 'POST',
      body: { gym_class_id: classId },
      headers: authStore.authHeaders
    })
    openModal('success', 'Reserva Confirmada!', 'Você garantiu sua vaga nesta aula. Te vemos lá!')
    showClasses.value = false
    loadData()
  } catch (e) {
    const errorMsg = e.response?._data?.message || 'Não foi possível reservar a aula no momento.'
    openModal('error', 'Falha na Reserva', errorMsg)
  }
}

const cancelBooking = async (id) => {
  try {
    await $fetch(`${config.public.apiBase}/class-bookings/${id}`, {
      method: 'DELETE',
      headers: authStore.authHeaders
    })
    openModal('success', 'Reserva Cancelada', 'Sua reserva foi cancelada com sucesso.')
    loadData()
  } catch (e) {
    openModal('error', 'Erro ao Cancelar', 'Não foi possível cancelar a aula.')
  }
}

const loadData = async () => {
  try {
    const data = await $fetch(`${config.public.apiBase}/member/dashboard`, {
      headers: authStore.authHeaders
    })
    const bookData = await $fetch(`${config.public.apiBase}/my/bookings`, {
      headers: authStore.authHeaders
    })
    const classData = await $fetch(`${config.public.apiBase}/gym-classes`, {
      headers: authStore.authHeaders
    })

    purchases.value = data.recent_purchases || []
    subscription.value = data.subscription
    latestWorkout.value = data.latest_workout
    monthlyCheckins.value = data.monthly_checkins || 0
    bookings.value = bookData || []
    availableClasses.value = classData || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(loadData)
</script>

<style scoped>
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4rem;
}

@media (max-width: 768px) {
  .dashboard-header { flex-direction: column; text-align: center; gap: 2rem; }
}

.user-intro {
  display: flex;
  align-items: center;
  gap: 2rem;
}

@media (max-width: 768px) { .user-intro { flex-direction: column; } }

.avatar-wrapper {
  position: relative;
  width: 100px;
  height: 100px;
}

.user-avatar {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 4px solid var(--accent-primary);
  box-shadow: 0 0 30px rgba(var(--accent-primary-rgb), 0.3);
}

.status-indicator {
  position: absolute;
  bottom: 5px;
  right: 5px;
  width: 18px;
  height: 18px;
  background: var(--success);
  border: 3px solid var(--bg-dark);
  border-radius: 50%;
}

.user-text h1 { font-size: 2.5rem; letter-spacing: -2px; }
.user-text p { color: var(--text-muted); font-size: 1.1rem; }

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;
}

.info-card {
  display: flex;
  gap: 2rem;
  padding: 2.5rem;
}

.wide-card { grid-column: span 2; }
@media (max-width: 992px) { .wide-card { grid-column: span 1; } }

.card-icon {
  font-size: 2rem;
  background: rgba(255,255,255,0.03);
  width: 64px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 18px;
  border: 1px solid var(--glass-border);
  flex-shrink: 0;
}

.card-content { flex: 1; display: flex; flex-direction: column; }
.card-content h3 { font-size: 1.2rem; font-weight: 800; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-dim); }

.workout-info .workout-title { font-size: 1.5rem; font-weight: 800; color: var(--accent-primary); }
.workout-info .workout-level { font-weight: 700; color: var(--accent-secondary); font-size: 0.9rem; margin-bottom: 1rem; }
.workout-excerpt { font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 2rem; }

.plan-tag {
  display: inline-block;
  padding: 0.5rem 1.5rem;
  background: rgba(var(--accent-primary-rgb), 0.1);
  border: 1px solid var(--accent-primary);
  border-radius: 50px;
  color: var(--accent-primary);
  font-weight: 800;
  margin-bottom: 1rem;
}

.plan-expiry { font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.5rem; }

.quick-metric {
  background: rgba(255,255,255,0.02);
  padding: 1rem;
  border-radius: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.m-label { font-size: 0.8rem; font-weight: 600; color: var(--text-dim); }
.m-value { font-size: 1.4rem; font-weight: 900; }

.assiduity-box { margin-top: 1rem; }
.assiduity-header { display: flex; justify-content: space-between; font-weight: 800; font-size: 1.2rem; margin-bottom: 1rem; }
.assiduity-track { height: 12px; background: rgba(255,255,255,0.05); border-radius: 20px; overflow: hidden; margin-bottom: 1rem; }
.assiduity-fill { height: 100%; background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary)); box-shadow: 0 0 20px rgba(var(--accent-primary-rgb), 0.4); border-radius: 20px; }
.assiduity-footer { font-size: 0.9rem; color: var(--text-muted); text-align: right; }

.list-card { padding: 2.5rem; }
.list-card .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }

.scroll-list { display: flex; flex-direction: column; gap: 1rem; max-height: 300px; overflow-y: auto; padding-right: 1rem; }

.list-item {
  background: rgba(255,255,255,0.02);
  padding: 1.2rem;
  border-radius: 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: 1px solid var(--glass-border);
}

.item-main { display: flex; flex-direction: column; }
.item-main strong { font-size: 1rem; }
.item-main span { font-size: 0.8rem; color: var(--text-muted); }

.price-tag { font-weight: 800; color: var(--accent-primary); }

.btn-cancel { background: transparent; border: none; color: var(--danger); cursor: pointer; font-size: 1.2rem; opacity: 0.5; transition: 0.3s; }
.btn-cancel:hover { opacity: 1; transform: scale(1.2); }

.available-overlay {
  position: absolute;
  inset: 1.5rem;
  background: #0c0c0e;
  border-radius: var(--radius-lg);
  padding: 2rem;
  z-index: 10;
  display: flex;
  flex-direction: column;
}

.available-list { flex: 1; overflow-y: auto; margin: 1.5rem 0; display: flex; flex-direction: column; gap: 1rem; }
.available-item { display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 12px; }
.c-info { display: flex; flex-direction: column; }
.c-info strong { font-size: 0.9rem; }
.c-info span { font-size: 0.75rem; color: var(--text-dim); }

.btn-icon-small {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 1px solid var(--glass-border);
  background: rgba(255,255,255,0.03);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: white;
}

/* Premium Modal */
.premium-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(5, 5, 7, 0.8);
  backdrop-filter: blur(10px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.premium-modal {
  background: rgba(15, 15, 20, 0.95);
  border: 1px solid var(--glass-border);
  border-radius: 24px;
  padding: 3rem;
  max-width: 450px;
  width: 100%;
  text-align: center;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.modal-icon {
  font-size: 3rem;
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(255,255,255,0.05);
  margin-bottom: 0.5rem;
}

.modal-icon.success {
  background: rgba(var(--success-rgb, 0, 255, 136), 0.1);
  color: #00ff88;
  box-shadow: 0 0 30px rgba(0, 255, 136, 0.3);
}

.modal-icon.error {
  background: rgba(255, 51, 102, 0.1);
  color: #ff3366;
  box-shadow: 0 0 30px rgba(255, 51, 102, 0.3);
}

.premium-modal h3 {
  font-size: 1.5rem;
  font-weight: 800;
  margin: 0;
}

.premium-modal p {
  color: var(--text-dim);
  font-size: 1.05rem;
  line-height: 1.6;
  margin: 0;
}

.premium-modal .btn-primary {
  width: 100%;
  margin-top: 1rem;
  height: 50px;
}
</style>
