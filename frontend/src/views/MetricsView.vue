<template>
  <div class="metrics-container animate-fade-in">
    <header class="section-header">
      <h2>Evolução <span class="text-gradient">Corporal</span></h2>
      <p>Acompanhe suas medidas e veja seu progresso físico.</p>
    </header>

    <div class="metrics-grid">
      <!-- Formulário de Nova Medição -->
      <div class="glass-card form-card">
        <h3>📏 Registrar Medidas</h3>
        <form @submit.prevent="saveMetrics" class="metrics-form">
          <div class="form-row">
            <div class="input-group">
              <label>Peso (kg)</label>
              <input v-model="newMetric.weight" type="number" step="0.1" required />
            </div>
            <div class="input-group">
              <label>BF (%)</label>
              <input v-model="newMetric.body_fat" type="number" step="0.1" />
            </div>
          </div>
          <div class="form-row">
            <div class="input-group">
              <label>Peitoral (cm)</label>
              <input v-model="newMetric.chest" type="number" step="0.1" />
            </div>
            <div class="input-group">
              <label>Cintura (cm)</label>
              <input v-model="newMetric.waist" type="number" step="0.1" />
            </div>
          </div>
          <div class="form-row">
            <div class="input-group">
              <label>Bíceps (cm)</label>
              <input v-model="newMetric.biceps" type="number" step="0.1" />
            </div>
            <div class="input-group">
              <label>Coxa (cm)</label>
              <input v-model="newMetric.thigh" type="number" step="0.1" />
            </div>
          </div>
          <button type="submit" class="btn-primary" :disabled="loading">
            {{ loading ? 'Salvando...' : '💾 Salvar Medição' }}
          </button>
        </form>
      </div>

      <!-- Gráfico de Peso (Simulado com CSS/SVG para evitar dependências externas complexas agora) -->
      <div class="glass-card chart-card">
        <h3>📈 Histórico de Peso</h3>
        <div v-if="history.length > 1" class="simple-chart">
          <div class="chart-bars">
            <div 
              v-for="(h, i) in history" 
              :key="i" 
              class="chart-bar-wrapper"
              :title="`${h.weight}kg em ${formatDate(h.measured_at)}`"
            >
              <div 
                class="chart-bar" 
                :style="{ height: (h.weight / maxWeight * 100) + '%' }"
              ></div>
              <span class="bar-label">{{ formatDateShort(h.measured_at) }}</span>
            </div>
          </div>
        </div>
        <p v-else class="text-muted">Registre mais de uma medição para ver o gráfico.</p>
      </div>

      <!-- Tabela de Histórico -->
      <div class="glass-card history-card">
        <h3>📋 Últimas Medições</h3>
        <div v-if="history.length > 0" class="history-list">
          <div v-for="m in sortedHistory" :key="m.id" class="history-item">
            <div class="item-date">
              <strong>{{ formatDate(m.measured_at) }}</strong>
            </div>
            <div class="item-stats">
              <span>⚖️ {{ m.weight }}kg</span>
              <span v-if="m.body_fat">💧 {{ m.body_fat }}% BF</span>
            </div>
          </div>
        </div>
        <p v-else class="text-muted">Nenhuma medição registrada ainda.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '../stores/notificationStore'

const notify = useNotificationStore()
const loading = ref(false)
const history = ref([])
const newMetric = ref({
  weight: '', body_fat: '', chest: '', waist: '', biceps: '', thigh: ''
})

const maxWeight = computed(() => {
  if (history.value.length === 0) return 100
  return Math.max(...history.value.map(h => h.weight)) * 1.1
})

const sortedHistory = computed(() => {
  return [...history.value].sort((a, b) => new Date(b.measured_at) - new Date(a.measured_at))
})

function formatDate(date) {
  return new Date(date).toLocaleDateString('pt-BR')
}

function formatDateShort(date) {
  const d = new Date(date)
  return `${d.getDate()}/${d.getMonth() + 1}`
}

async function loadHistory() {
  try {
    const res = await axios.get('/metrics')
    history.value = res.data
  } catch (e) {
    console.error('Erro ao carregar histórico', e)
  }
}

async function saveMetrics() {
  loading.value = true
  try {
    await axios.post('/metrics', newMetric.value)
    notify.success('Medição registrada com sucesso!')
    newMetric.value = { weight: '', body_fat: '', chest: '', waist: '', biceps: '', thigh: '' }
    loadHistory()
  } catch (e) {
    notify.error('Erro ao salvar medição.')
  } finally {
    loading.value = false
  }
}

onMounted(loadHistory)
</script>

<style scoped>
.metrics-container { padding: 2rem 5%; max-width: 1200px; margin: 0 auto; }
.section-header { margin-bottom: 2rem; }
.metrics-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }

.form-card { grid-row: span 2; }
.metrics-form { display: flex; flex-direction: column; gap: 1.2rem; margin-top: 1.5rem; }
.form-row { display: flex; gap: 1rem; }
.input-group { flex: 1; display: flex; flex-direction: column; gap: 0.5rem; }
.input-group label { font-size: 0.85rem; color: var(--text-muted); }
.input-group input {
  padding: 0.8rem; background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1); border-radius: 10px;
  color: white; outline: none;
}
.input-group input:focus { border-color: var(--accent-primary); }

/* Gráfico Simples */
.simple-chart { height: 200px; margin-top: 2rem; display: flex; align-items: flex-end; }
.chart-bars { display: flex; align-items: flex-end; gap: 0.5rem; width: 100%; height: 100%; border-bottom: 1px solid rgba(255,255,255,0.1); }
.chart-bar-wrapper { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; height: 100%; justify-content: flex-end; }
.chart-bar { width: 100%; background: linear-gradient(to top, var(--accent-primary), var(--accent-secondary)); border-radius: 4px 4px 0 0; transition: height 0.5s ease; min-height: 2px; }
.bar-label { font-size: 0.7rem; color: var(--text-muted); }

.history-list { margin-top: 1.5rem; max-height: 300px; overflow-y: auto; }
.history-item { display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
.item-stats { display: flex; gap: 1rem; }

@media (max-width: 800px) {
  .metrics-grid { grid-template-columns: 1fr; }
  .form-card { grid-row: auto; }
}
</style>
