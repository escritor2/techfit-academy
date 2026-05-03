<template>
  <Teleport to="body">
    <TransitionGroup name="toast" tag="div" class="toast-container">
      <div
        v-for="notif in notifications"
        :key="notif.id"
        :class="['toast-item', `toast-${notif.type}`]"
        @click="dismiss(notif.id)"
      >
        <div class="toast-glow"></div>
        <span class="toast-icon">{{ icons[notif.type] }}</span>
        <div class="toast-content">
          <span class="toast-message">{{ notif.message }}</span>
        </div>
        <button class="toast-close">✕</button>
      </div>
    </TransitionGroup>
  </Teleport>
</template>

<script setup>
const store = useNotificationStore()
const notifications = computed(() => store.notifications)
const dismiss = (id) => store.dismiss(id)

const icons = {
  success: '✨',
  error: '🚨',
  info: '💎',
  warning: '⚠️',
}
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 100px;
  right: 2.5rem;
  z-index: 10000;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-width: 400px;
  pointer-events: none;
}

.toast-item {
  position: relative;
  display: flex;
  align-items: center;
  gap: 1.2rem;
  padding: 1.2rem 1.8rem;
  border-radius: 18px;
  background: rgba(10, 10, 12, 0.85);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
  cursor: pointer;
  pointer-events: auto;
  overflow: hidden;
}

.toast-glow {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent);
  transform: translateX(-100%);
  animation: shimmer 3s infinite;
}

@keyframes shimmer {
  100% { transform: translateX(100%); }
}

.toast-success { border-color: var(--success); }
.toast-error { border-color: var(--danger); }
.toast-warning { border-color: var(--warning); }
.toast-info { border-color: var(--accent-primary); }

.toast-icon { font-size: 1.4rem; filter: drop-shadow(0 0 5px currentColor); }

.toast-content { flex: 1; }
.toast-message { font-size: 0.95rem; font-weight: 600; color: #fff; }

.toast-close {
  background: none;
  border: none;
  color: var(--text-dim);
  font-size: 1rem;
  cursor: pointer;
  padding: 5px;
  transition: color 0.3s;
}

.toast-close:hover { color: #fff; }

/* Transitions */
.toast-enter-active { transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.toast-leave-active { transition: all 0.4s cubic-bezier(0.7, 0, 0.84, 0); }

.toast-enter-from {
  opacity: 0;
  transform: translateX(100px) scale(0.9);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(50px) scale(0.95);
}

.toast-move { transition: transform 0.4s ease; }
</style>
