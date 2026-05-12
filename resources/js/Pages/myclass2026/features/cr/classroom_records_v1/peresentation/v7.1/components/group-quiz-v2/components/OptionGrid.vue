<script setup>
import OptionCard from './OptionCard.vue';

const props = defineProps({
  options: { type: Array, required: true },
  groupAnswers: { type: Object, default: () => ({}) },
  correctId: { type: String, default: '' },
  isGraded: { type: Boolean, default: false },
  isEditMode: { type: Boolean, default: false },
  isInteractive: { type: Boolean, default: false },
  hasActiveGroup: { type: Boolean, default: false },
  groups: { type: Array, default: () => [] }
});

const emit = defineEmits(['optionClick', 'optionHover', 'setCorrect', 'updateOption', 'select']);

function getGroupsOnOption(optId) {
  const entries = Object.entries(props.groupAnswers);
  return entries
    .filter(([, selectedOptId]) => selectedOptId === optId)
    .map(([groupId]) => props.groups.find((g) => g.id === groupId))
    .filter(Boolean);
}

function getOptionLetter(index) {
  return String.fromCharCode(65 + index);
}
</script>

<template>
  <div class="option-grid row q-col-gutter-sm">
    <div
      v-for="(opt, index) in options"
      :key="opt.id"
      class="col-12 col-sm-6"
    >
      <OptionCard
        :option="opt"
        :index="index"
        :groupsOnOption="getGroupsOnOption(opt.id)"
        :isCorrect="opt.id === correctId"
        :isGraded="isGraded"
        :isEditMode="isEditMode"
        :isInteractive="isInteractive"
        :hasActiveGroup="hasActiveGroup"
        :optionLetter="getOptionLetter(index)"
        @click="$emit('optionClick', opt.id)"
        @hover="$emit('optionHover')"
        @setCorrect="$emit('setCorrect', $event)"
        @updateOption="$emit('updateOption', { index, text: $event })"
        @select="$emit('select')"
      />
    </div>
  </div>
</template>

<style scoped>
.option-grid {
  width: 100%;
}
</style>
