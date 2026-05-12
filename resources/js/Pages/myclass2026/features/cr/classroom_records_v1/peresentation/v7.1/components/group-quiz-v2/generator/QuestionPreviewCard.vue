<script setup>
import EditableMath from '../../EditableMath.vue';

const props = defineProps({
  question: { type: Object, required: true },
  index: { type: Number, default: 0 }
});

const emit = defineEmits(['update', 'remove']);

const optionLetters = ['A', 'B', 'C', 'D', 'E'];
</script>

<template>
  <q-card flat bordered class="preview-card q-mb-sm">
    <q-card-section class="q-pa-sm">
      <div class="row items-center q-mb-xs">
        <q-badge color="primary" class="q-mr-sm">Q{{ index + 1 }}</q-badge>
        <div class="col text-subtitle2 text-weight-medium ellipsis">
          <EditableMath :content="question.question" :isEditMode="false" />
        </div>
        <q-btn flat round dense icon="delete" color="negative" size="sm" @click="$emit('remove')" />
      </div>

      <div class="options-list q-gutter-y-xs">
        <div
          v-for="(opt, oIdx) in question.options"
          :key="oIdx"
          class="option-row row items-center"
          :class="{ 'is-correct': question.answer && (question.answer.includes(opt) || opt.includes(question.answer)) }"
        >
          <q-avatar size="24px" color="grey-3" text-color="grey-8" class="q-mr-sm text-weight-bold">
            {{ optionLetters[oIdx] || oIdx + 1 }}
          </q-avatar>
          <div class="col">
            <EditableMath :content="opt" :isEditMode="false" />
          </div>
          <q-icon
            v-if="question.answer && (question.answer.includes(opt) || opt.includes(question.answer))"
            name="check_circle"
            color="positive"
            size="18px"
          />
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<style scoped>
.preview-card {
  background: #f8fafc;
  transition: all 0.2s ease;
}
.preview-card:hover {
  border-color: #cbd5e1;
}
.option-row {
  padding: 4px 8px;
  border-radius: 6px;
  background: #fff;
  border: 1px solid transparent;
}
.option-row.is-correct {
  border-color: #22c55e;
  background: #f0fdf4;
}
</style>
