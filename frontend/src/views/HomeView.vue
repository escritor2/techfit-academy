<template>
  <div class="home">
    <section class="hero animate-fade-in">
      <h1 class="logo">TECH<span class="text-gradient">FIT</span></h1>
      <p class="subtitle">Evolua seu corpo com inteligência artificial e alta performance.</p>
      <div class="hero-actions">
        <button class="btn-primary">Ver Treinos</button>
        <button class="btn-outline">Loja Suplementos</button>
      </div>
    </section>

    <div class="dashboard-grid">
      <div class="glass-card animate-fade-in">
        <h3>Treino do Dia</h3>
        <p class="text-muted">Personalizado pela IA para você.</p>
        <div class="stats">
          <span>Peito & Tríceps</span>
          <span>45 min</span>
        </div>
      </div>

      <div class="glass-card animate-fade-in" style="animation-delay: 0.2s">
        <h3>Meu Progresso</h3>
        <div class="progress-bar">
          <div class="progress" style="width: 75%"></div>
        </div>
        <p class="text-muted">75% da meta mensal atingida!</p>
      </div>

      <div class="glass-card animate-fade-in" style="animation-delay: 0.4s">
        <h3>IA Assistant</h3>
        <p class="text-muted">Pergunte qualquer coisa sobre seu treino.</p>
        <router-link to="/ai" class="btn-primary" style="display: inline-block; margin-top: 1rem; text-decoration: none; text-align: center;">Falar com IA</router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.home {
  padding-bottom: 5rem;
}

.hero {
  text-align: center;
  padding: 8rem 5%;
  background: radial-gradient(circle at center, rgba(0, 242, 255, 0.1) 0%, transparent 70%);
}

.hero h1 {
  font-size: 5rem;
  margin-bottom: 1rem;
}

.subtitle {
  font-size: 1.5rem;
  color: var(--text-muted);
  max-width: 600px;
  margin: 0 auto 2rem;
}

.hero-actions {
  display: flex;
  justify-content: center;
  gap: 1.5rem;
}

.btn-outline {
  background: transparent;
  border: 1px solid var(--accent-primary);
  color: var(--accent-primary);
  padding: 0.8rem 1.5rem;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
}

.stats {
  display: flex;
  justify-content: space-between;
  margin-top: 2rem;
  font-weight: 600;
  color: var(--accent-secondary);
}

.progress-bar {
  background: rgba(255, 255, 255, 0.1);
  height: 10px;
  border-radius: 5px;
  margin: 1.5rem 0 0.5rem;
  overflow: hidden;
}

.progress {
  background: var(--accent-primary);
  height: 100%;
  box-shadow: 0 0 10px var(--accent-primary);
}
</style>
