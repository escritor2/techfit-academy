<template>
  <div class="store-container animate-fade-in">
    <h2 class="text-gradient section-title">Techfit Store</h2>
    
    <div class="product-grid">
      <div v-for="product in products" :key="product.id" class="glass-card product-card">
        <div class="product-image">
          <img :src="product.image || 'https://via.placeholder.com/200'" alt="Product">
        </div>
        <h3>{{ product.name }}</h3>
        <p class="category">{{ product.category }}</p>
        <div class="price">R$ {{ product.price }}</div>
        <button class="btn-primary w-full">Adicionar ao Carrinho</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const products = ref([
  { id: 1, name: 'Whey Protein 1kg', category: 'Suplementos', price: '149.90' },
  { id: 2, name: 'Creatina 300g', category: 'Suplementos', price: '89.90' },
  { id: 3, name: 'Camiseta Techfit', category: 'Vestuário', price: '59.90' },
  { id: 4, name: 'Garrafa 1L', category: 'Acessórios', price: '35.00' },
]);
</script>

<style scoped>
.store-container {
  padding: 4rem 5%;
}

.section-title {
  font-size: 3rem;
  margin-bottom: 3rem;
  text-align: center;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 2rem;
}

.product-card {
  text-align: center;
}

.product-image img {
  width: 100%;
  border-radius: 10px;
  margin-bottom: 1rem;
}

.category {
  color: var(--text-muted);
  font-size: 0.9rem;
  margin-bottom: 1rem;
}

.price {
  font-size: 1.5rem;
  font-weight: 900;
  color: var(--accent-secondary);
  margin-bottom: 1.5rem;
}

.w-full {
  width: 100%;
}
</style>
