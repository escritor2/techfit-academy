<template>
  <div class="dashboard-container">
    <header class="dashboard-header">
      <h2>Painel da <span class="text-gradient">Recepção</span></h2>
      <p>Gerenciamento de entrada e atendimento aos membros.</p>
    </header>

    <div class="dashboard-grid">
      <div class="glass-card section-card" style="grid-column: span 2;">
        <h3>Registrar Presença</h3>
        <div class="checkin-container">
          <input type="text" placeholder="CPF do Membro..." class="checkin-input" />
          <button class="btn-primary">Registrar Entrada</button>
        </div>
      </div>
      
      <div class="glass-card section-card">
        <h3>Ações</h3>
        <div class="action-buttons">
          <button class="btn-outline">Novo Cadastro (Membro)</button>
          <button class="btn-outline">Vender Suplemento</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard-container {
  padding: 2rem 5%;
  max-width: 1200px;
  margin: 0 auto;
}

.dashboard-header {
  margin-bottom: 3rem;
}

.dashboard-header h2 {
  font-size: 2.5rem;
}

.checkin-container {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.checkin-input {
  flex: 1;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  color: white;
  font-size: 1.2rem;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1.5rem;
}
</style>
