<template>
  <div class="page-container">
    <header class="page-header" v-reveal>
      <h1 class="section-title">Leaderboard <span class="text-gradient">TechFit</span></h1>
      <p class="subtitle">Acompanhe a elite da performance. Onde cada check-in conta.</p>
      
      <div class="ranking-tabs">
        <button :class="{ active: activeTab === 'monthly' }" @click="activeTab = 'monthly'">Mês Atual</button>
        <button :class="{ active: activeTab === 'global' }" @click="activeTab = 'global'">Global Elite</button>
      </div>
    </header>

    <div class="ranking-layout">
      <!-- Top 3 Podium (Visual) -->
      <section v-if="!loading && ranking.length >= 3" class="podium-section" v-reveal>
        <!-- 2nd Place -->
        <div class="podium-item pos-2">
          <div class="podium-avatar">🥈</div>
          <div class="podium-bar">
            <span class="name">{{ ranking[1]?.name }}</span>
            <span class="score">{{ ranking[1]?.checkins_count }} pts</span>
          </div>
        </div>
        <!-- 1st Place -->
        <div class="podium-item pos-1">
          <div class="podium-avatar">🥇</div>
          <div class="podium-bar">
            <span class="name">{{ ranking[0]?.name }}</span>
            <span class="score">{{ ranking[0]?.checkins_count }} pts</span>
          </div>
        </div>
        <!-- 3rd Place -->
        <div class="podium-item pos-3">
          <div class="podium-avatar">🥉</div>
          <div class="podium-bar">
            <span class="name">{{ ranking[2]?.name }}</span>
            <span class="score">{{ ranking[2]?.checkins_count }} pts</span>
          </div>
        </div>
      </section>

      <!-- Full List -->
      <main class="glass-card ranking-card" v-reveal>
        <div v-if="loading" class="loading-state">
          <div class="loader-dots"><span></span><span></span><span></span></div>
        </div>
        <div v-else class="ranking-list">
          <div v-for="(user, index) in ranking" :key="user.id" class="rank-row" :class="{ 'me': user.id === authStore.user?.id }">
            <div class="rank-position">{{ index + 1 }}</div>
            <div class="rank-user">
              <span class="user-name">{{ user.name }}</span>
              <span v-if="user.id === authStore.user?.id" class="my-badge">Você</span>
            </div>
            <div class="rank-score">
              <span class="val">{{ user.checkins_count }}</span>
              <span class="lab">Check-ins</span>
            </div>
          </div>
        </div>
      </main>

      <!-- Achievements -->
      <section class="achievements-section" v-reveal>
        <h3 class="section-heading">🏆 Minhas Conquistas</h3>
        <div class="achievements-grid">
          <div v-for="ach in myAchievements" :key="ach.id" class="ach-card glass-card">
            <div class="ach-icon">{{ ach.icon }}</div>
            <div class="ach-details">
              <h4>{{ ach.name }}</h4>
              <p>{{ ach.description }}</p>
            </div>
          </div>
        </div>
        <div v-if="!loading && myAchievements.length === 0" class="empty-ach">
          <p>Você ainda não desbloqueou medalhas. Mantenha o foco!</p>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
const authStore = useAuthStore()
const config = useRuntimeConfig()
const activeTab = ref('monthly')
const loading = ref(true)
const ranking = ref([])
const myAchievements = ref([])

async function loadRanking() {
  loading.value = true
  try {
    const endpoint = activeTab.value === 'monthly' ? '/ranking/monthly' : '/ranking/global'
    const response = await $fetch(`${config.public.apiBase}${endpoint}`, {
      headers: authStore.authHeaders
    })
    ranking.value = response.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function loadMyAchievements() {
  try {
    const response = await $fetch(`${config.public.apiBase}/achievements`, {
      headers: authStore.authHeaders
    })
    myAchievements.value = response.data || []
  } catch (e) { /* ignore */ }
}

watch(activeTab, loadRanking)

onMounted(() => {
  loadRanking()
  loadMyAchievements()
})
</script>

<style scoped>
.page-header { text-align: center; margin-bottom: 4rem; }

.ranking-tabs {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 2.5rem;
}

.ranking-tabs button {
  padding: 0.8rem 2rem;
  border-radius: 50px;
  border: 1px solid var(--glass-border);
  background: rgba(255, 255, 255, 0.03);
  color: var(--text-muted);
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
}

.ranking-tabs button.active {
  background: var(--accent-primary);
  color: #000;
  border-color: transparent;
  box-shadow: 0 0 20px rgba(var(--accent-primary-rgb), 0.3);
}

.podium-section {
  display: flex;
  justify-content: center;
  align-items: flex-end;
  gap: 1rem;
  margin-bottom: 4rem;
  padding: 2rem 0;
}

.podium-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  max-width: 200px;
}

.podium-avatar {
  font-size: 3rem;
  margin-bottom: 1rem;
  filter: drop-shadow(0 0 10px rgba(255,255,255,0.2));
}

.podium-bar {
  width: 100%;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--glass-border);
  border-radius: 12px 12px 0 0;
  padding: 2rem 1rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.podium-bar .name { font-weight: 800; font-size: 0.9rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 100%; }
.podium-bar .score { color: var(--accent-primary); font-weight: 900; font-size: 1.2rem; }

.pos-1 .podium-bar { height: 200px; background: rgba(var(--accent-primary-rgb), 0.1); border-color: var(--accent-primary); box-shadow: 0 0 40px rgba(var(--accent-primary-rgb), 0.2); }
.pos-1 .podium-avatar { font-size: 4rem; }
.pos-2 .podium-bar { height: 150px; }
.pos-3 .podium-bar { height: 120px; }

.ranking-card { padding: 0; overflow: hidden; }

.rank-row {
  display: flex;
  align-items: center;
  padding: 1.5rem 2.5rem;
  border-bottom: 1px solid var(--glass-border);
  transition: all 0.3s;
}

.rank-row:last-child { border-bottom: none; }
.rank-row:hover { background: rgba(255,255,255,0.02); }
.rank-row.me { background: rgba(var(--accent-primary-rgb), 0.08); }

.rank-position { width: 60px; font-size: 1.5rem; font-weight: 900; color: var(--text-dim); }
.rank-user { flex: 1; display: flex; align-items: center; gap: 1rem; }
.user-name { font-size: 1.1rem; font-weight: 700; }
.my-badge { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; background: var(--accent-secondary); color: #000; padding: 0.2rem 0.6rem; border-radius: 4px; }

.rank-score { text-align: right; }
.rank-score .val { display: block; font-size: 1.6rem; font-weight: 900; color: var(--accent-primary); }
.rank-score .lab { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; }

.achievements-section { margin-top: 5rem; }
.section-heading { font-size: 1.4rem; font-weight: 800; margin-bottom: 2rem; }

.achievements-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.ach-card { padding: 1.5rem; display: flex; align-items: center; gap: 1.5rem; }
.ach-icon { font-size: 3rem; }
.ach-details h4 { font-size: 1.1rem; font-weight: 800; margin-bottom: 0.3rem; }
.ach-details p { font-size: 0.85rem; color: var(--text-muted); line-height: 1.4; }

.loading-state { padding: 5rem; display: flex; justify-content: center; }
.empty-ach { text-align: center; padding: 3rem; color: var(--text-dim); }
</style>
