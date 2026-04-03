import { computed } from 'vue';

/**
 * Normalizes raw timing slots by computing startMin/endMin from HH:MM strings.
 */
export function normalizeSlots(slots) {
  return (slots || []).map(slot => {
    const [sh = 0, sm = 0] = (slot.start || '00:00').split(':').map(Number);
    const [eh = 0, em = 0] = (slot.end || '00:00').split(':').map(Number);
    return {
      ...slot,
      startMin: sh * 60 + sm,
      endMin: eh * 60 + em
    };
  });
}

/**
 * Creates a computed ref that resolves the active time slots based on
 * the timing config hierarchy: stage+day override > stage default > global default > fallback.
 *
 * @param {import('vue').Ref} timingsConfig  - { default: [...], overrides: { prim: { default, days: { d1: [...] } } } }
 * @param {import('vue').Ref} selectedStage  - 'prim' | 'middle' | 'sec'
 * @param {import('vue').Ref} selectedDay    - 'd1' .. 'd6'
 * @param {Array} fallbackSlots              - static fallback from schedule_timing.json
 * @returns {{ resolvedTimeSlots: import('vue').ComputedRef, customTimingDays: import('vue').ComputedRef }}
 */
export function useTimingResolver(timingsConfig, selectedStage, selectedDay, fallbackSlots = []) {
  const resolvedTimeSlots = computed(() => {
    const config = timingsConfig.value;
    const stageOverride = config?.overrides?.[selectedStage.value];

    // 1. Stage + day specific override
    if (stageOverride?.days?.[selectedDay.value]) {
      return normalizeSlots(stageOverride.days[selectedDay.value]);
    }

    // 2. Stage default override
    if (stageOverride?.default) {
      return normalizeSlots(stageOverride.default);
    }

    // 3. Global default
    if (config?.default?.length) {
      return normalizeSlots(config.default);
    }

    // 4. Static fallback
    return normalizeSlots(fallbackSlots);
  });

  const customTimingDays = computed(() => {
    const stageOverride = timingsConfig.value?.overrides?.[selectedStage.value]?.days || {};
    return Object.entries(stageOverride)
      .filter(([, value]) => Array.isArray(value) && value.length > 0)
      .map(([dayId]) => dayId);
  });

  return { resolvedTimeSlots, customTimingDays };
}
