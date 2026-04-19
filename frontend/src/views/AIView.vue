<template>
  <div class="ai-container animate-fade-in">
    <div class="glass-card chat-card">
      <h2 class="text-gradient">Techfit AI Assistant</h2>
      <p class="text-muted">Gere seu treino personalizado em segundos.</p>

      <div class="form-group">
        <label>Qual seu objetivo?</label>
        <select v-model="goal">
          <option value="Hipertrofia">Hipertrofia</option>
          <option value="Emagrecimento">Emagrecimento</option>
          <option value="Resistência">Resistência</option>
        </select>
      </div>

      <div class="form-group">
        <label>Seu nível atual:</label>
        <select v-model="level">
          <option value="Iniciante">Iniciante</option>
          <option value="Intermediário">Intermediário</option>
          <option value="Avançado">Avançado</option>
        </select>
      </div>

      <button @click="generateWorkout" class="btn-primary" :disabled="loading">
        {{ loading ? 'Gerando...' : 'Gerar Treino' }}
      </button>

      <div v-if="result" class="result-area animate-fade-in">
        <hr>
        <div class="markdown-body" v-html="result"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const goal = ref('Hipertrofia');
const level = ref('Iniciante');
const loading = ref(false);
const result = ref('');

const generateWorkout = async () => {
  loading.ref = true;
  // Simulação de chamada para o backend Laravel
  // No mundo real: await fetch('/api/ai/generate-workout', { method: 'POST', body: JSON.stringify({ goal, level }) })
  
  setTimeout(() => {
    result.value = `
      ### Seu Treino Personalizado
      1. **Supino Reto**: 3x12
      2. **Crucifixo**: 3x15
      3. **Tríceps Pulley**: 4x10
      
      *Dica: Mantenha a cadência 2-0-2.*
    `;
    loading.value = false;
  }, 1500);
};
</script>

<style scoped>
.ai-container {
  padding: 4rem 5%;
  display: flex;
  justify-content: center;
}

.chat-card {
  max-width: 600px;
  width: 100%;
}

.form-group {
  margin: 1.5rem 0;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--text-muted);
}

select {
  width: 100%;
  padding: 0.8rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--glass-border);
  color: white;
  border-radius: 10px;
}

.result-area {
  margin-top: 2rem;
  line-height: 1.6;
}

hr {
  border: 0;
  border-top: 1px solid var(--glass-border);
  margin-bottom: 2rem;
}
</style>
