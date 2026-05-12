<script setup>
const props = defineProps({
  group: { type: Object, required: true },
  isGraded: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: true }
});

const emit = defineEmits(['updateName', 'updateScore', 'updateColor', 'remove']);
</script>

<template>
  <q-item class="group-card-item">
    <q-item-section avatar>
      <q-avatar :style="{ backgroundColor: group.color }" size="32px" />
    </q-item-section>

    <q-item-section>
      <q-input
        :model-value="group.name"
        @update:model-value="$emit('updateName', $event)"
        dense
        borderless
        class="name-input"
        input-class="text-weight-medium"
      />
    </q-item-section>

    <q-item-section side>
      <div class="row items-center q-gutter-sm">
        <q-input
          :model-value="group.score"
          @update:model-value="$emit('updateScore', Number($event) || 0)"
          type="number"
          dense
          outlined
          style="width: 80px"
          class="score-input"
        />

        <q-btn
          flat
          round
          dense
          icon="colorize"
          :style="{ color: group.color }"
        >
          <q-menu anchor="bottom left" self="top left">
            <q-color
              :model-value="group.color"
              @update:model-value="$emit('updateColor', $event)"
              default-view="palette"
              no-header
              no-footer
            />
          </q-menu>
          <q-tooltip>Change color</q-tooltip>
        </q-btn>

        <q-btn
          v-if="canDelete"
          flat
          round
          dense
          icon="delete"
          color="negative"
          @click="$emit('remove')"
        >
          <q-tooltip>Remove group</q-tooltip>
        </q-btn>
      </div>
    </q-item-section>
  </q-item>
</template>

<style scoped>
.group-card-item {
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  margin-bottom: 8px;
  transition: all 0.2s ease;
}
.group-card-item:hover {
  border-color: #cbd5e1;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.name-input :deep(.q-field__control) {
  padding: 0;
}
.score-input :deep(.q-field__control) {
  padding: 0 8px;
}
</style>
