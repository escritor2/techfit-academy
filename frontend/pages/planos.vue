<template>
  <div class="page-container">
    <header class="page-header" v-reveal>
      <h1 class="section-title">Escolha seu <span class="text-gradient">Plano</span></h1>
      <p class="subtitle">Acesso ilimitado ao futuro da sua performance.</p>
    </header>

    <div v-if="loading" class="loading-state">
      <div class="loader-dots"><span></span><span></span><span></span></div>
    </div>

    <div v-else class="plans-grid">
      <div 
        v-for="plan in plans" 
        :key="plan.id" 
        class="glass-card plan-card" 
        :class="{ 'featured': plan.name.toLowerCase().includes('anual') }"
        v-reveal
      >
        <div v-if="plan.name.toLowerCase().includes('anual')" class="featured-badge">Mais Popular</div>
        
        <div class="plan-header">
          <h3>{{ plan.name }}</h3>
          <p class="plan-duration">{{ plan.duration_days }} dias de acesso</p>
        </div>

        <div class="plan-price">
          <span class="currency">R$</span>
          <span class="value">{{ plan.price.split('.')[0] }}</span>
          <span class="cents">,{{ plan.price.split('.')[1] || '00' }}</span>
        </div>

        <div class="plan-divider"></div>

        <ul class="plan-features">
          <li><span>✨</span> Acesso 24/7 à Unidade</li>
          <li><span>🤖</span> Personal Trainer IA Ilimitado</li>
          <li><span>🥗</span> Plano Nutricional IA</li>
          <li><span>🏆</span> Ranking & Medalhas Elite</li>
          <li><span>📱</span> App TechFit Premium</li>
        </ul>

        <button @click="openCheckout(plan)" class="btn-primary btn-full">
          Assinar Agora
        </button>
      </div>
    </div>

    <!-- Premium Checkout Modal -->
    <Transition name="fade">
      <div v-if="showCheckout" class="modal-overlay" @click.self="showCheckout = false">
        <div class="glass-card checkout-modal" v-reveal>
          <div class="modal-header">
            <h3>💳 Checkout Seguro</h3>
            <button class="close-btn" @click="showCheckout = false">✕</button>
          </div>

          <div class="checkout-body">
            <div class="selected-plan-box">
              <span class="lab">Plano Selecionado</span>
              <div class="val">
                <strong>{{ selectedPlan.name }}</strong>
                <span class="price">R$ {{ selectedPlan.price }}</span>
              </div>
            </div>

            <div class="payment-selection">
              <p class="label">Método de Pagamento</p>
              <div class="methods-grid">
                <label class="method-card" :class="{ active: paymentMethod === 'pix' }">
                  <input type="radio" v-model="paymentMethod" value="pix" hidden>
                  <span class="m-icon">⚡</span>
                  <span class="m-text">PIX</span>
                </label>
                <label class="method-card" :class="{ active: paymentMethod === 'card' }">
                  <input type="radio" v-model="paymentMethod" value="card" hidden>
                  <span class="m-icon">💳</span>
                  <span class="m-text">Cartão</span>
                </label>
              </div>
            </div>

            <div class="security-info">
              <span>🔒</span>
              <p>Pagamento processado com criptografia de ponta a ponta.</p>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn-primary btn-full" @click="confirmPayment" :disabled="processing">
              {{ processing ? 'Processando...' : 'Confirmar e Ativar' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
const authStore = useAuthStore()
import axios from 'axios'
const config = useRuntimeConfig()
const router = useRouter()

const plans = ref([])
const loading = ref(true)
const showCheckout = ref(false)
const selectedPlan = ref(null)
const paymentMethod = ref('pix')
const processing = ref(false)

async function loadPlans() {
  try {
    const data = await $fetch(`${config.public.apiBase}/plans`)
    plans.value = data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openCheckout(plan) {
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }
  selectedPlan.value = plan
  showCheckout.value = true
}

async function confirmPayment() {
  processing.value = true
  try {
    await axios.post('/subscriptions/checkout', {
        plan_id: selectedPlan.value.id,
        payment_method: paymentMethod.value
    }, { headers: authStore.authHeaders })
    alert('Assinatura ativada! Bem-vindo à elite. 🎉')
    showCheckout.value = false
    router.push('/painel')
  } catch (e) {
    alert('Erro ao processar pagamento.')
  } finally {
    processing.value = false
  }
}

onMounted(loadPlans)
</script>

<style scoped>
.page-header { text-align: center; margin-bottom: 5rem; }

.plans-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 2.5rem;
  max-width: 1200px;
  margin: 0 auto;
}

.plan-card {
  padding: 3.5rem 2.5rem;
  text-align: center;
  position: relative;
  display: flex;
  flex-direction: column;
}

.featured {
  border-color: var(--accent-primary);
  box-shadow: 0 0 40px rgba(var(--accent-primary-rgb), 0.2);
  transform: scale(1.05);
}

@media (max-width: 992px) {
  .featured { transform: scale(1); }
}

.featured-badge {
  position: absolute;
  top: -15px;
  left: 50%;
  transform: translateX(-50%);
  background: var(--accent-primary);
  color: #000;
  padding: 0.5rem 1.5rem;
  border-radius: 50px;
  font-weight: 800;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.plan-header h3 { font-size: 1.8rem; font-weight: 900; margin-bottom: 0.5rem; }
.plan-duration { color: var(--text-dim); font-size: 0.9rem; font-weight: 600; }

.plan-price {
  margin: 2.5rem 0;
  display: flex;
  justify-content: center;
  align-items: baseline;
}

.plan-price .currency { font-size: 1.2rem; font-weight: 700; color: var(--accent-primary); margin-right: 0.3rem; }
.plan-price .value { font-size: 4.5rem; font-weight: 900; line-height: 1; }
.plan-price .cents { font-size: 1.2rem; font-weight: 700; color: var(--text-dim); }

.plan-divider { height: 1px; background: var(--glass-border); margin-bottom: 2.5rem; }

.plan-features {
  list-style: none;
  padding: 0;
  margin-bottom: 3rem;
  text-align: left;
  flex: 1;
}

.plan-features li {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
  font-size: 0.95rem;
  color: var(--text-main);
  font-weight: 500;
}

.plan-features li span { font-size: 1.2rem; }

.checkout-modal {
  width: 100%;
  max-width: 480px;
  padding: 3rem;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2.5rem;
}

.close-btn { background: none; border: none; color: var(--text-dim); font-size: 1.2rem; cursor: pointer; }

.selected-plan-box {
  background: rgba(255, 255, 255, 0.03);
  padding: 1.5rem;
  border-radius: 16px;
  margin-bottom: 2rem;
}

.selected-plan-box .lab { font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 0.5rem; }
.selected-plan-box .val { display: flex; justify-content: space-between; align-items: center; }
.selected-plan-box .val strong { font-size: 1.2rem; }
.selected-plan-box .price { color: var(--accent-primary); font-weight: 800; }

.payment-selection .label { font-size: 0.9rem; font-weight: 700; margin-bottom: 1rem; }
.methods-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem; }

.method-card {
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--glass-border);
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.3s;
}

.method-card.active {
  background: rgba(var(--accent-primary-rgb), 0.1);
  border-color: var(--accent-primary);
}

.m-icon { font-size: 1.5rem; }
.m-text { font-size: 0.85rem; font-weight: 700; }

.security-info {
  display: flex;
  gap: 1rem;
  align-items: center;
  color: var(--text-dim);
  font-size: 0.8rem;
  background: rgba(0, 0, 0, 0.2);
  padding: 1rem;
  border-radius: 12px;
}

.loading-state { padding: 5rem; display: flex; justify-content: center; }
</style>
