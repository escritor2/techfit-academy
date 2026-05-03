<template>
  <div class="page-container">
    <header class="section-header" v-reveal>
      <h1>Histórico de <span class="text-gradient">Compras</span></h1>
      <p>Confira todos os produtos que você já adquiriu na TechFit.</p>
    </header>

    <div v-if="loading" class="loading-grid">
      <SkeletonLoader variant="stat" :count="3" />
    </div>

    <div v-else class="history-container">
      <div v-if="sales.length === 0" class="empty-state glass-card" v-reveal>
        <span class="icon">🛍️</span>
        <h3>Nenhuma compra encontrada</h3>
        <p>Visite nossa loja para potencializar seus resultados.</p>
        <NuxtLink to="/loja" class="btn-primary mt-4">Ir para a Loja</NuxtLink>
      </div>

      <div v-else class="sales-list">
        <div v-for="sale in sales" :key="sale.id" class="sale-card glass-card" v-reveal>
          <div class="sale-header">
            <div class="sale-info">
              <span class="sale-id">Pedido #{{ sale.id }}</span>
              <span class="sale-date">📅 {{ formatDateTime(sale.created_at) }}</span>
            </div>
            <div class="sale-total">
              Total: <span class="text-gradient">R$ {{ parseFloat(sale.total_price).toFixed(2).replace('.', ',') }}</span>
            </div>
          </div>
          
          <div class="sale-items-divider"></div>
          
          <div class="sale-items">
            <div v-for="item in sale.items" :key="item.id" class="sale-item">
              <div class="item-img-placeholder">
                <span v-if="!item.product?.image_url">📦</span>
                <img v-else :src="item.product.image_url" alt="Produto">
              </div>
              <div class="item-details">
                <strong>{{ item.quantity }}x {{ item.product?.name || 'Produto indisponível' }}</strong>
                <span class="item-price">R$ {{ parseFloat(item.unit_price).toFixed(2).replace('.', ',') }} un.</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/authStore'
import { useRuntimeConfig } from '#app'

definePageMeta({
  middleware: ['auth']
})

const authStore = useAuthStore()
const config = useRuntimeConfig()
const sales = ref([])
const loading = ref(true)

function formatDateTime(d) {
  if (!d) return '-'
  const date = new Date(d)
  return date.toLocaleDateString('pt-BR') + ' às ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

const loadSales = async () => {
  try {
    const data = await $fetch(`${config.public.apiBase}/my/sales`, {
      headers: authStore.authHeaders
    })
    sales.value = data || []
  } catch (e) {
    console.error('Erro ao carregar histórico', e)
  } finally {
    loading.value = false
  }
}

onMounted(loadSales)
</script>

<style scoped>
.section-header {
  margin-bottom: 4rem;
  text-align: center;
}
.section-header h1 {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}
.section-header p {
  color: var(--text-muted);
  font-size: 1.1rem;
}

.history-container {
  max-width: 800px;
  margin: 0 auto;
}

.empty-state {
  padding: 4rem 2rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}
.empty-state .icon {
  font-size: 4rem;
}

.sales-list {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.sale-card {
  padding: 2rem;
  display: flex;
  flex-direction: column;
}

.sale-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.sale-info {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.sale-id {
  font-weight: 900;
  font-size: 1.2rem;
}

.sale-date {
  color: var(--text-muted);
  font-size: 0.9rem;
}

.sale-total {
  font-weight: 700;
  font-size: 1.1rem;
}

.sale-total span {
  font-size: 1.5rem;
  font-weight: 900;
  margin-left: 0.5rem;
}

.sale-items-divider {
  height: 1px;
  background: var(--glass-border);
  margin: 1.5rem 0;
}

.sale-items {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.sale-item {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  background: rgba(255, 255, 255, 0.02);
  padding: 1rem;
  border-radius: 12px;
}

.item-img-placeholder {
  width: 50px;
  height: 50px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  overflow: hidden;
}
.item-img-placeholder img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-details {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.item-details strong {
  font-size: 1.05rem;
}

.item-price {
  color: var(--text-dim);
  font-size: 0.9rem;
}

.mt-4 {
  margin-top: 1rem;
}

@media (max-width: 600px) {
  .sale-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
}
</style>
