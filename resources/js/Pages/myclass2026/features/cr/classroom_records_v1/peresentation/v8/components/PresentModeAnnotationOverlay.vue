<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  visible: { type: Boolean, default: true }
})

const emit = defineEmits(['toggle'])

const isExpanded = ref(false)
const hasHovered = ref(false)

const hasContent = computed(() => {
  return !!(props.title?.trim() || props.description?.trim())
})

const displayTitle = computed(() => {
  return props.title?.trim() || 'Untitled Slide'
})

const displayDescription = computed(() => {
  return props.description?.trim() || 'No notes for this slide.'
})

// Auto-expand briefly when slide changes, then collapse
watch(() => props.title + props.description, () => {
  if (hasContent.value && !hasHovered.value) {
    isExpanded.value = true
    setTimeout(() => { isExpanded.value = false }, 2500)
  }
})

function handleMouseEnter() {
  hasHovered.value = true
  isExpanded.value = true
}

function handleMouseLeave() {
  isExpanded.value = false
}

function handleToggle(e) {
  e.stopPropagation()
  emit('toggle')
}
</script>

<template>
  <Transition name="anno-fade">
    <div
      v-if="visible && hasContent"
      class="anno-overlay"
      :class="{ 'is-expanded': isExpanded }"
      @mouseenter="handleMouseEnter"
      @mouseleave="handleMouseLeave"
    >
      <!-- Collapsed: icon + truncated title -->
      <div class="anno-collapsed">
        <svg class="anno-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
          <polyline points="14 2 14 8 20 8" />
          <line x1="16" y1="13" x2="8" y2="13" />
          <line x1="16" y1="17" x2="8" y2="17" />
          <polyline points="10 9 9 9 8 9" />
        </svg>
        <span class="anno-title-truncated">{{ displayTitle }}</span>
        <button class="anno-toggle-btn" @click="handleToggle" title="Hide annotations (N)">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>

      <!-- Expanded: full content -->
      <div class="anno-expanded">
        <div class="anno-header">
          <svg class="anno-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <line x1="16" y1="13" x2="8" y2="13" />
            <line x1="16" y1="17" x2="8" y2="17" />
            <polyline points="10 9 9 9 8 9" />
          </svg>
          <h3 class="anno-title-full">{{ displayTitle }}</h3>
          <button class="anno-toggle-btn" @click="handleToggle" title="Hide annotations (N)">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>
        <p class="anno-description">{{ displayDescription }}</p>
      </div>
    </div>
  </Transition>

  <!-- Hidden state: small indicator to show annotations exist but are hidden -->
  <Transition name="anno-fade">
    <button
      v-if="!visible && hasContent"
      class="anno-hidden-indicator"
      @click="emit('toggle')"
      title="Show annotations (N)"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
        <polyline points="14 2 14 8 20 8" />
        <line x1="16" y1="13" x2="8" y2="13" />
        <line x1="16" y1="17" x2="8" y2="17" />
      </svg>
    </button>
  </Transition>
</template>

<style scoped>
/* Base overlay - floating pill, bottom-left */
.anno-overlay {
  position: fixed;
  bottom: 86px;
  left: 20px;
  z-index: 44;
  max-width: 380px;
  min-width: 0;
  border-radius: 14px;
  background: rgba(15, 23, 42, 0.72);
  backdrop-filter: blur(20px) saturate(1.2);
  -webkit-backdrop-filter: blur(20px) saturate(1.2);
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.35),
    0 2px 8px rgba(0, 0, 0, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.06);
  color: rgba(255, 255, 255, 0.9);
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  cursor: default;
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  pointer-events: auto;
}

/* Collapsed state - compact pill */
.anno-collapsed {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  height: 42px;
}

.anno-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
  opacity: 0.7;
  color: rgba(255, 255, 255, 0.85);
}

.anno-title-truncated {
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 200px;
  color: rgba(255, 255, 255, 0.95);
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5), 0 0 1px rgba(0, 0, 0, 0.8);
  letter-spacing: 0.01em;
}

/* Expanded state */
.anno-overlay.is-expanded .anno-collapsed {
  display: none;
}

.anno-expanded {
  display: none;
  padding: 14px 16px;
}

.anno-overlay.is-expanded .anno-expanded {
  display: block;
}

.anno-overlay.is-expanded {
  max-width: 420px;
  border-radius: 16px;
  background: rgba(15, 23, 42, 0.82);
  box-shadow:
    0 12px 48px rgba(0, 0, 0, 0.45),
    0 4px 12px rgba(0, 0, 0, 0.25),
    inset 0 1px 0 rgba(255, 255, 255, 0.08);
}

.anno-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}

.anno-title-full {
  margin: 0;
  font-size: 14px;
  font-weight: 700;
  color: white;
  flex: 1;
  line-height: 1.3;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5), 0 0 1px rgba(0, 0, 0, 0.8);
  letter-spacing: 0.01em;
}

.anno-description {
  margin: 0;
  font-size: 13px;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.75);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
  overflow-wrap: break-word;
}

/* Toggle button */
.anno-toggle-btn {
  flex-shrink: 0;
  width: 22px;
  height: 22px;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.06);
  color: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  padding: 0;
}

.anno-toggle-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  color: rgba(255, 255, 255, 0.9);
  border-color: rgba(255, 255, 255, 0.25);
}

/* Hidden indicator - small floating dot/pill */
.anno-hidden-indicator {
  position: fixed;
  bottom: 86px;
  left: 20px;
  z-index: 44;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(12px);
  color: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.25s;
  padding: 0;
}

.anno-hidden-indicator:hover {
  background: rgba(15, 23, 42, 0.8);
  color: rgba(255, 255, 255, 0.9);
  border-color: rgba(255, 255, 255, 0.2);
  transform: scale(1.08);
}

.anno-hidden-indicator svg {
  width: 16px;
  height: 16px;
}

/* Transitions */
.anno-fade-enter-active,
.anno-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.anno-fade-enter-from,
.anno-fade-leave-to {
  opacity: 0;
  transform: translateY(8px);
}

/* Mobile: keep compact, adjust positioning */
@media (max-width: 767px) {
  .anno-overlay {
    bottom: 70px;
    left: 12px;
    max-width: 280px;
    border-radius: 12px;
  }

  .anno-overlay.is-expanded {
    max-width: calc(100vw - 24px);
  }

  .anno-title-truncated {
    max-width: 140px;
  }

  .anno-hidden-indicator {
    bottom: 70px;
    left: 12px;
  }
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  .anno-overlay,
  .anno-hidden-indicator,
  .anno-toggle-btn {
    transition: none;
  }
}

/* Print: hide overlay */
@media print {
  .anno-overlay,
  .anno-hidden-indicator {
    display: none !important;
  }
}
</style>
