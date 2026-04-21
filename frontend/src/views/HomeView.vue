<template>
  <div class="home-dashboard">
    <!-- Hero / Welcome -->
    <header class="dashboard-hero animate-fade-in">
      <div class="welcome-text">
        <h1 class="logo">TECH<span class="text-gradient">FIT</span></h1>
        <p class="subtitle">Sua jornada de alta performance potencializada por IA.</p>
      </div>
      <div class="hero-actions">
        <router-link to="/ai" class="btn-primary">
          ✨ Gerar Treino IA
        </router-link>
        <router-link to="/loja" class="btn-outline">
          🛒 Ver Suplementos
        </router-link>
      </div>
    </header>

    <!-- Main Grid -->
    <main class="dashboard-grid">
      <!-- Card 1: Treino do Dia -->
      <section v-reveal class="glass-card main-card animate-fade-in" style="animation-delay: 0.1s">
        <div class="card-icon">⚡</div>
        <div class="card-content">
          <h3>Plano de Hoje</h3>
          <p class="text-muted">Foco em Hipertrofia Muscular</p>
          <div class="workout-status">
            <span class="status-badge">Peito & Tríceps</span>
            <span class="duration">⏱️ 55 min</span>
          </div>
          <button class="btn-text">Ver detalhes do treino →</button>
        </div>
      </section>

      <!-- Card 2: Evolução -->
      <section v-reveal class="glass-card main-card animate-fade-in" style="animation-delay: 0.2s">
        <div class="card-icon">📈</div>
        <div class="card-content">
          <h3>Evolução Semanal</h3>
          <div class="progress-container">
            <div class="progress-info">
              <span>Meta Mensal</span>
              <span>78%</span>
            </div>
            <div class="premium-progress">
              <div class="progress-fill" style="width: 78%"></div>
            </div>
          </div>
          <p class="text-muted small">Você está 12% acima da semana passada!</p>
        </div>
      </section>

      <!-- Card 3: Nutrição -->
      <section v-reveal class="glass-card main-card animate-fade-in" style="animation-delay: 0.3s">
        <div class="card-icon">🥗</div>
        <div class="card-content">
          <h3>Minha Dieta</h3>
          <div class="macros-preview">
            <div class="macro">
              <span class="m-val">150g</span>
              <span class="m-label">Prot</span>
            </div>
            <div class="macro">
              <span class="m-val">220g</span>
              <span class="m-label">Carb</span>
            </div>
            <div class="macro">
              <span class="m-val">65g</span>
              <span class="m-label">Gord</span>
            </div>
          </div>
          <router-link to="/nutricao" class="btn-text">Acessar plano nutricional →</router-link>
        </div>
      </section>

      <!-- Card 4: Ranking -->
      <section v-reveal class="glass-card main-card animate-fade-in" style="animation-delay: 0.4s">
        <div class="card-icon">🏆</div>
        <div class="card-content">
          <h3>Leaderboard</h3>
          <div class="ranking-preview">
            <div class="rank-item">
              <span class="rank-pos">#1</span>
              <span class="rank-name">Você</span>
              <span class="rank-pts">2,450 XP</span>
            </div>
            <div class="rank-item inactive">
              <span class="rank-pos">#2</span>
              <span class="rank-name">Gabriel S.</span>
              <span class="rank-pts">2,100 XP</span>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Achievement Badge (Premium Detail) -->
    <div class="achievement-badge animate-fade-in" style="animation-delay: 0.6s">
      <div class="badge-icon">🎖️</div>
      <div class="badge-text">
        <strong>Conquista Desbloqueada</strong>
        <span>Guerreiro da Alvorada (5 check-ins às 6h)</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.home-dashboard {
  padding: 2rem 5% 5rem;
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-hero {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 3rem;
  padding: 4rem 3rem;
  background: linear-gradient(135deg, rgba(0, 242, 255, 0.05) 0%, transparent 100%);
  border-radius: 30px;
  border: 1px solid var(--glass-border);
}

@media (max-width: 768px) {
  .dashboard-hero { flex-direction: column; text-align: center; gap: 2rem; }
}

.welcome-text h1 { font-size: 3.5rem; margin-bottom: 0.5rem; }
.subtitle { font-size: 1.1rem; color: var(--text-muted); }

.hero-actions { display: flex; gap: 1rem; }

.btn-outline {
  background: transparent;
  border: 1px solid var(--glass-border);
  color: #fff;
  padding: 0.8rem 1.5rem;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.3s;
}

.btn-outline:hover {
  border-color: var(--accent-primary);
  background: rgba(255,255,255,0.05);
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.main-card {
  display: flex;
  gap: 1.5rem;
  padding: 2.5rem !important;
  align-items: flex-start;
}

.card-icon {
  font-size: 2.5rem;
  background: rgba(255,255,255,0.03);
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 15px;
  border: 1px solid var(--glass-border);
}

.card-content { flex: 1; }
.card-content h3 { font-size: 1.4rem; margin-bottom: 0.5rem; }

.workout-status {
  display: flex;
  justify-content: space-between;
  margin: 1.5rem 0;
  font-weight: 700;
}

.status-badge {
  color: var(--accent-secondary);
  background: rgba(188, 255, 0, 0.1);
  padding: 4px 12px;
  border-radius: 50px;
  font-size: 0.8rem;
}

.btn-text {
  background: none;
  border: none;
  color: var(--accent-primary);
  font-weight: 700;
  cursor: pointer;
  padding: 0;
  margin-top: 1rem;
  text-decoration: none;
  display: inline-block;
}

/* Progress */
.progress-container { margin-top: 1.5rem; }
.progress-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-weight: 600;
  font-size: 0.9rem;
}

.premium-progress {
  height: 8px;
  background: rgba(255,255,255,0.05);
  border-radius: 10px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
  box-shadow: 0 0 15px rgba(0, 242, 255, 0.5);
  border-radius: 10px;
}

/* Macros */
.macros-preview {
  display: flex;
  justify-content: space-between;
  margin: 1.5rem 0;
  background: rgba(255,255,255,0.02);
  padding: 1rem;
  border-radius: 15px;
}

.macro { text-align: center; }
.m-val { display: block; font-weight: 900; font-size: 1.1rem; }
.m-label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; }

/* Ranking */
.ranking-preview { margin-top: 1.5rem; }
.rank-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.8rem;
  background: rgba(0, 242, 255, 0.05);
  border-radius: 12px;
  margin-bottom: 0.5rem;
}

.rank-item.inactive { opacity: 0.5; background: transparent; }
.rank-pos { font-weight: 900; color: var(--accent-primary); }
.rank-name { flex: 1; font-weight: 600; }
.rank-pts { font-size: 0.8rem; color: var(--text-muted); }

/* Badge */
.achievement-badge {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  background: rgba(5,5,5,0.8);
  backdrop-filter: blur(20px);
  border: 1px solid var(--accent-secondary);
  padding: 1rem 1.5rem;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 10px 30px rgba(188, 255, 0, 0.2);
  z-index: 10;
}

.badge-icon { font-size: 1.8rem; }
.badge-text { display: flex; flex-direction: column; }
.badge-text strong { color: var(--accent-secondary); }
.badge-text span { font-size: 0.8rem; color: var(--text-muted); }

@media (max-width: 600px) {
  .achievement-badge { bottom: 1rem; left: 1rem; right: 1rem; }
}
</style>
