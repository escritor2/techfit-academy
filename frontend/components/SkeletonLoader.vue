<template>
  <div class="skeleton-wrapper" :class="variant">
    <div v-for="n in count" :key="n" class="skeleton-item">
      <!-- Card variant -->
      <template v-if="variant === 'card'">
        <div class="skeleton-image shimmer"></div>
        <div class="skeleton-body">
          <div class="skeleton-line w-60 shimmer"></div>
          <div class="skeleton-line w-90 shimmer"></div>
          <div class="skeleton-line w-40 shimmer"></div>
        </div>
      </template>

      <!-- Table row variant -->
      <template v-else-if="variant === 'table'">
        <div class="skeleton-row">
          <div class="skeleton-cell w-20 shimmer"></div>
          <div class="skeleton-cell w-30 shimmer"></div>
          <div class="skeleton-cell w-25 shimmer"></div>
          <div class="skeleton-cell w-15 shimmer"></div>
        </div>
      </template>

      <!-- Stat card variant -->
      <template v-else-if="variant === 'stat'">
        <div class="skeleton-stat">
          <div class="skeleton-circle shimmer"></div>
          <div class="skeleton-stat-text">
            <div class="skeleton-line w-40 shimmer"></div>
            <div class="skeleton-line w-60 h-lg shimmer"></div>
          </div>
        </div>
      </template>

      <!-- Default line variant -->
      <template v-else>
        <div class="skeleton-line w-90 shimmer"></div>
        <div class="skeleton-line w-60 shimmer"></div>
        <div class="skeleton-line w-75 shimmer"></div>
      </template>
    </div>
  </div>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'default', // 'card' | 'table' | 'stat' | 'default'
  },
  count: {
    type: Number,
    default: 3,
  },
})
</script>

<style scoped>
.skeleton-wrapper {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.skeleton-wrapper.card {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 2rem;
}

.skeleton-wrapper.stat {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.5rem;
}

/* Shimmer animation */
.shimmer {
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0.03) 25%,
    rgba(0, 242, 255, 0.06) 50%,
    rgba(255, 255, 255, 0.03) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.8s ease-in-out infinite;
  border-radius: 8px;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Skeleton elements */
.skeleton-image {
  width: 100%;
  height: 200px;
  border-radius: 20px 20px 0 0;
}

.skeleton-body {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.skeleton-item.card-variant {
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.skeleton-line {
  height: 12px;
  border-radius: 6px;
}

.skeleton-line.h-lg {
  height: 28px;
}

/* Widths */
.w-15 { width: 15%; }
.w-20 { width: 20%; }
.w-25 { width: 25%; }
.w-30 { width: 30%; }
.w-40 { width: 40%; }
.w-60 { width: 60%; }
.w-75 { width: 75%; }
.w-90 { width: 90%; }

/* Table variant */
.skeleton-row {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}

.skeleton-cell {
  height: 14px;
}

/* Stat variant */
.skeleton-stat {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 2rem;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 20px;
}

.skeleton-circle {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  flex-shrink: 0;
}

.skeleton-stat-text {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
</style>
