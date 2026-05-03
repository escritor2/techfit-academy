export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore()
  
  // Public routes that don't need auth check
  const publicRoutes = ['/', '/login', '/loja', '/ai']
  
  // Check if route requires auth
  const requiresAuth = !publicRoutes.includes(to.path)
  
  if (requiresAuth && !authStore.isAuthenticated) {
    return navigateTo('/login')
  }

  // Role-based access control
  if (authStore.isAuthenticated) {
    // Admin has access to everything
    if (authStore.isAdmin) return

    // Redirect based on role if trying to access restricted areas
    if (to.path.startsWith('/admin') && !authStore.isAdmin) {
       return navigateTo(authStore.isEmployee ? '/recepcao' : '/painel')
    }
    
    if (to.path.startsWith('/recepcao') && !authStore.isEmployee && !authStore.isAdmin) {
       return navigateTo('/painel')
    }

    if (to.path.startsWith('/painel') && !authStore.isMember && !authStore.isAdmin) {
       return navigateTo('/recepcao')
    }
  }
})
