<script setup>
const props = defineProps({
  groups: { type: Array, required: true },
  activeGroupId: { type: String, default: null },
  groupAnswers: { type: Object, default: () => ({}) },
  isGraded: { type: Boolean, default: false },
  isInteractive: { type: Boolean, default: false },
  correctId: { type: String, default: '' }
});

const emit = defineEmits(['selectGroup', 'clearGroupAnswer', 'printQr', 'scanQr']);

function isAnswered(groupId) {
  return !!props.groupAnswers[groupId];
}

function getGroupScoreText(groupId) {
  if (!props.isGraded) return '';
  const ans = props.groupAnswers[groupId];
  if (!ans) return '';
  const isCorrect = ans === props.correctId;
  return isCorrect ? '+pts' : '-pts';
}

function getGroupResultIcon(groupId) {
  if (!props.isGraded) return null;
  const ans = props.groupAnswers[groupId];
  if (!ans) return null;
  return ans === props.correctId ? 'check_circle' : 'cancel';
}

function getGroupResultColor(groupId) {
  if (!props.isGraded) return '';
  const ans = props.groupAnswers[groupId];
  if (!ans) return '';
  return ans === props.correctId ? 'positive' : 'negative';
}
</script>

<template>
  <div class="group-selector">
    <div class="row items-center justify-between q-mb-sm">
      <div class="text-subtitle2 text-weight-bold text-grey-8">
        <q-icon name="people" size="18px" class="q-mr-xs" />
        Select Group
      </div>
      <div class="row q-gutter-xs">
        <q-btn
          flat
          round
          dense
          size="sm"
          icon="qr_code"
          color="grey-7"
          @click="$emit('printQr')"
        >
          <q-tooltip>Print QR codes</q-tooltip>
        </q-btn>
        <q-btn
          flat
          round
          dense
          size="sm"
          icon="qr_code_scanner"
          color="grey-7"
          @click="$emit('scanQr')"
        >
          <q-tooltip>Scan QR</q-tooltip>
        </q-btn>
      </div>
    </div>

    <div class="row q-gutter-sm">
      <q-chip
        v-for="g in groups"
        :key="g.id"
        clickable
        :removable="isAnswered(g.id) && !isGraded"
        :color="activeGroupId === g.id ? 'amber' : (isAnswered(g.id) ? 'grey-3' : 'white')"
        :text-color="activeGroupId === g.id ? 'white' : (isAnswered(g.id) ? 'grey-7' : 'grey-8')"
        :style="activeGroupId === g.id ? { backgroundColor: g.color + '!important' } : {}"
        @click="$emit('selectGroup', g.id)"
        @remove="$emit('clearGroupAnswer', g.id)"
        class="group-chip"
      >
        <q-avatar :style="{ backgroundColor: g.color }" size="24px" />
        {{ g.name }}

        <!-- Status indicators -->
        <q-icon
          v-if="isGraded && getGroupResultIcon(g.id)"
          :name="getGroupResultIcon(g.id)"
          :color="getGroupResultColor(g.id)"
          size="16px"
          class="q-ml-xs"
        />
        <q-spinner
          v-else-if="!isGraded && activeGroupId === g.id"
          type="clock"
          color="white"
          size="16px"
          class="q-ml-xs"
        />
        <q-icon
          v-else-if="!isGraded && isAnswered(g.id)"
          name="check"
          color="positive"
          size="16px"
          class="q-ml-xs"
        />
        <q-icon
          v-else-if="!isGraded && !isAnswered(g.id) && isInteractive"
          name="radio_button_unchecked"
          color="grey-4"
          size="16px"
          class="q-ml-xs"
        />
      </q-chip>
    </div>
  </div>
</template>

<style scoped>
.group-selector {
  user-select: none;
}
.group-chip {
  transition: all 0.2s ease;
  font-weight: 500;
}
.group-chip:hover {
  transform: translateY(-1px);
}
</style>
