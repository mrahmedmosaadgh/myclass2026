<script setup>
const props = defineProps({
  isInteractive: { type: Boolean, default: false },
  isGraded: { type: Boolean, default: false },
  isPracticeMode: { type: Boolean, default: false },
  hasAnswers: { type: Boolean, default: false },
  isMuted: { type: Boolean, default: false }
});

const emit = defineEmits([
  'start', 'lock', 'grade', 'practice', 'replay', 'shuffle', 'toggleMute'
]);
</script>

<template>
  <div class="quiz-control-bar row items-center q-gutter-sm">
    <!-- Pre-start state -->
    <template v-if="!isInteractive && !isGraded">
      <q-btn
        color="primary"
        icon="play_arrow"
        label="Unlock"
        no-caps
        unelevated
        @click="$emit('start')"
      >
        <q-tooltip>Start group quiz</q-tooltip>
      </q-btn>

      <q-btn
        color="grey-6"
        icon="shuffle"
        label="Shuffle"
        no-caps
        flat
        @click="$emit('shuffle')"
      />

      <q-btn
        color="amber-8"
        icon="school"
        label="Practice"
        no-caps
        flat
        @click="$emit('practice')"
      />
    </template>

    <!-- Active state -->
    <template v-else-if="isInteractive && !isGraded">
      <q-badge color="positive" class="q-px-md q-py-xs text-subtitle2">
        <q-icon name="lock_open" size="16px" class="q-mr-xs" />
        {{ isPracticeMode ? 'Practice Mode' : 'Unlocked' }}
      </q-badge>

      <div class="col" />

      <q-btn
        color="positive"
        icon="check_circle"
        label="Grade"
        no-caps
        unelevated
        :disable="!hasAnswers"
        @click="$emit('grade')"
      >
        <q-tooltip>Grade all submitted answers</q-tooltip>
      </q-btn>

      <q-btn
        color="negative"
        icon="lock"
        label="Lock"
        no-caps
        flat
        @click="$emit('lock')"
      />
    </template>

    <!-- Graded state -->
    <template v-else-if="isGraded">
      <q-badge color="grey-7" class="q-px-md q-py-xs text-subtitle2">
        <q-icon name="archive" size="16px" class="q-mr-xs" />
        Archived
      </q-badge>

      <div class="col" />

      <q-btn
        color="primary"
        icon="replay"
        label="Replay"
        no-caps
        unelevated
        @click="$emit('replay')"
      />
    </template>

    <q-separator vertical class="q-mx-sm" />

    <!-- Mute toggle -->
    <q-btn
      flat
      round
      dense
      :icon="isMuted ? 'volume_off' : 'volume_up'"
      :color="isMuted ? 'grey-5' : 'grey-7'"
      @click="$emit('toggleMute')"
    >
      <q-tooltip>{{ isMuted ? 'Unmute sounds' : 'Mute sounds' }}</q-tooltip>
    </q-btn>
  </div>
</template>

<style scoped>
.quiz-control-bar {
  min-height: 48px;
  user-select: none;
}
</style>
