<template>
  <div class="page-container">
    <header class="page-header" v-reveal>
      <NuxtLink to="/painel" class="btn-text-dim">← Voltar ao Painel</NuxtLink>
      <h1 class="section-title">Evolução <span class="text-gradient">Corporal</span></h1>
      <p class="subtitle">Transforme dados em resultados. Monitore cada centímetro da sua jornada.</p>
    </header>

    <div class="metrics-layout">
      <!-- Input Section -->
      <section class="glass-card record-card" v-reveal>
        <div class="card-header-main">
          <span class="icon">📏</span>
          <h3>Nova Medição</h3>
        </div>
        <form @submit.prevent="saveMetrics" class="metrics-form">
          <div class="input-grid">
            <div class="form-group">
              <label>Peso (kg)</label>
              <input v-model="newMetric.weight" type="number" step="0.1" required placeholder="00.0">
            </div>
            <div class="form-group">
              <label>Gordura (%)</label>
              <input v-model="newMetric.body_fat" type="number" step="0.1" placeholder="00.0">
            </div>
            <div class="form-group">
              <label>Peitoral (cm)</label>
              <input v-model="newMetric.chest" type="number" step="0.1" placeholder="00">
            </div>
            <div class="form-group">
              <label>Cintura (cm)</label>
              <input v-model="newMetric.waist" type="number" step="0.1" placeholder="00">
            </div>
            <div class="form-group">
              <label>Bíceps (cm)</label>
              <input v-model="newMetric.biceps" type="number" step="0.1" placeholder="00">
            </div>
            <div class="form-group">
              <label>Coxa (cm)</label>
              <input v-model="newMetric.thigh" type="number" step="0.1" placeholder="00">
            </div>
          </div>
          <button type="submit" class="btn-primary" :disabled="loading">
            {{ loading ? 'Salvando...' : 'Registrar Medição' }}
          </button>
        </form>
      </section>

      <!-- Visualization Section -->
      <div class="viz-section">
        <!-- Chart Card -->
        <section class="glass-card chart-card" v-reveal>
          <div class="chart-header">
            <h3>📈 Histórico de Peso</h3>
            <div class="chart-tags">
              <span class="tag active">Peso</span>
              <span class="tag">BF%</span>
            </div>
          </div>
          
          <div v-if="history.length > 1" class="chart-wrapper">
            <Line :data="chartData" :options="chartOptions" />
          </div>
          <div v-else class="empty-viz">
            <div class="empty-icon">📊</div>
            <p>Registre ao menos 2 medições para visualizar sua evolução gráfica.</p>
          </div>
        </section>

        <!-- History List -->
        <section class="glass-card history-card" v-reveal>
          <h3>📋 Registros Anteriores</h3>
          <div v-if="history.length > 0" class="history-table-wrapper">
            <table class="history-table">
              <thead>
                <tr>
                  <th>Data</th>
                  <th>Peso</th>
                  <th>BF%</th>
                  <th>Cintura</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="m in sortedHistory" :key="m.id">
                  <td>{{ formatDate(m.measured_at) }}</td>
                  <td class="text-glow">{{ m.weight }} kg</td>
                  <td>{{ m.body_fat ? m.body_fat + '%' : '--' }}</td>
                  <td>{{ m.waist ? m.waist + ' cm' : '--' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-else class="empty-msg">Ainda não há dados registrados.</p>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const authStore = useAuthStore()
const config = useRuntimeConfig()
const loading = ref(false)
const history = ref([])
const newMetric = ref({
  weight: '', body_fat: '', chest: '', waist: '', biceps: '', thigh: ''
})

// Chart Configuration
const chartData = computed(() => {
  const sorted = [...history.value].sort((a, b) => new Date(a.measured_at) - new Date(b.measured_at))
  return {
    labels: sorted.map(h => formatDateShort(h.measured_at)),
    datasets: [
      {
        label: 'Peso (kg)',
        backgroundColor: 'rgba(0, 242, 255, 0.1)',
        borderColor: '#00f2ff',
        pointBackgroundColor: '#00f2ff',
        pointBorderColor: '#fff',
        pointHoverBackgroundColor: '#fff',
        pointHoverBorderColor: '#00f2ff',
        fill: true,
        tension: 0.4,
        data: sorted.map(h => h.weight)
      }
    ]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: 'rgba(10, 10, 12, 0.9)',
      titleFont: { size: 14, weight: 'bold' },
      bodyFont: { size: 14 },
      padding: 12,
      cornerRadius: 10,
      displayColors: false
    }
  },
  scales: {
    y: {
      grid: { color: 'rgba(255, 255, 255, 0.05)' },
      ticks: { color: 'rgba(255, 255, 255, 0.5)', font: { size: 12 } }
    },
    x: {
      grid: { display: false },
      ticks: { color: 'rgba(255, 255, 255, 0.5)', font: { size: 12 } }
    }
  }
}

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
    const data = await $fetch(`${config.public.apiBase}/metrics`, {
      headers: authStore.authHeaders
    })
    history.value = data || []
  } catch (e) {
    console.error(e)
  }
}

async function saveMetrics() {
  loading.value = true
  try {
    await $fetch(`${config.public.apiBase}/metrics`, {
      method: 'POST',
      body: newMetric.value,
      headers: authStore.authHeaders
    })
    alert('Medição salva! 💪')
    newMetric.value = { weight: '', body_fat: '', chest: '', waist: '', biceps: '', thigh: '' }
    loadHistory()
  } catch (e) {
    alert('Erro ao salvar medição.')
  } finally {
    loading.value = false
  }
}

onMounted(loadHistory)
</script>

<style scoped>
.metrics-layout {
  display: grid;
  grid-template-columns: 400px 1fr;
  gap: 3rem;
  align-items: start;
}

@media (max-width: 1100px) {
  .metrics-layout { grid-template-columns: 1fr; }
}

.record-card { padding: 3rem; }
.card-header-main { display: flex; align-items: center; gap: 1rem; margin-bottom: 2.5rem; }
.card-header-main .icon { font-size: 2rem; }
.card-header-main h3 { font-size: 1.4rem; font-weight: 800; }

.metrics-form { display: flex; flex-direction: column; gap: 2rem; }
.input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }

.viz-section { display: flex; flex-direction: column; gap: 2.5rem; }

.chart-card { padding: 3rem; }
.chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.chart-tags { display: flex; gap: 0.8rem; }
.tag { padding: 0.3rem 1rem; border-radius: 50px; background: rgba(255,255,255,0.03); font-size: 0.75rem; font-weight: 700; color: var(--text-dim); cursor: pointer; }
.tag.active { background: var(--accent-primary); color: #000; }

.chart-wrapper { height: 350px; width: 100%; }

.history-card { padding: 3rem; }
.history-table-wrapper { margin-top: 2rem; }
.history-table { width: 100%; border-collapse: collapse; }
.history-table th { text-align: left; padding: 1.2rem; font-size: 0.8rem; text-transform: uppercase; color: var(--text-dim); }
.history-table td { padding: 1.2rem; border-top: 1px solid var(--glass-border); }

.empty-viz {
  height: 350px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  color: var(--text-dim);
  border: 1px dashed var(--glass-border);
  border-radius: 20px;
}
.empty-icon { font-size: 3rem; opacity: 0.2; }
</style>
