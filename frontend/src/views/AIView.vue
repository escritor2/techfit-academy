<template>
  <div class="ai-container animate-fade-in">
    <div class="glass-card chat-card">
      <h2 class="text-gradient">TechFit AI Assistant</h2>
      <p class="text-muted">Gere seu treino personalizado em segundos com inteligência artificial.</p>

      <div class="form-group">
        <label>Qual seu objetivo?</label>
        <select v-model="goal">
          <option value="Hipertrofia">💪 Hipertrofia</option>
          <option value="Emagrecimento">🔥 Emagrecimento</option>
          <option value="Resistência">⚡ Resistência</option>
        </select>
      </div>

      <div class="form-group">
        <label>Seu nível atual:</label>
        <select v-model="level">
          <option value="Iniciante">🌱 Iniciante</option>
          <option value="Intermediário">📈 Intermediário</option>
          <option value="Avançado">🏆 Avançado</option>
        </select>
      </div>

      <button @click="generateWorkout" class="btn-primary generate-btn" :disabled="loading">
        <span v-if="loading" class="spinner"></span>
        {{ loading ? 'Gerando com IA...' : '✨ Gerar Treino' }}
      </button>

      <!-- Resultado -->
      <div v-if="result" class="result-area animate-fade-in">
        <hr>
        <div class="result-header">
          <h3>Seu Treino Gerado</h3>
          <div class="result-tags">
            <span class="tag">{{ goal }}</span>
            <span class="tag">{{ level }}</span>
          </div>
        </div>
        <div class="markdown-body" v-html="renderedContent"></div>
      </div>

      <!-- Histórico -->
      <div v-if="history.length > 0" class="history-section">
        <hr>
        <h3>📋 Histórico de Treinos</h3>
        <div v-for="w in history" :key="w.id" class="history-item glass-card" @click="showHistoryItem(w)">
          <div class="history-meta">
            <span class="tag small">{{ w.goal }}</span>
            <span class="tag small">{{ w.level }}</span>
          </div>
          <p class="history-date">{{ new Date(w.created_at).toLocaleDateString('pt-BR') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/authStore'
import { useNotificationStore } from '../stores/notificationStore'

const authStore = useAuthStore()
const notify = useNotificationStore()

const goal = ref('Hipertrofia')
const level = ref('Iniciante')
const loading = ref(false)
const result = ref('')
const history = ref([])

// Simple markdown to HTML converter
function markdownToHtml(md) {
  if (!md) return ''
  let html = md
    // Headers
    .replace(/^### (.+)$/gm, '<h4>$1</h4>')
    .replace(/^## (.+)$/gm, '<h3>$1</h3>')
    .replace(/^# (.+)$/gm, '<h2>$1</h2>')
    // Bold
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    // Italic
    .replace(/\*(.+?)\*/g, '<em>$1</em>')
    // Blockquote
    .replace(/^> (.+)$/gm, '<blockquote>$1</blockquote>')
    // Tables
    .replace(/\|(.+)\|/g, (match) => {
      const cells = match.split('|').filter(c => c.trim())
      if (cells.every(c => /^[-:\s]+$/.test(c.trim()))) return '' // separator row
      const tag = 'td'
      const row = cells.map(c => `<${tag}>${c.trim()}</${tag}>`).join('')
      return `<tr>${row}</tr>`
    })
    // Line breaks
    .replace(/\n\n/g, '</p><p>')
    .replace(/\n/g, '<br>')

  // Wrap in paragraphs
  html = '<p>' + html + '</p>'
  // Clean up empty paragraphs
  html = html.replace(/<p><\/p>/g, '')
  // Wrap table rows
  html = html.replace(/(<tr>.*?<\/tr>)/gs, '<table class="workout-table">$1</table>')

  return html
}

const renderedContent = computed(() => markdownToHtml(result.value))

const generateWorkout = async () => {
  if (!authStore.isAuthenticated) {
    notify.warning('Faça login para gerar treinos personalizados.')
    return
  }

  loading.value = true
  result.value = ''

  try {
    const response = await axios.post('/ai/generate-workout', {
      goal: goal.value,
      level: level.value,
    })

    result.value = response.data.content
    
    // Save to localStorage as well
    localStorage.setItem('techfit_workout', JSON.stringify({
      name: `Treino ${goal.value} - ${level.value}`,
      preview: result.value.substring(0, 120),
      content: result.value,
    }))

    // Refresh history
    loadHistory()
    notify.success('Treino gerado com sucesso! 🎉')
  } catch (error) {
    notify.error(error.response?.data?.message || 'Erro ao gerar treino.')
  } finally {
    loading.value = false
  }
}

const loadHistory = async () => {
  if (!authStore.isAuthenticated) return
  try {
    const response = await axios.get('/my/workouts')
    history.value = response.data
  } catch (e) {
    // silent
  }
}

const showHistoryItem = (workout) => {
  result.value = workout.content
  goal.value = workout.goal
  level.value = workout.level
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(loadHistory)
</script>

<style scoped>
.ai-container {
  padding: 4rem 5%;
  display: flex;
  justify-content: center;
}

.chat-card {
  max-width: 750px;
  width: 100%;
}

.form-group {
  margin: 1.5rem 0;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--text-muted);
  font-weight: 500;
}

select {
  width: 100%;
  padding: 0.8rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--glass-border);
  color: white;
  border-radius: 10px;
  font-size: 1rem;
  cursor: pointer;
  transition: border-color 0.3s;
}

select:focus {
  outline: none;
  border-color: var(--accent-primary);
}

.generate-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 1rem;
  padding: 1rem;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(0,0,0,0.2);
  border-top-color: #000;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.result-area {
  margin-top: 2rem;
  line-height: 1.7;
}

.result-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.result-tags {
  display: flex;
  gap: 0.5rem;
}

.tag {
  background: rgba(0, 242, 255, 0.1);
  border: 1px solid rgba(0, 242, 255, 0.3);
  color: var(--accent-primary);
  padding: 4px 12px;
  border-radius: 50px;
  font-size: 0.8rem;
  font-weight: 600;
}

.tag.small { font-size: 0.7rem; padding: 2px 8px; }

hr {
  border: 0;
  border-top: 1px solid var(--glass-border);
  margin: 2rem 0;
}

.markdown-body :deep(h2), .markdown-body :deep(h3), .markdown-body :deep(h4) {
  margin: 1.5rem 0 0.8rem;
  color: var(--accent-primary);
}

.markdown-body :deep(strong) { color: white; }

.markdown-body :deep(blockquote) {
  border-left: 3px solid var(--accent-primary);
  padding-left: 1rem;
  margin: 1rem 0;
  color: var(--text-muted);
  font-style: italic;
}

.markdown-body :deep(.workout-table) {
  width: 100%;
  border-collapse: collapse;
  margin: 1rem 0;
}

.markdown-body :deep(td) {
  padding: 0.6rem 1rem;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  font-size: 0.9rem;
}

.markdown-body :deep(tr:first-child td) {
  font-weight: 700;
  color: var(--accent-primary);
  border-bottom: 1px solid rgba(0, 242, 255, 0.2);
}

/* History */
.history-section {
  margin-top: 1rem;
}

.history-section h3 {
  margin-bottom: 1rem;
}

.history-item {
  padding: 1rem !important;
  margin-bottom: 0.8rem;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.history-meta {
  display: flex;
  gap: 0.5rem;
}

.history-date {
  color: var(--text-muted);
  font-size: 0.85rem;
}
</style>
