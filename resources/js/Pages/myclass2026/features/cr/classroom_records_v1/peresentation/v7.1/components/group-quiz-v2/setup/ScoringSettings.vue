<script setup>
const props = defineProps({
  settings: { type: Object, required: true }
});

const emit = defineEmits(['update']);

function update(key, value) {
  emit('update', { ...props.settings, [key]: value });
}
</script>

<template>
  <q-card flat bordered class="scoring-settings q-pa-md">
    <div class="text-subtitle2 text-weight-bold text-grey-8 q-mb-md">
      <q-icon name="sports_score" size="20px" class="q-mr-xs" />
      Scoring Rules
    </div>

    <div class="row q-col-gutter-md">
      <div class="col-12 col-sm-6">
        <q-input
          :model-value="settings.correctPoints"
          @update:model-value="update('correctPoints', Number($event) || 0)"
          type="number"
          label="Points for Correct Answer"
          outlined
          dense
          min="0"
        >
          <template #prepend>
            <q-icon name="add_circle" color="positive" />
          </template>
        </q-input>
      </div>

      <div class="col-12 col-sm-6">
        <q-input
          :model-value="settings.wrongPoints"
          @update:model-value="update('wrongPoints', Number($event) || 0)"
          type="number"
          label="Penalty for Wrong Answer"
          outlined
          dense
          max="0"
          :disable="!settings.allowNegativeScore"
        >
          <template #prepend>
            <q-icon name="remove_circle" color="negative" />
          </template>
        </q-input>
      </div>
    </div>

    <q-toggle
      :model-value="settings.allowNegativeScore"
      @update:model-value="update('allowNegativeScore', $event)"
      label="Enable negative scoring (deduct points for wrong answers)"
      color="negative"
      class="q-mt-md"
    />

    <q-banner rounded class="bg-info-1 q-mt-md text-info">
      <template #avatar>
        <q-icon name="info" color="info" />
      </template>
      <div class="text-caption">
        <strong>Correct:</strong> +{{ settings.correctPoints }} pts
        <span v-if="settings.allowNegativeScore">
          &nbsp;|&nbsp; <strong>Wrong:</strong> {{ settings.wrongPoints || -5 }} pts
        </span>
        <span v-else>
          &nbsp;|&nbsp; <strong>Wrong:</strong> 0 pts (no penalty)
        </span>
      </div>
    </q-banner>
  </q-card>
</template>

<style scoped>
.scoring-settings {
  background: #f8fafc;
}
.bg-info-1 {
  background: #eff6ff;
}
</style>
