<template>
  <div class="page-container">
    <header class="admin-header" v-reveal>
      <div class="header-main">
        <h1 class="section-title">Painel de <span class="text-gradient">Controle</span></h1>
        <p class="subtitle">Gestão completa do ecossistema TechFit.</p>
      </div>
      <div class="admin-badge">ADMIN MODE</div>
    </header>

    <!-- Navigation Tabs -->
    <nav class="admin-nav" v-reveal>
      <button 
        v-for="tab in tabs" 
        :key="tab.id" 
        :class="['nav-item', { active: activeTab === tab.id }]"
        @click="activeTab = tab.id"
      >
        <span class="nav-icon">{{ tab.icon }}</span>
        <span class="nav-label">{{ tab.label }}</span>
      </button>
    </nav>

    <div v-if="loading" class="loading-state">
      <SkeletonLoader variant="stat" :count="4" />
    </div>

    <div v-else class="admin-content">
      <!-- DASHBOARD -->
      <section v-if="activeTab === 'dashboard'" class="tab-pane">
        <div class="stats-grid">
          <div class="stat-card glass-card" v-reveal>
            <div class="s-icon">👥</div>
            <div class="s-data">
              <span class="s-label">Membros Ativos</span>
              <span class="s-value">{{ stats.active_members || 0 }}</span>
            </div>
          </div>
          <div class="stat-card glass-card" v-reveal>
            <div class="s-icon">💰</div>
            <div class="s-data">
              <span class="s-label">Faturamento Mensal</span>
              <span class="s-value">R$ {{ formatMoney(stats.monthly_revenue) }}</span>
            </div>
          </div>
          <div class="stat-card glass-card" v-reveal>
            <div class="s-icon">🛒</div>
            <div class="s-data">
              <span class="s-label">Vendas Efetuadas</span>
              <span class="s-value">{{ stats.total_sales || 0 }}</span>
            </div>
          </div>
          <div class="stat-card glass-card" v-reveal>
            <div class="s-icon">🏃</div>
            <div class="s-data">
              <span class="s-label">Check-ins Hoje</span>
              <span class="s-value">{{ stats.today_checkins || 0 }}</span>
            </div>
          </div>
        </div>

        <div class="glass-card table-section" v-reveal>
          <h3 class="card-title">Novos Cadastros</h3>
          <div class="table-wrapper">
            <table class="premium-table">
              <thead><tr><th>Nome</th><th>E-mail</th><th>Data de Registro</th></tr></thead>
              <tbody>
                <tr v-for="user in stats.recent_registrations" :key="user.id">
                  <td><strong>{{ user.name }}</strong></td>
                  <td>{{ user.email }}</td>
                  <td>{{ formatDate(user.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- MEMBERS -->
      <section v-if="activeTab === 'members'" class="tab-pane">
        <div class="action-bar">
          <div class="search-box">
            <input v-model="memberSearch" placeholder="Buscar membros..." @input="searchMembers">
            <span class="search-icon">🔍</span>
          </div>
          <button class="btn-primary" @click="showMemberModal = true">+ Novo Membro</button>
        </div>

        <div class="glass-card table-section" v-reveal>
          <div class="table-wrapper">
            <table class="premium-table">
              <thead><tr><th>Membro</th><th>CPF</th><th>Check-ins</th><th>Status</th><th>Ações</th></tr></thead>
              <tbody>
                <tr v-for="m in members" :key="m.id">
                  <td>
                    <div class="user-cell">
                      <strong>{{ m.name }}</strong>
                      <span class="sub">{{ m.email }}</span>
                    </div>
                  </td>
                  <td>{{ m.cpf || '--' }}</td>
                  <td>{{ m.checkins_count }}</td>
                  <td>
                    <span v-if="m.subscriptions?.[0]" class="badge success">{{ m.subscriptions[0].plan?.name }}</span>
                    <span v-else class="badge danger">Inativo</span>
                  </td>
                  <td>
                    <button class="btn-icon delete" @click="deleteMember(m.id)">🗑️</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- PRODUCTS (Loja) -->
      <section v-if="activeTab === 'products'" class="tab-pane">
        <div class="action-bar">
          <h2>Gestão da Loja</h2>
          <button class="btn-primary" @click="showProductModal = true">+ Novo Produto</button>
        </div>
        <div class="glass-card table-section" v-reveal>
          <div class="table-wrapper">
            <table class="premium-table">
              <thead><tr><th>Produto</th><th>Preço</th><th>Estoque</th><th>Ações</th></tr></thead>
              <tbody>
                <tr v-for="p in products" :key="p.id">
                  <td><strong>{{ p.name }}</strong></td>
                  <td>R$ {{ formatMoney(p.price) }}</td>
                  <td>{{ p.stock_quantity }} un.</td>
                  <td><button class="btn-icon delete" @click="deleteProduct(p.id)">🗑️</button></td>
                </tr>
                <tr v-if="products.length === 0"><td colspan="4" class="text-center">Nenhum produto cadastrado.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- PLANS -->
      <section v-if="activeTab === 'plans'" class="tab-pane">
        <div class="action-bar">
          <h2>Gestão de Planos</h2>
          <button class="btn-primary" @click="showPlanModal = true">+ Novo Plano</button>
        </div>
        <div class="glass-card table-section" v-reveal>
          <div class="table-wrapper">
            <table class="premium-table">
              <thead><tr><th>Plano</th><th>Preço</th><th>Duração (dias)</th><th>Ações</th></tr></thead>
              <tbody>
                <tr v-for="pl in plans" :key="pl.id">
                  <td><strong>{{ pl.name }}</strong></td>
                  <td>R$ {{ formatMoney(pl.price) }}</td>
                  <td>{{ pl.duration_in_days }} dias</td>
                  <td><button class="btn-icon delete" @click="deletePlan(pl.id)">🗑️</button></td>
                </tr>
                <tr v-if="plans.length === 0"><td colspan="4" class="text-center">Nenhum plano cadastrado.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- CLASSES -->
      <section v-if="activeTab === 'classes'" class="tab-pane">
        <div class="action-bar">
          <h2>Gestão de Aulas</h2>
          <button class="btn-primary" @click="showClassModal = true">+ Nova Aula</button>
        </div>
        <div class="glass-card table-section" v-reveal>
          <div class="table-wrapper">
            <table class="premium-table">
              <thead><tr><th>Aula</th><th>Dia / Hora</th><th>Vagas</th><th>Ações</th></tr></thead>
              <tbody>
                <tr v-for="c in classes" :key="c.id">
                  <td><strong>{{ c.name }}</strong></td>
                  <td>{{ c.day_of_week }} às {{ c.start_time?.substring(0,5) }}</td>
                  <td>{{ c.capacity }} vagas</td>
                  <td><button class="btn-icon delete" @click="deleteClass(c.id)">🗑️</button></td>
                </tr>
                <tr v-if="classes.length === 0"><td colspan="4" class="text-center">Nenhuma aula cadastrada.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>

    <!-- Modals -->
    <Transition name="fade">
      <div v-if="showMemberModal" class="modal-overlay" @click.self="showMemberModal = false">
        <div class="glass-card admin-modal" v-reveal>
          <h3>Cadastro de Membro</h3>
          <form @submit.prevent="createMember" class="admin-form">
            <input v-model="newMember.name" placeholder="Nome Completo" required>
            <input v-model="newMember.email" type="email" placeholder="E-mail" required>
            <input v-model="newMember.cpf" placeholder="CPF">
            <input v-model="newMember.password" type="password" placeholder="Senha Temporária" required>
            <div class="modal-footer">
              <button type="button" class="btn-text" @click="showMemberModal = false">Cancelar</button>
              <button type="submit" class="btn-primary">Finalizar Cadastro</button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="fade">
      <div v-if="showProductModal" class="modal-overlay" @click.self="showProductModal = false">
        <div class="glass-card admin-modal" v-reveal>
          <h3>Novo Produto</h3>
          <form @submit.prevent="createProduct" class="admin-form">
            <input v-model="newProduct.name" placeholder="Nome do Produto" required>
            <textarea v-model="newProduct.description" placeholder="Descrição"></textarea>
            <input v-model="newProduct.price" type="number" step="0.01" placeholder="Preço" required>
            <input v-model="newProduct.stock_quantity" type="number" placeholder="Estoque Inicial" required>
            <div class="modal-footer">
              <button type="button" class="btn-text" @click="showProductModal = false">Cancelar</button>
              <button type="submit" class="btn-primary">Cadastrar Produto</button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="fade">
      <div v-if="showPlanModal" class="modal-overlay" @click.self="showPlanModal = false">
        <div class="glass-card admin-modal" v-reveal>
          <h3>Novo Plano</h3>
          <form @submit.prevent="createPlan" class="admin-form">
            <input v-model="newPlan.name" placeholder="Nome do Plano" required>
            <textarea v-model="newPlan.description" placeholder="Descrição"></textarea>
            <input v-model="newPlan.price" type="number" step="0.01" placeholder="Preço" required>
            <input v-model="newPlan.duration_in_days" type="number" placeholder="Duração em dias (ex: 30)" required>
            <div class="modal-footer">
              <button type="button" class="btn-text" @click="showPlanModal = false">Cancelar</button>
              <button type="submit" class="btn-primary">Cadastrar Plano</button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="fade">
      <div v-if="showClassModal" class="modal-overlay" @click.self="showClassModal = false">
        <div class="glass-card admin-modal" v-reveal>
          <h3>Nova Aula</h3>
          <form @submit.prevent="createClass" class="admin-form">
            <input v-model="newClass.name" placeholder="Nome da Aula" required>
            <select v-model="newClass.day_of_week" required class="admin-select">
              <option value="" disabled>Dia da Semana</option>
              <option value="Segunda">Segunda</option>
              <option value="Terça">Terça</option>
              <option value="Quarta">Quarta</option>
              <option value="Quinta">Quinta</option>
              <option value="Sexta">Sexta</option>
              <option value="Sábado">Sábado</option>
              <option value="Domingo">Domingo</option>
            </select>
            <div style="display:flex; gap:1rem;">
              <input v-model="newClass.start_time" type="time" required style="flex:1;">
              <input v-model="newClass.end_time" type="time" required style="flex:1;">
            </div>
            <input v-model="newClass.capacity" type="number" placeholder="Vagas (Capacidade)" required>
            <div class="modal-footer">
              <button type="button" class="btn-text" @click="showClassModal = false">Cancelar</button>
              <button type="submit" class="btn-primary">Cadastrar Aula</button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
const authStore = useAuthStore()
const config = useRuntimeConfig()
const loading = ref(true)
const activeTab = ref('dashboard')

const tabs = [
  { id: 'dashboard', icon: '📊', label: 'Visão Geral' },
  { id: 'members', icon: '👥', label: 'Membros' },
  { id: 'products', icon: '🛒', label: 'Loja' },
  { id: 'plans', icon: '💳', label: 'Planos' },
  { id: 'classes', icon: '📅', label: 'Aulas' },
]

const stats = ref({})
const members = ref([])
const products = ref([])
const plans = ref([])
const classes = ref([])

const memberSearch = ref('')
const showMemberModal = ref(false)
const showProductModal = ref(false)
const showPlanModal = ref(false)
const showClassModal = ref(false)

const newMember = ref({ name: '', email: '', cpf: '', password: '' })
const newProduct = ref({ name: '', description: '', price: '', stock_quantity: '' })
const newPlan = ref({ name: '', description: '', price: '', duration_in_days: '' })
const newClass = ref({ name: '', day_of_week: '', start_time: '', end_time: '', capacity: '' })

function formatMoney(v) { return parseFloat(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
function formatDate(d) { return new Date(d).toLocaleDateString('pt-BR') }

async function loadData() {
  loading.value = true
  try {
    const dash = await $fetch(`${config.public.apiBase}/admin/dashboard`, { headers: authStore.authHeaders })
    const mems = await $fetch(`${config.public.apiBase}/admin/members`, { headers: authStore.authHeaders })
    const prods = await $fetch(`${config.public.apiBase}/products`, { headers: authStore.authHeaders })
    const pls = await $fetch(`${config.public.apiBase}/plans`, { headers: authStore.authHeaders })
    const cls = await $fetch(`${config.public.apiBase}/gym-classes`, { headers: authStore.authHeaders })
    
    stats.value = dash
    members.value = mems
    products.value = prods
    plans.value = pls
    classes.value = cls
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

async function searchMembers() {
  try {
    const data = await $fetch(`${config.public.apiBase}/admin/members`, { 
      params: { search: memberSearch.value },
      headers: authStore.authHeaders 
    })
    members.value = data
  } catch (e) { }
}

async function createMember() {
  try {
    await $fetch(`${config.public.apiBase}/admin/members`, {
      method: 'POST', body: newMember.value, headers: authStore.authHeaders
    })
    showMemberModal.value = false
    newMember.value = { name: '', email: '', cpf: '', password: '' }
    loadData()
  } catch (e) { alert('Erro no cadastro.') }
}

async function deleteMember(id) {
  if (!confirm('Excluir permanentemente?')) return
  try {
    await $fetch(`${config.public.apiBase}/admin/members/${id}`, { method: 'DELETE', headers: authStore.authHeaders })
    loadData()
  } catch (e) { }
}

async function createProduct() {
  try {
    await $fetch(`${config.public.apiBase}/admin/products`, { method: 'POST', body: newProduct.value, headers: authStore.authHeaders })
    showProductModal.value = false
    newProduct.value = { name: '', description: '', price: '', stock_quantity: '' }
    loadData()
  } catch (e) { alert('Erro ao cadastrar produto.') }
}

async function deleteProduct(id) {
  if (!confirm('Excluir produto?')) return
  try {
    await $fetch(`${config.public.apiBase}/admin/products/${id}`, { method: 'DELETE', headers: authStore.authHeaders })
    loadData()
  } catch (e) { }
}

async function createPlan() {
  try {
    await $fetch(`${config.public.apiBase}/admin/plans`, { method: 'POST', body: newPlan.value, headers: authStore.authHeaders })
    showPlanModal.value = false
    newPlan.value = { name: '', description: '', price: '', duration_in_days: '' }
    loadData()
  } catch (e) { alert('Erro ao cadastrar plano.') }
}

async function deletePlan(id) {
  if (!confirm('Excluir plano?')) return
  try {
    await $fetch(`${config.public.apiBase}/admin/plans/${id}`, { method: 'DELETE', headers: authStore.authHeaders })
    loadData()
  } catch (e) { }
}

async function createClass() {
  try {
    await $fetch(`${config.public.apiBase}/admin/gym-classes`, { method: 'POST', body: newClass.value, headers: authStore.authHeaders })
    showClassModal.value = false
    newClass.value = { name: '', day_of_week: '', start_time: '', end_time: '', capacity: '' }
    loadData()
  } catch (e) { alert('Erro ao cadastrar aula.') }
}

async function deleteClass(id) {
  if (!confirm('Excluir aula?')) return
  try {
    await $fetch(`${config.public.apiBase}/admin/gym-classes/${id}`, { method: 'DELETE', headers: authStore.authHeaders })
    loadData()
  } catch (e) { }
}

onMounted(loadData)
</script>

<style scoped>
.admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4rem; }
.admin-badge { background: #ff3366; color: #fff; padding: 0.4rem 1rem; border-radius: 4px; font-weight: 900; font-size: 0.7rem; letter-spacing: 1px; }

.admin-nav {
  display: flex;
  gap: 1rem;
  background: rgba(255, 255, 255, 0.03);
  padding: 0.5rem;
  border-radius: 16px;
  border: 1px solid var(--glass-border);
  margin-bottom: 3rem;
  overflow-x: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 1rem 1.5rem;
  border: none;
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  border-radius: 12px;
  transition: all 0.3s;
  white-space: nowrap;
}

.nav-item:hover { color: var(--text-main); background: rgba(255, 255, 255, 0.05); }
.nav-item.active { background: var(--accent-primary); color: #000; font-weight: 700; }

.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
.stat-card { padding: 2rem; display: flex; align-items: center; gap: 1.5rem; }
.s-icon { font-size: 2rem; background: rgba(255,255,255,0.03); width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: 14px; }
.s-data { display: flex; flex-direction: column; }
.s-label { font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; }
.s-value { font-size: 1.5rem; font-weight: 900; }

.table-section { padding: 0; overflow: hidden; }
.card-title { padding: 2rem 2.5rem; font-size: 1.2rem; border-bottom: 1px solid var(--glass-border); }

.premium-table { width: 100%; border-collapse: collapse; }
.premium-table th { text-align: left; padding: 1.2rem 2.5rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-dim); }
.premium-table td { padding: 1.2rem 2.5rem; border-top: 1px solid var(--glass-border); }
.premium-table tr:hover { background: rgba(255,255,255,0.02); }

.user-cell { display: flex; flex-direction: column; }
.user-cell .sub { font-size: 0.8rem; color: var(--text-dim); }

.action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 2rem; }
.search-box { position: relative; flex: 1; max-width: 400px; }
.search-box input { width: 100%; padding: 1rem 1rem 1rem 3.5rem; background: rgba(255,255,255,0.03); }
.search-icon { position: absolute; left: 1.2rem; top: 50%; transform: translateY(-50%); opacity: 0.5; }

.badge { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 0.3rem 0.8rem; border-radius: 50px; }
.badge.success { background: rgba(var(--accent-primary-rgb), 0.1); color: var(--accent-primary); border: 1px solid var(--accent-primary); }
.badge.danger { background: rgba(255, 51, 102, 0.1); color: #ff3366; border: 1px solid #ff3366; }

.btn-icon.delete { opacity: 0.3; transition: 0.3s; background: none; border: none; cursor: pointer; }
.btn-icon.delete:hover { opacity: 1; color: #ff3366; transform: scale(1.2); }

.admin-modal { width: 100%; max-width: 450px; padding: 3rem; }
.admin-form { display: flex; flex-direction: column; gap: 1.5rem; margin-top: 2rem; }
.admin-form input, .admin-form textarea, .admin-select { 
  width: 100%; 
  padding: 1rem; 
  background: rgba(255,255,255,0.03); 
  border: 1px solid var(--glass-border); 
  border-radius: 8px; 
  color: white; 
}
.admin-form textarea { resize: vertical; min-height: 80px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem; }

.empty-state { padding: 5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 2rem; }
</style>
