<template>
  <div class="store-container animate-fade-in">
    <h2 class="text-gradient section-title">Loja Techfit</h2>
    <p class="store-subtitle">Suplementos, roupas e acessórios para potencializar seus resultados</p>

    <div class="product-grid">
      <div v-for="product in products" :key="product.id" class="glass-card product-card">
        <div class="product-image">
          <img :src="product.image" :alt="product.name" />
          <span class="product-badge">{{ product.category }}</span>
        </div>
        <div class="product-info">
          <h3>{{ product.name }}</h3>
          <p class="product-desc">{{ product.description }}</p>
          <div class="product-footer">
            <div class="price">R$ {{ product.price }}</div>
            <button class="btn-primary cart-btn" @click="addToCart(product)">
              <span>🛒</span> Ao Carrinho
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast de confirmação -->
    <div class="toast" :class="{ show: toast.show }">
      ✅ {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const toast = ref({ show: false, message: '' });

const products = ref([
  {
    id: 1,
    name: 'Whey Protein 1kg',
    category: 'Suplementos',
    description: 'Proteína de alta qualidade para ganho muscular rápido.',
    price: '149,90',
    image: '/products/whey.png'
  },
  {
    id: 2,
    name: 'Creatina 300g',
    category: 'Suplementos',
    description: 'Aumento de força e resistência para treinos intensos.',
    price: '89,90',
    image: '/products/creatina.png'
  },
  {
    id: 3,
    name: 'Camiseta Techfit',
    category: 'Vestuário',
    description: 'Tecido dry-fit com tecnologia de absorção de suor.',
    price: '59,90',
    image: '/products/camiseta.png'
  },
  {
    id: 4,
    name: 'Garrafa 1L',
    category: 'Acessórios',
    description: 'Garrafa premium com marcação de volume e tampa flip.',
    price: '35,00',
    image: '/products/garrafa.png'
  },
]);

function addToCart(product) {
  toast.value.message = `${product.name} adicionado ao carrinho!`;
  toast.value.show = true;
  setTimeout(() => { toast.value.show = false; }, 3000);
}
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
  margin-bottom: 3rem;
  font-size: 1rem;
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
.toast {
  position: fixed;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%) translateY(100px);
  background: rgba(0, 242, 255, 0.1);
  border: 1px solid var(--accent-primary);
  color: #fff;
  padding: 1rem 2rem;
  border-radius: 50px;
  font-weight: 600;
  backdrop-filter: blur(20px);
  transition: transform 0.4s ease, opacity 0.4s ease;
  opacity: 0;
  z-index: 1000;
}
.toast.show {
  transform: translateX(-50%) translateY(0);
  opacity: 1;
}
</style>
