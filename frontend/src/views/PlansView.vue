<template>
  <div class="plans-container animate-fade-in">
    <header class="section-header">
      <h2>Escolha seu <span class="text-gradient">Plano</span></h2>
      <p>Assine e tenha acesso completo à academia e ao nosso personal trainer com IA.</p>
    </header>

    <div v-if="loading" class="text-center py-10 text-muted">Carregando planos...</div>

    <div v-else class="plans-grid">
      <div v-for="plan in plans" :key="plan.id" class="glass-card plan-card" :class="{ featured: plan.name.includes('Anual') }">
        <div v-if="plan.name.includes('Anual')" class="featured-badge">Melhor Valor</div>
        <h3>{{ plan.name }}</h3>
        <div class="price">
          <span class="currency">R$</span>
          <span class="val">{{ plan.price.split('.')[0] }}</span>
          <span class="cents">,{{ plan.price.split('.')[1] || '00' }}</span>
        </div>
        <p class="duration">{{ plan.duration_days }} dias de acesso</p>
        <p class="desc">{{ plan.description }}</p>
        
        <ul class="features">
          <li>✅ Acesso 24/7</li>
          <li>✅ Treinos com IA ilimitados</li>
          <li>✅ Nutricionista com IA</li>
          <li>✅ Ranking e Conquistas</li>
        </ul>

        <button @click="openCheckout(plan)" class="btn-primary w-full">Assinar Agora</button>
      </div>
    </div>

    <!-- Modal de Checkout -->
    <div v-if="showCheckout" class="modal-overlay" @click.self="showCheckout = false">
      <div class="modal glass-card">
        <h3>💳 Checkout Seguro</h3>
        <p class="text-muted">Você está assinando o <strong>{{ selectedPlan.name }}</strong>.</p>
        
        <div class="checkout-details">
          <div class="checkout-row">
            <span>Total a pagar:</span>
            <strong>R$ {{ selectedPlan.price }}</strong>
          </div>
        </div>

        <div class="payment-methods">
          <label class="method" :class="{ active: paymentMethod === 'pix' }">
            <input type="radio" v-model="paymentMethod" value="pix" hidden />
            <span>⚡ PIX (Instantâneo)</span>
          </label>
          <label class="method" :class="{ active: paymentMethod === 'card' }">
            <input type="radio" v-model="paymentMethod" value="card" hidden />
            <span>💳 Cartão de Crédito</span>
          </label>
        </div>

        <div class="modal-actions">
          <button class="btn-outline" @click="showCheckout = false">Cancelar</button>
          <button class="btn-primary" @click="confirmPayment" :disabled="processing">
            {{ processing ? 'Processando...' : 'Confirmar Pagamento' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '../stores/notificationStore'
import { useRouter } from 'vue-router'

const router = useRouter()
const notify = useNotificationStore()
const plans = ref([])
const loading = ref(true)
const showCheckout = ref(false)
const selectedPlan = ref(null)
const paymentMethod = ref('pix')
const processing = ref(false)

async function loadPlans() {
  try {
    const res = await axios.get('/plans')
    plans.value = res.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openCheckout(plan) {
  selectedPlan.value = plan
  showCheckout.value = true
}

async function confirmPayment() {
  processing.value = true
  try {
    await axios.post('/subscriptions/checkout', {
      plan_id: selectedPlan.value.id,
      payment_method: paymentMethod.value
    })
    notify.success('Assinatura ativada com sucesso! Bem-vindo à TechFit. 🎉')
    showCheckout.value = false
    router.push('/painel')
  } catch (e) {
    notify.error('Erro ao processar pagamento.')
  } finally {
    processing.value = false
  }
}

onMounted(loadPlans)
</script>

<style scoped>
.plans-container { padding: 2rem 5%; max-width: 1100px; margin: 0 auto; }
.plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 3rem; }

.plan-card { position: relative; text-align: center; padding: 3rem 2rem !important; transition: transform 0.3s; }
.plan-card:hover { transform: translateY(-10px); }
.plan-card.featured { border: 2px solid var(--accent-primary); box-shadow: 0 0 30px rgba(0, 242, 255, 0.2); }

.featured-badge {
  position: absolute; top: -15px; left: 50%; transform: translateX(-50%);
  background: var(--accent-primary); color: black; font-weight: 900;
  padding: 5px 15px; border-radius: 20px; font-size: 0.8rem;
}

.price { margin: 1.5rem 0; display: flex; justify-content: center; align-items: flex-start; }
.currency { font-size: 1.2rem; font-weight: 600; margin-top: 0.5rem; }
.val { font-size: 4rem; font-weight: 900; color: var(--accent-primary); line-height: 1; }
.cents { font-size: 1.2rem; font-weight: 600; margin-top: 0.5rem; }

.duration { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem; }
.desc { font-size: 0.95rem; margin-bottom: 2rem; height: 3rem; overflow: hidden; }

.features { list-style: none; padding: 0; margin-bottom: 2.5rem; text-align: left; }
.features li { margin-bottom: 0.8rem; font-size: 0.9rem; }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.8);
  backdrop-filter: blur(8px); z-index: 1000;
  display: flex; align-items: center; justify-content: center;
}
.modal { width: 90%; max-width: 450px; padding: 2.5rem !important; }
.checkout-details { background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 12px; margin: 1.5rem 0; }
.checkout-row { display: flex; justify-content: space-between; font-size: 1.1rem; }

.payment-methods { display: flex; flex-direction: column; gap: 0.8rem; margin-bottom: 2rem; }
.method {
  padding: 1rem; border: 1px solid rgba(255,255,255,0.1);
  border-radius: 12px; cursor: pointer; transition: all 0.3s;
}
.method:hover { background: rgba(255,255,255,0.03); }
.method.active { border-color: var(--accent-primary); background: rgba(0,242,255,0.05); }

.modal-actions { display: flex; gap: 1rem; }
.modal-actions button { flex: 1; }
</style>
