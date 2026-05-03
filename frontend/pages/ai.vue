<template>
  <div class="page-container">
    <div class="ai-layout">
      <!-- Configuration Panel -->
      <aside class="config-panel glass-card" v-reveal>
        <div class="ai-brand">
          <div class="ai-icon">✨</div>
          <div class="ai-text">
            <h2>AI Assistant</h2>
            <p>Seu coach digital 24/7</p>
          </div>
        </div>

        <div class="divider"></div>

        <div class="form-section">
          <div class="form-group">
            <label>Qual seu objetivo principal?</label>
            <div class="custom-select">
              <select v-model="goal">
                <option value="Hipertrofia">💪 Hipertrofia Muscular</option>
                <option value="Emagrecimento">🔥 Queima de Gordura</option>
                <option value="Resistência">⚡ Condicionamento & Resistência</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Seu nível de experiência:</label>
            <div class="custom-select">
              <select v-model="level">
                <option value="Iniciante">🌱 Iniciante (0-1 ano)</option>
                <option value="Intermediário">📈 Intermediário (1-3 anos)</option>
                <option value="Avançado">🏆 Avançado (3+ anos)</option>
              </select>
            </div>
          </div>

          <button @click="generateWorkout" class="btn-primary generate-btn" :disabled="loading">
            <span v-if="!loading">Gerar Treino com IA</span>
            <div v-else class="loader-dots"><span></span><span></span><span></span></div>
          </button>
        </div>

        <div v-if="history.length > 0" class="history-list">
          <h3>📋 Histórico Recente</h3>
          <div 
            v-for="w in history.slice(0, 5)" 
            :key="w.id" 
            class="history-item" 
            @click="showHistoryItem(w)"
          >
            <div class="h-info">
              <span class="h-goal">{{ w.goal }}</span>
              <span class="h-date">{{ new Date(w.created_at).toLocaleDateString('pt-BR') }}</span>
            </div>
            <span class="h-arrow">→</span>
          </div>
        </div>
      </aside>

      <!-- Results Display -->
      <main class="result-panel">
        <div v-if="!result && !loading" class="empty-state glass-card" v-reveal>
          <div class="ai-sparkle">✨</div>
          <h3>Pronto para evoluir?</h3>
          <p>Configure seus objetivos ao lado e deixe nossa IA criar o plano perfeito para você.</p>
        </div>

        <Transition name="fade-up">
          <div v-if="result" class="result-card glass-card">
            <div class="result-header">
              <div class="header-main">
                <span class="workout-tag">Protocolo Gerado</span>
                <h3 class="text-gradient">Seu Plano de Alta Performance</h3>
              </div>
              <div class="header-actions">
                <button class="btn-icon" @click="copyResult" title="Copiar">📋</button>
                <button class="btn-icon" @click="printResult" title="Imprimir">🖨️</button>
              </div>
            </div>
            
            <div class="markdown-container" v-html="renderedContent"></div>
            
            <div class="result-footer">
              <p>⚡ Treino otimizado pelo motor TechFit-GPT v4.0</p>
              <button class="btn-outline btn-small" @click="result = null">Limpar</button>
            </div>
          </div>
        </Transition>

        <div v-if="loading" class="loading-overlay">
          <div class="cyber-loader">
            <div class="loader-circle"></div>
            <p>Processando Bio-Métricas...</p>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { marked } from 'marked'

const goal = ref('Hipertrofia')
const level = ref('Iniciante')
const loading = ref(false)
const result = ref('')
const history = ref([])

const config = useRuntimeConfig()
const authStore = useAuthStore()

const renderedContent = computed(() => marked(result.value || ''))

const generateWorkout = async () => {
  if (!authStore.isAuthenticated) {
    alert('Por favor, faça login para gerar treinos.')
    return
  }

  loading.value = true
  result.value = ''

  try {
    const response = await $fetch(`${config.public.apiBase}/ai/generate-workout`, {
      method: 'POST',
      body: { goal: goal.value, level: level.value },
      headers: authStore.authHeaders
    })

    if (response.success) {
      const workout = response.data.workout
      if (workout.status === 'ready' || workout.status === 'fallback') {
        result.value = workout.content
        loadHistory()
      } else {
        // Iniciar polling
        pollWorkoutStatus(workout.id)
      }
    }
  } catch (error) {
    console.error('Erro ao gerar treino', error)
  } finally {
    if (result.value) loading.value = false
  }
}

const pollWorkoutStatus = async (id) => {
  const poll = setInterval(async () => {
    try {
      const response = await $fetch(`${config.public.apiBase}/ai/workout-status/${id}`, {
        headers: authStore.authHeaders
      })
      
      if (response.success && response.data.status === 'ready') {
        result.value = response.data.content
        loading.value = false
        clearInterval(poll)
        loadHistory()
      } else if (response.success && response.data.status === 'error') {
        alert('Erro ao gerar treino pela IA. Tente novamente.')
        loading.value = false
        clearInterval(poll)
      }
    } catch (e) {
      console.error('Erro no polling', e)
      clearInterval(poll)
      loading.value = false
    }
  }, 3000)
}

const loadHistory = async () => {
  if (!authStore.isAuthenticated) return
  try {
    const response = await $fetch(`${config.public.apiBase}/my/workouts`, {
      headers: authStore.authHeaders
    })
    history.value = response.data || []
  } catch (e) {
    console.error('Erro ao carregar histórico', e)
  }
}

const showHistoryItem = (workout) => {
  result.value = workout.content
  goal.value = workout.goal
  level.value = workout.level
}

const copyResult = () => {
  navigator.clipboard.writeText(result.value)
  alert('Copiado para a área de transferência!')
}

const printResult = () => window.print()

onMounted(loadHistory)
</script>

<style scoped>
.ai-layout {
  display: grid;
  grid-template-columns: 350px 1fr;
  gap: 3rem;
  align-items: start;
}

@media (max-width: 992px) {
  .ai-layout { grid-template-columns: 1fr; }
}

.config-panel {
  padding: 2.5rem;
  position: sticky;
  top: 100px;
}

.ai-brand {
  display: flex;
  align-items: center;
  gap: 1.2rem;
}

.ai-icon {
  font-size: 2.5rem;
  background: rgba(var(--accent-primary-rgb), 0.1);
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 16px;
  border: 1px solid var(--glass-border);
}

.ai-text h2 { font-size: 1.4rem; font-weight: 800; margin-bottom: 0.2rem; }
.ai-text p { font-size: 0.85rem; color: var(--text-muted); }

.divider {
  height: 1px;
  background: var(--glass-border);
  margin: 2rem 0;
}

.form-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--text-muted);
  margin-bottom: 0.8rem;
  text-transform: uppercase;
}

.generate-btn { width: 100%; height: 56px; margin-top: 1rem; }

.history-list {
  margin-top: 3rem;
}

.history-list h3 { font-size: 1rem; color: var(--text-muted); margin-bottom: 1.2rem; }

.history-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: rgba(255,255,255,0.02);
  border-radius: 12px;
  margin-bottom: 0.8rem;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.3s;
}

.history-item:hover {
  background: rgba(255,255,255,0.05);
  border-color: var(--glass-border);
  transform: translateX(5px);
}

.h-info { display: flex; flex-direction: column; }
.h-goal { font-weight: 700; font-size: 0.95rem; }
.h-date { font-size: 0.75rem; color: var(--text-dim); }
.h-arrow { opacity: 0.3; }

.result-panel {
  min-height: 500px;
  position: relative;
}

.empty-state {
  padding: 5rem;
  text-align: center;
}

.ai-sparkle { font-size: 4rem; margin-bottom: 1.5rem; animation: pulse 2s infinite; }

.result-card {
  padding: 3rem;
  animation: slideIn 0.5s ease-out;
}

@keyframes slideIn { from { opacity: 0; transform: translateY(30px); } }

.result-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 3rem;
  border-bottom: 1px solid var(--glass-border);
  padding-bottom: 2rem;
}

.workout-tag {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: var(--accent-primary);
  font-weight: 800;
  display: block;
  margin-bottom: 0.5rem;
}

.header-actions { display: flex; gap: 0.8rem; }

.btn-icon {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: 1px solid var(--glass-border);
  background: rgba(255,255,255,0.03);
  color: white;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-icon:hover { background: var(--accent-primary); color: #000; border-color: transparent; }

.markdown-container {
  line-height: 1.8;
  font-size: 1.1rem;
}

.markdown-container :deep(h2), .markdown-container :deep(h3) {
  margin-top: 2rem;
  margin-bottom: 1rem;
  color: var(--accent-primary);
}

.markdown-container :deep(ul) {
  padding-left: 1.5rem;
  margin-bottom: 1.5rem;
}

.markdown-container :deep(li) {
  margin-bottom: 0.8rem;
}

.markdown-container :deep(blockquote) {
  padding: 1.5rem;
  background: rgba(255,255,255,0.02);
  border-left: 4px solid var(--accent-primary);
  border-radius: 0 12px 12px 0;
  font-style: italic;
  margin: 2rem 0;
}

.result-footer {
  margin-top: 4rem;
  padding-top: 2rem;
  border-top: 1px solid var(--glass-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: var(--text-dim);
  font-size: 0.85rem;
}

.loading-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(5,5,7,0.7);
  backdrop-filter: blur(10px);
  z-index: 100;
  border-radius: var(--radius-lg);
}

.cyber-loader { text-align: center; }
.loader-circle {
  width: 80px;
  height: 80px;
  border: 4px solid rgba(var(--accent-primary-rgb), 0.1);
  border-top-color: var(--accent-primary);
  border-radius: 50%;
  animation: spin 1s infinite linear;
  margin: 0 auto 1.5rem;
}

@keyframes spin { to { transform: rotate(360deg); } }

.loader-dots { display: flex; gap: 6px; justify-content: center; }
.loader-dots span { width: 10px; height: 10px; background: #000; border-radius: 50%; animation: pulse 0.6s infinite alternate; }
.loader-dots span:nth-child(2) { animation-delay: 0.2s; }
.loader-dots span:nth-child(3) { animation-delay: 0.4s; }

@keyframes pulse { to { opacity: 0.3; transform: scale(0.8); } }
</style>
