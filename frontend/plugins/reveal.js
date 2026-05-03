export default defineNuxtPlugin((nuxtApp) => {
  const revealDirective = {
    mounted(el) {
      if (process.client) {
        const revealObserver = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add('active');
            }
          });
        }, { threshold: 0.1 });
        
        el.classList.add('reveal');
        revealObserver.observe(el);
        el._revealObserver = revealObserver;
      }
    },
    unmounted(el) {
      if (process.client && el._revealObserver) {
        el._revealObserver.unobserve(el);
      }
    },
    getSSRProps() {
      // Return empty props for SSR to avoid "getSSRProps of undefined" error
      return {}
    }
  };

  nuxtApp.vueApp.directive('reveal', revealDirective);
})
