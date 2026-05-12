<template>
  <div class="presentation-manage-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <h1>📁 Manage Presentations</h1>
        <p>Create, organize, and manage all your presentations in one place</p>
      </div>
      <div class="header-actions">
        <a :href="route('classroom-records.presentation.builder-v5')" class="btn-primary">
          ✏️ Open Editor
        </a>
      </div>
    </div>

    <!-- Main Content -->
    <div class="page-content">
      <EnhancedPresentationManager 
        @presentation-loaded="handlePresentationLoaded"
        @presentation-created="handlePresentationCreated"
      />
    </div>

    <!-- Success Notification -->
    <transition name="fade">
      <div v-if="showNotification" class="notification success">
        <span>{{ notificationMessage }}</span>
        <button @click="showNotification = false" class="close-btn">✕</button>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import EnhancedPresentationManager from './components/EnhancedPresentationManager.vue';

defineProps({
  title: {
    type: String,
    default: 'Manage Presentations'
  }
});

const showNotification = ref(false);
const notificationMessage = ref('');

const handlePresentationLoaded = (presentation) => {
  // Navigate to editor with loaded presentation
  router.visit(route('classroom-records.presentation.builder-v5'), {
    data: { presentationId: presentation.id }
  });
};

const handlePresentationCreated = (result) => {
  notificationMessage.value = `Presentation "${result.title || 'Untitled'}" created successfully!`;
  showNotification.value = true;
  
  setTimeout(() => {
    showNotification.value = false;
  }, 3000);
};
</script>

<style scoped>
.presentation-manage-page {
  min-height: 100vh;
  background: #f9fafb;
  padding: 24px;
}

.page-header {
  max-width: 1200px;
  margin: 0 auto 32px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.header-content h1 {
  margin: 0 0 8px;
  font-size: 28px;
  font-weight: 700;
  color: #111827;
}

.header-content p {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #3b82f6;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-primary:hover {
  background: #2563eb;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2);
}

.page-content {
  max-width: 1200px;
  margin: 0 auto;
}

.notification {
  position: fixed;
  top: 24px;
  right: 24px;
  background: #10b981;
  color: white;
  padding: 16px 20px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 12px;
  z-index: 9999;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.notification.success {
  background: #10b981;
}

.notification.error {
  background: #ef4444;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  font-size: 18px;
  cursor: pointer;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: background 0.2s;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
