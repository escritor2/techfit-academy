<template>
  <div class="dashboard-container">
    <header class="dashboard-header">
      <h2>Bem-vindo, <span class="text-gradient">{{ authStore.user?.name || 'Membro' }}</span></h2>
      <p>Seu painel pessoal de evolução e treinos.</p>
    </header>

    <div class="dashboard-grid">
      <div class="glass-card">
        <h3>Treino Atual</h3>
        <p class="text-muted">Ficha A - Peito e Tríceps</p>
        <div class="workout-actions">
          <button class="btn-primary">Iniciar Treino</button>
          <router-link to="/ai" class="btn-outline" style="text-decoration: none; text-align: center;">Mudar Ficha com IA</router-link>
        </div>
      </div>
      
      <div class="glass-card">
        <h3>Seu Progresso</h3>
        <div class="progress-bar">
          <div class="progress" style="width: 60%"></div>
        </div>
        <p class="text-muted">12/20 treinos este mês</p>
      </div>

      <div class="glass-card">
        <h3>Compras Recentes</h3>
        <div v-if="loading" class="text-muted">Carregando...</div>
        <ul v-else-if="purchases.length > 0" class="purchase-list">
          <li v-for="sale in purchases" :key="sale.id">
            <span>Pedido #{{ sale.id }}</span>
            <span class="text-gradient">R$ {{ parseFloat(sale.total_price).toFixed(2).replace('.', ',') }}</span>
          </li>
        </ul>
        <p v-else class="text-muted" style="margin-top: 1rem;">Nenhuma compra recente encontrada.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/authStore'
import axios from 'axios'

const authStore = useAuthStore()
const purchases = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const response = await axios.get('/member/dashboard')
    purchases.value = response.data.recent_purchases
  } catch (error) {
    console.error('Erro ao buscar dados do membro', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.dashboard-container { padding: 2rem 5%; max-width: 1200px; margin: 0 auto; }
.dashboard-header { margin-bottom: 3rem; }
.dashboard-header h2 { font-size: 2.5rem; }
.workout-actions { display: flex; flex-direction: column; gap: 1rem; margin-top: 1.5rem; }
.progress-bar { background: rgba(255, 255, 255, 0.1); height: 10px; border-radius: 5px; margin: 1.5rem 0 0.5rem; overflow: hidden; }
.progress { background: var(--accent-primary); height: 100%; box-shadow: 0 0 10px var(--accent-primary); }
.purchase-list { list-style: none; padding: 0; margin-top: 1.5rem; }
.purchase-list li { display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
</style>
