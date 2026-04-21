import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

// ═══════════════════════════════════════════
// LAZY LOADING — Cada view é carregada apenas quando acessada
// Reduz o bundle inicial drasticamente para melhor performance na Vercel
// ═══════════════════════════════════════════

const HomeView      = () => import('../views/HomeView.vue')
const LoginView     = () => import('../views/LoginView.vue')
const AdminView     = () => import('../views/AdminView.vue')
const EmployeeView  = () => import('../views/EmployeeView.vue')
const MemberView    = () => import('../views/MemberView.vue')
const StoreView     = () => import('../views/StoreView.vue')
const AIView        = () => import('../views/AIView.vue')
const ProfileView   = () => import('../views/ProfileView.vue')
const MetricsView   = () => import('../views/MetricsView.vue')
const NutritionView = () => import('../views/NutritionView.vue')
const RankingView   = () => import('../views/RankingView.vue')
const PlansView     = () => import('../views/PlansView.vue')

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
  },
  {
    path: '/metricas',
    name: 'metrics',
    component: MetricsView,
    meta: { requiresAuth: true }
  },
  {
    path: '/nutricao',
    name: 'nutrition',
    component: NutritionView,
    meta: { requiresAuth: true }
  },
  {
    path: '/ranking',
    name: 'ranking',
    component: RankingView,
    meta: { requiresAuth: true }
  },
  {
    path: '/planos',
    name: 'plans',
    component: PlansView,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // Retorna ao topo ao navegar para nova página
    if (savedPosition) return savedPosition
    return { top: 0, behavior: 'smooth' }
  }
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
