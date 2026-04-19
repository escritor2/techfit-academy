import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/authStore'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import AdminView from '../views/AdminView.vue'
import EmployeeView from '../views/EmployeeView.vue'
import MemberView from '../views/MemberView.vue'
import StoreView from '../views/StoreView.vue'
import AIView from '../views/AIView.vue'
import ProfileView from '../views/ProfileView.vue'

const routes = [
  { path: '/', component: HomeView },
  { path: '/login', component: LoginView },
  { path: '/loja', component: StoreView },
  { path: '/ai', component: AIView },
  
  // Rotas Protegidas
  { 
    path: '/admin', 
    component: AdminView, 
    meta: { requiresAuth: true, role: 'admin' } 
  },
  { 
    path: '/recepcao', 
    component: EmployeeView, 
    meta: { requiresAuth: true, role: 'employee' } 
  },
  { 
    path: '/painel', 
    component: MemberView, 
    meta: { requiresAuth: true, role: 'member' } 
  },
  {
    path: '/perfil',
    component: ProfileView,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Guarda de Rotas (Proteção)
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    // Se a rota exige login e não está logado
    next('/login')
  } else if (to.meta.role) {
    // Se a rota exige um perfil específico
    if (authStore.isAdmin) {
      // Admin pode acessar tudo
      next()
    } else if (authStore.user?.role !== to.meta.role) {
      // Se não for admin e não tiver a role correta, manda pro painel dele
      if (authStore.isEmployee) next('/recepcao')
      else if (authStore.isMember) next('/painel')
      else next('/login')
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router
