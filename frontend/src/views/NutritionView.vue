<template>
  <div class="nutrition-container animate-fade-in">
    <header class="section-header">
      <h2>Plano <span class="text-gradient">Nutricional</span></h2>
      <p>Dieta personalizada gerada por nossa Inteligência Artificial.</p>
    </header>

    <div class="nutrition-grid">
      <!-- Gerador de Dieta -->
      <div class="glass-card gen-card" v-if="!currentDiet || showForm">
        <h3>🍏 Gerar Nova Dieta</h3>
        <form @submit.prevent="generateDiet" class="gen-form">
          <div class="input-group">
            <label>Qual seu objetivo?</label>
            <select v-model="form.goal" required>
              <option value="Perda de Peso">Perda de Peso / Definição</option>
              <option value="Ganho de Massa">Ganho de Massa / Bulking</option>
              <option value="Manutenção">Manutenção de Saúde</option>
            </select>
          </div>
          <div class="input-group">
            <label>Nível de Atividade</label>
            <select v-model="form.activity_level" required>
              <option value="Sedentário">Sedentário</option>
              <option value="Leve">Leve (1-2 dias/semana)</option>
              <option value="Moderado">Moderado (3-5 dias/semana)</option>
              <option value="Intenso">Intenso (6-7 dias/semana)</option>
            </select>
          </div>
          <div class="form-row">
            <div class="input-group">
              <label>Idade</label>
              <input v-model="form.age" type="number" required />
            </div>
            <div class="input-group">
              <label>Peso (kg)</label>
              <input v-model="form.weight" type="number" required />
            </div>
            <div class="input-group">
              <label>Altura (cm)</label>
              <input v-model="form.height" type="number" required />
            </div>
          </div>
          <button type="submit" class="btn-primary" :disabled="loading">
            {{ loading ? '🧠 Calculando dieta...' : '✨ Gerar Dieta Agora' }}
          </button>
          <button v-if="currentDiet" type="button" class="btn-outline" @click="showForm = false" style="margin-top:0.5rem;">
            Cancelar
          </button>
        </form>
      </div>

      <!-- Exibição da Dieta Atual -->
      <div v-if="currentDiet && !showForm" class="diet-display">
        <div class="glass-card macros-card">
          <div class="macros-grid">
            <div class="macro-item">
              <span class="macro-val">{{ currentDiet.daily_calories }}</span>
              <span class="macro-label">Kcal / dia</span>
            </div>
            <div class="macro-item">
              <span class="macro-val">{{ currentDiet.protein_grams }}g</span>
              <span class="macro-label">Proteína</span>
            </div>
            <div class="macro-item">
              <span class="macro-val">{{ currentDiet.carbs_grams }}g</span>
              <span class="macro-label">Carbos</span>
            </div>
            <div class="macro-item">
              <span class="macro-val">{{ currentDiet.fats_grams }}g</span>
              <span class="macro-label">Gorduras</span>
            </div>
          </div>
          <button class="btn-edit" @click="showForm = true">🔄 Atualizar Dieta</button>
        </div>

        <div class="glass-card diet-content-card">
          <h3>🍴 Seu Cardápio Sugerido</h3>
          <div class="markdown-body" v-html="renderMarkdown(currentDiet.content)"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '../stores/notificationStore'
import { marked } from 'marked'

const notify = useNotificationStore()
const loading = ref(false)
const showForm = ref(false)
const currentDiet = ref(null)

const form = ref({
  goal: 'Ganho de Massa',
  activity_level: 'Moderado',
  age: 25,
  weight: 75,
  height: 175
})

function renderMarkdown(text) {
  return marked(text || '')
}

async function loadLatestDiet() {
  try {
    const res = await axios.get('/nutrition/latest')
    currentDiet.value = res.data
  } catch (e) {
    console.error('Erro ao carregar dieta', e)
  }
}

async function generateDiet() {
  loading.value = true
  try {
    const res = await axios.post('/nutrition/generate', form.value)
    currentDiet.value = res.data.diet
    showForm.value = false
    notify.success('Dieta gerada com sucesso! 🍏')
  } catch (e) {
    notify.error('Erro ao gerar dieta com IA.')
  } finally {
    loading.value = false
  }
}

onMounted(loadLatestDiet)
</script>

<style scoped>
.nutrition-container { padding: 2rem 5%; max-width: 1200px; margin: 0 auto; }
.nutrition-grid { margin-top: 2rem; }

.gen-form { display: flex; flex-direction: column; gap: 1.5rem; margin-top: 1.5rem; }
.input-group { display: flex; flex-direction: column; gap: 0.5rem; }
.input-group label { font-size: 0.9rem; color: var(--text-muted); }
.input-group select, .input-group input {
  padding: 0.8rem; background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1); border-radius: 10px;
  color: white; outline: none;
}

.form-row { display: flex; gap: 1rem; }

.macros-card { margin-bottom: 1.5rem; position: relative; }
.macros-grid { display: grid; grid-template-columns: repeat(4, 1fr); text-align: center; }
.macro-item { display: flex; flex-direction: column; }
.macro-val { font-size: 1.8rem; font-weight: 900; color: var(--accent-primary); }
.macro-label { font-size: 0.8rem; color: var(--text-muted); }

.btn-edit {
  position: absolute; top: 1rem; right: 1rem;
  background: none; border: none; color: var(--accent-primary);
  font-size: 0.8rem; cursor: pointer; text-decoration: underline;
}

.diet-content-card { padding: 2rem; line-height: 1.6; }
.markdown-body :deep(h1), .markdown-body :deep(h2), .markdown-body :deep(h3) {
  margin-top: 1.5rem; color: var(--accent-primary);
}
.markdown-body :deep(ul) { padding-left: 1.5rem; }

@media (max-width: 600px) {
  .macros-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
  .form-row { flex-direction: column; }
}
</style>
