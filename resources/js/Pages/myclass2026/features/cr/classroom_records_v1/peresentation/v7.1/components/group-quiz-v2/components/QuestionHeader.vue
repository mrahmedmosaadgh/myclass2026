<script setup>
import EditableMath from '../../EditableMath.vue';

const props = defineProps({
  question: { type: String, default: '' },
  isEditMode: { type: Boolean, default: false }
});

const emit = defineEmits(['update', 'select']);
</script>

<template>
  <div class="question-header q-mb-md" @mousedown.stop="$emit('select')" @touchstart.stop="$emit('select')">
    <q-card flat bordered class="question-card">
      <q-card-section class="q-py-sm">
        <div class="text-subtitle2 text-grey-7 q-mb-xs">Question</div>
        <EditableMath
          :content="question"
          :isEditMode="isEditMode"
          @update="$emit('update', $event)"
          @select="$emit('select')"
        />
      </q-card-section>
    </q-card>
  </div>
</template>

<style scoped>
.question-header {
  user-select: none;
}
.question-card {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}
</style>
