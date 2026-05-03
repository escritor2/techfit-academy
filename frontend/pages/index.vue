<template>
  <div class="page-container">
    <!-- Hero Section with Video Background -->
    <header class="hero-section" v-reveal>
      <div class="video-overlay"></div>
      <video class="hero-video" autoplay loop muted playsinline>
        <!-- Public domain/royalty-free gym workout video from Pixabay -->
        <source src="https://cdn.pixabay.com/video/2020/05/26/40113-424898145_large.mp4" type="video/mp4" />
      </video>

      <div class="hero-content">
        <h1 class="section-title">A Evolução da sua <span class="text-gradient text-glow">Performance</span></h1>
        <p class="hero-subtitle">Combine tecnologia de ponta, inteligência artificial e suplementação premium em um só lugar.</p>
        <div class="hero-btns">
          <NuxtLink to="/ai" class="btn-primary">
            <span>✨ Gerar Treino IA</span>
          </NuxtLink>
          <NuxtLink to="/loja" class="btn-outline">
            <span>🛒 Explorar Loja</span>
          </NuxtLink>
        </div>
      </div>
      <div class="hero-stats hide-mobile">
        <div class="stat-item glass-card stat-blur">
          <span class="stat-value">500+</span>
          <span class="stat-label">Alunos Ativos</span>
        </div>
        <div class="stat-item glass-card stat-blur">
          <span class="stat-value">IA</span>
          <span class="stat-label">Treinos Únicos</span>
        </div>
      </div>
    </header>

    <!-- Dashboard Grid -->
    <div class="dashboard-grid" v-if="authStore.isAuthenticated">
      <!-- Workout Card -->
      <section v-reveal class="glass-card feature-card">
        <div class="card-header-icon">⚡</div>
        <div class="card-info">
          <h3 class="card-title">Plano de Hoje</h3>
          <p class="text-muted" v-if="dashboardData?.latest_workout">
            Objetivo: {{ dashboardData.latest_workout.goal }}
          </p>
          <p class="text-muted" v-else>Nenhum treino gerado ainda.</p>
          
          <div class="workout-details" v-if="dashboardData?.latest_workout">
            <span class="badge secondary">{{ dashboardData.latest_workout.level }}</span>
            <span class="duration">⏱️ Gerado em {{ new Date(dashboardData.latest_workout.created_at).toLocaleDateString('pt-BR') }}</span>
          </div>
          <NuxtLink to="/ai" class="btn-link">Ver detalhes do treino →</NuxtLink>
        </div>
      </section>

      <!-- Evolution Card -->
      <section v-reveal class="glass-card feature-card">
        <div class="card-header-icon">📈</div>
        <div class="card-info">
          <h3 class="card-title">Frequência Mensal</h3>
          <div class="progress-box">
            <div class="progress-labels">
              <span>Check-ins</span>
              <span class="text-gradient">{{ dashboardData?.monthly_checkins || 0 }}</span>
            </div>
            <div class="progress-bar-container">
              <div class="progress-bar-fill" :style="{ width: (dashboardData?.monthly_checkins || 0) * 10 + '%' }"></div>
            </div>
          </div>
          <p class="text-dim" v-if="dashboardData?.monthly_checkins > 0">Mandou bem! Continue focado.</p>
          <p class="text-dim" v-else>Bora treinar? Faça seu primeiro check-in!</p>
        </div>
      </section>

      <!-- Nutrition Card -->
      <section v-reveal class="glass-card feature-card">
        <div class="card-header-icon">🥗</div>
        <div class="card-info">
          <h3 class="card-title">Assinatura Atual</h3>
          <div v-if="dashboardData?.subscription" class="sub-details">
            <p class="plan-name">{{ dashboardData.subscription.plan.name }}</p>
            <p class="text-muted">Expira em: {{ new Date(dashboardData.subscription.expires_at).toLocaleDateString('pt-BR') }}</p>
          </div>
          <div v-else class="sub-details">
            <p class="text-muted">Sem plano ativo.</p>
          </div>
          <NuxtLink to="/planos" class="btn-link">Ver planos →</NuxtLink>
        </div>
      </section>

      <!-- Ranking Card -->
      <section v-reveal class="glass-card feature-card">
        <div class="card-header-icon">🏆</div>
        <div class="card-info">
          <h3 class="card-title">Leaderboard Global</h3>
          <div class="ranking-list" v-if="rankingData?.length">
            <div 
              v-for="(member, index) in rankingData.slice(0, 3)" 
              :key="member.id" 
              class="rank-row"
              :class="{ 'current-user': member.id === authStore.user?.id }"
            >
              <span class="pos">#{{ index + 1 }}</span>
              <span class="name">{{ member.name }} {{ member.id === authStore.user?.id ? '(Você)' : '' }}</span>
              <span class="pts">{{ member.login_days * 10 }} XP</span>
            </div>
          </div>
          <p v-else class="text-muted">Carregando rankings...</p>
          <NuxtLink to="/ranking" class="btn-link">Ver ranking completo →</NuxtLink>
        </div>
      </section>
    </div>

    <!-- Achievement Toast-style Detail -->
    <div class="floating-achievement glass-card" v-reveal>
      <div class="achievement-icon">🎖️</div>
      <div class="achievement-content">
        <strong>Conquista Desbloqueada</strong>
        <span>Guerreiro da Alvorada (5 check-ins às 6h)</span>
      </div>
    </div>

    <!-- Location Section -->
    <section class="location-section" v-reveal>
      <div class="section-intro">
        <h2 class="section-title">Onde a <span class="text-gradient">Mágica Acontece</span></h2>
        <p class="subtitle">Visite nossa unidade matriz e sinta a energia TechFit.</p>
      </div>
      
      <div class="glass-card map-card">
        <div class="map-container" v-if="isMounted">
          <client-only>
            <l-map ref="map" :zoom="15" :center="[-23.5505, -46.6333]" :use-global-leaflet="false">
              <l-tile-layer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                layer-type="base"
                name="OpenStreetMap"
              ></l-tile-layer>
              <l-marker :lat-lng="[-23.5505, -46.6333]">
                <l-popup>TechFit Academy - Unidade Centro</l-popup>
              </l-marker>
            </l-map>
          </client-only>
        </div>
        <div v-else class="map-placeholder">
          Carregando mapa...
        </div>
        <div class="location-info">
          <div class="info-item">
            <span class="icon">📍</span>
            <div class="text">
              <strong>Endereço</strong>
              <p>Av. Paulista, 1000 - São Paulo, SP</p>
            </div>
          </div>
          <div class="info-item">
            <span class="icon">⏰</span>
            <div class="text">
              <strong>Horário</strong>
              <p>Seg - Sex: 05h às 23h | Sáb - Dom: 08h às 14h</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { LMap, LTileLayer, LMarker, LPopup } from "@vue-leaflet/vue-leaflet";
import "leaflet/dist/leaflet.css";

const authStore = useAuthStore()
const config = useRuntimeConfig()
const isMounted = ref(false)
const dashboardData = ref({
  latest_workout: null,
  monthly_checkins: 0,
  subscription: null,
  upcoming_bookings: []
})
const rankingData = ref([])
const loading = ref(true)

const fetchDashboardData = async () => {
  if (!authStore.isAuthenticated) {
    loading.value = false
    return
  }
  
  try {
    const [dashRes, rankRes] = await Promise.all([
      $fetch(`${config.public.apiBase}/member/dashboard`, { headers: authStore.authHeaders }),
      $fetch(`${config.public.apiBase}/ranking/global`, { headers: authStore.authHeaders })
    ])

    if (dashRes.success) dashboardData.value = dashRes.data
    if (rankRes.success) rankingData.value = rankRes.data
  } catch (error) {
    console.error('Erro ao buscar dados do dashboard', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  isMounted.value = true
  fetchDashboardData()
})
</script>

<style scoped>
.hero-section {
  position: relative;
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: center;
  gap: 4rem;
  margin-bottom: 5rem;
  padding: 8rem 4rem; /* Increased padding for impact */
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 20px 50px rgba(0,0,0,0.5);
}

.hero-video {
  position: absolute;
  top: 50%;
  left: 50%;
  min-width: 100%;
  min-height: 100%;
  width: auto;
  height: auto;
  transform: translate(-50%, -50%);
  object-fit: cover;
  z-index: 0;
  opacity: 0.6; /* Slight dimming so text is readable */
}

.video-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, rgba(5,5,7,0.9) 0%, rgba(5,5,7,0.4) 100%);
  z-index: 1;
}

.hero-content {
  position: relative;
  z-index: 2;
}

.hero-stats {
  position: relative;
  z-index: 2;
}

.stat-blur {
  backdrop-filter: blur(25px);
  background: rgba(10, 10, 15, 0.4);
  border-color: rgba(255, 255, 255, 0.1);
}

@media (max-width: 992px) {
  .hero-section { grid-template-columns: 1fr; text-align: center; }
}

.hero-subtitle {
  font-size: 1.25rem;
  color: var(--text-muted);
  max-width: 600px;
  margin-bottom: 2.5rem;
}

@media (max-width: 992px) { .hero-subtitle { margin: 0 auto 2.5rem; } }

.hero-btns {
  display: flex;
  gap: 1.5rem;
}

@media (max-width: 992px) { .hero-btns { justify-content: center; } }

.hero-stats {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.stat-item {
  padding: 1.5rem 2.5rem;
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 2rem;
  font-weight: 900;
  color: var(--accent-primary);
}

.stat-label {
  font-size: 0.8rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Dashboard Grid */
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 2rem;
}

.feature-card {
  display: flex;
  gap: 1.5rem;
  padding: 2rem;
}

.card-header-icon {
  width: 60px;
  height: 60px;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--glass-border);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  flex-shrink: 0;
}

.card-info { flex: 1; }
.card-title { font-size: 1.4rem; font-weight: 700; margin-bottom: 0.5rem; }

.workout-details {
  display: flex;
  justify-content: space-between;
  margin: 1.5rem 0;
}

.badge.secondary {
  background: rgba(var(--accent-primary-rgb), 0.1);
  color: var(--accent-primary);
  padding: 0.4rem 1rem;
  border-radius: 50px;
  font-weight: 700;
  font-size: 0.8rem;
}

.btn-link {
  background: none;
  border: none;
  color: var(--accent-primary);
  font-weight: 700;
  cursor: pointer;
  padding: 0;
  margin-top: 1rem;
  transition: all 0.3s;
}

.btn-link:hover { transform: translateX(5px); }

/* Progress */
.progress-box { margin: 1.5rem 0; }
.progress-labels { display: flex; justify-content: space-between; margin-bottom: 0.6rem; font-weight: 700; }

.progress-bar-container {
  height: 8px;
  background: rgba(255,255,255,0.05);
  border-radius: 10px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
  box-shadow: 0 0 15px rgba(var(--accent-primary-rgb), 0.5);
  border-radius: 10px;
}

/* Macros */
.macros-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin: 1.5rem 0;
  background: rgba(255,255,255,0.02);
  padding: 1rem;
  border-radius: 12px;
}

.macro-item { text-align: center; }
.macro-item .val { display: block; font-weight: 900; font-size: 1.2rem; }
.macro-item .lab { font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; }

/* Ranking */
.ranking-list { margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.8rem; }
.rank-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: rgba(255,255,255,0.03);
  border-radius: 12px;
  border: 1px solid transparent;
}

.rank-row.current-user {
  background: rgba(var(--accent-primary-rgb), 0.08);
  border-color: rgba(var(--accent-primary-rgb), 0.2);
}

.rank-row .pos { font-weight: 900; color: var(--accent-primary); }
.rank-row .name { flex: 1; font-weight: 600; }
.rank-row .pts { font-size: 0.8rem; color: var(--text-muted); }

/* Floating Achievement */
.floating-achievement {
  position: fixed;
  bottom: 2.5rem;
  right: 2.5rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 1.2rem 2rem;
  border-color: var(--accent-secondary);
  box-shadow: 0 15px 40px rgba(188, 255, 0, 0.15);
  z-index: 100;
}

.achievement-icon { font-size: 2.5rem; }
.achievement-content { display: flex; flex-direction: column; }
.achievement-content strong { color: var(--accent-secondary); font-size: 1rem; }
.achievement-content span { font-size: 0.85rem; color: var(--text-muted); }

@media (max-width: 768px) {
  .floating-achievement {
    bottom: 1.5rem;
    left: 1.5rem;
    right: 1.5rem;
  }
}

.location-section { padding: 8rem 0; }
.map-card { display: grid; grid-template-columns: 1fr 350px; padding: 0 !important; overflow: hidden; height: 500px; margin-top: 4rem; }
.map-container { position: relative; z-index: 1; filter: grayscale(1) invert(0.9) hue-rotate(180deg); height: 100%; width: 100%; }
.map-placeholder { display: flex; align-items: center; justify-content: center; background: #0a0a0c; color: var(--text-dim); height: 500px; }
.location-info { padding: 4rem; display: flex; flex-direction: column; gap: 3rem; background: rgba(255,255,255,0.02); }
.info-item { display: flex; gap: 1.5rem; text-align: left; }
.info-item .icon { font-size: 1.5rem; }
.info-item strong { display: block; font-size: 1.1rem; margin-bottom: 0.5rem; }
.info-item p { color: var(--text-dim); line-height: 1.5; margin: 0; }

.section-intro { text-align: center; margin-bottom: 2rem; }

@media (max-width: 992px) {
  .map-card { grid-template-columns: 1fr; height: auto; }
  .map-container { height: 300px; }
  .location-info { padding: 3rem; }
}
</style>
