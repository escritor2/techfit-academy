<template>
  <div class="store-container animate-fade-in">
    <h2 class="text-gradient section-title">Loja Techfit</h2>
    <p class="store-subtitle">Suplementos, roupas e acessórios para potencializar seus resultados</p>

    <!-- Filtros de categoria -->
    <div class="filter-bar">
      <button 
        v-for="cat in categories" :key="cat"
        :class="['filter-btn', { active: activeCategory === cat }]"
        @click="activeCategory = cat"
      >
        {{ cat }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state text-muted">
      Carregando produtos...
    </div>

    <!-- Product Grid -->
    <div v-else class="product-grid">
      <div v-for="product in filteredProducts" :key="product.id" class="glass-card product-card">
        <div class="product-image">
          <img :src="product.image_url || 'https://via.placeholder.com/400x250'" :alt="product.name" />
          <span class="product-badge">{{ product.category }}</span>
          <span v-if="product.stock_quantity <= 5 && product.stock_quantity > 0" class="stock-badge low">
            Últimas {{ product.stock_quantity }} un.
          </span>
          <span v-if="product.stock_quantity === 0" class="stock-badge out">
            Esgotado
          </span>
        </div>
        <div class="product-info">
          <h3>{{ product.name }}</h3>
          <p class="product-desc">{{ product.description }}</p>
          <div class="product-footer">
            <div class="price">R$ {{ parseFloat(product.price).toFixed(2).replace('.', ',') }}</div>
            <button 
              class="btn-primary cart-btn" 
              @click="addToCart(product)"
              :disabled="product.stock_quantity === 0"
            >
              <span>🛒</span> {{ product.stock_quantity === 0 ? 'Esgotado' : 'Comprar' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!loading && filteredProducts.length === 0" class="empty-state">
      <p class="text-muted">Nenhum produto encontrado nesta categoria.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useCartStore } from '../stores/cartStore'

const cartStore = useCartStore()
const products = ref([])
const loading = ref(true)
const activeCategory = ref('Todos')

const categories = computed(() => {
  const cats = [...new Set(products.value.map(p => p.category).filter(Boolean))]
  return ['Todos', ...cats]
})

const filteredProducts = computed(() => {
  if (activeCategory.value === 'Todos') return products.value
  return products.value.filter(p => p.category === activeCategory.value)
})

function addToCart(product) {
  cartStore.addItem(product)
}

onMounted(async () => {
  try {
    const response = await axios.get('/products')
    products.value = response.data
  } catch (error) {
    console.error('Erro ao carregar produtos', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.store-container {
  padding: 4rem 5%;
  max-width: 1400px;
  margin: 0 auto;
}

.section-title {
  font-size: 3rem;
  margin-bottom: 0.5rem;
  text-align: center;
}

.store-subtitle {
  text-align: center;
  color: var(--text-muted);
  margin-bottom: 2rem;
  font-size: 1rem;
}

/* Filter bar */
.filter-bar {
  display: flex;
  justify-content: center;
  gap: 0.8rem;
  margin-bottom: 3rem;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 0.5rem 1.2rem;
  border-radius: 50px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.03);
  color: var(--text-muted);
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.3s;
}

.filter-btn:hover {
  border-color: var(--accent-primary);
  color: var(--accent-primary);
}

.filter-btn.active {
  background: rgba(0, 242, 255, 0.15);
  border-color: var(--accent-primary);
  color: var(--accent-primary);
}

.loading-state {
  text-align: center;
  padding: 4rem 0;
  font-size: 1.1rem;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 2rem;
}

.product-card {
  padding: 0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 0 30px rgba(0, 242, 255, 0.2);
}

.product-image {
  position: relative;
  width: 100%;
  height: 220px;
  overflow: hidden;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.product-card:hover .product-image img {
  transform: scale(1.05);
}

.product-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  background: rgba(0, 242, 255, 0.15);
  border: 1px solid var(--accent-primary);
  color: var(--accent-primary);
  font-size: 0.7rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 50px;
  text-transform: uppercase;
  letter-spacing: 1px;
  backdrop-filter: blur(6px);
}

.stock-badge {
  position: absolute;
  bottom: 12px;
  left: 12px;
  padding: 4px 10px;
  border-radius: 50px;
  font-size: 0.7rem;
  font-weight: 700;
  backdrop-filter: blur(6px);
}

.stock-badge.low {
  background: rgba(245, 158, 11, 0.2);
  border: 1px solid #f59e0b;
  color: #fbbf24;
}

.stock-badge.out {
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid #ef4444;
  color: #f87171;
}

.product-info {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.product-info h3 {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.product-desc {
  color: var(--text-muted);
  font-size: 0.85rem;
  line-height: 1.5;
  margin-bottom: 1.5rem;
  flex: 1;
}

.product-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.price {
  font-size: 1.4rem;
  font-weight: 900;
  color: var(--accent-secondary);
}

.cart-btn {
  padding: 0.6rem 1.2rem;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  white-space: nowrap;
}

.cart-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.empty-state {
  text-align: center;
  padding: 4rem 0;
}
</style>
