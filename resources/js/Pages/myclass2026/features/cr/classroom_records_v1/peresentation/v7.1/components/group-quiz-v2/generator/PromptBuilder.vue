<script setup>
const props = defineProps({
  topic: { type: String, default: '' },
  qCount: { type: Number, default: 3 },
  difficulty: { type: String, default: 'Medium' },
  extraInfo: { type: String, default: '' }
});

const emit = defineEmits(['update:topic', 'update:qCount', 'update:difficulty', 'update:extraInfo', 'copyPrompt', 'generateEmpty']);

const difficulties = ['Easy', 'Medium', 'Hard', 'Expert'];
</script>

<template>
  <div class="prompt-builder q-gutter-y-md">
    <q-input
      :model-value="topic"
      @update:model-value="$emit('update:topic', $event)"
      label="Lesson Topic"
      placeholder="e.g. History, Advanced Calculus..."
      outlined
      dense
    />

    <div class="row q-col-gutter-md">
      <div class="col-12 col-sm-6">
        <q-slider
          :model-value="qCount"
          @update:model-value="$emit('update:qCount', $event)"
          :min="1"
          :max="10"
          :step="1"
          label
          label-always
          color="primary"
        />
        <div class="text-caption text-grey-6 text-center">Number of Questions: {{ qCount }}</div>
      </div>

      <div class="col-12 col-sm-6">
        <q-btn-toggle
          :model-value="difficulty"
          @update:model-value="$emit('update:difficulty', $event)"
          :options="difficulties.map(d => ({ label: d, value: d }))"
          spread
          no-caps
          toggle-color="primary"
          color="grey-3"
          text-color="grey-8"
          class="difficulty-toggle"
        />
      </div>
    </div>

    <q-input
      :model-value="extraInfo"
      @update:model-value="$emit('update:extraInfo', $event)"
      type="textarea"
      label="Additional Instructions (Optional)"
      placeholder="e.g. Focus purely on algebraic fractions."
      outlined
      dense
      rows="2"
    />

    <div class="row q-gutter-sm q-pt-sm">
      <q-btn
        color="primary"
        icon="content_copy"
        label="Generate & Copy Prompt"
        no-caps
        unelevated
        @click="$emit('copyPrompt')"
      />
      <q-btn
        color="grey-6"
        icon="add"
        label="Generate Empty Questions"
        no-caps
        flat
        @click="$emit('generateEmpty')"
      />
    </div>
  </div>
</template>

<style scoped>
.difficulty-toggle :deep(.q-btn) {
  font-size: 0.8rem;
}
</style>
