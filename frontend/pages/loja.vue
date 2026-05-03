<template>
  <div class="page-container">
    <header class="store-header" v-reveal>
      <h1 class="section-title">Loja <span class="text-gradient">Techfit</span></h1>
      <p class="store-subtitle">Alta performance em cada detalhe. Suplementos, vestuário e tecnologia.</p>
      
      <!-- Filter Bar -->
      <div class="filter-wrapper">
        <div class="filter-bar">
          <button 
            v-for="cat in categories" :key="cat"
            :class="['filter-btn', { active: activeCategory === cat }]"
            @click="activeCategory = cat"
          >
            {{ cat }}
          </button>
        </div>
      </div>
    </header>

    <!-- Loading State -->
    <div v-if="loading" class="loading-grid">
      <SkeletonLoader variant="card" :count="8" />
    </div>

    <!-- Product Grid -->
    <div v-else class="product-grid">
      <div 
        v-for="(product, index) in filteredProducts" 
        :key="product.id" 
        class="glass-card product-card"
        v-reveal
        :style="{ transitionDelay: `${index * 0.05}s` }"
      >
        <div class="product-visual">
          <img :src="product.image_url || 'https://via.placeholder.com/400x300'" :alt="product.name" class="product-img">
          <div class="product-overlay">
            <button class="quick-add-btn" @click="addToCart(product)" :disabled="product.stock_quantity === 0">
              {{ product.stock_quantity === 0 ? 'Esgotado' : '⚡ Adicionar' }}
            </button>
          </div>
          <span class="category-tag">{{ product.category }}</span>
        </div>
        
        <div class="product-details">
          <h3 class="product-name">{{ product.name }}</h3>
          <p class="product-description">{{ product.description }}</p>
          
          <div class="product-footer">
            <div class="price-box">
              <span class="currency">R$</span>
              <span class="amount">{{ parseFloat(product.price).toFixed(2).replace('.', ',') }}</span>
            </div>
            <div class="stock-status" :class="{ 'low': product.stock_quantity <= 5 && product.stock_quantity > 0, 'out': product.stock_quantity === 0 }">
              <span v-if="product.stock_quantity === 0">Esgotado</span>
              <span v-else-if="product.stock_quantity <= 5">Apenas {{ product.stock_quantity }} restam</span>
              <span v-else>Disponível</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && filteredProducts.length === 0" class="empty-store" v-reveal>
      <div class="empty-icon">🔍</div>
      <h3>Nenhum produto encontrado</h3>
      <p>Tente mudar a categoria ou explore nossa coleção completa.</p>
      <button class="btn-outline" @click="activeCategory = 'Todos'">Ver Tudo</button>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'

const cartStore = useCartStore()
const products = ref([])
const loading = ref(true)
const activeCategory = ref('Todos')

// Auto-import handles useRuntimeConfig
const config = useRuntimeConfig()

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
    const response = await $fetch(`${config.public.apiBase}/products`)
    products.value = response.data || []
  } catch (error) {
    console.error('Erro ao carregar produtos', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.store-header {
  text-align: center;
  margin-bottom: 4rem;
}

.store-subtitle {
  color: var(--text-muted);
  font-size: 1.1rem;
  margin-bottom: 2.5rem;
}

.filter-wrapper {
  display: flex;
  justify-content: center;
}

.filter-bar {
  display: flex;
  gap: 1rem;
  background: rgba(255, 255, 255, 0.03);
  padding: 0.5rem;
  border-radius: 100px;
  border: 1px solid var(--glass-border);
  overflow-x: auto;
  scrollbar-width: none;
}

.filter-bar::-webkit-scrollbar { display: none; }

.filter-btn {
  padding: 0.6rem 1.5rem;
  border-radius: 100px;
  border: none;
  background: transparent;
  color: var(--text-muted);
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.3s;
}

.filter-btn:hover { color: var(--text-main); }

.filter-btn.active {
  background: var(--accent-primary);
  color: #000;
  box-shadow: 0 0 20px rgba(var(--accent-primary-rgb), 0.3);
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2.5rem;
}

.product-card {
  padding: 0;
  border-radius: var(--radius-lg);
  display: flex;
  flex-direction: column;
}

.product-visual {
  position: relative;
  height: 280px;
  overflow: hidden;
}

.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.product-card:hover .product-img {
  transform: scale(1.1);
}

.product-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s;
  backdrop-filter: blur(4px);
}

.product-card:hover .product-overlay {
  opacity: 1;
}

.quick-add-btn {
  padding: 0.8rem 1.5rem;
  background: #fff;
  color: #000;
  border: none;
  border-radius: 100px;
  font-weight: 800;
  text-transform: uppercase;
  font-size: 0.8rem;
  letter-spacing: 1px;
  cursor: pointer;
  transform: translateY(20px);
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.product-card:hover .quick-add-btn {
  transform: translateY(0);
}

.category-tag {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  padding: 0.4rem 1rem;
  background: rgba(10, 10, 12, 0.7);
  backdrop-filter: blur(10px);
  border: 1px solid var(--glass-border);
  border-radius: 50px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.product-details {
  padding: 2rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.product-name {
  font-size: 1.3rem;
  font-weight: 800;
  margin-bottom: 0.8rem;
}

.product-description {
  color: var(--text-dim);
  font-size: 0.95rem;
  line-height: 1.6;
  margin-bottom: 2rem;
  flex: 1;
}

.product-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.price-box {
  display: flex;
  align-items: baseline;
  gap: 0.2rem;
}

.price-box .currency { font-size: 1rem; font-weight: 700; color: var(--accent-primary); }
.price-box .amount { font-size: 1.8rem; font-weight: 900; }

.stock-status {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--success);
}

.stock-status.low { color: var(--warning); }
.stock-status.out { color: var(--danger); }

.empty-store {
  text-align: center;
  padding: 5rem 0;
}

.empty-icon { font-size: 4rem; margin-bottom: 1.5rem; }
.empty-store h3 { font-size: 1.8rem; margin-bottom: 1rem; }
.empty-store p { color: var(--text-muted); margin-bottom: 2.5rem; }
</style>
