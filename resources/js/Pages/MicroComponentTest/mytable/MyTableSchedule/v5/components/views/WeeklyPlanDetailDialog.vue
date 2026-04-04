<template>
  <Teleport to="body">
    <Transition name="dialog">
      <div v-if="open" class="dialog-overlay" @click.self="$emit('close')">
        <div class="dialog-content">
          <div class="dialog-header">
            <div class="header-info">
              <h3 class="dialog-title">{{ headerTitle }}</h3>
              <div v-if="weekTitleDisplay" class="week-title">{{ weekTitleDisplay }}</div>
            </div>
            <button @click="$emit('close')" class="close-btn">✕</button>
          </div>

          <div class="dialog-body">
            <div v-if="!planData" class="empty-state">
              <p>No weekly plan data saved for this slot.</p>
              <button @click="openWeeklyMenu" class="edit-btn">Edit in Weekly Menu</button>
            </div>

            <div v-else class="plan-details">
              <div class="detail-section">
                <h4>Classwork</h4>
                <div class="detail-item">
                  <label>Topic:</label>
                  <span>{{ planData.cw || 'Not set' }}</span>
                </div>
                <div class="detail-item">
                  <label>Pages:</label>
                  <span>{{ planData.cwPages || 'Not set' }}</span>
                </div>
              </div>

              <div class="detail-section">
                <h4>Homework</h4>
                <div class="detail-item">
                  <label>Assignment:</label>
                  <span>{{ planData.hw || 'Not set' }}</span>
                </div>
                <div class="detail-item">
                  <label>Pages:</label>
                  <span>{{ planData.hwPages || 'Not set' }}</span>
                </div>
              </div>

              <div class="detail-section">
                <h4>Resources</h4>
                <div class="detail-item">
                  <label>Presentation:</label>
                  <a 
                    v-if="planData.presentationLink" 
                    :href="planData.presentationLink" 
                    @click.prevent="openPresentation"
                    class="resource-link"
                    target="_blank"
                  >
                    Open Presentation →
                  </a>
                  <span v-else>Not set</span>
                </div>
                <div class="detail-item">
                  <label>Materials:</label>
                  <a 
                    v-if="planData.materialLink" 
                    :href="planData.materialLink"
                    class="resource-link"
                    target="_blank"
                  >
                    Open Materials →
                  </a>
                  <span v-else>Not set</span>
                </div>
              </div>

              <div v-if="planData.notesHtml" class="detail-section">
                <h4>Notes</h4>
                <div class="notes-content" v-html="sanitizedNotes"></div>
              </div>

              <div class="detail-section">
                <h4>Status & Rating</h4>
                <div class="detail-item">
                  <label>Status:</label>
                  <span :class="{ 'status-done': planData.done, 'status-pending': !planData.done }">
                    {{ planData.done ? '✅ Done' : '⏳ Pending' }}
                  </span>
                </div>
                <div class="detail-item">
                  <label>Rating:</label>
                  <div class="rating-display">
                    <span v-for="star in 5" :key="star" class="star">
                      {{ star <= (planData.rating || 0) ? '⭐' : '☆' }}
                    </span>
                    <span class="rating-text">({{ planData.rating || 0 }}/5)</span>
                  </div>
                </div>
              </div>

              <div class="dialog-actions">
                <button @click="openWeeklyMenu" class="edit-btn">Edit in Weekly Menu</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Presentation Viewer Dialog -->
    <PresentationViewerDialog 
      v-if="showPresentationViewer"
      :url="presentationUrl"
      @close="showPresentationViewer = false"
    />
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAppStore } from '../../composables/useAppStore.js';
import DOMPurify from 'dompurify';
import PresentationViewerDialog from './PresentationViewerDialog.vue';

const props = defineProps({
  open: Boolean,
  weekKey: String,
  className: String,
  dayId: String,
  periodId: String,
  dayName: String,
  periodTitle: String
});

const emit = defineEmits(['close']);

const store = useAppStore();
const showPresentationViewer = ref(false);

const planData = computed(() => {
  if (!props.weekKey || !props.className || !props.dayId || !props.periodId) {
    return null;
  }
  return store.getWeeklyPlanEntry(props.weekKey, props.className, props.dayId, props.periodId);
});

const headerTitle = computed(() => {
  if (!props.className || !props.periodTitle || !props.dayName) {
    return 'Weekly Plan Details';
  }
  return `${props.className} · ${props.periodTitle} · ${props.dayName}`;
});

const weekTitleDisplay = computed(() => {
  if (!props.weekKey) return null;
  const title = store.getWeekTitle(props.weekKey);
  return title !== props.weekKey ? title : null;
});

const sanitizedNotes = computed(() => {
  if (!planData.value?.notesHtml) return '';
  return DOMPurify.sanitize(planData.value.notesHtml);
});

const presentationUrl = computed(() => {
  return planData.value?.presentationLink || '';
});

const openPresentation = () => {
  if (presentationUrl.value) {
    showPresentationViewer.value = true;
  }
};

const openWeeklyMenu = () => {
  // Close this dialog and open weekly menu
  // This would need to be handled by the parent component
  // For now, just close this dialog
  emit('close');
  
  // TODO: Emit event to parent to switch to weekly menu
  // or use a global state/navigation system
};
</script>

<style scoped>
.dialog-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.dialog-content {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.5rem;
  border-bottom: 1px solid #eee;
}

.header-info {
  flex: 1;
}

.dialog-title {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #333;
}

.week-title {
  font-size: 0.875rem;
  color: #666;
  font-weight: 500;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.25rem;
  color: #666;
  line-height: 1;
}

.close-btn:hover {
  color: #000;
}

.dialog-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.empty-state p {
  margin: 0 0 1rem 0;
  font-size: 1rem;
}

.plan-details {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.detail-section {
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 1rem;
}

.detail-section h4 {
  margin: 0 0 0.75rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #333;
}

.detail-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.detail-item:last-child {
  margin-bottom: 0;
}

.detail-item label {
  font-weight: 500;
  color: #666;
  min-width: 80px;
  font-size: 0.875rem;
}

.detail-item span {
  color: #333;
  font-size: 0.875rem;
  flex: 1;
}

.resource-link {
  color: #007bff;
  text-decoration: none;
  font-size: 0.875rem;
  cursor: pointer;
}

.resource-link:hover {
  text-decoration: underline;
}

.notes-content {
  font-size: 0.875rem;
  line-height: 1.5;
  color: #333;
}

.notes-content :deep(p) {
  margin: 0 0 0.5rem 0;
}

.notes-content :deep(ul), .notes-content :deep(ol) {
  margin: 0.5rem 0;
  padding-left: 1.5rem;
}

.notes-content :deep(li) {
  margin-bottom: 0.25rem;
}

.notes-content :deep(strong) {
  font-weight: 600;
}

.notes-content :deep(em) {
  font-style: italic;
}

.dialog-actions {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #eee;
}

.edit-btn {
  background: #007bff;
  color: white;
  border: none;
  padding: 0.625rem 1.25rem;
  border-radius: 6px;
  font-size: 0.875rem;
  cursor: pointer;
  font-weight: 500;
}

.edit-btn:hover {
  background: #0056b3;
}

/* Transitions */
.dialog-enter-active,
.dialog-leave-active {
  transition: opacity 0.2s ease;
}

.dialog-enter-from,
.dialog-leave-to {
  opacity: 0;
}

/* Responsive */
@media (max-width: 640px) {
  .dialog-content {
    max-height: 95vh;
  }
  
  .dialog-header,
  .dialog-body {
    padding: 1rem;
  }
  
  .detail-item {
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .detail-item label {
    min-width: auto;
  }
}

/* Status and Rating Styles */
.status-done {
  color: #28a745;
  font-weight: 600;
}

.status-pending {
  color: #6c757d;
}

.rating-display {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.star {
  font-size: 1rem;
}

.rating-text {
  font-size: 0.875rem;
  color: #666;
}
</style>
