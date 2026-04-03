<template>
  <div class="menu-timing">
    <h3 class="section-title">Timing Configuration</h3>
    <p class="section-desc">Edit period start/end times per stage and day.</p>

    <!-- Stage / Day Selector -->
    <div class="selector-row">
      <div class="selector-group">
        <label class="selector-label">Stage</label>
        <div class="selector-btns">
          <button
            v-for="s in stages"
            :key="s.id"
            class="sel-btn"
            :class="{ active: editStage === s.id }"
            @click="editStage = s.id"
          >{{ s.label }}</button>
        </div>
      </div>

      <div class="selector-group">
        <label class="selector-label">Day</label>
        <div class="selector-btns">
          <button
            class="sel-btn"
            :class="{ active: editDay === '' }"
            @click="editDay = ''"
          >All</button>
          <button
            v-for="d in days"
            :key="d.id"
            class="sel-btn"
            :class="{ active: editDay === d.id, 'has-custom': customDays.includes(d.id) }"
            @click="editDay = d.id"
          >{{ d.short }}</button>
        </div>
      </div>
    </div>

    <!-- Active Slots Editor -->
    <div class="slots-editor">
      <div class="slots-header">
        <span class="slots-title">{{ slotsTitle }}</span>
        <button class="action-sm" @click="addSlot">+ Add</button>
      </div>

      <div v-if="editableSlots.length === 0" class="empty-state">
        <p v-if="editDay">No custom timing for this day. Using stage default.</p>
        <p v-else>No stage default timing set. Click "Copy from default" to load the default schedule.</p>
        <button class="action-sm" @click="copyFromDefault">Copy from default</button>
      </div>

      <TransitionGroup name="slot-list" tag="div" class="slots-list">
        <div
          v-for="(slot, index) in editableSlots"
          :key="slot._key || index"
          class="slot-row"
        >
          <input
            v-model="slot.title"
            class="slot-input title"
            placeholder="Title"
            @change="onSlotChange"
          />
          <select v-model="slot.type" class="slot-input type" @change="onSlotChange">
            <option value="lesson">Lesson</option>
            <option value="break">Break</option>
            <option value="activity">Activity</option>
          </select>
          <input
            v-model="slot.start"
            type="time"
            class="slot-input time"
            @change="onSlotChange"
          />
          <input
            v-model="slot.end"
            type="time"
            class="slot-input time"
            @change="onSlotChange"
          />
          <button class="slot-remove" @click="removeSlot(index)">✕</button>
        </div>
      </TransitionGroup>
    </div>

    <!-- Actions -->
    <div class="timing-actions">
      <button class="btn-primary" @click="applyTimings" :disabled="editableSlots.length === 0">
        ✅ Apply
      </button>
      <button class="btn-secondary" @click="resetToDefault">
        ↩ Reset
      </button>
    </div>

    <div v-if="statusMessage" class="status-msg" :class="statusType">
      {{ statusMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useAppStore } from '../../composables/useAppStore';

const store = useAppStore();

const stages = [
  { id: 'prim', label: 'Primary' },
  { id: 'middle', label: 'Middle' },
  { id: 'sec', label: 'Secondary' }
];

const days = [
  { id: 'd1', short: 'D1' },
  { id: 'd2', short: 'D2' },
  { id: 'd3', short: 'D3' },
  { id: 'd4', short: 'D4' },
  { id: 'd5', short: 'D5' },
  { id: 'd6', short: 'D6' }
];

const editStage = ref(store.selectedStage.value || 'prim');
const editDay = ref('');
const editableSlots = ref([]);
const statusMessage = ref('');
const statusType = ref('success');
let keyCounter = 0;

const clone = (v) => JSON.parse(JSON.stringify(v));

const customDays = computed(() => {
  const stageOverride = store.timingsConfig.value?.overrides?.[editStage.value]?.days || {};
  return Object.entries(stageOverride)
    .filter(([, v]) => Array.isArray(v) && v.length > 0)
    .map(([k]) => k);
});

const slotsTitle = computed(() => {
  const stageLabel = stages.find(s => s.id === editStage.value)?.label || editStage.value;
  if (editDay.value) {
    const dayLabel = days.find(d => d.id === editDay.value)?.short || editDay.value;
    return `${stageLabel} — ${dayLabel}`;
  }
  return `${stageLabel} — Default`;
});

const resolveCurrentSlots = () => {
  const config = store.timingsConfig.value;
  const stageOverride = config?.overrides?.[editStage.value];

  if (editDay.value && stageOverride?.days?.[editDay.value]) {
    return clone(stageOverride.days[editDay.value]);
  }

  if (!editDay.value && stageOverride?.default) {
    return clone(stageOverride.default);
  }

  // No default available - return empty
  return [];
};

const loadSlots = () => {
  const slots = resolveCurrentSlots();
  editableSlots.value = slots.map(s => ({ ...s, _key: ++keyCounter }));
};

watch([editStage, editDay], loadSlots, { immediate: true });

const addSlot = () => {
  const last = editableSlots.value[editableSlots.value.length - 1];
  editableSlots.value.push({
    _key: ++keyCounter,
    id: editableSlots.value.length + 1,
    title: `Period ${editableSlots.value.length + 1}`,
    type: 'lesson',
    start: last?.end || '09:00',
    end: last ? addMinutes(last.end, 30) : '09:30'
  });
};

const addMinutes = (timeStr, mins) => {
  const [h, m] = timeStr.split(':').map(Number);
  const total = h * 60 + m + mins;
  const nh = Math.floor(total / 60) % 24;
  const nm = total % 60;
  return `${String(nh).padStart(2, '0')}:${String(nm).padStart(2, '0')}`;
};

const removeSlot = (index) => {
  editableSlots.value.splice(index, 1);
};

const onSlotChange = () => {
  // Reactive — changes tracked automatically
};

const copyFromDefault = async () => {
  let source = [];
  
  // Try to get stage default from config first
  const config = store.timingsConfig.value;
  if (config?.overrides?.[editStage.value]?.default) {
    source = config.overrides[editStage.value].default;
  } else {
    // Load from stage file if no default is set
    try {
      const stageTimings = await import(`../../data/timings/${editStage.value}.json`);
      source = stageTimings.default;
    } catch (e) {
      console.warn('Failed to load stage defaults:', e);
    }
  }
  
  editableSlots.value = clone(source).map(s => ({ ...s, _key: ++keyCounter }));
};

const applyTimings = async () => {
  const config = clone(store.timingsConfig.value);
  const slots = editableSlots.value.map(({ _key, ...rest }) => rest);

  if (!config.overrides) config.overrides = {};
  if (!config.overrides[editStage.value]) {
    config.overrides[editStage.value] = { default: null, days: { d1: null, d2: null, d3: null, d4: null, d5: null, d6: null } };
  }

  if (editDay.value) {
    config.overrides[editStage.value].days[editDay.value] = slots;
  } else {
    config.overrides[editStage.value].default = slots;
  }

  await store.setTimingsConfig(config);
  statusMessage.value = 'Timing applied successfully!';
  statusType.value = 'success';
  setTimeout(() => { statusMessage.value = ''; }, 3000);
};

const resetToDefault = async () => {
  const config = clone(store.timingsConfig.value);

  if (!config.overrides?.[editStage.value]) return;

  if (editDay.value) {
    // Reset specific day to inherit stage default
    config.overrides[editStage.value].days[editDay.value] = null;
  } else {
    // Reset stage default - load from the stage file
    try {
      const stageTimings = await import(`../../data/timings/${editStage.value}.json`);
      config.overrides[editStage.value].default = stageTimings.default;
    } catch (e) {
      console.warn('Failed to load stage defaults:', e);
      config.overrides[editStage.value].default = null;
    }
  }

  await store.setTimingsConfig(config);
  loadSlots();
  statusMessage.value = 'Reset to default.';
  statusType.value = 'info';
  setTimeout(() => { statusMessage.value = ''; }, 3000);
};
</script>

<style scoped>
.section-title {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #64748b;
  margin: 0 0 0.25rem 0;
}

.section-desc {
  color: #475569;
  font-size: 0.8rem;
  margin: 0 0 1rem 0;
}

.selector-row {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.selector-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.25rem;
  display: block;
}

.selector-btns {
  display: flex;
  gap: 0.25rem;
  flex-wrap: wrap;
}

.sel-btn {
  padding: 0.35rem 0.6rem;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: transparent;
  color: #94a3b8;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
  min-height: 32px;
  min-width: 32px;
}

.sel-btn:hover { background: rgba(255, 255, 255, 0.05); }
.sel-btn.active { background: rgba(59, 130, 246, 0.25); color: #60a5fa; border-color: rgba(59, 130, 246, 0.4); }
.sel-btn.has-custom { border-color: rgba(16, 185, 129, 0.4); }

.slots-editor {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 12px;
  padding: 0.75rem;
  margin-bottom: 1rem;
}

.slots-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.slots-title {
  font-weight: 600;
  font-size: 0.85rem;
}

.action-sm {
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  border: 1px solid rgba(59, 130, 246, 0.3);
  background: rgba(59, 130, 246, 0.1);
  color: #60a5fa;
  font-size: 0.75rem;
  cursor: pointer;
  min-height: 28px;
}

.empty-state {
  text-align: center;
  padding: 1rem;
  color: #64748b;
  font-size: 0.8rem;
}

.empty-state .action-sm { margin-top: 0.5rem; }

.slots-list {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.slot-row {
  display: grid;
  grid-template-columns: 1fr auto auto auto auto;
  gap: 0.25rem;
  align-items: center;
}

.slot-input {
  padding: 0.35rem 0.4rem;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: #e2e8f0;
  font-size: 0.75rem;
  min-height: 32px;
}

.slot-input.title { min-width: 80px; }
.slot-input.type { min-width: 70px; }
.slot-input.time { min-width: 70px; }

.slot-remove {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  border: none;
  background: rgba(239, 68, 68, 0.2);
  color: #f87171;
  cursor: pointer;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.timing-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-primary {
  flex: 1;
  padding: 0.6rem;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  min-height: 44px;
}

.btn-primary:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.btn-secondary {
  padding: 0.6rem 1rem;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: transparent;
  color: #94a3b8;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  min-height: 44px;
}

.status-msg {
  margin-top: 0.75rem;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  font-size: 0.8rem;
  text-align: center;
}

.status-msg.success { background: rgba(16, 185, 129, 0.2); color: #34d399; }
.status-msg.info { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }

.slot-list-enter-active,
.slot-list-leave-active {
  transition: all 0.2s ease;
}
.slot-list-enter-from,
.slot-list-leave-to {
  opacity: 0;
  transform: translateX(-10px);
}

@media (max-width: 400px) {
  .slot-row {
    grid-template-columns: 1fr 1fr;
    gap: 0.2rem;
  }
  .slot-remove { grid-column: span 2; justify-self: end; }
}
</style>
