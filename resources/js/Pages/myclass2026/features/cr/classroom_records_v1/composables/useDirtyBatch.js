/**
 * useDirtyBatch - Composable for managing dirty state and debounced batch saves
 * 
 * Features:
 * - Tracks dirty (modified) student periods
 * - Debounces API calls (default 1.5s)
 * - Handles save status (idle, saving, success, error)
 * - Page unload protection for unsaved changes
 */

import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

export function useDirtyBatch(options = {}) {
  const {
    debounceDelay = 500, // 0.5 seconds for snappier saves
    autoSave = true,
    enableUnloadProtection = true,
  } = options;

  // State
  const dirtyItems = ref(new Map()); // Map<student_period_id, updateData>
  const saveStatus = ref('idle'); // 'idle' | 'saving' | 'success' | 'error'
  const lastError = ref(null);
  const lastSavedAt = ref(null);
  const saveCount = ref(0);

  // Computed
  const hasUnsavedChanges = computed(() => dirtyItems.value.size > 0);
  const isSaving = computed(() => saveStatus.value === 'saving');
  const isSuccess = computed(() => saveStatus.value === 'success');
  const isError = computed(() => saveStatus.value === 'error');

  // Debounce timer
  let saveTimer = null;

  /**
   * Mark a student period as dirty (modified)
   */
  const markDirty = (studentPeriodId, updateData) => {
    const existing = dirtyItems.value.get(studentPeriodId) || {};
    dirtyItems.value.set(studentPeriodId, {
      ...existing,
      ...updateData,
      // If both have scores arrays, merge them by mapping_id so multiple
      // category edits before save are preserved.
      ...(existing.scores || updateData.scores
        ? {
            scores: Object.values(
              [
                ...(existing.scores || []),
                ...(updateData.scores || []),
              ].reduce((acc, score) => {
                if (!score || typeof score.mapping_id === 'undefined') return acc;
                acc[score.mapping_id] = score;
                return acc;
              }, {})
            ),
          }
        : {}),
      timestamp: Date.now(),
    });

    // Trigger auto-save if enabled
    if (autoSave) {
      triggerAutoSave();
    }
  };

  /**
   * Mark multiple items as dirty at once
   */
  const markMultipleDirty = (items) => {
    items.forEach(item => {
      const existing = dirtyItems.value.get(item.student_period_id) || {};
      dirtyItems.value.set(item.student_period_id, {
        ...existing,
        ...item,
        ...(existing.scores || item.scores
          ? {
              scores: Object.values(
                [
                  ...(existing.scores || []),
                  ...(item.scores || []),
                ].reduce((acc, score) => {
                  if (!score || typeof score.mapping_id === 'undefined') return acc;
                  acc[score.mapping_id] = score;
                  return acc;
                }, {})
              ),
            }
          : {}),
        timestamp: Date.now(),
      });
    });

    if (autoSave) {
      triggerAutoSave();
    }
  };

  /**
   * Remove item from dirty list after successful save
   */
  const markClean = (studentPeriodId) => {
    dirtyItems.value.delete(studentPeriodId);
  };

  /**
   * Clear all dirty items
   */
  const clearDirty = () => {
    dirtyItems.value.clear();
  };

  /**
   * Trigger auto-save with debounce
   */
  const triggerAutoSave = () => {
    // Clear existing timer
    if (saveTimer) {
      clearTimeout(saveTimer);
    }

    // Set new timer
    saveTimer = setTimeout(() => {
      saveBatch();
    }, debounceDelay);
  };

  /**
   * Save all dirty items to backend
   */
  const saveBatch = async () => {
    if (dirtyItems.value.size === 0 || isSaving.value) {
      return;
    }

    saveStatus.value = 'saving';
    lastError.value = null;

    try {
      // Convert Map to array of updates
      const updates = Array.from(dirtyItems.value.values());

      // Make API call with correct method (PATCH)
      const response = await axios.patch('/api/cr/batch', {
        updates: updates,
      });

      saveStatus.value = 'success';
      lastSavedAt.value = new Date();
      saveCount.value++;

      // Clear dirty items that were successfully saved
      const updatedIds = response.data.updated || [];
      updatedIds.forEach(id => markClean(id));

      // Reset status after delay
      setTimeout(() => {
        if (saveStatus.value === 'success') {
          saveStatus.value = 'idle';
        }
      }, 2000);
    } catch (error) {
      saveStatus.value = 'error';
      lastError.value = error.response?.data || error.message;
      console.error('Batch save failed:', error);
    }
  };

  /**
   * Force immediate save (bypass debounce)
   */
  const forceSave = async () => {
    if (saveTimer) {
      clearTimeout(saveTimer);
      saveTimer = null;
    }
    await saveBatch();
  };

  /**
   * Cancel pending auto-save
   */
  const cancelPendingSave = () => {
    if (saveTimer) {
      clearTimeout(saveTimer);
      saveTimer = null;
    }
  };

  /**
   * Handle page unload warning
   */
  const handleBeforeUnload = (event) => {
    if (hasUnsavedChanges.value && !isSaving.value) {
      event.preventDefault();
      event.returnValue = '';
      return '';
    }
  };

  // Setup unload protection
  onMounted(() => {
    if (enableUnloadProtection) {
      window.addEventListener('beforeunload', handleBeforeUnload);
    }
  });

  onUnmounted(() => {
    if (enableUnloadProtection) {
      window.removeEventListener('beforeunload', handleBeforeUnload);
    }
    if (saveTimer) {
      clearTimeout(saveTimer);
    }
  });

  return {
    // State
    dirtyItems,
    saveStatus,
    lastError,
    lastSavedAt,
    saveCount,

    // Computed
    hasUnsavedChanges,
    isSaving,
    isSuccess,
    isError,

    // Methods
    markDirty,
    markMultipleDirty,
    markClean,
    clearDirty,
    saveBatch,
    forceSave,
    cancelPendingSave,
  };
}
