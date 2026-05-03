// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },
  
  modules: [
    '@pinia/nuxt',
  ],

  css: [
    '~/assets/style.css'
  ],

  app: {
    pageTransition: { name: 'page-fade', mode: 'out-in' },
    head: {
      title: 'Techfit Academy | Sua Evolução Começa Aqui',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { hid: 'description', name: 'description', content: 'A plataforma definitiva para sua evolução fitness com IA e suporte total.' }
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap' }
      ]
    }
  },

  // Auto-import stores
  pinia: {
    storesDirs: ['./stores/**'],
  },

  // Enable experimental features for better performance
  experimental: {
    payloadExtraction: false,
    inlineSSRStyles: false,
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api'
    }
  }
})
