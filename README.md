# 🏋️‍♂️ TechFit Gym Management System

![TechFit Banner](https://via.placeholder.com/1200x300/0a0a0a/00f2ff?text=TechFit+-+A+Evolu%C3%A7%C3%A3o+do+Seu+Treino)

> Sistema completo para gestão de academias focado em alta tecnologia, inteligência artificial e alta performance.

O **TechFit** é uma solução *Fullstack* moderna desenvolvida para simplificar a gestão da sua academia. Ele separa completamente a experiência de Administradores, Recepcionistas e Membros (alunos) com painéis dedicados, integração de IA e uma loja nativa de suplementos.

---

## 🚀 Tecnologias Utilizadas

O projeto foi construído utilizando as melhores e mais recentes tecnologias do mercado, separando a aplicação em duas camadas:

### Backend (API REST)
- **PHP 8.3+**
- **Laravel**: Framework PHP focado em sintaxe elegante e segurança.
- **Laravel Sanctum**: Autenticação segura via Tokens JWT para APIs e SPAs.
- **SQLite**: Banco de dados leve configurado para rápido desenvolvimento e prototipação.

### Frontend (SPA)
- **Vue.js 3**: Framework progressivo (utilizando Composition API com `<script setup>`).
- **Vite**: Build tool extremamente rápido.
- **Pinia**: Gerenciamento de estado global (Substituindo o antigo Vuex).
- **Vue Router**: Roteamento dinâmico com Navigation Guards de segurança.
- **Axios**: Para consumo seguro e padronizado da API do Backend.
- **CSS Vanilla (Glassmorphism)**: Estilo dark/neon totalmente responsivo com efeitos de vidro translúcido.

---

## ⚙️ Principais Funcionalidades

- **Autenticação RBAC (Role-Based Access Control)**: O sistema identifica automaticamente quem está fazendo login e redireciona para a visão correta (`admin`, `employee`, ou `member`).
- **Painel Administrativo**: Visão global do negócio para tomada de decisões, exibindo métricas ao vivo de faturamento, vendas e quantidade de usuários ativos.
- **Painel de Recepção**: Interface enxuta focada em velocidade para cadastro de novos alunos e check-in.
- **Painel do Membro**: Espaço do aluno. Mostra sua progressão, fichas de treinos atuais e atalhos para gerar treinos novos via Inteligência Artificial.
- **Loja Virtual Integrada**: Ambiente onde alunos podem adquirir suplementos, roupas e pacotes de aulas extras.
- **Comunicação 100% via API**: O frontend do Vue não recarrega páginas. Todos os dados financeiros e de perfil mudam em tempo real requisitando dados do banco de dados Laravel.

---

## 🛠️ Como Instalar e Rodar Localmente

Certifique-se de ter o **PHP 8.3+**, **Composer** e o **Node.js** instalados em seu computador.

### 1. Configurando o Backend (Laravel)
Abra um terminal na pasta `backend` do projeto e instale as dependências:
```bash
cd backend
composer install
```
Se necessário, copie o arquivo de ambiente e gere a chave:
```bash
cp .env.example .env
php artisan key:generate
```
Crie o banco de dados e as tabelas:
```bash
php artisan migrate
```
Inicie o servidor local da API (geralmente vai rodar em `http://localhost:8000`):
```bash
php artisan serve
```

### 2. Configurando o Frontend (Vue)
Abra **um novo terminal**, entre na pasta `frontend` e instale os pacotes Node:
```bash
cd frontend
npm install
```
Inicie o servidor de desenvolvimento do Vue:
```bash
npm run dev
```
*Após iniciar, você receberá um link (como `http://localhost:5173`) para acessar o aplicativo no navegador.*

---

## 🧪 Como Testar as Permissões

Você pode popular seu banco de dados com contas de teste para ver os diferentes painéis. Com o servidor backend rodando, abra outro terminal na pasta `backend` e rode:

```bash
php artisan tinker
```
Em seguida, cole estes comandos para criar os usuários de cada cargo:

```php
// Criar o Administrador
App\Models\User::create(['name' => 'Admin Boss', 'email' => 'admin@techfit.com', 'password' => bcrypt('123456'), 'role' => 'admin']);

// Criar o Recepcionista
App\Models\User::create(['name' => 'Recepcionista', 'email' => 'recepcao@techfit.com', 'password' => bcrypt('123456'), 'role' => 'employee']);

// Criar o Aluno (Membro)
App\Models\User::create(['name' => 'Aluno Fit', 'email' => 'aluno@techfit.com', 'password' => bcrypt('123456'), 'role' => 'member']);
```
Saia do Tinker digitando `exit`. Volte no frontend, clique em "Entrar" e teste cada um dos emails com a senha `123456`!

---

## 🎨 Design System

O visual do sistema foi feito sob medida para transmitir velocidade e alta tecnologia. Ele utiliza:
- Modo Escuro (Dark Mode) nativo.
- Paleta de cores em base de Cyan (`#00f2ff`) e Magenta (`#ff00ea`).
- Efeitos Glassmorphism: Cards transparentes com bordas luminosas usando `backdrop-filter: blur()`.
- Micro-animações suaves em todos os botões e transições de tela.


