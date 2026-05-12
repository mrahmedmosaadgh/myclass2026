<script setup>
import EditableMath from '../../EditableMath.vue';

const props = defineProps({
  option: { type: Object, required: true },
  index: { type: Number, default: 0 },
  groupsOnOption: { type: Array, default: () => [] },
  isCorrect: { type: Boolean, default: false },
  isGraded: { type: Boolean, default: false },
  isEditMode: { type: Boolean, default: false },
  isInteractive: { type: Boolean, default: false },
  hasActiveGroup: { type: Boolean, default: false },
  isCorrectlyAnswered: { type: Boolean, default: false }, // group picked correct
  isWronglyAnswered: { type: Boolean, default: false },    // group picked wrong
  optionLetter: { type: String, default: '' }
});

const emit = defineEmits(['click', 'hover', 'setCorrect', 'updateOption', 'select']);

function getCardClass() {
  if (props.isEditMode) return 'edit-mode';
  if (props.isGraded) {
    if (props.isCorrect) return 'correct-answer';
    if (props.groupsOnOption.length > 0) return 'wrong-answer';
    return 'graded-neutral';
  }
  if (!props.isInteractive) return 'locked';
  if (props.hasActiveGroup) return 'assignable';
  return 'interactive';
}

function handleClick() {
  if (props.isEditMode) return;
  emit('click');
}

function handleHover() {
  if (props.isEditMode || !props.isInteractive || props.isGraded) return;
  emit('hover');
}
</script>

<template>
  <q-card
    flat
    :class="['option-card', getCardClass()]"
    @click.stop="handleClick"
    @mouseenter="handleHover"
  >
    <q-card-section horizontal class="q-pa-sm items-center">
      <!-- Edit mode: radio to set correct -->
      <q-radio
        v-if="isEditMode"
        :model-value="isCorrect"
        :val="true"
        @update:model-value="$emit('setCorrect', option.id)"
        dense
        class="q-mr-sm"
      />

      <!-- Option letter badge -->
      <q-avatar
        size="32px"
        :color="isGraded && isCorrect ? 'positive' : (isGraded && groupsOnOption.length > 0 ? 'negative' : 'primary')"
        text-color="white"
        class="q-mr-sm text-weight-bold"
      >
        {{ optionLetter || String(option.id).charAt(0).toUpperCase() }}
      </q-avatar>

      <!-- Option text -->
      <div class="option-text col">
        <EditableMath
          :content="option.text"
          :isEditMode="isEditMode"
          @update="$emit('updateOption', $event)"
          @select="$emit('select')"
        />
      </div>

      <!-- Group badges -->
      <div v-if="!isEditMode && groupsOnOption.length > 0" class="group-badges row q-gutter-xs">
        <q-chip
          v-for="g in groupsOnOption"
          :key="g.id"
          dense
          size="sm"
          :style="{ backgroundColor: g.color, color: '#fff' }"
          class="text-weight-medium"
        >
          {{ g.name }}
        </q-chip>
      </div>

      <!-- Grading feedback icon -->
      <q-icon
        v-if="isGraded && isCorrect"
        name="check_circle"
        color="positive"
        size="24px"
        class="q-ml-sm"
      />
      <q-icon
        v-else-if="isGraded && groupsOnOption.length > 0"
        name="cancel"
        color="negative"
        size="24px"
        class="q-ml-sm"
      />
    </q-card-section>
  </q-card>
</template>

<style scoped>
.option-card {
  transition: all 0.2s ease;
  cursor: default;
  border: 2px solid transparent;
  min-height: 56px;
}

.option-card.edit-mode {
  cursor: default;
  border-color: #e2e8f0;
}

.option-card.locked {
  opacity: 0.7;
  background: #f8fafc;
  border-color: #e2e8f0;
}

.option-card.interactive {
  cursor: pointer;
  background: #fff;
  border-color: #cbd5e1;
}
.option-card.interactive:hover {
  border-color: #3b82f6;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
  transform: translateY(-2px);
}

.option-card.assignable {
  cursor: pointer;
  background: #fff;
  border-color: #f59e0b;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
}
.option-card.assignable:hover {
  border-color: #f59e0b;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
  transform: translateY(-2px);
}

.option-card.correct-answer {
  background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
  border-color: #22c55e;
}

.option-card.wrong-answer {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  border-color: #ef4444;
}

.option-card.graded-neutral {
  opacity: 0.5;
  background: #f1f5f9;
  border-color: #e2e8f0;
}

.option-text {
  min-width: 0;
  overflow: hidden;
}
.group-badges {
  flex-wrap: wrap;
  justify-content: flex-end;
  max-width: 200px;
}
</style>
