<template>
  <div class="page-container">
    <header class="page-header" v-reveal>
      <NuxtLink to="/painel" class="btn-text-dim">← Voltar ao Painel</NuxtLink>
      <h1 class="section-title">Plano <span class="text-gradient">Nutricional</span></h1>
      <p class="subtitle">Sua combustível para a alta performance, calculado por IA.</p>
    </header>

    <div class="nutrition-layout">
      <!-- Diet Generator Form -->
      <Transition name="fade">
        <section v-if="!currentDiet || showForm" class="glass-card form-card" v-reveal>
          <div class="card-header-main">
            <span class="icon">🥗</span>
            <h3>Configurar Minha Dieta</h3>
          </div>
          
          <form @submit.prevent="generateDiet" class="nutrition-form">
            <div class="form-grid">
              <div class="form-group">
                <label>Objetivo</label>
                <select v-model="form.goal" required>
                  <option value="Perda de Peso">📉 Queima de Gordura / Definição</option>
                  <option value="Ganho de Massa">📈 Ganho de Massa / Bulking</option>
                  <option value="Manutenção">⚖️ Manutenção & Saúde</option>
                </select>
              </div>

              <div class="form-group">
                <label>Nível de Atividade</label>
                <select v-model="form.activity_level" required>
                  <option value="Sedentário">🛋️ Sedentário</option>
                  <option value="Leve">🚶 Leve (1-2x/sem)</option>
                  <option value="Moderado">🏃 Moderado (3-5x/sem)</option>
                  <option value="Intenso">🏋️ Intenso (6-7x/sem)</option>
                </select>
              </div>

              <div class="form-group">
                <label>Idade</label>
                <input v-model="form.age" type="number" required placeholder="Anos">
              </div>

              <div class="form-group">
                <label>Peso (kg)</label>
                <input v-model="form.weight" type="number" required placeholder="00">
              </div>

              <div class="form-group">
                <label>Altura (cm)</label>
                <input v-model="form.height" type="number" required placeholder="000">
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary btn-full" :disabled="loading">
                <span v-if="!loading">✨ Gerar Plano Nutricional IA</span>
                <div v-else class="loader-dots"><span></span><span></span><span></span></div>
              </button>
              <button v-if="currentDiet" type="button" class="btn-outline btn-full" @click="showForm = false">Cancelar</button>
            </div>
          </form>
        </section>
      </Transition>

      <!-- Current Diet Display -->
      <main v-if="currentDiet && !showForm" class="diet-results">
        <!-- Macros Overview -->
        <section class="glass-card macros-banner" v-reveal>
          <div class="banner-content">
            <div class="macro-main">
              <span class="m-val">{{ currentDiet.daily_calories }}</span>
              <span class="m-lab">Kcal / dia</span>
            </div>
            <div class="macro-divider"></div>
            <div class="macros-list">
              <div class="macro-box p">
                <span class="v">{{ currentDiet.protein_grams }}g</span>
                <span class="l">Proteína</span>
              </div>
              <div class="macro-box c">
                <span class="v">{{ currentDiet.carbs_grams }}g</span>
                <span class="l">Carbos</span>
              </div>
              <div class="macro-box f">
                <span class="v">{{ currentDiet.fats_grams }}g</span>
                <span class="l">Gorduras</span>
              </div>
            </div>
          </div>
          <button class="btn-edit-floating" @click="showForm = true" title="Editar">✏️</button>
        </section>

        <!-- Menu Details -->
        <section class="glass-card diet-card" v-reveal>
          <div class="card-header">
            <h3>🍴 Cardápio Recomendado</h3>
            <span class="tag-ai">Powered by TechFit IA</span>
          </div>
          
          <div class="markdown-content" v-html="renderMarkdown(currentDiet.content)"></div>
          
          <footer class="diet-footer">
            <p>💡 Beba pelo menos 3L de água diariamente para otimizar os resultados.</p>
          </footer>
        </section>
      </main>
    </div>
  </div>
</template>

<script setup>
import { marked } from 'marked'

const authStore = useAuthStore()
const config = useRuntimeConfig()
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
    const data = await $fetch(`${config.public.apiBase}/nutrition/latest`, {
      headers: authStore.authHeaders
    })
    currentDiet.value = data
  } catch (e) {
    console.error(e)
  }
}

async function generateDiet() {
  loading.value = true
  try {
    const res = await $fetch(`${config.public.apiBase}/nutrition/generate`, {
      method: 'POST',
      body: form.value,
      headers: authStore.authHeaders
    })
    currentDiet.value = res.diet
    showForm.value = false
  } catch (e) {
    alert('Erro ao gerar dieta.')
  } finally {
    loading.value = false
  }
}

onMounted(loadLatestDiet)
</script>

<style scoped>
.nutrition-layout { max-width: 900px; margin: 0 auto; }

.form-card { padding: 3rem; }
.card-header-main { display: flex; align-items: center; gap: 1rem; margin-bottom: 2.5rem; }
.card-header-main .icon { font-size: 2rem; }
.card-header-main h3 { font-size: 1.4rem; font-weight: 800; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
@media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }

.form-actions { display: flex; flex-direction: column; gap: 1rem; }

.macros-banner { padding: 2.5rem; margin-bottom: 2rem; position: relative; overflow: visible; }
.banner-content { display: flex; align-items: center; justify-content: space-between; gap: 2rem; }

@media (max-width: 768px) {
  .banner-content { flex-direction: column; text-align: center; }
}

.macro-main { display: flex; flex-direction: column; align-items: center; }
.macro-main .m-val { font-size: 3rem; font-weight: 900; line-height: 1; color: var(--accent-primary); }
.macro-main .m-lab { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--text-dim); }

.macro-divider { width: 2px; height: 60px; background: var(--glass-border); }
@media (max-width: 768px) { .macro-divider { width: 100%; height: 2px; } }

.macros-list { display: flex; gap: 2rem; flex: 1; justify-content: space-around; }

.macro-box { display: flex; flex-direction: column; align-items: center; }
.macro-box .v { font-size: 1.5rem; font-weight: 800; }
.macro-box .l { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; }
.macro-box.p .v { color: var(--accent-secondary); }
.macro-box.c .v { color: var(--accent-primary); }
.macro-box.f .v { color: var(--accent-tertiary); }

.btn-edit-floating {
  position: absolute;
  top: -15px;
  right: -15px;
  width: 44px;
  height: 44px;
  background: var(--bg-surface);
  border: 2px solid var(--accent-primary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 0 20px rgba(var(--accent-primary-rgb), 0.3);
  transition: 0.3s;
}
.btn-edit-floating:hover { transform: rotate(15deg) scale(1.1); }

.diet-card { padding: 3rem; }
.diet-card .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--glass-border); }
.tag-ai { font-size: 0.7rem; font-weight: 800; color: var(--accent-primary); text-transform: uppercase; letter-spacing: 1px; }

.markdown-content :deep(h2), .markdown-content :deep(h3) { color: var(--accent-primary); margin: 2rem 0 1rem; }
.markdown-content :deep(ul) { padding-left: 1.5rem; margin-bottom: 1.5rem; }
.markdown-content :deep(li) { margin-bottom: 0.8rem; }

.diet-footer { margin-top: 4rem; padding: 1.5rem; background: rgba(var(--accent-primary-rgb), 0.05); border-radius: 12px; font-size: 0.9rem; color: var(--accent-primary); font-weight: 600; text-align: center; }
</style>
