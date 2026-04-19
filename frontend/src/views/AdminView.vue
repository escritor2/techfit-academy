<template>
  <div class="dashboard-container">
    <header class="dashboard-header">
      <h2>Painel do <span class="text-gradient">Administrador</span></h2>
      <p>Gerencie o sistema completo da TechFit.</p>
    </header>

    <div v-if="loading" class="loading-state text-muted">
      Carregando estatísticas ao vivo...
    </div>

    <div v-else>
      <div class="dashboard-grid">
        <div class="glass-card stat-card">
          <div class="stat-icon">👥</div>
          <div class="stat-info">
            <h3>Membros Ativos</h3>
            <p class="text-gradient stat-number">{{ stats.active_members || 0 }}</p>
          </div>
        </div>
        
        <div class="glass-card stat-card">
          <div class="stat-icon">💰</div>
          <div class="stat-info">
            <h3>Faturamento Mês</h3>
            <p class="text-gradient stat-number">R$ {{ stats.monthly_revenue ? parseFloat(stats.monthly_revenue).toFixed(2).replace('.', ',') : '0,00' }}</p>
          </div>
        </div>
        
        <div class="glass-card stat-card">
          <div class="stat-icon">🛒</div>
          <div class="stat-info">
            <h3>Vendas Loja</h3>
            <p class="text-gradient stat-number">{{ stats.total_sales || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="dashboard-sections">
        <div class="glass-card section-card">
          <h3>Ações Rápidas</h3>
          <div class="action-buttons">
            <button class="btn-primary">Novo Membro</button>
            <button class="btn-primary">Novo Funcionário</button>
            <button class="btn-outline">Relatório Financeiro</button>
          </div>
        </div>
        
        <div class="glass-card section-card">
          <h3>Últimos Cadastros</h3>
          <table class="data-table" v-if="stats.recent_registrations && stats.recent_registrations.length > 0">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Data</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in stats.recent_registrations" :key="user.id">
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>{{ new Date(user.created_at).toLocaleDateString('pt-BR') }}</td>
                <td><span class="badge active">Ativo</span></td>
              </tr>
            </tbody>
          </table>
          <p v-else class="text-muted" style="margin-top: 1rem;">Nenhum membro recente encontrado.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const stats = ref({})
const loading = ref(true)

onMounted(async () => {
  try {
    const response = await axios.get('/admin/dashboard')
    stats.value = response.data
  } catch (error) {
    console.error('Erro ao buscar dados do dashboard', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.dashboard-container { padding: 2rem 5%; max-width: 1200px; margin: 0 auto; }
.dashboard-header { margin-bottom: 3rem; }
.dashboard-header h2 { font-size: 2.5rem; }
.loading-state { font-size: 1.2rem; text-align: center; margin-top: 2rem; }
.stat-card { display: flex; align-items: center; gap: 1.5rem; padding: 1.5rem; }
.stat-icon { font-size: 3rem; background: rgba(0, 242, 255, 0.1); padding: 1rem; border-radius: 12px; }
.stat-number { font-size: 2rem; font-weight: bold; margin-top: 0.5rem; }
.dashboard-sections { margin-top: 2rem; display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; }
.action-buttons { display: flex; flex-direction: column; gap: 1rem; margin-top: 1.5rem; }
.data-table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; text-align: left; }
.data-table th { padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); color: var(--text-muted); }
.data-table td { padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
.badge { padding: 0.3rem 0.6rem; border-radius: 12px; font-size: 0.8rem; }
.badge.active { background: rgba(16, 185, 129, 0.2); color: #10b981; }
</style>
