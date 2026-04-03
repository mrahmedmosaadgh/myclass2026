<template>
  <Teleport to="body">
    <Transition name="menu">
      <div v-if="open" class="slide-menu-overlay" @click.self="$emit('close')">
        <div class="slide-menu">
          <div class="menu-header">
            <h2 class="menu-title">Menu</h2>
            <button @click="$emit('close')" class="close-btn">✕</button>
          </div>

          <nav class="menu-nav">
            <button
              v-for="item in menuItems"
              :key="item.id"
              class="menu-item"
              :class="{ active: activeSection === item.id }"
              @click="activeSection = item.id"
            >
              <span class="menu-item-icon">{{ item.icon }}</span>
              <span class="menu-item-text">{{ item.label }}</span>
            </button>
          </nav>

          <div class="menu-content">
            <MenuHome v-if="activeSection === 'home'" @close="$emit('close')" />
            <MenuViewSelector v-else-if="activeSection === 'views'" @close="$emit('close')" />
            <MenuTimingConfig v-else-if="activeSection === 'timing'" @close="$emit('close')" />
            <MenuDataManager v-else-if="activeSection === 'data'" @close="$emit('close')" />
            <MenuSettings v-else-if="activeSection === 'settings'" @close="$emit('close')" />
            <MenuAbout v-else-if="activeSection === 'about'" @close="$emit('close')" />
          </div>

          <div class="menu-footer">
            <p>Schedule App V5 • Offline-First</p>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import MenuHome from './menu/MenuHome.vue';
import MenuViewSelector from './menu/MenuViewSelector.vue';
import MenuTimingConfig from './menu/MenuTimingConfig.vue';
import MenuDataManager from './menu/MenuDataManager.vue';
import MenuSettings from './menu/MenuSettings.vue';
import MenuAbout from './menu/MenuAbout.vue';

const props = defineProps({
  open: Boolean
});

defineEmits(['close']);

const activeSection = ref('home');

const menuItems = [
  { id: 'home', icon: '🏠', label: 'Home' },
  { id: 'views', icon: '👁️', label: 'Views' },
  { id: 'timing', icon: '⏰', label: 'Timing' },
  { id: 'data', icon: '📁', label: 'Data' },
  { id: 'settings', icon: '⚙️', label: 'Settings' },
  { id: 'about', icon: 'ℹ️', label: 'About' }
];

watch(() => props.open, (val) => {
  if (val) activeSection.value = 'home';
});
</script>

<style scoped>
.slide-menu-overlay {
  position: fixed;
  inset: 0;
  z-index: 200;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
}

.slide-menu {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  width: 100%;
  max-width: 420px;
  background: #0f172a;
  color: #f1f5f9;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.menu-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  padding-top: calc(1rem + env(safe-area-inset-top, 0px));
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.menu-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
}

.close-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: none;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  font-size: 1.1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.menu-nav {
  display: flex;
  gap: 0.25rem;
  padding: 0.75rem 1rem;
  overflow-x: auto;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  flex-shrink: 0;
}

.menu-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem 0.75rem;
  border-radius: 10px;
  border: none;
  background: transparent;
  color: #94a3b8;
  font-size: 0.7rem;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
  min-width: 56px;
  min-height: 44px;
}

.menu-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #e2e8f0;
}

.menu-item.active {
  background: rgba(59, 130, 246, 0.2);
  color: #60a5fa;
}

.menu-item-icon {
  font-size: 1.25rem;
}

.menu-item-text {
  font-weight: 600;
}

.menu-content {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 1.25rem;
}

.menu-footer {
  padding: 0.75rem 1.25rem;
  padding-bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px));
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  text-align: center;
  font-size: 0.7rem;
  color: #475569;
}

.menu-footer p { margin: 0; }

/* Transitions */
.menu-enter-active,
.menu-leave-active {
  transition: all 0.3s ease;
}

.menu-enter-active .slide-menu,
.menu-leave-active .slide-menu {
  transition: transform 0.3s ease;
}

.menu-enter-from,
.menu-leave-to {
  opacity: 0;
}

.menu-enter-from .slide-menu,
.menu-leave-to .slide-menu {
  transform: translateX(-100%);
}

@media (min-width: 768px) {
  .slide-menu {
    max-width: 480px;
    box-shadow: 4px 0 24px rgba(0, 0, 0, 0.3);
  }
}
</style>
