<template>
  <div class="dashboard-container animate-fade-in">
    <header class="dashboard-header">
      <h2>Painel do <span class="text-gradient">Administrador</span></h2>
      <p>Gerencie o sistema completo da TechFit.</p>
    </header>

    <!-- Tab Navigation -->
    <div class="tab-bar">
      <button v-for="tab in tabs" :key="tab.id" :class="['tab-btn', { active: activeTab === tab.id }]" @click="activeTab = tab.id">
        {{ tab.icon }} {{ tab.label }}
      </button>
    </div>

    <div v-if="loading" style="padding: 2rem 0;">
      <SkeletonLoader variant="stat" :count="6" />
    </div>

    <div v-else>
      <!-- ═══ TAB: DASHBOARD ═══ -->
      <div v-if="activeTab === 'dashboard'">
        <div class="stats-grid">
          <div class="glass-card stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info"><h3>Membros Ativos</h3><p class="text-gradient stat-number">{{ stats.active_members || 0 }}</p></div>
          </div>
          <div class="glass-card stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info"><h3>Faturamento Mês</h3><p class="text-gradient stat-number">R$ {{ formatMoney(stats.monthly_revenue) }}</p></div>
          </div>
          <div class="glass-card stat-card">
            <div class="stat-icon">🛒</div>
            <div class="stat-info"><h3>Vendas Totais</h3><p class="text-gradient stat-number">{{ stats.total_sales || 0 }}</p></div>
          </div>
          <div class="glass-card stat-card">
            <div class="stat-icon">🚪</div>
            <div class="stat-info"><h3>Check-ins Hoje</h3><p class="text-gradient stat-number">{{ stats.today_checkins || 0 }}</p></div>
          </div>
          <div class="glass-card stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-info"><h3>Planos Ativos</h3><p class="text-gradient stat-number">{{ stats.active_plans || 0 }}</p></div>
          </div>
          <div class="glass-card stat-card">
            <div class="stat-icon">⚠️</div>
            <div class="stat-info"><h3>Inadimplentes</h3><p class="stat-number" style="color: #fbbf24;">{{ stats.expired_members || 0 }}</p></div>
          </div>
        </div>

        <div class="glass-card" style="margin-top: 2rem;">
          <h3>Últimos Cadastros</h3>
          <div class="table-container" v-if="stats.recent_registrations && stats.recent_registrations.length > 0">
            <table class="data-table">
              <thead><tr><th>Nome</th><th>Email</th><th>Data</th></tr></thead>
              <tbody>
                <tr v-for="user in stats.recent_registrations" :key="user.id">
                  <td>{{ user.name }}</td><td>{{ user.email }}</td>
                  <td>{{ new Date(user.created_at).toLocaleDateString('pt-BR') }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-else class="text-muted" style="margin-top: 1rem;">Nenhum membro recente.</p>
        </div>
      </div>

      <!-- ═══ TAB: MEMBROS ═══ -->
      <div v-if="activeTab === 'members'">
        <div class="section-header">
          <input v-model="memberSearch" placeholder="🔍 Buscar por nome, email ou CPF..." class="search-input" @input="searchMembers" />
          <button class="btn-primary" @click="showMemberModal = true">+ Novo Membro</button>
        </div>
        <div class="table-container" v-if="members.length > 0">
          <table class="data-table admin-table-mobile">
            <thead><tr><th>Nome</th><th>Email</th><th>CPF</th><th>Check-ins</th><th>Plano</th><th>Ações</th></tr></thead>
            <tbody>
              <tr v-for="m in members" :key="m.id">
                <td data-label="Nome">{{ m.name }}</td><td data-label="Email">{{ m.email }}</td><td data-label="CPF">{{ m.cpf || '-' }}</td>
                <td data-label="Check-ins">{{ m.checkins_count }}</td>
                <td>
                  <span v-if="m.subscriptions && m.subscriptions.length > 0" class="badge active">{{ m.subscriptions[0].plan?.name }}</span>
                  <span v-else class="badge expired">Sem Plano</span>
                </td>
                <td>
                  <button class="action-btn delete" @click="deleteMember(m.id)" title="Remover">🗑️</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="text-muted" style="margin-top: 2rem; text-align: center;">Nenhum membro encontrado.</p>

        <!-- Modal Novo Membro -->
        <div v-if="showMemberModal" class="modal-overlay" @click.self="showMemberModal = false">
          <div class="modal glass-card">
            <h3>Cadastrar Novo Membro</h3>
            <form @submit.prevent="createMember" class="modal-form">
              <input v-model="newMember.name" placeholder="Nome completo" required />
              <input v-model="newMember.email" type="email" placeholder="Email" required />
              <input v-model="newMember.cpf" placeholder="CPF" />
              <input v-model="newMember.age" type="number" placeholder="Idade" />
              <input v-model="newMember.password" type="password" placeholder="Senha (min. 6)" required />
              <div class="modal-actions">
                <button type="button" class="btn-outline" @click="showMemberModal = false">Cancelar</button>
                <button type="submit" class="btn-primary">Cadastrar</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- ═══ TAB: PRODUTOS ═══ -->
      <div v-if="activeTab === 'products'">
        <div class="section-header">
          <h3>Gerenciar Produtos</h3>
          <button class="btn-primary" @click="showProductModal = true">+ Novo Produto</button>
        </div>
        <div class="table-container" v-if="products.length > 0">
          <table class="data-table admin-table-mobile">
            <thead><tr><th>Nome</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Ações</th></tr></thead>
            <tbody>
              <tr v-for="p in products" :key="p.id">
                <td data-label="Nome">{{ p.name }}</td><td data-label="Categoria">{{ p.category }}</td>
                <td data-label="Preço">R$ {{ formatMoney(p.price) }}</td>
                <td>
                  <span :class="p.stock_quantity <= 5 ? 'text-warn' : ''">{{ p.stock_quantity }}</span>
                </td>
                <td><button class="action-btn delete" @click="deleteProduct(p.id)">🗑️</button></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Modal Novo Produto -->
        <div v-if="showProductModal" class="modal-overlay" @click.self="showProductModal = false">
          <div class="modal glass-card">
            <h3>Novo Produto</h3>
            <form @submit.prevent="createProduct" class="modal-form">
              <input v-model="newProduct.name" placeholder="Nome do produto" required />
              <input v-model="newProduct.category" placeholder="Categoria" />
              <textarea v-model="newProduct.description" placeholder="Descrição"></textarea>
              <input v-model="newProduct.price" type="number" step="0.01" placeholder="Preço" required />
              <input v-model="newProduct.stock_quantity" type="number" placeholder="Qtd. Estoque" required />
              <input v-model="newProduct.image_url" placeholder="URL da imagem" />
              <div class="modal-actions">
                <button type="button" class="btn-outline" @click="showProductModal = false">Cancelar</button>
                <button type="submit" class="btn-primary">Salvar</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- ═══ TAB: PLANOS ═══ -->
      <div v-if="activeTab === 'plans'">
        <div class="section-header">
          <h3>Gerenciar Planos</h3>
          <button class="btn-primary" @click="showPlanModal = true">+ Novo Plano</button>
        </div>
        <div class="plans-grid">
          <div v-for="plan in plans" :key="plan.id" class="glass-card plan-card">
            <h4>{{ plan.name }}</h4>
            <p class="plan-price text-gradient">R$ {{ formatMoney(plan.price) }}</p>
            <p class="text-muted">{{ plan.duration_days }} dias</p>
            <p class="plan-desc">{{ plan.description }}</p>
            <button class="action-btn delete" @click="deletePlan(plan.id)" style="margin-top: 1rem;">🗑️ Remover</button>
          </div>
        </div>

        <!-- Modal Novo Plano -->
        <div v-if="showPlanModal" class="modal-overlay" @click.self="showPlanModal = false">
          <div class="modal glass-card">
            <h3>Novo Plano</h3>
            <form @submit.prevent="createPlan" class="modal-form">
              <input v-model="newPlan.name" placeholder="Nome do plano" required />
              <input v-model="newPlan.duration_days" type="number" placeholder="Duração (dias)" required />
              <input v-model="newPlan.price" type="number" step="0.01" placeholder="Preço" required />
              <textarea v-model="newPlan.description" placeholder="Descrição"></textarea>
              <div class="modal-actions">
                <button type="button" class="btn-outline" @click="showPlanModal = false">Cancelar</button>
                <button type="submit" class="btn-primary">Salvar</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- ═══ TAB: AULAS ═══ -->
      <div v-if="activeTab === 'classes'">
        <div class="section-header">
          <h3>Gerenciar Aulas</h3>
          <button class="btn-primary" @click="showClassModal = true">+ Nova Aula</button>
        </div>
        <div class="table-container" v-if="gymClasses.length > 0">
          <table class="data-table">
            <thead><tr><th>Nome</th><th>Dia</th><th>Horário</th><th>Capacidade</th><th>Inscritos</th><th>Ações</th></tr></thead>
            <tbody>
              <tr v-for="c in gymClasses" :key="c.id">
                <td>{{ c.name }}</td><td>{{ c.day_of_week }}</td>
                <td>{{ c.start_time?.substring(0,5) }} - {{ c.end_time?.substring(0,5) }}</td>
                <td>{{ c.capacity }}</td>
                <td>{{ c.active_bookings_count || 0 }}</td>
                <td><button class="action-btn delete" @click="deleteClass(c.id)">🗑️</button></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Modal Nova Aula -->
        <div v-if="showClassModal" class="modal-overlay" @click.self="showClassModal = false">
          <div class="modal glass-card">
            <h3>Nova Aula</h3>
            <form @submit.prevent="createClass" class="modal-form">
              <input v-model="newClass.name" placeholder="Nome da aula" required />
              <textarea v-model="newClass.description" placeholder="Descrição"></textarea>
              <select v-model="newClass.day_of_week" required>
                <option value="">Dia da semana</option>
                <option v-for="d in ['Segunda','Terça','Quarta','Quinta','Sexta','Sábado']" :key="d" :value="d">{{ d }}</option>
              </select>
              <div style="display:flex; gap:1rem;">
                <input v-model="newClass.start_time" type="time" placeholder="Início" required style="flex:1;" />
                <input v-model="newClass.end_time" type="time" placeholder="Fim" required style="flex:1;" />
              </div>
              <input v-model="newClass.capacity" type="number" placeholder="Capacidade" required />
              <input v-model="newClass.instructor_id" type="number" placeholder="ID do Instrutor" required />
              <div class="modal-actions">
                <button type="button" class="btn-outline" @click="showClassModal = false">Cancelar</button>
                <button type="submit" class="btn-primary">Salvar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '../stores/notificationStore'
import SkeletonLoader from '../components/SkeletonLoader.vue'

const notify = useNotificationStore()
const loading = ref(true)
const activeTab = ref('dashboard')

const tabs = [
  { id: 'dashboard', icon: '📊', label: 'Dashboard' },
  { id: 'members', icon: '👥', label: 'Membros' },
  { id: 'products', icon: '🛒', label: 'Produtos' },
  { id: 'plans', icon: '💳', label: 'Planos' },
  { id: 'classes', icon: '📅', label: 'Aulas' },
]

// Data
const stats = ref({})
const members = ref([])
const products = ref([])
const plans = ref([])
const gymClasses = ref([])

// Search
const memberSearch = ref('')

// Modals
const showMemberModal = ref(false)
const showProductModal = ref(false)
const showPlanModal = ref(false)
const showClassModal = ref(false)

// New item forms
const newMember = ref({ name: '', email: '', cpf: '', age: '', password: '' })
const newProduct = ref({ name: '', category: '', description: '', price: '', stock_quantity: '', image_url: '' })
const newPlan = ref({ name: '', duration_days: '', price: '', description: '' })
const newClass = ref({ name: '', description: '', day_of_week: '', start_time: '', end_time: '', capacity: '', instructor_id: '' })

function formatMoney(v) {
  return parseFloat(v || 0).toFixed(2).replace('.', ',')
}

// ── API Calls ──

async function loadAll() {
  try {
    const [dashRes, membersRes, productsRes, plansRes, classesRes] = await Promise.all([
      axios.get('/admin/dashboard'),
      axios.get('/admin/members'),
      axios.get('/products'),
      axios.get('/plans'),
      axios.get('/gym-classes'),
    ])
    stats.value = dashRes.data
    members.value = membersRes.data
    products.value = productsRes.data
    plans.value = plansRes.data
    gymClasses.value = classesRes.data
  } catch (e) {
    console.error('Erro ao buscar dados do admin', e)
  } finally {
    loading.value = false
  }
}

async function searchMembers() {
  try {
    const res = await axios.get('/admin/members', { params: { search: memberSearch.value } })
    members.value = res.data
  } catch (e) { /* silent */ }
}

async function createMember() {
  try {
    await axios.post('/admin/members', newMember.value)
    notify.success('Membro cadastrado! 🎉')
    showMemberModal.value = false
    newMember.value = { name: '', email: '', cpf: '', age: '', password: '' }
    searchMembers()
  } catch (e) {
    notify.error(e.response?.data?.errors ? Object.values(e.response.data.errors)[0][0] : 'Erro ao cadastrar.')
  }
}

async function deleteMember(id) {
  if (!confirm('Remover este membro?')) return
  try {
    await axios.delete(`/admin/members/${id}`)
    members.value = members.value.filter(m => m.id !== id)
    notify.success('Membro removido.')
  } catch (e) { notify.error('Erro ao remover.') }
}

async function createProduct() {
  try {
    const res = await axios.post('/admin/products', newProduct.value)
    products.value.push(res.data)
    showProductModal.value = false
    newProduct.value = { name: '', category: '', description: '', price: '', stock_quantity: '', image_url: '' }
    notify.success('Produto criado!')
  } catch (e) { notify.error('Erro ao criar produto.') }
}

async function deleteProduct(id) {
  if (!confirm('Remover este produto?')) return
  try {
    await axios.delete(`/admin/products/${id}`)
    products.value = products.value.filter(p => p.id !== id)
    notify.success('Produto removido.')
  } catch (e) { notify.error('Erro ao remover.') }
}

async function createPlan() {
  try {
    const res = await axios.post('/admin/plans', newPlan.value)
    plans.value.push(res.data)
    showPlanModal.value = false
    newPlan.value = { name: '', duration_days: '', price: '', description: '' }
    notify.success('Plano criado!')
  } catch (e) { notify.error('Erro ao criar plano.') }
}

async function deletePlan(id) {
  if (!confirm('Remover este plano?')) return
  try {
    await axios.delete(`/admin/plans/${id}`)
    plans.value = plans.value.filter(p => p.id !== id)
    notify.success('Plano removido.')
  } catch (e) { notify.error('Erro ao remover.') }
}

async function createClass() {
  try {
    const res = await axios.post('/admin/gym-classes', newClass.value)
    gymClasses.value.push(res.data)
    showClassModal.value = false
    newClass.value = { name: '', description: '', day_of_week: '', start_time: '', end_time: '', capacity: '', instructor_id: '' }
    notify.success('Aula criada!')
  } catch (e) { notify.error('Erro ao criar aula.') }
}

async function deleteClass(id) {
  if (!confirm('Remover esta aula?')) return
  try {
    await axios.delete(`/admin/gym-classes/${id}`)
    gymClasses.value = gymClasses.value.filter(c => c.id !== id)
    notify.success('Aula removida.')
  } catch (e) { notify.error('Erro ao remover.') }
}

onMounted(loadAll)
</script>

<style scoped>
.dashboard-container { padding: 2rem 5%; max-width: 1400px; margin: 0 auto; }
.dashboard-header { margin-bottom: 1.5rem; }
.dashboard-header h2 { font-size: 2.5rem; }
.dashboard-header p { color: var(--text-muted); margin-top: 0.5rem; }
.loading-state { text-align: center; padding: 4rem 0; font-size: 1.1rem; }

/* Tab bar */
.tab-bar {
  display: flex; gap: 0.5rem; margin-bottom: 2rem; flex-wrap: wrap;
  border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem;
}
.tab-btn {
  padding: 0.6rem 1.2rem; border-radius: 10px 10px 0 0;
  border: 1px solid transparent; border-bottom: none;
  background: rgba(255,255,255,0.03); color: var(--text-muted);
  font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s;
}
.tab-btn:hover { color: var(--accent-primary); border-color: rgba(255,255,255,0.1); }
.tab-btn.active {
  background: rgba(0,242,255,0.08); color: var(--accent-primary);
  border-color: rgba(0,242,255,0.3);
}

/* Stats grid */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; }
.stat-card { display: flex; align-items: center; gap: 1.5rem; padding: 1.5rem; }
.stat-icon { font-size: 2.5rem; background: rgba(0,242,255,0.1); padding: 0.8rem; border-radius: 12px; }
.stat-number { font-size: 1.8rem; font-weight: bold; margin-top: 0.3rem; }

/* Section header */
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.search-input {
  flex: 1; max-width: 400px; padding: 0.8rem 1rem;
  background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px; color: white; font-size: 0.95rem;
}
.search-input:focus { outline: none; border-color: var(--accent-primary); }

/* Data table */
.data-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.data-table th { padding: 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.1); color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
.data-table td { padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; }
.data-table tr:hover td { background: rgba(255,255,255,0.02); }

.badge { padding: 0.3rem 0.6rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
.badge.active { background: rgba(16,185,129,0.2); color: #10b981; }
.badge.expired { background: rgba(239,68,68,0.15); color: #f87171; }
.text-warn { color: #fbbf24; font-weight: 700; }

.action-btn { background: none; border: none; cursor: pointer; font-size: 1rem; transition: transform 0.2s; }
.action-btn:hover { transform: scale(1.2); }

/* Plans grid */
.plans-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem; }
.plan-card { text-align: center; }
.plan-price { font-size: 2rem; font-weight: 900; margin: 0.5rem 0; }
.plan-desc { color: var(--text-muted); font-size: 0.85rem; margin-top: 0.5rem; line-height: 1.5; }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.7);
  backdrop-filter: blur(6px); z-index: 500;
  display: flex; align-items: center; justify-content: center;
}
.modal { max-width: 480px; width: 90%; max-height: 80vh; overflow-y: auto; }
.modal h3 { margin-bottom: 1.5rem; }
.modal-form { display: flex; flex-direction: column; gap: 1rem; }
.modal-form input, .modal-form select, .modal-form textarea {
  padding: 0.8rem 1rem; background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1); border-radius: 10px;
  color: white; font-size: 0.95rem;
}
.modal-form input:focus, .modal-form select:focus, .modal-form textarea:focus {
  outline: none; border-color: var(--accent-primary);
}
.modal-form textarea { min-height: 80px; resize: vertical; }
.modal-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 0.5rem; }

/* ── Mobile Responsiveness ── */
@media (max-width: 768px) {
  .dashboard-container { padding: 1.5rem 4%; }
  .dashboard-header h2 { font-size: 1.8rem; }
  .tab-bar { gap: 0.3rem; overflow-x: auto; flex-wrap: nowrap; -webkit-overflow-scrolling: touch; padding-bottom: 0.5rem; }
  .tab-btn { font-size: 0.8rem; padding: 0.5rem 0.9rem; white-space: nowrap; flex-shrink: 0; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 0.8rem; }
  .stat-card { flex-direction: column; text-align: center; padding: 1rem !important; gap: 0.8rem; }
  .stat-icon { font-size: 1.8rem; padding: 0.5rem; }
  .stat-number { font-size: 1.4rem; }
  .section-header { flex-direction: column; align-items: stretch; }
  .search-input { max-width: 100%; }
  .plans-grid { grid-template-columns: 1fr; }
  .modal { width: 95%; max-height: 90vh; }
}

@media (max-width: 480px) {
  .stats-grid { grid-template-columns: 1fr; }
  .dashboard-header h2 { font-size: 1.5rem; }
}
</style>
