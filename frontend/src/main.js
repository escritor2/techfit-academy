import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import { useAuthStore } from './stores/authStore'
import './style.css'
import App from './App.vue'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Initialize auth headers if token exists
const authStore = useAuthStore()
authStore.initializeAuth()

// Scroll Reveal Logic
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('active');
    }
  });
}, { threshold: 0.1 });

app.directive('reveal', {
  mounted(el) {
    el.classList.add('reveal');
    revealObserver.observe(el);
  }
});

app.mount('#app')
