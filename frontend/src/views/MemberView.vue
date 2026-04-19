<template>
  <div class="dashboard-container">
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

    <div class="dashboard-grid">

      <!-- Treino Atual -->
      <div class="glass-card">
        <h3>🏋️ Treino Atual</h3>
        <div v-if="workout" class="workout-display">
          <p class="workout-name">{{ workout.name || 'Ficha Personalizada' }}</p>
          <p class="text-muted workout-preview">{{ workout.preview }}</p>
        </div>
        <p v-else class="text-muted" style="margin-top: 1rem;">Nenhuma ficha gerada ainda.</p>
        <div class="workout-actions">
          <button class="btn-primary" :disabled="!workout">Iniciar Treino</button>
          <router-link to="/ai" class="btn-outline" style="text-decoration: none; text-align: center;">
            {{ workout ? '🔄 Mudar Ficha com IA' : '✨ Gerar Ficha com IA' }}
          </router-link>
        </div>
      </div>

      <!-- Meu Plano -->
      <div class="glass-card">
        <h3>💳 Meu Plano</h3>
        <div class="plan-display">
          <div class="plan-badge active">✅ Plano Ativo</div>
          <div class="plan-details">
            <div class="plan-item">
              <span class="text-muted">Tipo</span>
              <span>Mensal Premium</span>
            </div>
            <div class="plan-item">
              <span class="text-muted">Início</span>
              <span>01/04/2025</span>
            </div>
            <div class="plan-item">
              <span class="text-muted">Vencimento</span>
              <span class="highlight">30/04/2025</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Seu Progresso -->
      <div class="glass-card">
        <h3>📈 Seu Progresso</h3>
        <div class="progress-bar">
          <div class="progress" style="width: 60%"></div>
        </div>
        <p class="text-muted">12/20 treinos este mês</p>
        <div class="stat-row">
          <div class="stat"><span class="stat-num text-gradient">12</span><span class="stat-label">Treinos</span></div>
          <div class="stat"><span class="stat-num text-gradient">3</span><span class="stat-label">Semanas</span></div>
          <div class="stat"><span class="stat-num text-gradient">86%</span><span class="stat-label">Assiduidade</span></div>
        </div>
      </div>

      <!-- Compras Recentes -->
      <div class="glass-card">
        <h3>🛍️ Compras Recentes</h3>
        <div v-if="loading" class="text-muted">Carregando...</div>
        <ul v-else-if="purchases.length > 0" class="purchase-list">
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
import axios from 'axios'

const authStore = useAuthStore()
const purchases = ref([])
const loading = ref(true)

// Foto de perfil do localStorage
const AVATAR_KEY = computed(() => `techfit_avatar_${authStore.user?.id}`)
const avatarSrc = ref('')

onMounted(async () => {
  // Carrega avatar
  avatarSrc.value = localStorage.getItem(AVATAR_KEY.value)
    || `https://ui-avatars.com/api/?name=${encodeURIComponent(authStore.user?.name || 'User')}&background=00f2ff&color=000&bold=true&size=200`

  // Carrega compras
  try {
    const response = await axios.get('/member/dashboard')
    purchases.value = response.data.recent_purchases
  } catch (error) {
    console.error('Erro ao buscar dados do membro', error)
  } finally {
    loading.value = false
  }
})

// Treino gerado pela IA (salvo no localStorage pelo AIView)
const workout = computed(() => {
  const raw = localStorage.getItem('techfit_workout')
  if (!raw) return null
  try {
    const parsed = JSON.parse(raw)
    return { name: parsed.name || 'Ficha Personalizada', preview: parsed.preview || parsed.summary || 'Ficha gerada pelo assistente IA.' }
  } catch {
    return { name: 'Ficha Personalizada', preview: raw.substring(0, 120) + '...' }
  }
})
</script>

<style scoped>
.dashboard-container { padding: 2rem 5%; max-width: 1200px; margin: 0 auto; }
.dashboard-header { margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; }
.dashboard-header h2 { font-size: 2.5rem; }
.header-left p { color: var(--text-muted); margin-top: 0.5rem; }

.profile-link {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 50px;
  padding: 0.5rem 1rem 0.5rem 0.5rem;
  text-decoration: none;
  color: white;
  font-size: 0.9rem;
  font-weight: 600;
  transition: border-color 0.3s, background 0.3s;
}
.profile-link:hover { border-color: var(--accent-primary); background: rgba(0,242,255,0.05); }

.header-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--accent-primary);
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.workout-name { font-weight: 700; font-size: 1rem; margin: 1rem 0 0.4rem; color: var(--accent-primary); }
.workout-preview { font-size: 0.85rem; line-height: 1.5; }
.workout-actions { display: flex; flex-direction: column; gap: 1rem; margin-top: 1.5rem; }

.plan-display { margin-top: 1rem; }
.plan-badge { display: inline-block; padding: 5px 14px; border-radius: 50px; font-size: 0.85rem; font-weight: 600; margin-bottom: 1.2rem; background: rgba(0,255,136,0.1); border: 1px solid #00ff88; color: #00ff88; }
.plan-details { display: flex; flex-direction: column; gap: 0.8rem; }
.plan-item { display: flex; justify-content: space-between; font-size: 0.9rem; padding-bottom: 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
.highlight { color: var(--accent-secondary); font-weight: 700; }

.progress-bar { background: rgba(255,255,255,0.1); height: 10px; border-radius: 5px; margin: 1.5rem 0 0.5rem; overflow: hidden; }
.progress { background: var(--accent-primary); height: 100%; box-shadow: 0 0 10px var(--accent-primary); }

.stat-row { display: flex; justify-content: space-around; margin-top: 1.5rem; }
.stat { display: flex; flex-direction: column; align-items: center; gap: 0.3rem; }
.stat-num { font-size: 1.6rem; font-weight: 900; }
.stat-label { font-size: 0.75rem; color: var(--text-muted); }

.purchase-list { list-style: none; padding: 0; margin-top: 1.5rem; }
.purchase-list li { display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; }
</style>
