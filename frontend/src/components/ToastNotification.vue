<template>
  <Teleport to="body">
    <TransitionGroup name="toast" tag="div" class="toast-container">
      <div
        v-for="notif in notifications"
        :key="notif.id"
        :class="['toast-item', `toast-${notif.type}`]"
        @click="dismiss(notif.id)"
      >
        <span class="toast-icon">{{ icons[notif.type] }}</span>
        <span class="toast-message">{{ notif.message }}</span>
        <button class="toast-close">✕</button>
      </div>
    </TransitionGroup>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import { useNotificationStore } from '../stores/notificationStore'

const store = useNotificationStore()
const notifications = computed(() => store.notifications)
const dismiss = (id) => store.dismiss(id)

const icons = {
  success: '✅',
  error: '❌',
  info: 'ℹ️',
  warning: '⚠️',
}
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 80px;
  right: 20px;
  z-index: 10000;
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  max-width: 380px;
}

.toast-item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 1rem 1.2rem;
  border-radius: 14px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  transition: all 0.3s ease;
}

.toast-item:hover {
  transform: translateX(-4px);
}

.toast-success {
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.4);
  color: #34d399;
}

.toast-error {
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.4);
  color: #f87171;
}

.toast-info {
  background: rgba(0, 242, 255, 0.1);
  border: 1px solid rgba(0, 242, 255, 0.3);
  color: #00f2ff;
}

.toast-warning {
  background: rgba(245, 158, 11, 0.15);
  border: 1px solid rgba(245, 158, 11, 0.4);
  color: #fbbf24;
}

.toast-icon { font-size: 1.1rem; flex-shrink: 0; }
.toast-message { flex: 1; }

.toast-close {
  background: none;
  border: none;
  color: inherit;
  opacity: 0.5;
  cursor: pointer;
  font-size: 0.8rem;
  padding: 2px;
  transition: opacity 0.2s;
}

.toast-close:hover { opacity: 1; }

/* Transitions */
.toast-enter-active {
  transition: all 0.4s ease;
}
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(80px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(80px) scale(0.9);
}
.toast-move {
  transition: transform 0.3s ease;
}
</style>
