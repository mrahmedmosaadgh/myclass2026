import { ref } from 'vue'

/**
 * createSelectionManager
 * Factory to manage selected students and bulk behavior actions.
 * Accepts small set of callbacks/refs so it stays decoupled from the component.
 *
 * Options:
 * - settingsRef: ref to settings object (for attendance updates)
 * - selDateRef: ref to selected date
 * - periodCodeRef: ref to period_code
 * - loadStudentSummary: function(studentId) -> Promise (optional)
 * - notify: function({message, color, position, timeout}) (optional)
 * - applyBehaviorApi: function(studentIds, behaviorId, options) -> Promise
 */
export function createSelectionManager({
  settingsRef,
  selDateRef,
  periodCodeRef,
  loadStudentSummary = null,
  notify = null,
  applyBehaviorApi = null,
  selectedIdsRef = null,
  applyingBehaviorRef = null,
} = {}) {
  // allow external refs to be supplied so component can keep references stable
  const selectedIds = selectedIdsRef || ref([])
  const applyingBehavior = applyingBehaviorRef || ref(false)

  function toggleSelected(id) {
    if (id == null) return
    const idx = selectedIds.value.findIndex(sid => sid === id)
    if (idx === -1) selectedIds.value.push(id)
    else selectedIds.value.splice(idx, 1)
  }

  function clearSelection() {
    selectedIds.value = []
  }

  function markSelectedPresent() {
    if (!selectedIds.value.length) return
    try {
      selectedIds.value.forEach(id => {
        if (settingsRef && settingsRef.value && typeof settingsRef.value.attendance === 'object') {
          settingsRef.value.attendance[id] = true
        }
      })
      if (typeof settingsRef?.value?.save === 'function') {
        settingsRef.value.save()
      }
      if (notify) notify({ message: `Marked ${selectedIds.value.length} students present`, color: 'positive', position: 'top' })
    } catch (e) {
      if (notify) notify({ message: 'Failed to mark present', color: 'negative', position: 'top' })
    }
  }

  function markSelectedAbsent() {
    if (!selectedIds.value.length) return
    try {
      selectedIds.value.forEach(id => {
        if (settingsRef && settingsRef.value && typeof settingsRef.value.attendance === 'object') {
          settingsRef.value.attendance[id] = false
        }
      })
      if (typeof settingsRef?.value?.save === 'function') {
        settingsRef.value.save()
      }
      if (notify) notify({ message: `Marked ${selectedIds.value.length} students absent`, color: 'warning', position: 'top' })
    } catch (e) {
      if (notify) notify({ message: 'Failed to mark absent', color: 'negative', position: 'top' })
    }
  }

  async function applyBehaviorToSelected(behaviorId, options = {}) {
    if (!selectedIds.value.length || !behaviorId) return { success: false, message: 'Nothing to apply' }
    if (!applyBehaviorApi) {
      if (notify) notify({ message: 'Apply API not configured', color: 'negative', position: 'top' })
      return { success: false, message: 'API not configured' }
    }

    applyingBehavior.value = true

    const payloadOptions = {
      date: selDateRef?.value || options.date || new Date().toISOString().split('T')[0],
      periodCode: periodCodeRef?.value || options.periodCode || null,
      notes: options.notes || null,
    }

    try {
      const res = await applyBehaviorApi(selectedIds.value, behaviorId, payloadOptions)

      // refresh summaries if loader provided
      if (loadStudentSummary) {
        const updates = selectedIds.value.map(sid => loadStudentSummary(sid))
        await Promise.all(updates)
      }

      if (notify) notify({ message: res?.message || 'Behavior applied', color: res?.success ? 'positive' : 'warning', position: 'top' })

      return res
    } catch (e) {
      if (notify) notify({ message: 'Failed to apply behavior', color: 'negative', position: 'top' })
      return { success: false, error: e }
    } finally {
      applyingBehavior.value = false
    }
  }

  return {
    selectedIds,
    applyingBehavior,
    toggleSelected,
    clearSelection,
    markSelectedPresent,
    markSelectedAbsent,
    applyBehaviorToSelected,
  }
}
