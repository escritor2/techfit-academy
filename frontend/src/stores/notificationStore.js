import { defineStore } from 'pinia'

let notifId = 0

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [],
  }),

  actions: {
    _add(type, message, duration = 4000) {
      const id = ++notifId
      this.notifications.push({ id, type, message })

      if (duration > 0) {
        setTimeout(() => {
          this.dismiss(id)
        }, duration)
      }

      return id
    },

    success(message, duration = 4000) {
      return this._add('success', message, duration)
    },

    error(message, duration = 5000) {
      return this._add('error', message, duration)
    },

    info(message, duration = 4000) {
      return this._add('info', message, duration)
    },

    warning(message, duration = 5000) {
      return this._add('warning', message, duration)
    },

    dismiss(id) {
      this.notifications = this.notifications.filter(n => n.id !== id)
    },

    clearAll() {
      this.notifications = []
    }
  }
})
