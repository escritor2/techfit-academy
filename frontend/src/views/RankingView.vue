<template>
  <div class="ranking-container animate-fade-in">
    <header class="section-header">
      <h2>Leaderboard <span class="text-gradient">TechFit</span></h2>
      <p>Os membros mais focados da academia este mês.</p>
    </header>

    <div class="ranking-tabs">
      <button :class="{ active: activeTab === 'monthly' }" @click="activeTab = 'monthly'">Mês Atual</button>
      <button :class="{ active: activeTab === 'global' }" @click="activeTab = 'global'">Global</button>
    </div>

    <div class="glass-card leaderboard-card">
      <div v-if="loading" class="text-muted text-center py-10">Carregando ranking...</div>
      <div v-else class="ranking-list">
        <div v-for="(user, index) in ranking" :key="user.id" class="ranking-row">
          <div class="rank-num" :class="`pos-${index + 1}`">
            {{ index + 1 }}º
          </div>
          <div class="rank-info">
            <span class="user-name">{{ user.name }}</span>
          </div>
          <div class="rank-score">
            <span class="score-val">{{ user.checkins_count }}</span>
            <span class="score-label">Check-ins</span>
          </div>
        </div>
      </div>
    </div>

    <div class="achievements-section mt-10">
      <h3>🏆 Minhas Conquistas</h3>
      <div class="achievements-grid">
        <div v-for="ach in myAchievements" :key="ach.id" class="achievement-card glass-card">
          <span class="ach-icon">{{ ach.icon }}</span>
          <div class="ach-info">
            <h4>{{ ach.name }}</h4>
            <p>{{ ach.description }}</p>
          </div>
        </div>
      </div>
      <p v-if="myAchievements.length === 0" class="text-muted">Você ainda não conquistou medalhas. Continue treinando!</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const activeTab = ref('monthly')
const loading = ref(true)
const ranking = ref([])
const myAchievements = ref([])

async function loadRanking() {
  loading.value = true
  try {
    const endpoint = activeTab.value === 'monthly' ? '/ranking/monthly' : '/ranking/global'
    const res = await axios.get(endpoint)
    ranking.value = res.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function loadMyAchievements() {
  try {
    const res = await axios.get('/achievements')
    myAchievements.value = res.data
  } catch (e) { /* ignore */ }
}

watch(activeTab, loadRanking)

onMounted(() => {
  loadRanking()
  loadMyAchievements()
})
</script>

<style scoped>
.ranking-container { padding: 2rem 5%; max-width: 900px; margin: 0 auto; }
.ranking-tabs { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
.ranking-tabs button {
  padding: 0.6rem 1.5rem; border: none; background: rgba(255,255,255,0.05);
  color: var(--text-muted); border-radius: 10px; cursor: pointer; transition: all 0.3s;
}
.ranking-tabs button.active { background: var(--accent-primary); color: black; font-weight: bold; }

.leaderboard-card { padding: 0 !important; overflow: hidden; }
.ranking-list { display: flex; flex-direction: column; }
.ranking-row {
  display: flex; align-items: center; padding: 1.2rem 2rem;
  border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.3s;
}
.ranking-row:hover { background: rgba(255,255,255,0.02); }

.rank-num { width: 40px; font-weight: 900; font-size: 1.1rem; color: var(--text-muted); }
.rank-num.pos-1 { color: #fbbf24; font-size: 1.4rem; }
.rank-num.pos-2 { color: #94a3b8; }
.rank-num.pos-3 { color: #d97706; }

.rank-info { flex: 1; margin-left: 1rem; }
.user-name { font-weight: 600; font-size: 1.1rem; }

.rank-score { text-align: right; }
.score-val { display: block; font-size: 1.4rem; font-weight: 900; color: var(--accent-primary); }
.score-label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; }

.achievements-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; margin-top: 1rem; }
.achievement-card { display: flex; align-items: center; gap: 1rem; padding: 1rem !important; }
.ach-icon { font-size: 2.5rem; }
.ach-info h4 { margin: 0; font-size: 1rem; }
.ach-info p { margin: 0.2rem 0 0; font-size: 0.8rem; color: var(--text-muted); }
.mt-10 { margin-top: 3rem; }
</style>
